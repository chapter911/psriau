<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SimakPaketModel;

class Simak extends BaseController
{
    protected $paketModel;

    public function __construct()
    {
        $this->paketModel = new SimakPaketModel();
    }

    public function paket()
    {
        if (! $this->checkAccess('admin/simak/paket')) {
            return redirect()->to('/admin/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = [
            'title' => 'Simak Paket',
            'description' => 'Kelola data paket simak konstruksi',
        ];

        return view('admin/simak/paket/index', $data);
    }

    public function dataTable()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('simak_paket');

        // Filters
        $namaPacket = trim((string) $this->request->getGet('nama_paket'));
        $tahunAnggaran = trim((string) $this->request->getGet('tahun_anggaran'));
        $penyedia = trim((string) $this->request->getGet('penyedia'));

        if ($namaPacket !== '') {
            $builder->like('nama_paket', $namaPacket);
        }

        if ($tahunAnggaran !== '') {
            $builder->where('tahun_anggaran', (int) $tahunAnggaran);
        }

        if ($penyedia !== '') {
            $builder->like('penyedia', $penyedia);
        }

        $recordsTotal = $db->table('simak_paket')->countAllResults();
        $recordsFiltered = $builder->countAllResults();

        // Ordering
        $orderBy = 'created_date';
        $orderDir = 'DESC';
        $builder->orderBy($orderBy, $orderDir);

        // Pagination
        $start = (int) $this->request->getGet('start') ?? 0;
        $length = (int) $this->request->getGet('length') ?? 10;
        $builder->limit($length, $start);

        $rows = $builder->get()->getResultArray();

        $data = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $nilaiKontrak = (float) ($row['nilai_kontrak'] ?? 0);

            $data[] = [
                'id' => $id,
                'nama_paket' => (string) ($row['nama_paket'] ?? '-'),
                'tahun_anggaran' => (int) ($row['tahun_anggaran'] ?? 0),
                'penyedia' => (string) ($row['penyedia'] ?? '-'),
                'nomor_kontrak' => (string) ($row['nomor_kontrak'] ?? '-'),
                'nilai_kontrak' => $nilaiKontrak,
                'add_kontrak' => (string) ($row['add_kontrak'] ?? ''),
                'tahapan_pekerjaan' => (string) ($row['tahapan_pekerjaan'] ?? '-'),
                'tanggal_pemeriksaan' => (string) ($row['tanggal_pemeriksaan'] ?? '-'),
                'satker' => (string) ($row['satker'] ?? '-'),
                'ppk' => (string) ($row['ppk'] ?? '-'),
                'nip' => (string) ($row['nip'] ?? '-'),
                'created_by' => (string) ($row['created_by'] ?? '-'),
                'created_date' => (string) ($row['created_date'] ?? '-'),
                'edit_url' => site_url('/admin/simak/paket/ubah/' . $id),
                'delete_url' => site_url('/admin/simak/paket/hapus/' . $id),
            ];
        }

