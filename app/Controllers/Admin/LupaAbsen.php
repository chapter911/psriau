<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LupaAbsenModel;
use App\Models\MstPegawaiModel;
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
            'pegawai_options' => $this->loadPegawaiOptions(),
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
            $builder->orLike('keterangan', $search);
            $builder->groupEnd();
        }

        $recordsFiltered = (int) $builder->countAllResults(false);

        // Order
        $orderColumns = ['id', 'nip', 'nama', 'tanggal_absen', 'jenis_absen', 'jam_absen', 'status', 'id'];
        $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0];
        $builder->orderBy($orderColumn, $orderDirection);

        $rows = $builder->limit($length, $start)->get()->getResultArray();

        // Map status
        $data = array_map(function (array $row) use ($canEdit, $canApprove) {
            $status = trim((string) ($row['status'] ?? 'pending'));
            $statusBadge = match ($status) {
                'disetujui' => '<span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>',
                'ditolak' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>',
                default => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            };

            $row['status_badge'] = $statusBadge;
            $row['tanggal_formatted'] = $row['tanggal_absen'] ? date('d/m/Y', strtotime($row['tanggal_absen'])) : '-';
            $row['jam_formatted'] = $row['jam_absen'] ?? '-';
            $row['jenis_formatted'] = $row['jenis_absen'] === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';

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
            return view('admin/surat/lupa_absen_form', [
                'title' => 'Ubah Lupa Absen',
                'current_input' => $existing,
                'form_error' => null,
                'is_edit' => true,
                'id' => $id,
            ]);
        }

        return $this->simpanLupaAbsen($id);
    }

    private function simpanLupaAbsen(int $id): RedirectResponse
    {
        $nip = trim((string) $this->request->getPost('nip'));
        $nama = trim((string) $this->request->getPost('nama'));
        $tanggalAbsen = trim((string) $this->request->getPost('tanggal_absen'));
        $jenisAbsen = trim((string) $this->request->getPost('jenis_absen'));
        $jamAbsen = trim((string) $this->request->getPost('jam_absen'));
        $keterangan = trim((string) $this->request->getPost('keterangan'));

        $errors = [];

        if ($nip === '') {
            $errors[] = 'NIP wajib diisi.';
        }
        if ($nama === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if ($tanggalAbsen === '') {
            $errors[] = 'Tanggal absen wajib diisi.';
        }
        if (! in_array($jenisAbsen, ['masuk', 'pulang'], true)) {
            $errors[] = 'Jenis absen tidak valid.';
        }
        if ($jamAbsen === '') {
            $errors[] = 'Jam absen wajib diisi.';
        }

        if ($errors !== []) {
            return view('admin/surat/lupa_absen_form', [
                'title' => $id > 0 ? 'Ubah Lupa Absen' : 'Ajukan Lupa Absen',
                'current_input' => [
                    'nip' => $nip,
                    'nama' => $nama,
                    'tanggal_absen' => $tanggalAbsen,
                    'jenis_absen' => $jenisAbsen,
                    'jam_absen' => $jamAbsen,
                    'keterangan' => $keterangan,
                ],
                'form_error' => implode(' ', $errors),
                'is_edit' => $id > 0,
                'id' => $id > 0 ? $id : null,
            ]);
        }

        $model = new LupaAbsenModel();
        $username = trim((string) session()->get('username'));

        $data = [
            'nip' => $nip,
            'nama' => $nama,
            'tanggal_absen' => $tanggalAbsen,
            'jenis_absen' => $jenisAbsen,
            'jam_absen' => $jamAbsen,
            'keterangan' => $keterangan,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $model->update($id, $data);
            return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Data berhasil diperbarui.');
        }

        $data['status'] = 'pending';
        $data['created_by'] = $username;
        $data['created_at'] = date('Y-m-d H:i:s');
        $model->insert($data);

        return redirect()->to(site_url('admin/surat/lupa-absen'))->with('success', 'Pengajuan lupa absen berhasil disimpan.');
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

    private function loadPegawaiOptions(): array
    {
        if (! db_connect()->tableExists('mst_pegawai')) {
            return [];
        }

        $rows = (new MstPegawaiModel())
            ->select('mst_pegawai.id, mst_pegawai.nip, mst_pegawai.nama, ju.jabatan AS jabatan_label, mst_pegawai.is_active')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.is_active', 1)
            ->orderBy('mst_pegawai.nama', 'ASC')
            ->findAll();

        return array_map(static function (array $row): array {
            $display = trim((string) ($row['nama'] ?? 'Pegawai'));
            $nip = trim((string) ($row['nip'] ?? ''));
            $jabatan = trim((string) ($row['jabatan_label'] ?? ''));

            if ($nip !== '') {
                $display .= ' | NIP ' . $nip;
            }
            if ($jabatan !== '') {
                $display .= ' | ' . $jabatan;
            }

            $row['display_label'] = $display;
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
