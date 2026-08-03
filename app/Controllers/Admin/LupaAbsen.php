<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LupaAbsenModel;
use App\Models\MstPegawaiModel;
use App\Models\AppSettingModel;
use App\Models\HomeSettingModel;
use CodeIgniter\HTTP\RedirectResponse;

class LupaAbsen extends BaseController
{
    public function index()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if ($this->request->isAJAX() || $this->isDataTableRequest()) {
            return $this->dataTable();
        }

        $role = strtolower((string) session()->get('role'));
        $canApprove = in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        // Get current employee data for modal display
        $pegawaiData = $this->getCurrentPegawaiData();

        $db = db_connect();
        
        // Self-healing database migration
        try {
            $fields = $db->getFieldData('lupa_absen');
            $hasKopSuratCol = false;
            foreach ($fields as $f) {
                if (strtolower((string) $f->name) === 'kop_surat_id') {
                    $hasKopSuratCol = true;
                    break;
                }
            }
            if (! $hasKopSuratCol) {
                $db->query("ALTER TABLE lupa_absen ADD COLUMN kop_surat_id INT UNSIGNED NULL AFTER jabatan_id;");
            }
        } catch (\Throwable $e) {
            // Ignore if any DB exception occurs
        }

        $kopSuratList = [];
        try {
            if ($db->tableExists('kop_surat')) {
                $kopSuratList = $db->table('kop_surat')
                    ->select('id, title AS nama, is_active')
                    ->orderBy('is_active', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getResultArray();
            }
        } catch (\Throwable $e) {
            // Fallback empty array
        }

        return view('admin/surat/lupa_absen', [
            'title' => 'Lupa Absen',
            'can_edit' => $this->canAccess(),
            'can_approve' => $canApprove,
            'current_pegawai' => $pegawaiData,
            'kop_surat_list' => $kopSuratList,
        ]);
    }

    private function dataTable()
    {
        $canEdit = $this->canAccess();
        $role = strtolower((string) session()->get('role'));
        $canApprove = in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        $draw = $this->getDataTableDraw();
        $start = $this->getDataTableStart();
        $length = $this->getDataTableLength();
        $search = $this->getDataTableSearchTerm();
        $orderIndex = $this->getDataTableOrderColumnIndex();
        $orderDirection = $this->getDataTableOrderDirection();

        $db = db_connect();

        // Check if table exists
        if (! $db->tableExists('lupa_absen')) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $builder = $db->table('lupa_absen');

        $recordsTotal = (int) $builder->countAllResults(false);

        // Search
        if ($search !== '') {
            $builder->groupStart();
            $builder->like('nip', $search);
            $builder->orLike('nama', $search);
            $builder->orLike('alasan_detail', $search);
            $builder->groupEnd();
        }

        $recordsFiltered = (int) $builder->countAllResults(false);

        // Order
        $orderColumns = ['id', 'nip', 'nama', 'tanggal_absen', 'jenis_absen', 'alasan_detail', 'status', 'id'];
        $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0];
        $builder->orderBy($orderColumn, $orderDirection);

        $rows = $builder->limit($length, $start)->get()->getResultArray();

