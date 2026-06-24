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

        return view('admin/surat/lupa_absen', [
            'title' => 'Lupa Absen',
            'can_edit' => $this->canAccess(),
            'can_approve' => $canApprove,
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

        if (! $db->tableExists('lupa_absen')) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $builder = $db->table('lupa_absen');

        // Filter by logged in user (non-admin can only see their own)
        $username = trim((string) session()->get('username'));
        $role = strtolower((string) session()->get('role'));
        if (! in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
            $builder->where('created_by', $username);
        }

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
        $orderColumns = ['id', 'nip', 'nama', 'tanggal_surat', 'id', 'status', 'id'];
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
            $row['tanggal_surat_formatted'] = !empty($row['tanggal_surat']) ? date('d/m/Y', strtotime($row['tanggal_surat'])) : '-';

            // Count entries
            $entries = json_decode((string) ($row['entries_json'] ?? '[]'), true);
            $row['jumlah_entri'] = is_array($entries) ? count($entries) : 0;

            // Dokumen button
            $dokumenHtml = '<div class="doc-btn-group">';
            $dokumenHtml .= '<a href="' . site_url('admin/surat/lupa-absen/' . (int) ($row['id'] ?? 0) . '/pdf') . '" class="btn btn-danger" title="Download PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>';
            $dokumenHtml .= '</div>';
            $row['dokumen_html'] = $dokumenHtml;

            $actions = '';
            if ($canEdit && $status === 'pending') {
                $actions .= '<a href="' . site_url('admin/surat/lupa-absen/' . (int) ($row['id'] ?? 0) . '/ubah') . '" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="fas fa-pen"></i></a> ';
                $actions .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . (int) ($row['id'] ?? 0) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
            }
            if ($canApprove && $status === 'pending') {
                $actions .= ' <button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . (int) ($row['id'] ?? 0) . '" title="Setujui"><i class="fas fa-check"></i></button>';
                $actions .= ' <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . (int) ($row['id'] ?? 0) . '" title="Tolak"><i class="fas fa-times"></i></button>';
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
            return view('admin/surat/lupa_absen_form', [
                'title' => 'Ajukan Lupa Absen',
                'current_input' => [],
                'form_error' => null,
                'is_edit' => false,
                'existing_entries' => [],
                'jabatan_options' => $this->loadJabatanOptions(),
            ]);
        }

        return $this->simpanLupaAbsen(0);
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

        // Non-admin can only edit their own pending submissions
        $role = strtolower((string) session()->get('role'));
        $username = trim((string) session()->get('username'));
        if (! in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
            if (($existing['created_by'] ?? '') !== $username) {
                return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Anda tidak memiliki akses untuk mengubah data ini.');
            }
            if (($existing['status'] ?? '') !== 'pending') {
                return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data yang sudah diproses tidak dapat diubah.');
            }
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            $entries = json_decode((string) ($existing['entries_json'] ?? '[]'), true);
            if (! is_array($entries)) {
                $entries = [];
            }

            return view('admin/surat/lupa_absen_form', [
                'title' => 'Ubah Lupa Absen',
                'current_input' => $existing,
                'form_error' => null,
                'is_edit' => true,
                'id' => $id,
                'existing_entries' => $entries,
                'jabatan_options' => $this->loadJabatanOptions(),
            ]);
        }

        return $this->simpanLupaAbsen($id);
    }

    private function simpanLupaAbsen(int $id): RedirectResponse
    {
        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jabatanId = (int) $this->request->getPost('jabatan_id');
        $jabatan = trim((string) $this->request->getPost('jabatan_display') ?? '');
        $unitKerja = trim((string) $this->request->getPost('unit_kerja'));
        $tanggalSurat = trim((string) $this->request->getPost('tanggal_surat'));
        $alasanKategori = trim((string) $this->request->getPost('alasan_kategori'));
        $alasanDetail = trim((string) $this->request->getPost('alasan_detail'));

        // Get entries from form
        $entriesRaw = $this->request->getPost('entries');
        $entries = [];
        if (is_array($entriesRaw)) {
            foreach ($entriesRaw as $entry) {
                if (is_array($entry) && !empty($entry['tanggal'])) {
                    $entries[] = [
                        'tanggal' => trim((string) ($entry['tanggal'] ?? '')),
                        'hari' => trim((string) ($entry['hari'] ?? '')),
                        'jam' => trim((string) ($entry['jam'] ?? '')),
                        'jenis' => trim((string) ($entry['jenis'] ?? '')),
                        'keterangan' => trim((string) ($entry['keterangan'] ?? '')),
                    ];
                }
            }
        }

        $errors = [];

        if ($nama === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if ($nip === '') {
            $errors[] = 'NIP wajib diisi.';
        }
        if ($jabatanId <= 0) {
            $errors[] = 'Jabatan wajib dipilih.';
        }
        if ($unitKerja === '') {
            $errors[] = 'Unit kerja wajib diisi.';
        }
        if ($tanggalSurat === '') {
            $errors[] = 'Tanggal surat wajib diisi.';
        }
        if ($alasanKategori === '') {
            $errors[] = 'Kategori alasan wajib dipilih.';
        }
        if ($alasanDetail === '') {
            $errors[] = 'Detail alasan wajib diisi.';
        }
        if (empty($entries)) {
            $errors[] = 'Minimal 1 entri absensi wajib diisi.';
        }

        // Get jabatan label from options
        $jabatanOptions = $this->loadJabatanOptions();
        $jabatanLabel = '';
        foreach ($jabatanOptions as $opt) {
            if ((int) ($opt['id'] ?? 0) === $jabatanId) {
                $jabatanLabel = $opt['jabatan'] ?? '';
                break;
            }
        }

        if ($errors !== []) {
            return view('admin/surat/lupa_absen_form', [
                'title' => $id > 0 ? 'Ubah Lupa Absen' : 'Ajukan Lupa Absen',
                'current_input' => [
                    'nama' => $nama,
                    'nip' => $nip,
                    'jabatan_id' => $jabatanId,
                    'unit_kerja' => $unitKerja,
                    'tanggal_surat' => $tanggalSurat,
                    'alasan_kategori' => $alasanKategori,
                    'alasan_detail' => $alasanDetail,
                ],
                'form_error' => implode(' ', $errors),
                'is_edit' => $id > 0,
                'id' => $id > 0 ? $id : null,
                'existing_entries' => $entries,
                'jabatan_options' => $jabatanOptions,
            ]);
        }

        $model = new LupaAbsenModel();
        $username = trim((string) session()->get('username'));

        // Generate nomor surat
        $nomorSurat = $this->generateNomorSurat($id > 0);

        $data = [
            'nama' => $nama,
            'nip' => $nip,
            'jabatan_id' => $jabatanId,
            'jabatan' => $jabatanLabel ?: $jabatan,
            'unit_kerja' => $unitKerja,
            'tanggal_surat' => $tanggalSurat,
            'nomor_surat' => $nomorSurat,
            'alasan_kategori' => $alasanKategori,
            'alasan_detail' => $alasanDetail,
            'entries_json' => json_encode($entries, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $model->update($id, $data);
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Data berhasil diperbarui.');
        }

        $data['created_by'] = $username;
        $data['created_at'] = date('Y-m-d H:i:s');
        $model->insert($data);

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Pengajuan lupa absen berhasil disimpan.');
    }

    private function generateNomorSurat(bool $isEdit): string
    {
        $db = db_connect();
        $year = date('Y');

        // Get max id for this year
        $builder = $db->table('lupa_absen')
            ->selectMax('id', 'max_id')
            ->get();

        $row = $builder->getRowArray();
        $nextNum = ((int) ($row['max_id'] ?? 0)) + 1;

        return sprintf('%03d/SUR.PERNYATAAN-LOA/%s', $nextNum, $year);
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

        // Parse entries
        $entries = json_decode((string) ($row['entries_json'] ?? '[]'), true);
        if (! is_array($entries)) {
            $entries = [];
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

        $data = [
            'app_name' => $appSetting['app_name'] ?? 'KEMENTERIAN PEKERJAAN UMUM',
            'official_name' => $homeSetting['official_name'] ?? 'Satuan Kerja Prasarana Strategis Riau',
            'kop_surat_logo' => !empty($appSetting['app_logo_url']) ? base_url($appSetting['app_logo_url']) : '',
            'nomor_surat' => $row['nomor_surat'] ?? '',
            'nama' => $row['nama'] ?? '',
            'nip' => $row['nip'] ?? '',
            'jabatan' => $row['jabatan'] ?? '',
            'unit_kerja' => $row['unit_kerja'] ?? '',
            'tanggal_surat' => $row['tanggal_surat'] ?? date('Y-m-d'),
            'alasan_kategori' => $row['alasan_kategori'] ?? '',
            'alasan_detail' => $row['alasan_detail'] ?? '',
            'entries' => $entries,
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
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function hapus(int $id): RedirectResponse
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $role = strtolower((string) session()->get('role'));
        $username = trim((string) session()->get('username'));

        $model = new LupaAbsenModel();
        $existing = $model->find($id);

        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data tidak ditemukan.');
        }

        // Non-admin can only delete their own pending submissions
        if (! in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
            if (($existing['created_by'] ?? '') !== $username) {
                return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Anda tidak memiliki akses untuk menghapus data ini.');
            }
            if (($existing['status'] ?? '') !== 'pending') {
                return redirect()->to(site_url('admin/surat/lupa-absen'))->with('error', 'Data yang sudah diproses tidak dapat dihapus.');
            }
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

    private function loadJabatanOptions(): array
    {
        if (! db_connect()->tableExists('mst_jabatan')) {
            return [];
        }

        $db = db_connect();
        $rows = $db->table('mst_jabatan')
            ->select('id, jabatan, jenis_jabatan')
            ->where('is_active', 1)
            ->orderBy('jabatan', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static function (array $row) {
            $row['display_label'] = trim((string) ($row['jabatan'] ?? ''));
            return $row;
        }, $rows);
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
