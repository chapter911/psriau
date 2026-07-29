<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StrukturOrganisasiModel;
use CodeIgniter\HTTP\ResponseInterface;

class StrukturOrganisasi extends BaseController
{
    protected StrukturOrganisasiModel $strukturModel;

    public function __construct()
    {
        $this->strukturModel = new StrukturOrganisasiModel();
    }

    public function index()
    {
        $db = db_connect();

        // 1. Fetch tree nodes
        $treeNodes = [];
        if ($db->tableExists('tb_struktur_organisasi')) {
            $treeNodes = $this->strukturModel->getTreeNodes();
        }

        // 2. Fetch list of active pegawai for selection modal
        $pegawaiList = [];
        if ($db->tableExists('mst_pegawai')) {
            $builder = $db->table('mst_pegawai p');
            $builder->select('p.id, p.nip, p.nama, p.foto, p.jenis_pegawai, p.eselon, p.golongan, j.jabatan AS jabatan_label');
            $builder->join('mst_jabatan j', 'j.id = p.jabatan_utama_id', 'left');
            $builder->where('p.is_active', 1);
            $builder->orderBy('p.nama', 'ASC');
            $pegawaiList = $builder->get()->getResultArray();
        }

        $data = [
            'title'       => 'Struktur Organisasi',
            'treeNodes'   => $treeNodes,
            'pegawaiList' => $pegawaiList,
        ];

        return view('admin/master/struktur_organisasi/index', $data);
    }

    public function getChartData(): ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('tb_struktur_organisasi')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tabel tb_struktur_organisasi belum tersedia.',
                'nodes'   => [],
            ]);
        }

        $treeNodes = $this->strukturModel->getTreeNodes();

        return $this->response->setJSON([
            'status' => 'success',
            'nodes'  => $treeNodes,
        ]);
    }

    public function saveNode(): ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('tb_struktur_organisasi')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tabel tb_struktur_organisasi belum tersedia.',
            ]);
        }

        $id              = $this->request->getPost('id');
        $parentId        = $this->request->getPost('parent_id');
        $sourceType      = $this->request->getPost('source_type') ?? 'master';
        $pegawaiId       = $this->request->getPost('pegawai_id');
        $namaManual      = trim((string) $this->request->getPost('nama_manual'));
        $nipManual       = trim((string) $this->request->getPost('nip_manual'));
        $jabatanBagian   = trim((string) $this->request->getPost('jabatan_bagian'));
        $kategori        = trim((string) $this->request->getPost('kategori_kelompok'));
        $urutan          = (int) ($this->request->getPost('urutan') ?? 1);
        $level           = (int) ($this->request->getPost('level') ?? 1);

        if ($jabatanBagian === '') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Judul Posisi / Jabatan Bagan wajib diisi.',
            ]);
        }

        $payload = [
            'parent_id'         => ! empty($parentId) ? (int) $parentId : null,
            'jabatan_bagian'   => $jabatanBagian,
            'kategori_kelompok' => $kategori !== '' ? $kategori : 'utama',
            'urutan'            => $urutan > 0 ? $urutan : 1,
            'level'             => $level > 0 ? $level : 1,
            'is_active'         => 1,
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        if ($sourceType === 'manual') {
            $payload['pegawai_id']  = null;
            $payload['nama_manual'] = $namaManual !== '' ? $namaManual : null;
            $payload['nip_manual']  = $nipManual !== '' ? $nipManual : null;
        } else {
            $payload['pegawai_id']  = ! empty($pegawaiId) ? (int) $pegawaiId : null;
            $payload['nama_manual'] = null;
            $payload['nip_manual']  = null;
        }

        // Handle Custom Foto Upload for Manual Input
        $fotoFile = $this->request->getFile('foto_manual_file');
        if ($fotoFile && $fotoFile->isValid() && ! $fotoFile->hasMoved()) {
            $newName = $fotoFile->getRandomName();
            $uploadDir = ROOTPATH . 'public/uploads/struktur_organisasi';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fotoFile->move($uploadDir, $newName);
            $payload['foto_manual'] = 'uploads/struktur_organisasi/' . $newName;
        }

        try {
            if (! empty($id)) {
                $this->strukturModel->update((int) $id, $payload);
                $msg = 'Node posisi bagan berhasil diperbarui.';
            } else {
                $payload['created_at'] = date('Y-m-d H:i:s');
                $id = $this->strukturModel->insert($payload);
                $msg = 'Node posisi bagan baru berhasil ditambahkan.';
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $msg,
                'node_id' => $id,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menyimpan node: ' . $e->getMessage(),
            ]);
        }
    }

    public function saveBatchNodes(): ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('tb_struktur_organisasi')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tabel tb_struktur_organisasi belum tersedia.',
            ]);
        }

        $parentId      = $this->request->getPost('parent_id');
        $jabatanBagian = trim((string) ($this->request->getPost('jabatan_bagian') ?? 'Anggota'));
        $kategori      = trim((string) ($this->request->getPost('kategori_kelompok') ?? 'staf'));
        $pegawaiIds    = $this->request->getPost('pegawai_ids');

        if (empty($parentId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Posisi atasan wajib dipilih.',
            ]);
        }

        if (! is_array($pegawaiIds) || empty($pegawaiIds)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Pilih minimal satu pegawai untuk ditambahkan.',
            ]);
        }

        // Get max urutan under this parent
        $maxRow = $this->strukturModel
            ->selectMax('urutan', 'max_urutan')
            ->where('parent_id', (int) $parentId)
            ->first();

        $startUrutan = ((int) ($maxRow['max_urutan'] ?? 0)) + 1;

        // Get parent level
        $parentNode = $this->strukturModel->find((int) $parentId);
        $level = ((int) ($parentNode['level'] ?? 1)) + 1;

        $insertedCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($pegawaiIds as $pId) {
            $pId = (int) $pId;
            if ($pId <= 0) {
                continue;
            }

            $this->strukturModel->insert([
                'parent_id'         => (int) $parentId,
                'pegawai_id'        => $pId,
                'jabatan_bagian'   => $jabatanBagian !== '' ? $jabatanBagian : 'Anggota',
                'kategori_kelompok' => $kategori !== '' ? $kategori : 'staf',
                'urutan'            => $startUrutan++,
                'level'             => $level,
                'is_active'         => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $insertedCount++;
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Berhasil menambahkan {$insertedCount} anggota tim sekaligus.",
        ]);
    }

    public function deleteNode($id = null): ResponseInterface
    {
        $db = db_connect();

        if ($id === null) {
            $id = $this->request->getPost('id');
        }

        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID Node tidak valid.',
            ]);
        }

        try {
            // Check if node has children
            $childCount = $this->strukturModel->where('parent_id', $id)->countAllResults();
            if ($childCount > 0) {
                // Re-parent children to current parent_id
                $currentNode = $this->strukturModel->find($id);
                $parentOfCurrent = $currentNode['parent_id'] ?? null;
                $this->strukturModel->where('parent_id', $id)->set(['parent_id' => $parentOfCurrent])->update();
            }

            $this->strukturModel->delete($id);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Node bagan berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus node: ' . $e->getMessage(),
            ]);
        }
    }
}