        return $this->response->setJSON([
            'draw' => (int) $this->request->getGet('draw') ?? 0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create()
    {
        if (! $this->checkAccess('admin/simak/paket') || empty($this->getMenuAccess('admin/simak/paket')['FiturAdd'])) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $data = [
            'title' => 'Tambah Paket Simak',
            'mode' => 'create',
        ];

        return view('admin/simak/paket/form', $data);
    }

    public function store()
    {
        if (! $this->checkAccess('admin/simak/paket') || empty($this->getMenuAccess('admin/simak/paket')['FiturAdd'])) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $userId = session()->get('userId') ?: 'system';

        $payload = [
            'nama_paket' => trim((string) $this->request->getPost('nama_paket')) ?: '',
            'tahun_anggaran' => (int) $this->request->getPost('tahun_anggaran') ?: 0,
            'penyedia' => trim((string) $this->request->getPost('penyedia')) ?: '',
            'nomor_kontrak' => trim((string) $this->request->getPost('nomor_kontrak')) ?: '',
            'nilai_kontrak' => (float) str_replace(['Rp', '.', ' '], ['', '', ''], (string) $this->request->getPost('nilai_kontrak')) ?: 0,
            'add_kontrak' => trim((string) $this->request->getPost('add_kontrak')) ?: null,
            'tahapan_pekerjaan' => trim((string) $this->request->getPost('tahapan_pekerjaan')) ?: null,
            'tanggal_pemeriksaan' => trim((string) $this->request->getPost('tanggal_pemeriksaan')) ?: null,
            'satker' => trim((string) $this->request->getPost('satker')) ?: null,
            'ppk' => trim((string) $this->request->getPost('ppk')) ?: null,
            'nip' => trim((string) $this->request->getPost('nip')) ?: null,
            'created_by' => $userId,
        ];

        if ($payload['nama_paket'] === '' || $payload['tahun_anggaran'] === 0) {
            return redirect()->back()->withInput()->with('error', 'Nama paket dan tahun anggaran harus diisi.');
        }

        if ($this->paketModel->insert($payload)) {
            return redirect()->to('/admin/simak/paket')->with('message', 'Data paket berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data paket.');
    }

    public function edit(int $id)
    {
        if (! $this->checkAccess('admin/simak/paket') || empty($this->getMenuAccess('admin/simak/paket')['FiturEdit'])) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $paket = $this->paketModel->find($id);
        if (! $paket) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Data paket tidak ditemukan.');
        }

        $data = [
            'title' => 'Ubah Paket Simak',
            'mode' => 'edit',
            'paket' => $paket,
        ];

        return view('admin/simak/paket/form', $data);
    }

    public function update(int $id)
    {
        if (! $this->checkAccess('admin/simak/paket') || empty($this->getMenuAccess('admin/simak/paket')['FiturEdit'])) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Anda tidak memiliki akses untuk mengubah data.');
        }

        $paket = $this->paketModel->find($id);
        if (! $paket) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Data paket tidak ditemukan.');
        }

        $userId = session()->get('userId') ?: 'system';

        $payload = [
            'nama_paket' => trim((string) $this->request->getPost('nama_paket')) ?: '',
            'tahun_anggaran' => (int) $this->request->getPost('tahun_anggaran') ?: 0,
            'penyedia' => trim((string) $this->request->getPost('penyedia')) ?: '',
            'nomor_kontrak' => trim((string) $this->request->getPost('nomor_kontrak')) ?: '',
            'nilai_kontrak' => (float) str_replace(['Rp', '.', ' '], ['', '', ''], (string) $this->request->getPost('nilai_kontrak')) ?: 0,
            'add_kontrak' => trim((string) $this->request->getPost('add_kontrak')) ?: null,
            'tahapan_pekerjaan' => trim((string) $this->request->getPost('tahapan_pekerjaan')) ?: null,
            'tanggal_pemeriksaan' => trim((string) $this->request->getPost('tanggal_pemeriksaan')) ?: null,
            'satker' => trim((string) $this->request->getPost('satker')) ?: null,
            'ppk' => trim((string) $this->request->getPost('ppk')) ?: null,
            'nip' => trim((string) $this->request->getPost('nip')) ?: null,
            'updated_by' => $userId,
            'updated_date' => date('Y-m-d H:i:s'),
        ];

        if ($payload['nama_paket'] === '' || $payload['tahun_anggaran'] === 0) {
            return redirect()->back()->withInput()->with('error', 'Nama paket dan tahun anggaran harus diisi.');
        }

        if ($this->paketModel->update($id, $payload)) {
            return redirect()->to('/admin/simak/paket')->with('message', 'Data paket berhasil diubah.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal mengubah data paket.');
    }

    public function delete(int $id)
    {
        if (! $this->checkAccess('admin/simak/paket') || empty($this->getMenuAccess('admin/simak/paket')['FiturDelete'])) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $paket = $this->paketModel->find($id);
        if (! $paket) {
            return redirect()->to('/admin/simak/paket')->with('error', 'Data paket tidak ditemukan.');
        }

        if ($this->paketModel->delete($id)) {
            return redirect()->to('/admin/simak/paket')->with('message', 'Data paket berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data paket.');
    }

    private function checkAccess(string $menu): bool
    {
        if (! session()->has('userId')) {
            return false;
        }

        $menuAccess = $this->getMenuAccess($menu);
        return ! empty($menuAccess);
    }

    private function getMenuAccess(string $link): array
    {
        $userId = session()->get('userId');
        $roleId = session()->get('roleId') ?? session()->get('group_id');

        if (! $userId || ! $roleId) {
            return [];
        }

        $roleColumn = \Config\Database::connect()->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $menu = \Config\Database::connect()
            ->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', (function ($builder) use ($link) {
                $builder->select('id')->from('menu_lv2')->where('LOWER(link)', strtolower($link));
            }))
            ->get()
            ->getRowArray();

        return $menu ?: [];
    }
}