        // Map data
        $data = array_map(function (array $row) use ($canEdit, $canApprove) {
            $status = trim((string) ($row['status'] ?? 'pending'));
            $statusBadge = match ($status) {
                'disetujui' => '<span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>',
                'ditolak' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>',
                default => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            };

            $row['status_badge'] = $statusBadge;
            $row['tanggal_formatted'] = !empty($row['tanggal_absen']) ? date('d/m/Y', strtotime($row['tanggal_absen'])) : '-';

            $jenis = strtolower(trim((string) ($row['jenis_absen'] ?? '')));
            $row['jenis_formatted'] = $jenis === 'masuk' ? '<span class="badge badge-info">Masuk</span>' : '<span class="badge badge-secondary">Pulang</span>';

            // Dokumen button
            $dokumenHtml = '<div class="doc-btn-group">';
            $dokumenHtml .= '<a href="' . site_url('admin/surat/lupa-absen/' . (int) ($row['id'] ?? 0) . '/pdf') . '" class="btn btn-danger" title="Download PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>';
            $dokumenHtml .= '</div>';
            $row['dokumen_html'] = $dokumenHtml;

            $actions = '';
            if (($canEdit || $canApprove) && $status === 'pending') {
                $actions .= '<div class="d-flex justify-content-center align-items-center" style="gap: 5px; white-space: nowrap;">';
                if ($canEdit) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="' . (int) ($row['id'] ?? 0) . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . (int) ($row['id'] ?? 0) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }
                if ($canApprove) {
                    $actions .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . (int) ($row['id'] ?? 0) . '" title="Setujui"><i class="fas fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . (int) ($row['id'] ?? 0) . '" title="Tolak"><i class="fas fa-times"></i></button>';
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

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url('admin/surat/lupa-absen'));
        }

        return $this->simpanLupaAbsen();
    }

    private function simpanLupaAbsen()
    {
        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jabatanId = (int) $this->request->getPost('jabatan_id');
        $jabatan = trim((string) $this->request->getPost('jabatan') ?? '');
        $unitKerja = trim((string) $this->request->getPost('unit_kerja') ?? '');
        $tanggalAbsen = trim((string) $this->request->getPost('tanggal_absen'));
        $jenisAbsen = strtolower(trim((string) $this->request->getPost('jenis_absen')));
        $alasanDetail = trim((string) $this->request->getPost('alasan_detail'));
        $kopSuratId = (int) $this->request->getPost('kop_surat_id');

        $errors = [];

        if ($nama === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if ($nip === '') {
            $errors[] = 'NIP wajib diisi.';
        }
        if ($tanggalAbsen === '') {
            $errors[] = 'Tanggal absen wajib diisi.';
        }
        if (! in_array($jenisAbsen, ['masuk', 'pulang'], true)) {
            $errors[] = 'Jenis absen tidak valid.';
        }
        if ($alasanDetail === '') {
            $errors[] = 'Alasan wajib diisi.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', implode(' ', $errors));
        }

        $model = new LupaAbsenModel();
        $username = trim((string) session()->get('username'));

        // Nomor surat akan di-generate saat PDF di-generate
        $data = [
            'nama' => $nama,
            'nip' => $nip,
            'jabatan_id' => $jabatanId ?: null,
            'jabatan' => $jabatan,
            'unit_kerja' => $unitKerja,
            'tanggal_absen' => $tanggalAbsen,
            'jenis_absen' => $jenisAbsen,
            'alasan_detail' => $alasanDetail,
            'nomor_surat' => null, // Akan di-generate saat PDF
            'status' => 'pending',
            'created_by' => $username,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'kop_surat_id' => $kopSuratId > 0 ? $kopSuratId : null,
        ];

        $insertId = $model->insert($data);

        if ($insertId === false) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Pengajuan lupa absen berhasil disimpan.');
    }

    public function ubah(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new LupaAbsenModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data tidak ditemukan.');
        }

        if (($existing['status'] ?? '') !== 'pending') {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data yang sudah diproses tidak dapat diubah.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url('admin/surat/lupa-absen'));
        }

        $tanggalAbsen = trim((string) $this->request->getPost('tanggal_absen'));
        $jenisAbsen = strtolower(trim((string) $this->request->getPost('jenis_absen')));
        $alasanDetail = trim((string) $this->request->getPost('alasan_detail'));
        $kopSuratId = (int) $this->request->getPost('kop_surat_id');

        $errors = [];

