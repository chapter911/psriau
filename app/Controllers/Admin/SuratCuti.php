<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SuratCutiModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;

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
