<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisPinjamPakaiModel;
use App\Models\InventarisSatkerModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisPinjamPakai extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/pinjam-pakai';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

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
            $builder = $db->table('mst_pegawai')->select('id, nama, nip, jabatan, no_hp, email');
            if ($db->fieldExists('is_active', 'mst_pegawai')) {
                $builder->where('is_active', 1);
            }
            $pegawaiList = $builder->orderBy('nama', 'ASC')->get()->getResultArray();
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/pinjam_pakai/index', [
            'pageTitle'        => 'Pinjam Pakai Aset BMN',
            'pinjamList'       => $pinjamList,
            'summary'          => $summary,
            'availableAssets'  => $availableAssets,
            'pegawaiList'      => $pegawaiList,
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
            'inventaris_id'   => 'required|is_natural_no_zero',
            'nama_peminjam'   => 'required|max_length[150]',
            'no_surat'        => 'required|max_length[100]',
            'tgl_pinjam'      => 'required|valid_date',
            'keperluan'       => 'required',
            'kondisi_pinjam'  => 'permit_empty|in_list[baik,rusak_ringan,rusak_berat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->withInput()->with('error', 'Mohon lengkapi seluruh field wajib dengan benar.');
        }

        $inventarisId = (int) $this->request->getPost('inventaris_id');
        $satkerModel = new InventarisSatkerModel();
        $asset = $satkerModel->find($inventarisId);

        if (! is_array($asset)) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', 'Aset BMN yang dipilih tidak ditemukan.');
        }

        $pinjamModel = new InventarisPinjamPakaiModel();

        // Periksa apakah aset sedang dipinjam aktif
        $activeCheck = $pinjamModel->where('inventaris_id', $inventarisId)->where('status', 'dipinjam')->first();
        if ($activeCheck) {
            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('error', "Aset \"{$asset['nama_barang']}\" (NUP {$asset['nup']}) sedang dalam status dipinjam oleh {$activeCheck['nama_peminjam']}.");
        }

        $pegawaiId = (int) $this->request->getPost('pegawai_id') ?: null;
        $namaPeminjam = trim((string) $this->request->getPost('nama_peminjam'));
        $nipPeminjam = trim((string) $this->request->getPost('nip_peminjam')) ?: null;
        $jabatanPeminjam = trim((string) $this->request->getPost('jabatan_peminjam')) ?: null;
        $kontakPeminjam = trim((string) $this->request->getPost('kontak_peminjam')) ?: null;

        // Auto-fill dari master pegawai jika ID dipilih
        if ($pegawaiId !== null && $pegawaiId > 0) {
            $db = db_connect();
            if ($db->tableExists('mst_pegawai')) {
                $peg = $db->table('mst_pegawai')->where('id', $pegawaiId)->get()->getRowArray();
                if (is_array($peg)) {
                    $namaPeminjam = $namaPeminjam ?: ($peg['nama'] ?? '');
                    $nipPeminjam = $nipPeminjam ?: ($peg['nip'] ?? null);
                    $jabatanPeminjam = $jabatanPeminjam ?: ($peg['jabatan'] ?? null);
                    $kontakPeminjam = $kontakPeminjam ?: ($peg['no_hp'] ?? null);
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

        $userId = (int) (session()->get('userId') ?? 0);

        $pinjamId = $pinjamModel->insert([
            'inventaris_id'        => $inventarisId,
            'pegawai_id'           => $pegawaiId,
            'nama_peminjam'        => $namaPeminjam,
            'nip_peminjam'         => $nipPeminjam,
            'jabatan_peminjam'     => $jabatanPeminjam,
            'kontak_peminjam'      => $kontakPeminjam,
            'no_surat'             => trim((string) $this->request->getPost('no_surat')),
            'tgl_pinjam'           => trim((string) $this->request->getPost('tgl_pinjam')),
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
            // Update status aset menjadi 'Dipinjam Pakai'
            $satkerModel->update($inventarisId, [
                'status_bmn'     => 'Dipinjam Pakai',
                'lokasi_ruangan' => 'Pinjam Pakai: ' . $namaPeminjam,
                'updated_by'     => $userId ?: null,
            ]);

            return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', "Aset \"{$asset['nama_barang']}\" (NUP {$asset['nup']}) berhasil dicatat dipinjam pakai oleh {$namaPeminjam}.");
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
            'no_surat'       => 'required|max_length[100]',
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

        $userId = (int) (session()->get('userId') ?? 0);

        $pinjamModel->update($id, [
            'pegawai_id'          => $pegawaiId,
            'nama_peminjam'       => $namaPeminjam,
            'nip_peminjam'        => $nipPeminjam,
            'jabatan_peminjam'    => $jabatanPeminjam,
            'kontak_peminjam'     => $kontakPeminjam,
            'no_surat'            => trim((string) $this->request->getPost('no_surat')),
            'tgl_pinjam'          => trim((string) $this->request->getPost('tgl_pinjam')),
            'tgl_kembali_rencana' => trim((string) $this->request->getPost('tgl_kembali_rencana')) ?: null,
            'keperluan'           => trim((string) $this->request->getPost('keperluan')),
            'kondisi_pinjam'      => trim((string) $this->request->getPost('kondisi_pinjam')) ?: 'baik',
            'kelengkapan'         => trim((string) $this->request->getPost('kelengkapan')) ?: null,
            'catatan'             => trim((string) $this->request->getPost('catatan')) ?: null,
            'file_surat'          => $fileSuratPath,
            'updated_by'          => $userId ?: null,
        ]);

        // Jika statusnya masih dipinjam, sinkronkan nama peminjam ke lokasi_ruangan aset
        if ($existing['status'] === 'dipinjam') {
            $satkerModel = new InventarisSatkerModel();
            $satkerModel->update($existing['inventaris_id'], [
                'lokasi_ruangan' => 'Pinjam Pakai: ' . $namaPeminjam,
                'updated_by'     => $userId ?: null,
            ]);
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

        // Pulihkan status aset di tabel trn_inventaris_satker
        $satkerModel = new InventarisSatkerModel();
        $satkerModel->update($existing['inventaris_id'], [
            'status_bmn'     => 'Digunakan Sendiri',
            'kondisi'        => $kondisiKembali,
            'lokasi_ruangan' => 'Gudang / Belum Berlokasi',
            'ruangan_id'     => null,
            'updated_by'     => $userId ?: null,
        ]);

        return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', "Aset berhasil dikembalikan oleh {$existing['nama_peminjam']} dan status barang telah aktif kembali.");
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

        // Jika aset masih dalam status dipinjam, kembalikan status aset ke semula
        if ($existing['status'] === 'dipinjam') {
            $satkerModel = new InventarisSatkerModel();
            $satkerModel->update($existing['inventaris_id'], [
                'status_bmn'     => 'Digunakan Sendiri',
                'lokasi_ruangan' => 'Belum berlokasi',
            ]);
        }

        // Hapus file fisik jika ada
        if (! empty($existing['file_surat']) && file_exists(FCPATH . $existing['file_surat'])) {
            @unlink(FCPATH . $existing['file_surat']);
        }

        $pinjamModel->delete($id);

        return redirect()->to('/admin/inventaris/pinjam-pakai')->with('message', 'Data transaksi pinjam pakai berhasil dihapus.');
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

        // Logo PUPR base64
        $logoBase64 = '';
        $logoPath = FCPATH . 'assets/images/logo_pupr.png';
        if (! file_exists($logoPath)) {
            $logoPath = FCPATH . 'assets/img/logo_pupr.png';
        }
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Format tanggal Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tglPinjamParts = explode('-', (string) $loan['tgl_pinjam']);
        $tglPinjamIndo = count($tglPinjamParts) === 3
            ? ((int) $tglPinjamParts[2]) . ' ' . ($bulanIndo[(int) $tglPinjamParts[1]] ?? '') . ' ' . $tglPinjamParts[0]
            : $loan['tgl_pinjam'];

        $tglCetak = date('j') . ' ' . ($bulanIndo[(int) date('n')] ?? date('F')) . ' ' . date('Y');

        $data = [
            'loan'           => $loan,
            'logoBase64'     => $logoBase64,
            'tglPinjamIndo'  => $tglPinjamIndo,
            'tglCetak'       => $tglCetak,
            'namaUakpb'      => 'PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU',
            'kodeUakpb'      => '145060900691285000KP',
            'kasatker'       => [
                'nama'    => 'Muhammad Yudi Prasetya, S.T.',
                'nip'     => '198002142014121002',
                'jabatan' => 'Kepala Balai / Kuasa Pengguna Barang',
            ],
            'pengurusBarang' => [
                'nama'    => 'Petugas Penatausahaan BMN',
                'nip'     => '199001012015031001',
                'jabatan' => 'Pengurus Barang Pengguna',
            ],
        ];

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $html = view('admin/inventaris/pinjam_pakai/surat_pinjam_pdf', $data);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Surat_Pinjam_Pakai_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $loan['no_surat']) . '.pdf';
        return $dompdf->stream($filename, ['Attachment' => false]);
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
}
