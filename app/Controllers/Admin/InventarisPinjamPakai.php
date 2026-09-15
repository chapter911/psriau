<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisPinjamPakaiModel;
use App\Models\InventarisSatkerModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisPinjamPakai extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/pinjam-pakai';

    private function ensureNoSuratNullable(): void
    {
        try {
            $db = db_connect();
            if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
                $fields = $db->getFieldData('trn_inventaris_pinjam_pakai');
                foreach ($fields as $field) {
                    if ($field->name === 'no_surat' && empty($field->nullable)) {
                        $db->query("ALTER TABLE trn_inventaris_pinjam_pakai MODIFY COLUMN no_surat VARCHAR(100) NULL DEFAULT NULL");
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if DB user lacks alter permission
        }
    }

    private function ensureKopSuratIdColumn(): void
    {
        try {
            $db = db_connect();
            if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
                if (! $db->fieldExists('kop_surat_id', 'trn_inventaris_pinjam_pakai')) {
                    $db->query("ALTER TABLE trn_inventaris_pinjam_pakai ADD COLUMN kop_surat_id INT UNSIGNED NULL AFTER no_surat");
                }
            }
        } catch (\Throwable $e) {
            // Ignore if DB user lacks alter permission
        }
    }

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $this->ensureNoSuratNullable();
        $this->ensureKopSuratIdColumn();

        $pinjamModel = new InventarisPinjamPakaiModel();
        $db = db_connect();

        $filterStatus = trim((string) ($this->request->getGet('status') ?? ''));
        if (! in_array($filterStatus, ['dipinjam', 'dikembalikan'], true)) {
            $filterStatus = null;
        }

        $pinjamList = $pinjamModel->getPinjamWithRelations($filterStatus);
        $summary = $pinjamModel->getSummaryStats();
        $availableAssets = $pinjamModel->getAvailableAssetsForLoan();

        // Ambil daftar pegawai aktif dari master pegawai
        $pegawaiList = [];
        if ($db->tableExists('mst_pegawai')) {
            $builder = $db->table('mst_pegawai p')
                ->select('p.id, p.nama, p.nip, p.email, p.jenis_pegawai, ju.jabatan AS jabatan')
                ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left');
            if ($db->fieldExists('is_active', 'mst_pegawai')) {
                $builder->where('p.is_active', 1);
            }
            $pegawaiList = $builder->orderBy('p.nama', 'ASC')->get()->getResultArray();
        }

        // Ambil daftar Kop Surat (prioritas pengaturan dokumen BMN)
        $kopSuratList = [];
        if ($db->tableExists('cfg_inventaris_kop_surat')) {
            $kopSuratList = $db->table('cfg_inventaris_kop_surat')
                ->select('id, nama_kop AS nama, berlaku_dari, berlaku_sampai, is_active')
                ->orderBy('is_active', 'DESC')
                ->orderBy('berlaku_dari', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        } elseif ($db->tableExists('kop_surat')) {
            $kopSuratList = $db->table('kop_surat')
                ->select('id, title AS nama, is_active')
                ->orderBy('is_active', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        $nextNoSurat = $pinjamModel->generateNextNoSurat((int) date('Y'));

        return view('admin/inventaris/pinjam_pakai/index', [
            'pageTitle'        => 'Pinjam Pakai Aset BMN',
            'pinjamList'       => $pinjamList,
            'summary'          => $summary,
            'stats'            => $summary,
            'availableAssets'  => $availableAssets,
            'pegawaiList'      => $pegawaiList,
            'kopSuratList'     => $kopSuratList,
            'nextNoSurat'      => $nextNoSurat,
            'tahunIni'         => (int) date('Y'),
            'currentFilter'    => $filterStatus,
            'can_add'          => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'         => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'       => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'       => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    public function createPinjam()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Anda tidak memiliki hak akses untuk menambah pinjam pakai.');
        }

        $rules = [
            'nama_peminjam'   => 'required|max_length[150]',
            'no_surat'        => 'permit_empty|max_length[100]',
            'kop_surat_id'    => 'permit_empty|is_natural_no_zero',
            'tgl_pinjam'      => 'required|valid_date',
            'keperluan'       => 'required',
            'kondisi_pinjam'  => 'permit_empty|in_list[baik,rusak_ringan,rusak_berat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Mohon lengkapi seluruh field wajib dengan benar.');
        }

        // Mendukung pemilihan multi-aset (array) atau single aset (fallback)
        $rawIds = $this->request->getPost('inventaris_ids');
        if (empty($rawIds)) {
            $singleId = $this->request->getPost('inventaris_id');
            if (! empty($singleId)) {
                $rawIds = [$singleId];
            }
        }

        $inventarisIds = [];
        if (is_array($rawIds)) {
            foreach ($rawIds as $rid) {
                $rid = (int) $rid;
                if ($rid > 0) {
                    $inventarisIds[] = $rid;
                }
            }
        }
        $inventarisIds = array_unique($inventarisIds);

        if (empty($inventarisIds)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Pilih minimal satu aset BMN yang akan dipinjamkan.');
        }

        $satkerModel = new InventarisSatkerModel();
        $assets = $satkerModel->whereIn('id', $inventarisIds)->findAll();

        if (empty($assets) || count($assets) !== count($inventarisIds)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Salah satu atau beberapa aset BMN yang dipilih tidak ditemukan.');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $db = db_connect();

        // Periksa apakah ada aset terpilih yang sedang dipinjam aktif
        $activeLoans = [];
        if ($db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $activeRows = $db->table('trn_inventaris_pinjam_pakai_item itm')
                ->select('itm.inventaris_id, p.nama_peminjam')
                ->join('trn_inventaris_pinjam_pakai p', 'p.id = itm.pinjam_pakai_id', 'left')
                ->whereIn('itm.inventaris_id', $inventarisIds)
                ->where('itm.status', 'dipinjam')
                ->get()
                ->getResultArray();
            if (! empty($activeRows)) {
                $activeLoans = $activeRows;
            }
        }
        if (empty($activeLoans)) {
            $activeParent = $pinjamModel->whereIn('inventaris_id', $inventarisIds)->where('status', 'dipinjam')->findAll();
            if (! empty($activeParent)) {
                $activeLoans = $activeParent;
            }
        }

        if (! empty($activeLoans)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Salah satu atau beberapa aset yang dipilih sedang dalam status dipinjam oleh pegawai lain.');
        }

        $pegawaiId = (int) $this->request->getPost('pegawai_id') ?: null;
        $namaPeminjam = trim((string) $this->request->getPost('nama_peminjam'));
        $nipPeminjam = trim((string) $this->request->getPost('nip_peminjam')) ?: null;
        $jabatanPeminjam = trim((string) $this->request->getPost('jabatan_peminjam')) ?: null;
        $kontakPeminjam = trim((string) $this->request->getPost('kontak_peminjam')) ?: null;
        $kopSuratId = (int) $this->request->getPost('kop_surat_id') ?: null;

        // Auto-fill dari master pegawai jika ID dipilih
        if ($pegawaiId !== null && $pegawaiId > 0) {
            if ($db->tableExists('mst_pegawai')) {
                $peg = $db->table('mst_pegawai p')
                    ->select('p.id, p.nama, p.nip, p.jenis_pegawai, ju.jabatan AS jabatan')
                    ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left')
                    ->where('p.id', $pegawaiId)
                    ->get()
                    ->getRowArray();
                if (is_array($peg)) {
                    $namaPeminjam = $namaPeminjam ?: ($peg['nama'] ?? '');
                    $isKonsultanPeg = (strtolower(trim((string) ($peg['jenis_pegawai'] ?? ''))) === 'konsultan');
                    if ($isKonsultanPeg) {
                        if (empty($nipPeminjam) || preg_match('/^NIP[A-Z]+$/i', (string) $nipPeminjam)) {
                            $nipPeminjam = 'Tenaga Penunjang Kegiatan';
                        }
                    } else {
                        $nipPeminjam = $nipPeminjam ?: ($peg['nip'] ?? null);
                    }
                    $jabatanPeminjam = $jabatanPeminjam ?: ($peg['jabatan'] ?? null);
                }
            }
        }

        // Handle file upload scan surat pinjam (PDF)
        $fileSuratPath = null;
        $file = $this->request->getFile('file_surat');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = strtolower($file->getClientExtension());
            if ($ext !== 'pdf') {
                return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'File scan surat pinjam harus berformat PDF.');
            }

            if ($file->getSizeByUnit('mb') > 10) {
                return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Ukuran file surat pinjam maksimal 10 MB.');
            }

            $uploadDir = FCPATH . 'uploads/inventaris/surat_pinjam';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = 'SPP_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $file->move($uploadDir, $newName);
            $fileSuratPath = 'uploads/inventaris/surat_pinjam/' . $newName;
        }

        $this->ensureNoSuratNullable();
        $this->ensureKopSuratIdColumn();

        $userId = (int) (session()->get('userId') ?? 0);
        $tglPinjamInput = trim((string) $this->request->getPost('tgl_pinjam'));
        $tahunPinjam = ! empty($tglPinjamInput) ? (int) date('Y', strtotime($tglPinjamInput)) : (int) date('Y');
        $rawNoSurat = trim((string) $this->request->getPost('no_surat'));

        // Aturan nomor surat:
        // Tahun 2025 (atau <= 2025): boleh tidak menggunakan nomor surat (opsional, boleh isi manual jika ada)
        // Tahun 2026 ke atas: OTOMATIS & TERKUNCI (selalu di-generate otomatis dengan format PS.03.01/B/Gs7/{tahun}/{nomor}, tidak bisa diedit/di-override manual)
        if ($tahunPinjam >= 2026) {
            $rawNoSurat = $pinjamModel->generateNextNoSurat($tahunPinjam);
        } else {
            if ($rawNoSurat === '') {
                $rawNoSurat = null;
            }
        }

        $isNullable = true;
        try {
            $fieldData = $db->getFieldData('trn_inventaris_pinjam_pakai');
            foreach ($fieldData as $f) {
                if ($f->name === 'no_surat') {
                    $isNullable = ! empty($f->nullable);
                    break;
                }
            }
        } catch (\Throwable $e) {}

        $noSuratVal = ($rawNoSurat !== null && $rawNoSurat !== '') ? $rawNoSurat : ($isNullable ? null : '');

        // Simpan header transaksi pinjam pakai
        $pinjamId = $pinjamModel->insert([
            'inventaris_id'        => $assets[0]['id'], // Simpan aset pertama untuk backward compatibility
            'pegawai_id'           => $pegawaiId,
            'nama_peminjam'        => $namaPeminjam,
            'nip_peminjam'         => $nipPeminjam,
            'jabatan_peminjam'     => $jabatanPeminjam,
            'kontak_peminjam'      => $kontakPeminjam,
            'no_surat'             => $noSuratVal,
            'kop_surat_id'         => $kopSuratId,
            'tgl_pinjam'           => $tglPinjamInput,
            'tgl_kembali_rencana'  => trim((string) $this->request->getPost('tgl_kembali_rencana')) ?: null,
            'keperluan'            => trim((string) $this->request->getPost('keperluan')),
            'kondisi_pinjam'       => trim((string) $this->request->getPost('kondisi_pinjam')) ?: 'baik',
            'kelengkapan'          => trim((string) $this->request->getPost('kelengkapan')) ?: null,
            'catatan'              => trim((string) $this->request->getPost('catatan')) ?: null,
            'file_surat'           => $fileSuratPath,
            'status'               => 'dipinjam',
            'created_by'           => $userId ?: null,
            'updated_by'           => $userId ?: null,
        ]);

        if ($pinjamId) {
            $kondisiPinjam = trim((string) $this->request->getPost('kondisi_pinjam')) ?: 'baik';
            $now = date('Y-m-d H:i:s');
            $itemRows = [];
            foreach ($assets as $ast) {
                $itemRows[] = [
                    'pinjam_pakai_id' => $pinjamId,
                    'inventaris_id'   => $ast['id'],
                    'kondisi_pinjam'  => $kondisiPinjam,
                    'kondisi_kembali' => null,
                    'catatan'         => null,
                    'status'          => 'dipinjam',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if ($db->tableExists('trn_inventaris_pinjam_pakai_item') && ! empty($itemRows)) {
                $db->table('trn_inventaris_pinjam_pakai_item')->insertBatch($itemRows);
            }

            // Update status seluruh aset terpilih menjadi 'Dipinjam Pakai'
            $satkerModel->whereIn('id', $inventarisIds)->set([
                'status_bmn'     => 'Dipinjam Pakai',
                'lokasi_ruangan' => 'Pinjam Pakai: ' . $namaPeminjam,
                'updated_by'     => $userId ?: null,
            ])->update();

            $cntAset = count($assets);
            $asetSummary = implode(', ', array_map(function($a) {
                return $a['nama_barang'] . ' (NUP ' . $a['nup'] . ')';
            }, array_slice($assets, 0, 2)));
            if ($cntAset > 2) {
                $asetSummary .= ' dan ' . ($cntAset - 2) . ' barang lainnya';
            }

            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', "Sebanyak {$cntAset} unit aset BMN ({$asetSummary}) berhasil dicatat dipinjam pakai oleh {$namaPeminjam}.");
        }

        return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Gagal menyimpan transaksi pinjam pakai.');
    }

    public function editPinjam(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Anda tidak memiliki hak akses untuk mengubah data pinjam pakai.');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $existing = $pinjamModel->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Data pinjam pakai tidak ditemukan.');
        }

        $rules = [
            'nama_peminjam'  => 'required|max_length[150]',
            'no_surat'       => 'permit_empty|max_length[100]',
            'kop_surat_id'   => 'permit_empty|is_natural_no_zero',
            'tgl_pinjam'     => 'required|valid_date',
            'keperluan'      => 'required',
            'kondisi_pinjam' => 'permit_empty|in_list[baik,rusak_ringan,rusak_berat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Mohon lengkapi seluruh field wajib dengan benar.');
        }

        $pegawaiId = (int) $this->request->getPost('pegawai_id') ?: null;
        $namaPeminjam = trim((string) $this->request->getPost('nama_peminjam'));
        $nipPeminjam = trim((string) $this->request->getPost('nip_peminjam')) ?: null;
        $jabatanPeminjam = trim((string) $this->request->getPost('jabatan_peminjam')) ?: null;
        $kontakPeminjam = trim((string) $this->request->getPost('kontak_peminjam')) ?: null;
        $kopSuratId = (int) $this->request->getPost('kop_surat_id') ?: null;

        // Otomatisasi NIP untuk Konsultan menjadi "Tenaga Penunjang Kegiatan"
        if ($pegawaiId !== null && $pegawaiId > 0) {
            $db = db_connect();
            if ($db->tableExists('mst_pegawai')) {
                $peg = $db->table('mst_pegawai p')
                    ->select('p.id, p.nama, p.nip, p.jenis_pegawai, ju.jabatan AS jabatan')
                    ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left')
                    ->where('p.id', $pegawaiId)
                    ->get()
                    ->getRowArray();
                if (is_array($peg)) {
                    $isKonsultanPeg = (strtolower(trim((string) ($peg['jenis_pegawai'] ?? ''))) === 'konsultan');
                    if ($isKonsultanPeg && (empty($nipPeminjam) || preg_match('/^NIP[A-Z]+$/i', (string) $nipPeminjam))) {
                        $nipPeminjam = 'Tenaga Penunjang Kegiatan';
                    }
                }
            }
        }

        $fileSuratPath = $existing['file_surat'];
        $file = $this->request->getFile('file_surat');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = strtolower($file->getClientExtension());
            if ($ext !== 'pdf') {
                return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'File scan surat pinjam harus berformat PDF.');
            }

            $uploadDir = FCPATH . 'uploads/inventaris/surat_pinjam';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Hapus file lama jika ada
            if ($fileSuratPath && file_exists(FCPATH . $fileSuratPath)) {
                @unlink(FCPATH . $fileSuratPath);
            }

            $newName = 'SPP_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $file->move($uploadDir, $newName);
            $fileSuratPath = 'uploads/inventaris/surat_pinjam/' . $newName;
        }

        $this->ensureNoSuratNullable();
        $this->ensureKopSuratIdColumn();

        $userId = (int) (session()->get('userId') ?? 0);
        $tglPinjamEdit = trim((string) $this->request->getPost('tgl_pinjam'));
        $tahunPinjamEdit = ! empty($tglPinjamEdit) ? (int) date('Y', strtotime($tglPinjamEdit)) : (int) date('Y');
        $rawNoSuratEdit = trim((string) $this->request->getPost('no_surat'));

        // Aturan nomor surat pada Edit:
        // Tahun 2026 ke atas: nomor surat OTOMATIS & TERKUNCI (tidak bisa diedit lagi).
        // Pertahankan nomor yang sudah terbit pada tahun tersebut, atau terbitkan otomatis jika belum ada nomor.
        // Tahun 2025 ke bawah: boleh tidak menggunakan nomor surat (opsional), atau diedit bebas jika diperlukan.
        if ($tahunPinjamEdit >= 2026) {
            $existingYear = ! empty($existing['tgl_pinjam']) ? (int) date('Y', strtotime($existing['tgl_pinjam'])) : 0;
            if (! empty($existing['no_surat']) && $existingYear === $tahunPinjamEdit) {
                // Kunci nomor surat resmi yang sudah terbit (abaikan input POST / tidak bisa diubah)
                $rawNoSuratEdit = $existing['no_surat'];
            } else {
                // Jika belum ada nomor surat atau tahun diubah ke tahun 2026+, generate nomor otomatis baru
                $rawNoSuratEdit = $pinjamModel->generateNextNoSurat($tahunPinjamEdit);
            }
        } else {
            if ($rawNoSuratEdit === '') {
                $rawNoSuratEdit = null;
            }
        }

        $isNullable = true;
        try {
            $db = db_connect();
            $fieldData = $db->getFieldData('trn_inventaris_pinjam_pakai');
            foreach ($fieldData as $f) {
                if ($f->name === 'no_surat') {
                    $isNullable = ! empty($f->nullable);
                    break;
                }
            }
        } catch (\Throwable $e) {}

        $noSuratEdit = ($rawNoSuratEdit !== null && $rawNoSuratEdit !== '') ? $rawNoSuratEdit : ($isNullable ? null : '');

        $pinjamModel->update($id, [
            'pegawai_id'          => $pegawaiId,
            'nama_peminjam'       => $namaPeminjam,
            'nip_peminjam'        => $nipPeminjam,
            'jabatan_peminjam'    => $jabatanPeminjam,
            'kontak_peminjam'     => $kontakPeminjam,
            'no_surat'            => $noSuratEdit,
            'kop_surat_id'        => $kopSuratId,
            'tgl_pinjam'          => $tglPinjamEdit,
            'tgl_kembali_rencana' => trim((string) $this->request->getPost('tgl_kembali_rencana')) ?: null,
            'keperluan'           => trim((string) $this->request->getPost('keperluan')),
            'kondisi_pinjam'      => trim((string) $this->request->getPost('kondisi_pinjam')) ?: 'baik',
            'kelengkapan'         => trim((string) $this->request->getPost('kelengkapan')) ?: null,
            'catatan'             => trim((string) $this->request->getPost('catatan')) ?: null,
            'file_surat'          => $fileSuratPath,
            'updated_by'          => $userId ?: null,
        ]);

        // Jika statusnya masih dipinjam, sinkronkan nama peminjam ke lokasi_ruangan seluruh aset terkait
        if ($existing['status'] === 'dipinjam') {
            $items = $pinjamModel->getItemsByPinjamId($id);
            $itemIds = array_column($items, 'inventaris_id');
            if (empty($itemIds) && ! empty($existing['inventaris_id'])) {
                $itemIds = [(int) $existing['inventaris_id']];
            }
            if (! empty($itemIds)) {
                $satkerModel = new InventarisSatkerModel();
                $satkerModel->whereIn('id', $itemIds)->set([
                    'lokasi_ruangan' => 'Pinjam Pakai: ' . $namaPeminjam,
                    'updated_by'     => $userId ?: null,
                ])->update();
            }
        }

        return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', 'Data pinjam pakai berhasil diperbarui.');
    }

    public function kembalikanAset(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Anda tidak memiliki hak akses untuk memproses pengembalian aset.');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $existing = $pinjamModel->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Data pinjam pakai tidak ditemukan.');
        }

        $rules = [
            'tgl_kembali_realisasi' => 'required|valid_date',
            'kondisi_kembali'       => 'required|in_list[baik,rusak_ringan,rusak_berat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Tanggal pengembalian dan kondisi fisik wajib diisi.');
        }

        $kondisiKembali = trim((string) $this->request->getPost('kondisi_kembali'));
        $catatanKembali = trim((string) $this->request->getPost('catatan_kembali'));
        $userId = (int) (session()->get('userId') ?? 0);

        // Update record pinjam pakai
        $catatanLengkap = $existing['catatan'] ?: '';
        if ($catatanKembali !== '') {
            $catatanLengkap = ($catatanLengkap !== '' ? $catatanLengkap . " | Pengembalian: " : "Pengembalian: ") . $catatanKembali;
        }

        $pinjamModel->update($id, [
            'status'                => 'dikembalikan',
            'tgl_kembali_realisasi' => trim((string) $this->request->getPost('tgl_kembali_realisasi')),
            'kondisi_kembali'       => $kondisiKembali,
            'catatan'               => $catatanLengkap ?: null,
            'updated_by'            => $userId ?: null,
        ]);

        // Ambil seluruh child items
        $items = $pinjamModel->getItemsByPinjamId($id);
        $itemIds = array_column($items, 'inventaris_id');
        if (empty($itemIds) && ! empty($existing['inventaris_id'])) {
            $itemIds = [(int) $existing['inventaris_id']];
        }

        $db = db_connect();
        // Update status seluruh child items menjadi 'dikembalikan'
        if ($db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $db->table('trn_inventaris_pinjam_pakai_item')
                ->where('pinjam_pakai_id', $id)
                ->update([
                    'status'          => 'dikembalikan',
                    'kondisi_kembali' => $kondisiKembali,
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
        }

        // Pulihkan status seluruh aset di tabel trn_inventaris_satker
        if (! empty($itemIds)) {
            $satkerModel = new InventarisSatkerModel();
            $satkerModel->whereIn('id', $itemIds)->set([
                'status_bmn'     => 'Digunakan Sendiri',
                'kondisi'        => $kondisiKembali,
                'lokasi_ruangan' => 'Gudang / Belum Berlokasi',
                'ruangan_id'     => null,
                'updated_by'     => $userId ?: null,
            ])->update();
        }

        $cntAset = count($itemIds);
        return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', "Sebanyak {$cntAset} unit aset berhasil dikembalikan oleh {$existing['nama_peminjam']} dan status barang telah aktif kembali.");
    }

    public function deletePinjam(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['delete'] ?? false)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Anda tidak memiliki hak akses untuk menghapus data pinjam pakai.');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $existing = $pinjamModel->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Data pinjam pakai tidak ditemukan.');
        }

        $items = $pinjamModel->getItemsByPinjamId($id);
        $itemIds = array_column($items, 'inventaris_id');
        if (empty($itemIds) && ! empty($existing['inventaris_id'])) {
            $itemIds = [(int) $existing['inventaris_id']];
        }

        // Jika aset masih dalam status dipinjam, kembalikan status seluruh aset ke semula
        if ($existing['status'] === 'dipinjam' && ! empty($itemIds)) {
            $satkerModel = new InventarisSatkerModel();
            $satkerModel->whereIn('id', $itemIds)->set([
                'status_bmn'     => 'Digunakan Sendiri',
                'lokasi_ruangan' => 'Belum berlokasi',
            ])->update();
        }

        // Hapus file fisik jika ada
        if (! empty($existing['file_surat']) && file_exists(FCPATH . $existing['file_surat'])) {
            @unlink(FCPATH . $existing['file_surat']);
        }

        $db = db_connect();
        if ($db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $db->table('trn_inventaris_pinjam_pakai_item')->where('pinjam_pakai_id', $id)->delete();
        }

        $pinjamModel->delete($id);

        return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', 'Data transaksi pinjam pakai berhasil dihapus.');
    }

    private function terbilangAngka(int $angka): string
    {
        $bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return $this->terbilangAngka($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            return $this->terbilangAngka((int) ($angka / 10)) . ' Puluh' . ($angka % 10 ? ' ' . $bilangan[$angka % 10] : '');
        } elseif ($angka < 200) {
            return 'Seratus' . ($angka % 100 ? ' ' . $this->terbilangAngka($angka % 100) : '');
        } elseif ($angka < 1000) {
            return $this->terbilangAngka((int) ($angka / 100)) . ' Ratus' . ($angka % 100 ? ' ' . $this->terbilangAngka($angka % 100) : '');
        } elseif ($angka < 2000) {
            return 'Seribu' . ($angka % 1000 ? ' ' . $this->terbilangAngka($angka % 1000) : '');
        } elseif ($angka < 1000000) {
            return $this->terbilangAngka((int) ($angka / 1000)) . ' Ribu' . ($angka % 1000 ? ' ' . $this->terbilangAngka($angka % 1000) : '');
        }
        return (string) $angka;
    }

    private function formatTanggalPerjanjian(?string $tglStr): string
    {
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $ts = strtotime((string) $tglStr) ?: time();
        $dayOfWeek = (int) date('w', $ts);
        $d = (int) date('j', $ts);
        $m = (int) date('n', $ts);
        $y = (int) date('Y', $ts);

        $hariNama = $hari[$dayOfWeek];
        $tglTerbilang = ucfirst(strtolower($this->terbilangAngka($d)));
        $blnNama = $bulan[$m] ?? date('F', $ts);
        $thnTerbilang = ucfirst(strtolower($this->terbilangAngka($y)));

        return "Hari ini {$hariNama} tanggal {$tglTerbilang} bulan {$blnNama} tahun {$thnTerbilang} (" . date('d/m/Y', $ts) . "), kami yang bertandatangan di bawah ini :";
    }

    public function cetakSuratPdf(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $loan = $pinjamModel->getPinjamDetail($id);

        if (! is_array($loan)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Data pinjam pakai tidak ditemukan.');
        }

        $pengaturanModel = new \App\Models\InventarisPengaturanModel();

        // 1. Ambil Kop Surat: Jika ada kop_surat_id spesifik gunakan itu, jika tidak cari berdasarkan tanggal pinjam
        $kopSuratId = ! empty($loan['kop_surat_id']) ? (int) $loan['kop_surat_id'] : null;
        $kopSuratImg = '';

        if (! empty($kopSuratId)) {
            if (function_exists('kop_surat_img_tag')) {
                $kopSuratImg = kop_surat_img_tag('', 'width: 100%; max-height: 105px; object-fit: contain;', 'Kop Surat Instansi', $kopSuratId);
            }
        } else {
            $matchedKop = $pengaturanModel->getKopSuratByDate($loan['tgl_pinjam'] ?? null);
            if ($matchedKop && ! empty($matchedKop['image_url'])) {
                $kopUrl = media_url((string) $matchedKop['image_url']);
                $kopSuratImg = '<img src="' . esc($kopUrl) . '" alt="' . esc($matchedKop['nama_kop'] ?? 'Kop Surat') . '" style="width: 100%; max-height: 105px; object-fit: contain;" />';
            } elseif (function_exists('kop_surat_img_tag')) {
                $kopSuratImg = kop_surat_img_tag('', 'width: 100%; max-height: 105px; object-fit: contain;', 'Kop Surat Instansi');
            }
        }

        // Logo PU Base64 (Fallback jika master kop tidak ada)
        $logoPath = FCPATH . 'assets/img/logo_pupr.png';
        if (! file_exists($logoPath)) {
            $logoPath = FCPATH . 'uploads/branding/1774740768_77e8482499660c14c637.png';
        }
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        // Format tanggal Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tglPinjamParts = explode('-', (string) ($loan['tgl_pinjam'] ?? ''));
        $tglPinjamIndo = count($tglPinjamParts) === 3
            ? ((int) $tglPinjamParts[2]) . ' ' . ($bulanIndo[(int) $tglPinjamParts[1]] ?? '') . ' ' . $tglPinjamParts[0]
            : (string) ($loan['tgl_pinjam'] ?? '');

        $tglCetak = date('j') . ' ' . ($bulanIndo[(int) date('n')] ?? date('F')) . ' ' . date('Y');

        $introText = $this->formatTanggalPerjanjian($loan['tgl_pinjam'] ?? null);
        $tahunPinjam = ! empty($loan['tgl_pinjam']) ? date('Y', strtotime((string) $loan['tgl_pinjam'])) : date('Y');

        // Deteksi apakah peminjam adalah Konsultan / Non-ASN
        $isKonsultan = false;
        $jenisPegawai = strtolower(trim((string) ($loan['pegawai_master_jenis_pegawai'] ?? '')));
        $jabatanPeminjam = strtolower(trim((string) ($loan['jabatan_peminjam'] ?? '')));
        $nipPeminjamRaw = trim((string) ($loan['nip_peminjam'] ?? ''));

        if ($jenisPegawai === 'konsultan') {
            $isKonsultan = true;
        } elseif (empty($jenisPegawai) && ! empty($loan['nama_peminjam'])) {
            $db = db_connect();
            if ($db->tableExists('mst_pegawai')) {
                $matchedPeg = $db->table('mst_pegawai')
                    ->select('jenis_pegawai')
                    ->where('LOWER(nama)', strtolower(trim((string) $loan['nama_peminjam'])))
                    ->get()
                    ->getRowArray();
                if ($matchedPeg && strtolower(trim((string) ($matchedPeg['jenis_pegawai'] ?? ''))) === 'konsultan') {
                    $isKonsultan = true;
                }
            }
        }

        if (
            ! $isKonsultan && (
                stripos($jabatanPeminjam, 'konsultan') !== false ||
                stripos($nipPeminjamRaw, 'konsultan') !== false ||
                stripos($nipPeminjamRaw, 'tenaga penunjang') !== false ||
                preg_match('/^NIP[A-Z]+$/i', $nipPeminjamRaw)
            )
        ) {
            $isKonsultan = true;
        }

        $nipPeminjamDisplay = $isKonsultan
            ? 'Tenaga Penunjang Kegiatan'
            : (! empty($loan['nip_peminjam']) ? $loan['nip_peminjam'] : '-');

        $nipPeminjamTtd = $isKonsultan
            ? 'Tenaga Penunjang Kegiatan'
            : (! empty($loan['nip_peminjam']) ? 'NIP. ' . $loan['nip_peminjam'] : 'NIP. -');

        // 2. Resolusi Pihak Pertama (Cek Benturan Kepentingan: Peminjam == Kasatker)
        $pihakPertama = $pengaturanModel->resolvePihakPertama(
            (string) ($loan['nama_peminjam'] ?? ''),
            (string) ($loan['nip_peminjam'] ?? ''),
            $loan['tgl_pinjam'] ?? null
        );
        $kasatker = $pihakPertama['kasatker_asli'] ?? $pengaturanModel->getKasatkerByDate($loan['tgl_pinjam'] ?? null);
        $delegasi = $pengaturanModel->getDelegasi();

        $data = [
            'loan'               => $loan,
            'isKonsultan'        => $isKonsultan,
            'nipPeminjamDisplay' => $nipPeminjamDisplay,
            'nipPeminjamTtd'     => $nipPeminjamTtd,
            'kopSuratImg'        => $kopSuratImg,
            'logoBase64'         => $logoBase64,
            'introText'          => $introText,
            'tahunPinjam'        => $tahunPinjam,
            'tglPinjamIndo'      => $tglPinjamIndo,
            'tglCetak'           => $tglCetak,
            'namaUakpb'          => 'PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU',
            'kodeUakpb'          => '145060900691285000KP',
            'pihakPertama'       => $pihakPertama,
            'isDelegasi'         => (bool) ($pihakPertama['is_delegasi'] ?? false),
            'kasatker'           => $kasatker,
            'pengurusBarang'     => $delegasi,
        ];

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        // 1. Render Surat Perjanjian (Halaman 1-2: A4 Portrait)
        $htmlPortrait = view('admin/inventaris/pinjam_pakai/surat_pinjam_pdf', $data);
        $dompdfPortrait = new Dompdf($options);
        $dompdfPortrait->loadHtml($htmlPortrait);
        $dompdfPortrait->setPaper('A4', 'portrait');
        $dompdfPortrait->render();
        $pdfPortraitStream = $dompdfPortrait->output();

        // 2. Render Lampiran BMN (Halaman Akhir: A4 Landscape)
        $htmlLandscape = view('admin/inventaris/pinjam_pakai/surat_pinjam_lampiran_pdf', $data);
        $dompdfLandscape = new Dompdf($options);
        $dompdfLandscape->loadHtml($htmlLandscape);
        $dompdfLandscape->setPaper('A4', 'landscape');
        $dompdfLandscape->render();
        $pdfLandscapeStream = $dompdfLandscape->output();

        // Pastikan pustaka FPDF & FPDI ter-load (baik via Composer maupun fallback APPPATH . 'ThirdParty/setasign')
        if (! class_exists('FPDF') && file_exists(APPPATH . 'ThirdParty/setasign/fpdf/fpdf.php')) {
            require_once APPPATH . 'ThirdParty/setasign/fpdf/fpdf.php';
        }
        if (! class_exists('setasign\Fpdi\Fpdi') && file_exists(APPPATH . 'ThirdParty/setasign/fpdi/src/autoload.php')) {
            require_once APPPATH . 'ThirdParty/setasign/fpdi/src/autoload.php';
        }

        // 3. Gabungkan Portrait dan Landscape ke satu dokumen PDF dengan FPDI
        $pdfOutput = '';
        if (class_exists(Fpdi::class) && class_exists(StreamReader::class)) {
            try {
                $fpdi = new Fpdi();
                $fpdi->SetAutoPageBreak(false);

                // Masukkan Halaman Surat (Portrait)
                $pageCountPortrait = $fpdi->setSourceFile(StreamReader::createByString($pdfPortraitStream));
                for ($pageNo = 1; $pageNo <= $pageCountPortrait; $pageNo++) {
                    $tplId = $fpdi->importPage($pageNo);
                    $size  = $fpdi->getTemplateSize($tplId);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tplId);
                }

                // Masukkan Halaman Lampiran (Landscape)
                $pageCountLandscape = $fpdi->setSourceFile(StreamReader::createByString($pdfLandscapeStream));
                for ($pageNo = 1; $pageNo <= $pageCountLandscape; $pageNo++) {
                    $tplId = $fpdi->importPage($pageNo);
                    $size  = $fpdi->getTemplateSize($tplId);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tplId);
                }

                $pdfOutput = $fpdi->Output('S');
            } catch (\Throwable $e) {
                log_message('error', 'Gagal menggabungkan PDF Pinjam Pakai via FPDI: ' . $e->getMessage());
                $pdfOutput = $pdfPortraitStream;
            }
        } else {
            $pdfOutput = $pdfPortraitStream;
        }

        $suratSlug = ! empty($loan['no_surat'])
            ? preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $loan['no_surat'])
            : ('ID_' . $id . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($loan['nama_peminjam'] ?? 'Aset')));
        $filename = 'Surat_Pinjam_Pakai_' . $suratSlug . '.pdf';

        if (ob_get_length()) {
            ob_clean();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdfOutput);
    }

    public function exportExcel()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Anda tidak memiliki hak akses untuk mengekspor data.');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $filterStatus = trim((string) ($this->request->getGet('status') ?? ''));
        if (! in_array($filterStatus, ['dipinjam', 'dikembalikan'], true)) {
            $filterStatus = null;
        }

        $list = $pinjamModel->getPinjamWithRelations($filterStatus);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pinjam Pakai BMN');

        // Header Title
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'REKAPITULASI PINJAM PAKAI BARANG MILIK NEGARA (BMN)');
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA PERMUKIMAN STRATEGIS PROVINSI RIAU');
        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3', 'Status Data: ' . ($filterStatus ? ucfirst($filterStatus) : 'Semua Status') . ' | Diekspor pada: ' . date('d/m/Y H:i') . ' WIB');

        $sheet->getStyle('A1:P2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A1:P3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column Headers
        $headers = [
            'A5' => 'NO',
            'B5' => 'NO. SURAT / BAPP',
            'C5' => 'TGL PINJAM',
            'D5' => 'RENCANA KEMBALI',
            'E5' => 'REALISASI KEMBALI',
            'F5' => 'STATUS',
            'G5' => 'NAMA PEMINJAM',
            'H5' => 'NIP',
            'I5' => 'JABATAN',
            'J5' => 'KODE BARANG',
            'K5' => 'NUP',
            'L5' => 'NAMA BARANG',
            'M5' => 'MERK / TIPE',
            'N5' => 'KONDISI AWAL',
            'O5' => 'NILAI ASET (RP)',
            'P5' => 'KEPERLUAN PINJAM',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col, $title);
        }

        $sheet->getStyle('A5:P5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A5:P5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('A5:P5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(26);

        $rowNum = 6;
        $no = 1;
        $totalNilai = 0;

        foreach ($list as $it) {
            $nilai = (float) ($it['nilai_perolehan'] ?? 0);
            $totalNilai += $nilai;

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValueExplicit('B' . $rowNum, (string) $it['no_surat'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, (string) $it['tgl_pinjam']);
            $sheet->setCellValue('D' . $rowNum, (string) ($it['tgl_kembali_rencana'] ?: '-'));
            $sheet->setCellValue('E' . $rowNum, (string) ($it['tgl_kembali_realisasi'] ?: '-'));
            $sheet->setCellValue('F' . $rowNum, ucfirst((string) $it['status']));
            $sheet->setCellValue('G' . $rowNum, (string) $it['nama_peminjam']);
            $sheet->setCellValueExplicit('H' . $rowNum, (string) ($it['nip_peminjam'] ?: '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I' . $rowNum, (string) ($it['jabatan_peminjam'] ?: '-'));
            $sheet->setCellValueExplicit('J' . $rowNum, (string) $it['kode_barang'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('K' . $rowNum, (string) $it['nup'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L' . $rowNum, (string) $it['nama_barang']);
            $sheet->setCellValue('M' . $rowNum, (string) ($it['merk_tipe'] ?: '-'));
            $sheet->setCellValue('N' . $rowNum, ucfirst((string) $it['kondisi_pinjam']));
            $sheet->setCellValue('O' . $rowNum, $nilai);
            $sheet->setCellValue('P' . $rowNum, (string) $it['keperluan']);

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum . ':F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $rowNum . ':K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('N' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('O' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');

            $rowNum++;
        }

        // Total Row
        $sheet->mergeCells("A{$rowNum}:N{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL NILAI ASET TERDATA');
        $sheet->setCellValue("O{$rowNum}", $totalNilai);
        $sheet->setCellValue("P{$rowNum}", count($list) . ' Transaksi');
        $sheet->getStyle("A{$rowNum}:P{$rowNum}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("O{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("P{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A5:P{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'P') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Rekap_Pinjam_Pakai_BMN_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($roleId === null || $menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit'     => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete'   => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export'   => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import'   => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator') {
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalized === 'super_administrator' || $normalized === 'super-admin' || $normalized === 'superadmin') {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            }

            $row = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', array_values(array_unique($variants)))
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }

    /**
     * Endpoint AJAX untuk mengambil nomor surat pinjam pakai berikutnya
     */
    public function getNextNoSurat()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();
        $nextNo = $pinjamModel->generateNextNoSurat($tahun);

        return $this->response->setJSON([
            'status'      => 'success',
            'tahun'       => $tahun,
            'no_surat'    => $nextNo,
            'is_required' => ($tahun >= 2026),
        ]);
    }
}