        if ($tanggalAbsen === '') {
            $errors[] = 'Tanggal absen wajib diisi.';
        }
        if (! in_array($jenisAbsen, ['masuk', 'pulang'], true)) {
            $errors[] = 'Jenis absen tidak valid.';
        }
        if ($alasanDetail === '') {
            $errors[] = 'Alasan wajib diisi.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', implode(' ', $errors));
        }

        $data = [
            'tanggal_absen' => $tanggalAbsen,
            'jenis_absen' => $jenisAbsen,
            'alasan_detail' => $alasanDetail,
            'updated_at' => date('Y-m-d H:i:s'),
            'kop_surat_id' => $kopSuratId > 0 ? $kopSuratId : null,
        ];

        if ($model->update($id, $data) === false) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Gagal memperbarui data. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Pengajuan lupa absen berhasil diperbarui.');
    }

    public function pdf(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new LupaAbsenModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data tidak ditemukan.');
        }

        // Generate nomor surat on-the-fly if not set
        $nomorSurat = $row['nomor_surat'] ?? '';
        if (empty($nomorSurat)) {
            $nomorSurat = $this->generateNomorSurat($id);
            // Update the record with the generated nomor_surat
            $model->update($id, ['nomor_surat' => $nomorSurat]);
        }

        // Get app settings for header
        $appSetting = [
            'app_name' => 'KEMENTERIAN PEKERJAAN UMUM',
            'official_name' => 'Satuan Kerja Prasarana Strategis Riau',
            'logo_url' => '',
        ];

        try {
            $setting = (new AppSettingModel())->first();
            if (is_array($setting)) {
                $appSetting = array_merge($appSetting, $setting);
            }
        } catch (\Throwable $e) {
            // Use defaults
        }

        $homeSetting = [
            'official_name' => 'Satuan Kerja Prasarana Strategis Riau',
            'logo_url' => '',
        ];

        try {
            $setting = (new HomeSettingModel())->first();
            if (is_array($setting)) {
                $homeSetting = array_merge($homeSetting, $setting);
            }
        } catch (\Throwable $e) {
            // Use defaults
        }

        // Fetch employee golongan from database
        $db = db_connect();
        $pegawai = $db->table('mst_pegawai')
            ->select('golongan')
            ->where('nip', $row['nip'])
            ->get()
            ->getRowArray();
        $golongan = $pegawai['golongan'] ?? '';

        $data = [
            'app_name' => $appSetting['app_name'] ?? 'KEMENTERIAN PEKERJAAN UMUM',
            'official_name' => $homeSetting['official_name'] ?? 'Satuan Kerja Prasarana Strategis Riau',
            'kop_surat_logo' => !empty($appSetting['app_logo_url']) ? base_url($appSetting['app_logo_url']) : '',
            'nomor_surat' => $nomorSurat,
            'nama' => $row['nama'] ?? '',
            'nip' => $row['nip'] ?? '',
            'golongan' => $golongan,
            'jabatan' => $row['jabatan'] ?? '',
            'unit_kerja' => $row['unit_kerja'] ?? '',
            'tanggal_absen' => $row['tanggal_absen'] ?? '',
            'jenis_absen' => $row['jenis_absen'] ?? '',
            'alasan_detail' => $row['alasan_detail'] ?? '',
            'tanggal_surat' => !empty($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : date('Y-m-d'),
            'kop_surat_id' => isset($row['kop_surat_id']) ? (int) $row['kop_surat_id'] : null,
        ];

        $html = view('admin/surat/lupa_absen_pdf', $data);

        // Generate PDF using Dompdf
        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'surat_lupa_absen_' . ($row['nip'] ?? 'unknown') . '_' . date('Ymd') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    private function generateNomorSurat(int $id): string
    {
        $year = date('Y');

        return sprintf('KP0602/B/Gs7/%s/', $year);
    }

    public function hapus(int $id): RedirectResponse
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new LupaAbsenModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Data berhasil dihapus.');
    }

    public function approve(int $id): RedirectResponse
    {
        $role = strtolower((string) session()->get('role'));
        if (! in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Anda tidak memiliki akses untuk menyetujui.');
        }

        $model = new LupaAbsenModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data tidak ditemukan.');
        }

        if (($existing['status'] ?? '') !== 'pending') {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data sudah diproses sebelumnya.');
        }

        $username = trim((string) session()->get('username'));

        $model->update($id, [
            'status' => 'disetujui',
            'approved_by' => $username,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Pengajuan lupa absen telah disetujui.');
    }

    public function reject(int $id): RedirectResponse
    {
        $role = strtolower((string) session()->get('role'));
        if (! in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Anda tidak memiliki akses untuk menolak.');
        }

        $model = new LupaAbsenModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data tidak ditemukan.');
        }

        if (($existing['status'] ?? '') !== 'pending') {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data sudah diproses sebelumnya.');
        }

        $username = trim((string) session()->get('username'));

        $model->update($id, [
            'status' => 'ditolak',
            'approved_by' => $username,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Pengajuan lupa absen telah ditolak.');
    }

    /**
     * Get current logged-in employee's data from mst_pegawai table
     */
    private function getCurrentPegawaiData(): array
    {
        $db = db_connect();

        if (! $db->tableExists('mst_pegawai')) {
            return [
                'nama' => session()->get('fullName') ?? session()->get('username') ?? '',
                'nip' => '',
                'jabatan_id' => 0,
                'jabatan' => '',
                'unit_kerja' => '',
            ];
        }

        $username = trim((string) session()->get('username'));
        $fullName = trim((string) (session()->get('fullName') ?? ''));

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
                'jabatan_id' => 0,
                'jabatan' => '',
                'unit_kerja' => '',
            ];
        }

        return [
            'nama' => trim((string) ($pegawai['nama'] ?? '')),
            'nip' => trim((string) ($pegawai['nip'] ?? '')),
            'jabatan_id' => (int) ($pegawai['jabatan_utama_id'] ?? 0),
            'jabatan' => trim((string) ($pegawai['jabatan_label'] ?? '')),
            'unit_kerja' => trim((string) ($pegawai['unit_kerja'] ?? '')) ?: 'Satuan Kerja Prasarana Strategis Riau',
        ];
    }

    private function canAccess(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
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
}
