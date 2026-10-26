<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SuratCutiModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class SuratCuti extends BaseController
{
    public function index()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if ($this->request->isAJAX() || $this->isDataTableRequest()) {
            return $this->dataTable();
        }

        $permissions = $this->getPermissions();
        $role = strtolower((string) session()->get('role'));
        $canApprove = ($permissions['approval'] ?? false) || in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        // Current logged-in employee details
        $pegawaiData = $this->getCurrentPegawaiData();

        // All active employees for dropdown selection
        $db = db_connect();
        $pegawaiList = [];
        if ($db->tableExists('mst_pegawai')) {
            $builder = $db->table('mst_pegawai');
            $builder->select('mst_pegawai.*, ju.jabatan AS jabatan_label');
            $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
            $builder->where('mst_pegawai.is_active', 1);
            $builder->orderBy('mst_pegawai.nama', 'ASC');
            $pegawaiList = $builder->get()->getResultArray();
            foreach ($pegawaiList as &$p) {
                $computed = $this->resolveMasaKerjaFromNip((string) ($p['nip'] ?? ''));
                if ($computed !== null) {
                    $p['masa_kerja'] = $computed;
                }
            }
            unset($p);
        }

        return view('admin/surat/cuti', [
            'title' => 'Pengajuan Cuti',
            'can_add' => $permissions['add'] ?? false,
            'can_edit' => $permissions['edit'] ?? false,
            'can_delete' => $permissions['delete'] ?? false,
            'can_approve' => $canApprove,
            'can_export' => $permissions['export'] ?? false,
            'current_pegawai' => $pegawaiData,
            'pegawai_list' => $pegawaiList,
        ]);
    }

    private function dataTable()
    {
        $permissions = $this->getPermissions();
        $canEdit = $permissions['edit'] ?? false;
        $canDelete = $permissions['delete'] ?? false;
        $canExport = $permissions['export'] ?? false;
        $role = strtolower((string) session()->get('role'));
        $canApprove = ($permissions['approval'] ?? false) || in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        $draw = $this->getDataTableDraw();
        $start = $this->getDataTableStart();
        $length = $this->getDataTableLength();
        $search = $this->getDataTableSearchTerm();
        $orderIndex = $this->getDataTableOrderColumnIndex();
        $orderDirection = $this->getDataTableOrderDirection();

        $db = db_connect();

        if (! $db->tableExists('surat_cuti')) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $builder = $db->table('surat_cuti');
        $recordsTotal = (int) $builder->countAllResults(false);

        if ($search !== '') {
            $builder->groupStart();
            $builder->like('nip', $search);
            $builder->orLike('nama', $search);
            $builder->orLike('jenis_cuti', $search);
            $builder->orLike('alasan_cuti', $search);
            $builder->groupEnd();
        }

        $recordsFiltered = (int) $builder->countAllResults(false);

        $orderColumns = ['id', 'tanggal_pengajuan', 'nama', 'jenis_cuti', 'tanggal_mulai', 'status', 'id'];
        $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0];
        $builder->orderBy($orderColumn, $orderDirection);

        $rows = $builder->limit($length, $start)->get()->getResultArray();

        $data = array_map(function (array $row) use ($canEdit, $canDelete, $canApprove, $canExport) {
            $status = trim((string) ($row['status'] ?? 'pending'));
            $statusBadge = match ($status) {
                'disetujui' => '<span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>',
                'ditolak' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>',
                default => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            };

            $row['status_badge'] = $statusBadge;
            $row['tanggal_pengajuan_formatted'] = !empty($row['tanggal_pengajuan']) ? date('d/m/Y', strtotime($row['tanggal_pengajuan'])) : '-';

            $tglMulai = !empty($row['tanggal_mulai']) ? date('d/m/Y', strtotime($row['tanggal_mulai'])) : '-';
            $tglSelesai = !empty($row['tanggal_selesai']) ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-';
            $lama = (int) ($row['lama_cuti_jumlah'] ?? 1) . ' ' . esc($row['lama_cuti_satuan'] ?? 'Hari');
            $row['periode_formatted'] = $tglMulai . ' s/d ' . $tglSelesai . '<br><small class="text-muted">(' . $lama . ')</small>';

            // Dokumen Export buttons (Word & PDF)
            $id = (int) ($row['id'] ?? 0);
            if ($canExport) {
                $row['dokumen_html'] = '<div class="btn-group btn-group-sm" role="group">' .
                                        '<a href="' . site_url('admin/surat/cuti/' . $id . '/export-word') . '" class="btn btn-primary" title="Export Word (.docx)" target="_blank"><i class="fas fa-file-word mr-1"></i> Word</a>' .
                                        '<a href="' . site_url('admin/surat/cuti/' . $id . '/export-pdf') . '" class="btn btn-outline-danger" title="Export PDF" target="_blank"><i class="fas fa-file-pdf mr-1"></i> PDF</a>' .
                                        '</div>';
            } else {
                $row['dokumen_html'] = '<span class="text-muted">-</span>';
            }

            $actions = '';
            if ($canEdit || $canDelete || $canApprove) {
                $actions .= '<div class="d-flex justify-content-center align-items-center" style="gap: 5px; white-space: nowrap;">';
                if ($canEdit && $status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                }
                if ($canDelete) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . $id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }
                if ($canApprove && $status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . $id . '" title="Setujui"><i class="fas fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . $id . '" title="Tolak"><i class="fas fa-times"></i></button>';
                }
                $actions .= '</div>';
            }
            $row['action_html'] = $actions ?: '<span class="text-muted">-</span>';

            return $row;
        }, $rows);

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * AJAX endpoint untuk menghitung total hari kerja efektif cuti
     * Aturan: Tidak menghitung Sabtu, Minggu, Libur Nasional, dan Cuti Bersama
     */
    public function hitungHari(): ResponseInterface
    {
        if (! $this->canAccess()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Akses ditolak.',
                'total_hari' => 0,
            ]);
        }

        $mulai = trim((string) ($this->request->getGet('mulai') ?? $this->request->getPost('mulai') ?? ''));
        $selesai = trim((string) ($this->request->getGet('selesai') ?? $this->request->getPost('selesai') ?? ''));

        $res = $this->hitungHariKerja($mulai, $selesai);

        return $this->response->setJSON($res);
    }

    public function buat()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! ($this->getPermissions()['add'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk menambah pengajuan cuti.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url('admin/surat/cuti'));
        }

        return $this->simpanCuti();
    }

    private function simpanCuti()
    {
        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $masaKerja = trim((string) $this->request->getPost('masa_kerja'));
        $computedMasaKerja = $this->resolveMasaKerjaFromNip($nip);
        if ($computedMasaKerja !== null) {
            $masaKerja = $computedMasaKerja;
        }
        $unitKerja = trim((string) $this->request->getPost('unit_kerja')) ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $jenisCuti = trim((string) $this->request->getPost('jenis_cuti'));
        $alasanCuti = trim((string) $this->request->getPost('alasan_cuti'));
        $lamaJumlah = max(1, (int) $this->request->getPost('lama_cuti_jumlah'));
        $lamaSatuan = trim((string) $this->request->getPost('lama_cuti_satuan')) ?: 'Hari';
        $tanggalMulai = trim((string) $this->request->getPost('tanggal_mulai'));
        $tanggalSelesai = trim((string) $this->request->getPost('tanggal_selesai'));
        $alamat = trim((string) $this->request->getPost('alamat_selama_cuti'));
        $telepon = trim((string) $this->request->getPost('telepon'));

        $catatanN2 = max(0, (int) $this->request->getPost('catatan_cuti_n2'));
        $catatanN1 = max(0, (int) $this->request->getPost('catatan_cuti_n1'));
        $catatanN = max(0, (int) $this->request->getPost('catatan_cuti_n'));
        $catatanKet = trim((string) $this->request->getPost('catatan_cuti_keterangan'));

        $atasanNama = trim((string) $this->request->getPost('atasan_nama')) ?: 'Muhammad Yudi Prasetya, ST';
        $atasanNip = trim((string) $this->request->getPost('atasan_nip')) ?: '198002142014121002';
        $atasanJabatan = trim((string) $this->request->getPost('atasan_jabatan')) ?: 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';

        $pejabatNama = trim((string) $this->request->getPost('pejabat_nama')) ?: 'Ir. Agung Hari Prabowo, M.T';
        $pejabatNip = trim((string) $this->request->getPost('pejabat_nip')) ?: '196910301998031005';
        $pejabatJabatan = trim((string) $this->request->getPost('pejabat_jabatan')) ?: 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis';

        // Tanggal pengajuan CANNOT be changed by user -> locked to current date
        $tanggalPengajuan = date('Y-m-d');

        $errors = [];
        if ($nama === '') {
            $errors[] = 'Nama pegawai wajib diisi.';
        }
        if ($nip === '') {
            $errors[] = 'NIP wajib diisi.';
        }
        if ($jenisCuti === '') {
            $errors[] = 'Jenis cuti wajib dipilih.';
        }
        if ($alasanCuti === '') {
            $errors[] = 'Alasan cuti wajib diisi.';
        }
        if ($tanggalMulai === '' || $tanggalSelesai === '') {
            $errors[] = 'Tanggal mulai dan selesai cuti wajib diisi.';
        } elseif ($tanggalSelesai < $tanggalMulai) {
            $errors[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
        } elseif ($lamaSatuan === 'Hari') {
            $calc = $this->hitungHariKerja($tanggalMulai, $tanggalSelesai);
            if (! $calc['success']) {
                $errors[] = $calc['message'] ?? 'Gagal menghitung hari cuti.';
            } elseif ($calc['total_hari'] <= 0) {
                $errors[] = 'Periode cuti yang dipilih tidak memiliki hari kerja aktif (seluruh hari bertepatan dengan akhir pekan, libur nasional, atau cuti bersama).';
            } else {
                $lamaJumlah = $calc['total_hari'];
            }
        }

        if ($errors !== []) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', implode(' ', $errors));
        }

        $model = new SuratCutiModel();
        $username = trim((string) session()->get('username'));

        $data = [
            'tanggal_pengajuan' => $tanggalPengajuan,
            'pegawai_id' => (int) $this->request->getPost('pegawai_id') ?: null,
            'nama' => $nama,
            'nip' => $nip,
            'jabatan' => $jabatan,
            'masa_kerja' => $masaKerja,
            'unit_kerja' => $unitKerja,
            'jenis_cuti' => $jenisCuti,
            'alasan_cuti' => $alasanCuti,
            'lama_cuti_jumlah' => $lamaJumlah,
            'lama_cuti_satuan' => $lamaSatuan,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'alamat_selama_cuti' => $alamat,
            'telepon' => $telepon,
            'catatan_cuti_n2' => $catatanN2,
            'catatan_cuti_n1' => $catatanN1,
            'catatan_cuti_n' => $catatanN,
            'catatan_cuti_keterangan' => $catatanKet,
            'atasan_nama' => $atasanNama,
            'atasan_nip' => $atasanNip,
            'atasan_jabatan' => $atasanJabatan,
            'pejabat_nama' => $pejabatNama,
            'pejabat_nip' => $pejabatNip,
            'pejabat_jabatan' => $pejabatJabatan,
            'pertimbangan_atasan' => 'Pending',
            'keputusan_pejabat' => 'Pending',
            'status' => 'pending',
            'created_by' => $username,
        ];

        $insertId = $model->insert($data);

        if ($insertId === false) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Gagal menyimpan pengajuan cuti.');
        }

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil disimpan.');
    }

    public function detail(int $id)
    {
        if (! $this->canAccess()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak.']);
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data cuti tidak ditemukan.']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $row]);
    }

    public function ubah(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! ($this->getPermissions()['edit'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk mengubah data cuti.');
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $masaKerja = trim((string) $this->request->getPost('masa_kerja'));
        $computedMasaKerja = $this->resolveMasaKerjaFromNip($nip);
        if ($computedMasaKerja !== null) {
            $masaKerja = $computedMasaKerja;
        }
        $unitKerja = trim((string) $this->request->getPost('unit_kerja')) ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $jenisCuti = trim((string) $this->request->getPost('jenis_cuti'));
        $alasanCuti = trim((string) $this->request->getPost('alasan_cuti'));
        $lamaJumlah = max(1, (int) $this->request->getPost('lama_cuti_jumlah'));
        $lamaSatuan = trim((string) $this->request->getPost('lama_cuti_satuan')) ?: 'Hari';
        $tanggalMulai = trim((string) $this->request->getPost('tanggal_mulai'));
        $tanggalSelesai = trim((string) $this->request->getPost('tanggal_selesai'));

        if ($tanggalMulai === '' || $tanggalSelesai === '') {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Tanggal mulai dan selesai cuti wajib diisi.');
        }

        if ($tanggalSelesai < $tanggalMulai) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        }

        if ($lamaSatuan === 'Hari') {
            $calc = $this->hitungHariKerja($tanggalMulai, $tanggalSelesai);
            if (! $calc['success']) {
                return redirect()->to(site_url('admin/surat/cuti'))->with('error', $calc['message'] ?? 'Gagal menghitung hari cuti.');
            }
            if ($calc['total_hari'] <= 0) {
                return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Periode cuti yang dipilih tidak memiliki hari kerja aktif (seluruh hari bertepatan dengan akhir pekan, libur nasional, atau cuti bersama).');
            }
            $lamaJumlah = $calc['total_hari'];
        }

        $alamat = trim((string) $this->request->getPost('alamat_selama_cuti'));
        $telepon = trim((string) $this->request->getPost('telepon'));

        $catatanN2 = max(0, (int) $this->request->getPost('catatan_cuti_n2'));
        $catatanN1 = max(0, (int) $this->request->getPost('catatan_cuti_n1'));
        $catatanN = max(0, (int) $this->request->getPost('catatan_cuti_n'));
        $catatanKet = trim((string) $this->request->getPost('catatan_cuti_keterangan'));

        $data = [
            'nama' => $nama,
            'nip' => $nip,
            'jabatan' => $jabatan,
            'masa_kerja' => $masaKerja,
            'unit_kerja' => $unitKerja,
            'jenis_cuti' => $jenisCuti,
            'alasan_cuti' => $alasanCuti,
            'lama_cuti_jumlah' => $lamaJumlah,
            'lama_cuti_satuan' => $lamaSatuan,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'alamat_selama_cuti' => $alamat,
            'telepon' => $telepon,
            'catatan_cuti_n2' => $catatanN2,
            'catatan_cuti_n1' => $catatanN1,
            'catatan_cuti_n' => $catatanN,
            'catatan_cuti_keterangan' => $catatanKet,
        ];

        $model->update($id, $data);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! ($this->getPermissions()['delete'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk menghapus data cuti.');
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

    public function setujui(int $id)
    {
        $permissions = $this->getPermissions();
        $role = strtolower((string) session()->get('role'));
        $canApprove = ($permissions['approval'] ?? false) || in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        if (! $canApprove) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses persetujuan.');
        }

        $model = new SuratCutiModel();
        $model->update($id, [
            'pertimbangan_atasan' => 'Disetujui',
            'keputusan_pejabat'   => 'Disetujui',
            'status'              => 'disetujui',
        ]);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil disetujui.');
    }

    public function tolak(int $id)
    {
        $permissions = $this->getPermissions();
        $role = strtolower((string) session()->get('role'));
        $canApprove = ($permissions['approval'] ?? false) || in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        if (! $canApprove) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses persetujuan.');
        }

        $model = new SuratCutiModel();
        $model->update($id, [
            'pertimbangan_atasan' => 'Tidak Disetujui',
            'keputusan_pejabat'   => 'Tidak Disetujui',
            'status'              => 'ditolak',
        ]);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti ditolak.');
    }

    public function exportPdf(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! ($this->getPermissions()['export'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $html = view('admin/surat/cuti_pdf', [
            'data' => $row,
        ]);

        if (class_exists(Dompdf::class)) {
            $dompdf = new Dompdf(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Form_Cuti_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['nama']) . '.pdf';
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setBody($dompdf->output());
        }

        return $this->response->setBody($html);
    }

    private function getCurrentPegawaiData(): array
    {
        $username = trim((string) session()->get('username'));
        $fullName = trim((string) (session()->get('fullName') ?? ''));

        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return [
                'nama' => $fullName ?: $username,
                'nip' => $username,
                'jabatan' => '',
                'masa_kerja' => '',
                'unit_kerja' => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            ];
        }

        $builder = $db->table('mst_pegawai');
        $builder->select('mst_pegawai.*, ju.jabatan AS jabatan_label');
        $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
        $builder->where('mst_pegawai.is_active', 1);
        $builder->groupStart();
        $builder->where('mst_pegawai.nip', $username);
        $builder->orWhere('LOWER(mst_pegawai.nama)', strtolower($fullName));
        $builder->groupEnd();
        $builder->limit(1);

        $pegawai = $builder->get()->getRowArray();

        if (! is_array($pegawai)) {
            return [
                'nama' => $fullName ?: $username,
                'nip' => $username,
                'jabatan' => '',
                'masa_kerja' => '',
                'unit_kerja' => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            ];
        }

        $nipVal = trim((string) ($pegawai['nip'] ?? ''));
        $computedMasaKerja = $this->resolveMasaKerjaFromNip($nipVal);

        return [
            'nama' => trim((string) ($pegawai['nama'] ?? '')),
            'nip' => $nipVal,
            'jabatan' => trim((string) ($pegawai['jabatan_label'] ?? '')),
            'masa_kerja' => $computedMasaKerja ?? trim((string) ($pegawai['masa_kerja'] ?? '')),
            'unit_kerja' => trim((string) ($pegawai['unit_kerja'] ?? '')) ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
        ];
    }

    private function canAccess(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'staf', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function isDataTableRequest(): bool
    {
        return $this->request->getGet('draw') !== null
            && $this->request->getGet('start') !== null
            && $this->request->getGet('length') !== null;
    }

    private function getDataTableDraw(): int
    {
        return max(0, (int) $this->request->getGet('draw'));
    }

    private function getDataTableStart(): int
    {
        return max(0, (int) $this->request->getGet('start'));
    }

    private function getDataTableLength(): int
    {
        $length = (int) $this->request->getGet('length');
        return $length > 0 ? $length : 10;
    }

    private function getDataTableSearchTerm(): string
    {
        $search = $this->request->getGet('search');
        if (! is_array($search)) {
            return '';
        }
        return trim((string) ($search['value'] ?? ''));
    }

    private function getDataTableOrderColumnIndex(): int
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 0;
        }
        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 0;
        }
        return max(0, (int) ($first['column'] ?? 0));
    }

    private function getDataTableOrderDirection(): string
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 'DESC';
        }
        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 'DESC';
        }
        return strtolower((string) ($first['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
    }

    private function resolveMasaKerjaFromNip(string $nip): ?string
    {
        $digits = preg_replace('/\D+/', '', $nip) ?? '';
        if (strlen($digits) < 14) {
            return null;
        }

        $yearPart = substr($digits, 8, 4);
        $monthPart = substr($digits, 12, 2);

        $year = (int) $yearPart;
        $month = (int) $monthPart;

        if ($year < 1950 || $year > (int) date('Y') || $month < 1 || $month > 12) {
            return null;
        }

        $tmtDate = \DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
        if (! $tmtDate || $tmtDate->format('Y-m-d') !== sprintf('%04d-%02d-01', $year, $month)) {
            return null;
        }

        $today = new \DateTimeImmutable('today');
        if ($tmtDate > $today) {
            return null;
        }

        $diff = $tmtDate->diff($today);
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' Tahun';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' Bulan';
        }

        if ($parts === []) {
            $parts[] = '0 Bulan';
        }

        return implode(' ', $parts);
    }

    /**
     * Hitung hari kerja efektif antara tanggal mulai dan tanggal selesai
     * Aturan:
     * - Sabtu & Minggu tidak dihitung (akhir pekan)
     * - Tanggal Merah / Libur Nasional (holiday) tidak dihitung
     * - Cuti Bersama (leave) tidak dihitung
     */
    public function hitungHariKerja(?string $tanggalMulai, ?string $tanggalSelesai): array
    {
        if (empty($tanggalMulai) || empty($tanggalSelesai)) {
            return [
                'success' => false,
                'message' => 'Tanggal mulai dan tanggal selesai wajib diisi.',
                'total_hari' => 0,
            ];
        }

        try {
            $start = new \DateTimeImmutable($tanggalMulai);
            $end = new \DateTimeImmutable($tanggalSelesai);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Format tanggal tidak valid.',
                'total_hari' => 0,
            ];
        }

        if ($end < $start) {
            return [
                'success' => false,
                'message' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
                'total_hari' => 0,
            ];
        }

        $db = db_connect();
        $holidays = [];
        if ($db->tableExists('mst_tanggal_merah')) {
            $rows = $db->table('mst_tanggal_merah')
                ->select('tanggal, nama_libur, tipe')
                ->where('tanggal >=', $start->format('Y-m-d'))
                ->where('tanggal <=', $end->format('Y-m-d'))
                ->get()
                ->getResultArray();

            foreach ($rows as $r) {
                $holidays[$r['tanggal']] = [
                    'nama' => $r['nama_libur'],
                    'tipe' => $r['tipe'] ?? 'holiday',
                ];
            }
        }

        $interval = new \DateInterval('P1D');
        // DateTimePeriod end is exclusive, so modify +1 day to make end date inclusive
        $period = new \DatePeriod($start, $interval, $end->modify('+1 day'));

        $totalHariKerja = 0;
        $totalKalender = 0;
        $weekendCount = 0;
        $holidayCount = 0;
        $leaveCount = 0;
        $excludedList = [];
        $workingList = [];

        foreach ($period as $dt) {
            $totalKalender++;
            $dateStr = $dt->format('Y-m-d');
            $dayOfWeek = (int) $dt->format('N'); // 1 (Senin) s/d 7 (Minggu)
            $isWeekend = ($dayOfWeek === 6 || $dayOfWeek === 7);
            $dayNameIndo = match ($dayOfWeek) {
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
                default => '',
            };

            $hasHoliday = isset($holidays[$dateStr]);

            if ($isWeekend) {
                $weekendCount++;
                $alasan = ($dayOfWeek === 6 ? 'Sabtu (Akhir Pekan)' : 'Minggu (Akhir Pekan)');
                if ($hasHoliday) {
                    $h = $holidays[$dateStr];
                    $hTypeLabel = ($h['tipe'] === 'leave') ? 'Cuti Bersama' : 'Libur Nasional';
                    $alasan .= ' & ' . $hTypeLabel . ' (' . $h['nama'] . ')';
                }
                $excludedList[] = [
                    'tanggal'    => $dateStr,
                    'hari'       => $dayNameIndo,
                    'kategori'   => 'weekend',
                    'keterangan' => $alasan,
                ];
            } elseif ($hasHoliday) {
                $h = $holidays[$dateStr];
                $isCutiBersama = ($h['tipe'] === 'leave');
                if ($isCutiBersama) {
                    $leaveCount++;
                } else {
                    $holidayCount++;
                }
                $excludedList[] = [
                    'tanggal'    => $dateStr,
                    'hari'       => $dayNameIndo,
                    'kategori'   => $h['tipe'],
                    'keterangan' => ($isCutiBersama ? 'Cuti Bersama: ' : 'Libur Nasional: ') . $h['nama'],
                ];
            } else {
                $totalHariKerja++;
                $workingList[] = [
                    'tanggal' => $dateStr,
                    'hari'    => $dayNameIndo,
                ];
            }
        }

        $summaryParts = [];
        if ($weekendCount > 0) {
            $summaryParts[] = $weekendCount . ' hari akhir pekan (Sabtu/Minggu)';
        }
        if ($holidayCount > 0) {
            $summaryParts[] = $holidayCount . ' hari libur nasional';
        }
        if ($leaveCount > 0) {
            $summaryParts[] = $leaveCount . ' hari cuti bersama';
        }

        $keteranganDikecualikan = ($summaryParts !== []) ? implode(', ', $summaryParts) : 'Tidak ada';

        return [
            'success'                 => true,
            'total_hari'              => $totalHariKerja,
            'total_kalender'          => $totalKalender,
            'total_dikecualikan'      => count($excludedList),
            'weekend_count'           => $weekendCount,
            'holiday_count'           => $holidayCount,
            'leave_count'             => $leaveCount,
            'keterangan_dikecualikan' => $keteranganDikecualikan,
            'excluded_list'           => $excludedList,
            'working_list'            => $workingList,
        ];
    }

    public function exportWord(int $id)
    {
        helper(['url', 'form']);
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! ($this->getPermissions()['export'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $templateFile = APPPATH . 'Views/admin/surat/form_surat_cuti_template.docx';
        if (! file_exists($templateFile)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Template Surat Cuti (.docx) tidak ditemukan.');
        }

        $months = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];

        $formatIndoDate = static function (?string $dateStr) use ($months): string {
            if (empty($dateStr)) return '-';
            $time = strtotime($dateStr);
            if (! $time) return '-';
            $d = date('j F Y', $time);
            foreach ($months as $en => $idMonth) {
                $d = str_replace($en, $idMonth, $d);
            }
            return $d;
        };

        $jenisKey = strtolower(trim((string) ($row['jenis_cuti'] ?? '')));
        $pertimbangan = strtolower(trim((string) ($row['pertimbangan_atasan'] ?? '')));
        $keputusan = strtolower(trim((string) ($row['keputusan_pejabat'] ?? '')));
        $checkSymbol = 'V';

        $partsJabatan = explode(',', $row['jabatan'] ?? '');
        $jabatanClean = trim($partsJabatan[0] ?? '');

        $sequential = [
            ['search' => 'nama', 'replace' => (string) ($row['nama'] ?? ''), 'limit' => 1],
            ['search' => 'nama', 'replace' => (string) ($row['nama'] ?? ''), 'limit' => 1],
            ['search' => 'nip', 'replace' => (string) ($row['nip'] ?? ''), 'limit' => 1],
            ['search' => 'nip', 'replace' => (string) ($row['nip'] ?? ''), 'limit' => 1],
        ];

        $simple = [
            'tgl_pengajuan' => $formatIndoDate($row['tanggal_pengajuan'] ?? date('Y-m-d')),
            'pejabat_jabatan_tujuan' => $row['pejabat_jabatan'] ?? 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis',
            'jabatan' => $jabatanClean,
            'masa_kerja' => $row['masa_kerja'] ?? '',
            'unit_kerja' => $row['unit_kerja'] ?? '',
            'v_ct' => $jenisKey === 'cuti tahunan' ? $checkSymbol : '',
            'v_cb' => $jenisKey === 'cuti besar' ? $checkSymbol : '',
            'v_cs' => $jenisKey === 'cuti sakit' ? $checkSymbol : '',
            'v_cm' => $jenisKey === 'cuti melahirkan' ? $checkSymbol : '',
            'v_cap' => $jenisKey === 'cuti karena alasan penting' ? $checkSymbol : '',
            'v_cltn' => $jenisKey === 'cuti di luar tanggungan negara' ? $checkSymbol : '',
            'alasan_cuti' => $row['alasan_cuti'] ?? '',
            'lama_cuti' => ((int)($row['lama_cuti_jumlah'] ?? 1)) . ' ' . ($row['lama_cuti_satuan'] ?? 'Hari'),
            'tanggal_mulai' => $formatIndoDate($row['tanggal_mulai']),
            'tanggal_selesai' => $formatIndoDate($row['tanggal_selesai']),
            'catatan_tahun' => date('Y'),
            'catatan_cuti_n' => ((int)($row['catatan_cuti_n'] ?? 0)) . ' Hari',
            'catatan_cuti_keterangan' => $row['catatan_cuti_keterangan'] ?? '',
            'alamat_selama_cuti' => $row['alamat_selama_cuti'] ?? '',
            'telepon' => $row['telepon'] ?? '',
            'v_atasan_setuju' => ($pertimbangan === 'disetujui' || $pertimbangan === 'setuju') ? $checkSymbol : '',
            'v_atasan_ubah' => ($pertimbangan === 'perubahan') ? $checkSymbol : '',
            'v_atasan_tangguh' => ($pertimbangan === 'ditangguhkan') ? $checkSymbol : '',
            'v_atasan_tolak' => ($pertimbangan === 'tidak disetujui' || $pertimbangan === 'ditolak') ? $checkSymbol : '',
            'atasan_jabatan' => $row['atasan_jabatan'] ?? 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'atasan_nama' => $row['atasan_nama'] ?? 'Muhammad Yudi Prasetya, ST',
            'atasan_nip' => $row['atasan_nip'] ?? '198002142014121002',
            'v_pejabat_setuju' => ($keputusan === 'disetujui' || $keputusan === 'setuju') ? $checkSymbol : '',
            'v_pejabat_ubah' => ($keputusan === 'perubahan') ? $checkSymbol : '',
            'v_pejabat_tangguh' => ($keputusan === 'ditangguhkan') ? $checkSymbol : '',
            'v_pejabat_tolak' => ($keputusan === 'tidak disetujui' || $keputusan === 'ditolak') ? $checkSymbol : '',
            'pejabat_jabatan' => $row['pejabat_jabatan'] ?? 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis',
            'pejabat_nama' => $row['pejabat_nama'] ?? 'Ir. Agung Hari Prabowo, M.T',
            'pejabat_nip' => $row['pejabat_nip'] ?? '196910301998031005',
        ];

        $fileContent = $this->processWordDocx($templateFile, $simple, $sequential);
        if ($fileContent === null) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Gagal memproses dokumen Word.');
        }

        $filename = 'Form_Cuti_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['nama']) . '.docx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($fileContent);
    }

    public function exportWordKosong()
    {
        helper(['url', 'form']);
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $permissions = $this->getPermissions();
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $templateFile = APPPATH . 'Views/admin/surat/form_surat_cuti_template.docx';
        if (! file_exists($templateFile)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Template Surat Cuti (.docx) tidak ditemukan.');
        }

        $months = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];

        $formatIndoDate = static function (?string $dateStr) use ($months): string {
            if (empty($dateStr)) return '-';
            $time = strtotime($dateStr);
            if (! $time) return '-';
            $d = date('j F Y', $time);
            foreach ($months as $en => $idMonth) {
                $d = str_replace($en, $idMonth, $d);
            }
            return $d;
        };

        // First occurrence: Data Pegawai (blank cell)
        // Second occurrence: Hormat saya signature block (dotted line)
        $sequential = [
            ['search' => 'nama', 'replace' => '', 'limit' => 1],
            ['search' => 'nama', 'replace' => '( ............................................ )', 'limit' => 1],
            ['search' => 'nip', 'replace' => '', 'limit' => 1],
            ['search' => 'nip', 'replace' => '............................................', 'limit' => 1],
        ];

        $simple = [
            'tgl_pengajuan' => $formatIndoDate(date('Y-m-d')),
            'pejabat_jabatan_tujuan' => 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis',
            'jabatan' => '',
            'masa_kerja' => '',
            'unit_kerja' => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'v_ct' => '',
            'v_cb' => '',
            'v_cs' => '',
            'v_cm' => '',
            'v_cap' => '',
            'v_cltn' => '',
            'alasan_cuti' => '',
            'lama_cuti' => '',
            'tanggal_mulai' => '',
            'tanggal_selesai' => '',
            'catatan_tahun' => date('Y'),
            'catatan_cuti_n' => '',
            'catatan_cuti_keterangan' => '',
            'alamat_selama_cuti' => '',
            'telepon' => '',
            'v_atasan_setuju' => '',
            'v_atasan_ubah' => '',
            'v_atasan_tangguh' => '',
            'v_atasan_tolak' => '',
            'atasan_jabatan' => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'atasan_nama' => 'Muhammad Yudi Prasetya, ST',
            'atasan_nip' => '198002142014121002',
            'v_pejabat_setuju' => '',
            'v_pejabat_ubah' => '',
            'v_pejabat_tangguh' => '',
            'v_pejabat_tolak' => '',
            'pejabat_jabatan' => 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis',
            'pejabat_nama' => 'Ir. Agung Hari Prabowo, M.T',
            'pejabat_nip' => '196910301998031005',
        ];

        $fileContent = $this->processWordDocx($templateFile, $simple, $sequential);
        if ($fileContent === null) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Gagal memproses dokumen Word.');
        }

        $filename = 'Form_Permintaan_dan_Pemberian_Cuti_Kosong.docx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($fileContent);
    }

    /**
     * Export Matriks Horizontal Rekapitulasi Cuti Pegawai (Excel .xlsx)
     * Matriks tanggal memanjang ke samping dengan pewarnaan:
     * - Merah (#DC2626): Tanggal Cuti yang Diambil Pegawai
     * - Kuning/Emas (#FDE047): Cuti Bersama SKB 3 Menteri
     * - Merah Muda (#FCA5A5): Tanggal Merah / Libur Nasional
     * - Abu-abu (#E2E8F0): Akhir Pekan (Sabtu / Minggu)
     * - Putih (#FFFFFF): Hari Kerja Efektif Normal
     * Dilengkapi Freeze Panes & Formula Total Otomatis.
     */
    public function exportExcel()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! ($this->getPermissions()['export'] ?? false)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses untuk export data.');
        }

        $tglMulaiInput = trim((string) ($this->request->getGet('tanggal_mulai') ?? $this->request->getPost('tanggal_mulai')));
        $tglSelesaiInput = trim((string) ($this->request->getGet('tanggal_selesai') ?? $this->request->getPost('tanggal_selesai')));
        $statusFilter = strtolower(trim((string) ($this->request->getGet('status') ?? $this->request->getPost('status'))));
        if (! in_array($statusFilter, ['disetujui', 'semua'], true)) {
            $statusFilter = 'disetujui';
        }

        // Default rentang tanggal: Bulan berjalan (1 s/d akhir bulan)
        if (empty($tglMulaiInput)) {
            $tglMulaiInput = date('Y-m-01');
        }
        if (empty($tglSelesaiInput)) {
            $tglSelesaiInput = date('Y-m-t');
        }

        try {
            $startDate = new \DateTimeImmutable($tglMulaiInput);
            $endDate = new \DateTimeImmutable($tglSelesaiInput);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Format rentang tanggal tidak valid.');
        }

        if ($endDate < $startDate) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        }

        // Batasi rentang maksimal 366 hari (1 tahun penuh) untuk menjaga performa dan keterbacaan matriks
        $diffDays = (int) $startDate->diff($endDate)->days + 1;
        if ($diffDays > 366) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Rentang tanggal maksimal adalah 1 tahun (366 hari) agar file Excel optimal.');
        }

        $db = db_connect();

        // 1. Ambil Hari Libur Nasional & Cuti Bersama
        $holidays = [];
        if ($db->tableExists('mst_tanggal_merah')) {
            $hRows = $db->table('mst_tanggal_merah')
                ->select('tanggal, nama_libur, tipe')
                ->where('tanggal >=', $startDate->format('Y-m-d'))
                ->where('tanggal <=', $endDate->format('Y-m-d'))
                ->get()
                ->getResultArray();

            foreach ($hRows as $hr) {
                $holidays[$hr['tanggal']] = [
                    'nama' => $hr['nama_libur'],
                    'tipe' => $hr['tipe'] ?? 'holiday',
                ];
            }
        }

        // 2. Ambil Daftar Pegawai Aktif
        $employees = [];
        if ($db->tableExists('mst_pegawai')) {
            $builder = $db->table('mst_pegawai');
            $builder->select('mst_pegawai.id, mst_pegawai.nip, mst_pegawai.nama, ju.jabatan AS jabatan_label');
            $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
            $builder->where('mst_pegawai.is_active', 1);
            $builder->orderBy('mst_pegawai.nama', 'ASC');
            $employees = $builder->get()->getResultArray();
        }

        // 3. Ambil Rekap Pengajuan Cuti dalam Rentang Tanggal
        $cutiRows = [];
        if ($db->tableExists('surat_cuti')) {
            $cBuilder = $db->table('surat_cuti')
                ->select('id, pegawai_id, nip, nama, jabatan, jenis_cuti, alasan_cuti, tanggal_mulai, tanggal_selesai, status')
                ->where('tanggal_mulai <=', $endDate->format('Y-m-d'))
                ->where('tanggal_selesai >=', $startDate->format('Y-m-d'));

            if ($statusFilter === 'disetujui') {
                $cBuilder->where('status', 'disetujui');
            } else {
                $cBuilder->whereIn('status', ['disetujui', 'pending']);
            }

            $cutiRows = $cBuilder->get()->getResultArray();
        }

        // Index pegawai berdasarkan ID & NIP
        $empByPegawaiId = [];
        $empByNip = [];
        foreach ($employees as $idx => $emp) {
            if (! empty($emp['id'])) {
                $empByPegawaiId[(int) $emp['id']] = $idx;
            }
            $cleanNip = preg_replace('/\s+/', '', (string) ($emp['nip'] ?? ''));
            if ($cleanNip !== '') {
                $empByNip[$cleanNip] = $idx;
            }
        }

        // Hubungkan data cuti ke pegawai (tambahkan jika ada pemohon cuti yang belum terdaftar di mst_pegawai aktif)
        $empLeaves = [];
        foreach ($cutiRows as $cRow) {
            $cPegId = ! empty($cRow['pegawai_id']) ? (int) $cRow['pegawai_id'] : null;
            $cNip = preg_replace('/\s+/', '', (string) ($cRow['nip'] ?? ''));

            $idx = null;
            if ($cPegId !== null && isset($empByPegawaiId[$cPegId])) {
                $idx = $empByPegawaiId[$cPegId];
            } elseif ($cNip !== '' && isset($empByNip[$cNip])) {
                $idx = $empByNip[$cNip];
            }

            if ($idx === null) {
                $newIdx = count($employees);
                $employees[$newIdx] = [
                    'id'            => $cPegId,
                    'nip'           => $cRow['nip'] ?? '-',
                    'nama'          => $cRow['nama'] ?? 'Pegawai',
                    'jabatan_label' => $cRow['jabatan'] ?? '-',
                ];
                if ($cPegId !== null) {
                    $empByPegawaiId[$cPegId] = $newIdx;
                }
                if ($cNip !== '') {
                    $empByNip[$cNip] = $newIdx;
                }
                $idx = $newIdx;
            }

            $empLeaves[$idx][] = $cRow;
        }

        // 4. Susun Daftar Hari Kalender
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->modify('+1 day'));
        $days = [];
        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $dayOfWeek = (int) $dt->format('N'); // 1 = Senin, 7 = Minggu
            $isWeekend = ($dayOfWeek === 6 || $dayOfWeek === 7);
            $dayNameIndo = match ($dayOfWeek) {
                1 => 'Sen',
                2 => 'Sel',
                3 => 'Rab',
                4 => 'Kam',
                5 => 'Jum',
                6 => 'Sab',
                7 => 'Min',
                default => '',
            };

            $holiday = $holidays[$dateStr] ?? null;
            $isHoliday = ($holiday !== null && ($holiday['tipe'] ?? 'holiday') === 'holiday');
            $isLeave = ($holiday !== null && ($holiday['tipe'] ?? '') === 'leave');

            $days[] = [
                'date'         => $dateStr,
                'day_num'      => $dt->format('d'),
                'month_num'    => $dt->format('m'),
                'month_name'   => $this->bulanIndo((int) $dt->format('m')),
                'year'         => $dt->format('Y'),
                'day_name'     => $dayNameIndo,
                'day_of_week'  => $dayOfWeek,
                'is_weekend'   => $isWeekend,
                'is_holiday'   => $isHoliday,
                'is_leave'     => $isLeave,
                'holiday_name' => $holiday['nama'] ?? '',
            ];
        }

        // 5. Inisialisasi PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Cuti Pegawai');

        // Header Dokumen
        $sheet->setCellValue('A1', 'KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));

        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('334155'));

        $sheet->setCellValue('A3', 'REKAPITULASI CUTI PEGAWAI');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0A66C2'));

        $filterLabel = ($statusFilter === 'disetujui') ? 'Hanya Cuti Disetujui' : 'Semua Pengajuan Aktif (Disetujui & Pending)';
        $sheet->setCellValue('A4', 'Periode: ' . $startDate->format('d/m/Y') . ' s/d ' . $endDate->format('d/m/Y') . ' (' . $filterLabel . ')');
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Legend / Keterangan Warna
        $sheet->setCellValue('B6', 'Keterangan Warna:');
        $sheet->getStyle('B6')->getFont()->setBold(true)->setSize(9);

        // 1. Cuti Diambil
        $sheet->setCellValue('C6', ' C ');
        $sheet->getStyle('C6')->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('C6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDC2626');
        $sheet->getStyle('C6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('D6', 'Cuti Diambil Pegawai');
        $sheet->getStyle('D6')->getFont()->setSize(9);

        // 2. Cuti Bersama
        $sheet->setCellValue('F6', ' CB ');
        $sheet->getStyle('F6')->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('854D0E'));
        $sheet->getStyle('F6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDE047');
        $sheet->getStyle('F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G6', 'Cuti Bersama SKB 3 Menteri');
        $sheet->getStyle('G6')->getFont()->setSize(9);

        // 3. Tanggal Merah / Libur Nasional
        $sheet->setCellValue('I6', ' LN ');
        $sheet->getStyle('I6')->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('991B1B'));
        $sheet->getStyle('I6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCA5A5');
        $sheet->getStyle('I6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('J6', 'Tanggal Merah / Libur Nasional');
        $sheet->getStyle('J6')->getFont()->setSize(9);

        // 4. Akhir Pekan
        $sheet->setCellValue('L6', ' - ');
        $sheet->getStyle('L6')->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));
        $sheet->getStyle('L6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle('L6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('M6', 'Akhir Pekan (Sabtu / Minggu)');
        $sheet->getStyle('M6')->getFont()->setSize(9);

        // 5. Normal
        $sheet->setCellValue('O6', ' ');
        $sheet->getStyle('O6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('O6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet->setCellValue('P6', 'Hari Kerja Normal');
        $sheet->getStyle('P6')->getFont()->setSize(9);

        // Header Tabel Tetap (Kolom A-E)
        $sheet->mergeCells('A8:A10');
        $sheet->setCellValue('A8', 'No');
        $sheet->mergeCells('B8:B10');
        $sheet->setCellValue('B8', 'NIP');
        $sheet->mergeCells('C8:C10');
        $sheet->setCellValue('C8', 'Nama Pegawai');
        $sheet->mergeCells('D8:D10');
        $sheet->setCellValue('D8', 'Jabatan');
        $sheet->mergeCells('E8:E10');
        $sheet->setCellValue('E8', 'Total Cuti');

        $sheet->getStyle('A8:E10')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A8:E10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $sheet->getStyle('A8:E10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        $sheet->getColumnDimension('A')->setWidth(5.5);
        $sheet->getColumnDimension('B')->setWidth(21);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(12);

        // Header Kolom Tanggal (Mulai dari Kolom F)
        $colIdx = 6;
        $monthGroups = [];

        foreach ($days as $d) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setWidth(5.0);

            // Grouping Bulan
            $mKey = $d['year'] . '-' . $d['month_num'];
            if (! isset($monthGroups[$mKey])) {
                $monthGroups[$mKey] = [
                    'startCol' => $colIdx,
                    'endCol'   => $colIdx,
                    'label'    => strtoupper($d['month_name']) . ' ' . $d['year'],
                ];
            } else {
                $monthGroups[$mKey]['endCol'] = $colIdx;
            }

            // Baris 9: Nama Hari
            $sheet->setCellValue($colLetter . '9', $d['day_name']);
            // Baris 10: Angka Tanggal
            $sheet->setCellValue($colLetter . '10', $d['day_num']);

            // Warna Header Tanggal
            if ($d['is_holiday']) {
                $hColor = 'FFB91C1C';
                if ($d['holiday_name']) {
                    $sheet->getComment($colLetter . '10')->getText()->createTextRun('Libur Nasional: ' . $d['holiday_name']);
                }
            } elseif ($d['is_leave']) {
                $hColor = 'FFD97706';
                if ($d['holiday_name']) {
                    $sheet->getComment($colLetter . '10')->getText()->createTextRun('Cuti Bersama: ' . $d['holiday_name']);
                }
            } elseif ($d['is_weekend']) {
                $hColor = 'FF475569';
            } else {
                $hColor = 'FF1E293B';
            }

            $sheet->getStyle($colLetter . '9:' . $colLetter . '10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($hColor);
            $sheet->getStyle($colLetter . '9:' . $colLetter . '10')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'))->setSize(8);
            $sheet->getStyle($colLetter . '9:' . $colLetter . '10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            $colIdx++;
        }

        $lastColIdx = $colIdx - 1;
        $lastColLetter = Coordinate::stringFromColumnIndex($lastColIdx);

        // Render Baris 8: Pengelompokan Bulan
        foreach ($monthGroups as $mg) {
            $startLetter = Coordinate::stringFromColumnIndex($mg['startCol']);
            $endLetter = Coordinate::stringFromColumnIndex($mg['endCol']);
            if ($startLetter !== $endLetter) {
                $sheet->mergeCells($startLetter . '8:' . $endLetter . '8');
            }
            $sheet->setCellValue($startLetter . '8', $mg['label']);
            $sheet->getStyle($startLetter . '8:' . $endLetter . '8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
            $sheet->getStyle($startLetter . '8:' . $endLetter . '8')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'))->setSize(9);
            $sheet->getStyle($startLetter . '8:' . $endLetter . '8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }

        // 6. Data Baris Pegawai
        $rowNum = 11;
        $no = 1;

        foreach ($employees as $empIdx => $emp) {
            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValueExplicit('B' . $rowNum, (string) ($emp['nip'] ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $emp['nama']);
            $sheet->setCellValue('D' . $rowNum, $emp['jabatan_label'] ?: '-');

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $rowNum)->getFont()->setBold(true);

            // Formula Total Hari Cuti
            $sheet->setCellValue('E' . $rowNum, '=COUNTIF(F' . $rowNum . ':' . $lastColLetter . $rowNum . ', "C*")');
            $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $rowNum)->getFont()->setBold(true);

            $cColIdx = 6;
            foreach ($days as $d) {
                $cellCoord = Coordinate::stringFromColumnIndex($cColIdx) . $rowNum;

                if ($d['is_holiday']) {
                    $sheet->setCellValue($cellCoord, 'LN');
                    $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCA5A5');
                    $sheet->getStyle($cellCoord)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('991B1B'))->setSize(8);
                    if ($d['holiday_name']) {
                        $sheet->getComment($cellCoord)->getText()->createTextRun('Libur Nasional: ' . $d['holiday_name']);
                    }
                } elseif ($d['is_leave']) {
                    $sheet->setCellValue($cellCoord, 'CB');
                    $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDE047');
                    $sheet->getStyle($cellCoord)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('854D0E'))->setSize(8);
                    if ($d['holiday_name']) {
                        $sheet->getComment($cellCoord)->getText()->createTextRun('Cuti Bersama: ' . $d['holiday_name']);
                    }
                } elseif ($d['is_weekend']) {
                    $sheet->setCellValue($cellCoord, '-');
                    $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
                    $sheet->getStyle($cellCoord)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('94A3B8'))->setSize(8);
                } else {
                    // Hari kerja: periksa apakah pegawai mengambil cuti
                    $takenLeave = null;
                    if (! empty($empLeaves[$empIdx])) {
                        foreach ($empLeaves[$empIdx] as $lv) {
                            if ($lv['tanggal_mulai'] <= $d['date'] && $lv['tanggal_selesai'] >= $d['date']) {
                                $takenLeave = $lv;
                                break;
                            }
                        }
                    }

                    if ($takenLeave !== null) {
                        $cutiCode = match (trim((string) $takenLeave['jenis_cuti'])) {
                            'Cuti Tahunan'                  => 'CT',
                            'Cuti Sakit'                    => 'CS',
                            'Cuti Melahirkan'               => 'CM',
                            'Cuti Karena Alasan Penting'    => 'CAP',
                            'Cuti Besar'                    => 'CBK',
                            'Cuti di Luar Tanggungan Negara' => 'CLTN',
                            default                         => 'C',
                        };

                        $sheet->setCellValue($cellCoord, $cutiCode);
                        $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDC2626');
                        $sheet->getStyle($cellCoord)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'))->setSize(8);

                        $commentText = "Pegawai: {$emp['nama']}\nJenis Cuti: {$takenLeave['jenis_cuti']}\nPeriode: {$takenLeave['tanggal_mulai']} s/d {$takenLeave['tanggal_selesai']}\nAlasan: {$takenLeave['alasan_cuti']}\nStatus: " . ucfirst($takenLeave['status']);
                        $sheet->getComment($cellCoord)->getText()->createTextRun($commentText);
                    } else {
                        $sheet->setCellValue($cellCoord, '');
                    }
                }

                $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $cColIdx++;
            }

            $rowNum++;
            $no++;
        }

        $lastEmpRow = $rowNum - 1;

        // 7. Baris Rekapitulasi Total (Footer)
        $summaryRow = $rowNum;
        $sheet->mergeCells('A' . $summaryRow . ':D' . $summaryRow);
        $sheet->setCellValue('A' . $summaryRow, 'TOTAL PEGAWAI CUTI PER HARI');
        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $summaryRow . ':D' . $summaryRow)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A' . $summaryRow . ':D' . $summaryRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // Total akumulasi seluruh hari cuti yang diambil
        $sheet->setCellValue('E' . $summaryRow, '=SUM(E11:E' . $lastEmpRow . ')');
        $sheet->getStyle('E' . $summaryRow)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('E' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $summaryRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        // Total pegawai cuti per kolom tanggal
        $cColIdx = 6;
        foreach ($days as $d) {
            $colLetter = Coordinate::stringFromColumnIndex($cColIdx);
            $cellCoord = $colLetter . $summaryRow;

            $sheet->setCellValue($cellCoord, '=COUNTIF(' . $colLetter . '11:' . $colLetter . $lastEmpRow . ', "C*")');
            $sheet->getStyle($cellCoord)->getFont()->setBold(true)->setSize(8);
            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
            $cColIdx++;
        }

        // Format Garis Border Seluruh Tabel
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A8:' . $lastColLetter . $summaryRow)->applyFromArray($borderStyle);

        // Freeze Panes (Header baris 1-10 & Kolom Identitas Pegawai A-E tetap terkunci saat scroll)
        $sheet->freezePane('F11');

        // Tampilkan Gridlines
        $sheet->setShowGridLines(true);

        // Atur Halaman Cetak Landscape
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $filename = 'Rekap_Cuti_Pegawai_' . $startDate->format('Ymd') . '_sd_' . $endDate->format('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function bulanIndo(int $bulan): string
    {
        return match ($bulan) {
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
            default => '',
        };
    }

    /**
     * Generate docx content using PHPWord if available, or native ZipArchive fallback.
     */
    private function processWordDocx(string $templateFile, array $simpleReplacements, array $sequentialReplacements = []): ?string
    {
        if (! file_exists($templateFile)) {
            return null;
        }

        // Method 1: Use PHPWord TemplateProcessor if installed
        if (class_exists(\PhpOffice\PhpWord\TemplateProcessor::class)) {
            try {
                $processor = new \PhpOffice\PhpWord\TemplateProcessor($templateFile);
                foreach ($sequentialReplacements as $item) {
                    $processor->setValue($item['search'], $item['replace'], $item['limit'] ?? 1);
                }
                foreach ($simpleReplacements as $key => $val) {
                    $processor->setValue($key, (string) $val);
                }
                $tempPath = WRITEPATH . 'uploads/' . uniqid('word_', true) . '.docx';
                $processor->saveAs($tempPath);
                $content = file_get_contents($tempPath);
                @unlink($tempPath);
                return $content ?: null;
            } catch (\Throwable $e) {
                log_message('warning', 'PHPWord TemplateProcessor failed, falling back to native ZipArchive: ' . $e->getMessage());
            }
        }

        // Method 2: Native PHP ZipArchive fallback (no external library required)
        if (class_exists(\ZipArchive::class)) {
            $tempPath = WRITEPATH . 'uploads/' . uniqid('native_word_', true) . '.docx';
            if (! @copy($templateFile, $tempPath)) {
                return null;
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath) !== true) {
                @unlink($tempPath);
                return null;
            }

            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) {
                $zip->close();
                @unlink($tempPath);
                return null;
            }

            $xmlEscape = static function ($val): string {
                return htmlspecialchars((string) $val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            };

            // Sequential replacements with limit
            foreach ($sequentialReplacements as $item) {
                $search = '${' . $item['search'] . '}';
                $replace = $xmlEscape($item['replace']);
                $limit = $item['limit'] ?? 1;
                for ($i = 0; $i < $limit; $i++) {
                    $pos = strpos($xml, $search);
                    if ($pos === false) {
                        break;
                    }
                    $xml = substr_replace($xml, $replace, $pos, strlen($search));
                }
            }

            // Simple replacements
            $keys = [];
            $values = [];
            foreach ($simpleReplacements as $key => $val) {
                $keys[] = '${' . $key . '}';
                $values[] = $xmlEscape($val);
            }
            $xml = str_replace($keys, $values, $xml);

            $zip->addFromString('word/document.xml', $xml);
            $zip->close();

            $content = file_get_contents($tempPath);
            @unlink($tempPath);
            return $content ?: null;
        }

        return null;
    }

    private function getPermissions(): array
    {
        $role = strtolower(trim((string) session()->get('role')));
        $isSuperAdmin = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        $default = [
            'add' => $isSuperAdmin,
            'edit' => $isSuperAdmin,
            'delete' => $isSuperAdmin,
            'export' => $isSuperAdmin,
            'import' => $isSuperAdmin,
            'approval' => $isSuperAdmin,
        ];

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink('admin/surat/cuti', $db);
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
            'add' => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit' => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete' => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export' => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import' => (bool) ((int) ($row['FiturImport'] ?? 0)),
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

        return match ($normalized) {
            'admin' => 1,
            'editor' => 2,
            default => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table) || ! $db->fieldExists('link', $table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower(trim($menuLink)))
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
