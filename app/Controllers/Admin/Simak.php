<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SimakModel;
use App\Models\SimakFileModel;

class Simak extends BaseController
{
    protected $simakModel;
    protected $simakFileModel;

    public function __construct()
    {
        $this->simakModel = new SimakModel();
        $this->simakFileModel = new SimakFileModel();
    }

    // LIST SIMAK
    public function index()
    {
        if (!$this->canViewSimak()) {
            return redirect()->to(site_url('/admin'))->with('error', 'Anda tidak memiliki akses untuk melihat SIMAK.');
        }

        $search = $this->request->getGet('search') ?? '';
        $tahun = $this->request->getGet('tahun') ?? '';

        $builder = $this->simakModel->getNotDeleted();

        if ($search !== '') {
            $builder->like('nama_paket', $search)
                ->orLike('nomor_kontrak', $search)
                ->orLike('satker', $search)
                ->orLike('ppk', $search);
        }

        if ($tahun !== '') {
            $builder->where('tahun_anggaran', $tahun);
        }

        $data = [
            'title' => 'SIMAK',
            'items' => $builder->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->simakModel->pager,
            'search' => $search,
            'tahun' => $tahun,
            'canCreate' => $this->canManageSimak(),
            'canEdit' => $this->canManageSimak(),
        ];

        return view('admin/simak/index', $data);
    }

    // SHOW / DETAIL SIMAK
    public function show($id)
    {
        if (!$this->canViewSimak()) {
            return redirect()->to(site_url('/admin'))->with('error', 'Anda tidak memiliki akses untuk melihat SIMAK.');
        }

        $simak = $this->simakModel->getWithFiles($id);

        if (!$simak) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        return view('admin/simak/show', [
            'title' => 'Detail SIMAK',
            'simak' => $simak,
            'canEdit' => $this->canManageSimak(),
            'canDelete' => $this->canManageSimak(),
        ]);
    }

    // FORM CREATE
    public function create()
    {
        if (!$this->canManageSimak()) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Anda tidak memiliki akses untuk membuat SIMAK.');
        }

        return view('admin/simak/form', [
            'title' => 'Tambah SIMAK',
            'simak' => null,
        ]);
    }

    // FORM EDIT
    public function edit($id)
    {
        if (!$this->canManageSimak()) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Anda tidak memiliki akses untuk mengubah SIMAK.');
        }

        $simak = $this->simakModel->find($id);

        if (!$simak) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        if ($simak['deleted_at'] !== null) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Data SIMAK telah dihapus.');
        }

        return view('admin/simak/form', [
            'title' => 'Ubah SIMAK',
            'simak' => $simak,
        ]);
    }

    // STORE (CREATE / UPDATE)
    public function store()
    {
        if (!$this->canManageSimak()) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Anda tidak memiliki akses.');
        }

        $id = $this->request->getPost('id');
        $is_update = !empty($id);

        $rules = [
            'nama_paket' => 'required|min_length[3]|max_length[255]',
            'tahun_anggaran' => 'required|min_length[4]|max_length[50]',
            'satker' => 'max_length[255]',
            'ppk' => 'max_length[255]',
            'nip' => 'max_length[50]',
            'penyedia' => 'max_length[255]',
            'nomor_kontrak' => 'max_length[255]',
            'tanggal_kontrak' => 'valid_date[Y-m-d]',
            'nilai_kontrak' => 'numeric',
            'nilai_add_kontrak' => 'numeric',
        ];

        if (!$this->validate($rules)) {
            if ($is_update) {
                return redirect()->back()->withInput()->with('validation', $this->validator);
            } else {
                return redirect()->back()->withInput()->with('validation', $this->validator);
            }
        }

        $payload = [
            'nama_paket' => trim($this->request->getPost('nama_paket')),
            'tahun_anggaran' => trim($this->request->getPost('tahun_anggaran')),
            'satker' => trim($this->request->getPost('satker') ?? ''),
            'ppk' => trim($this->request->getPost('ppk') ?? ''),
            'nip' => trim($this->request->getPost('nip') ?? ''),
            'penyedia' => trim($this->request->getPost('penyedia') ?? ''),
            'nomor_kontrak' => trim($this->request->getPost('nomor_kontrak') ?? ''),
            'tanggal_kontrak' => trim($this->request->getPost('tanggal_kontrak') ?? null),
            'nilai_kontrak' => trim($this->request->getPost('nilai_kontrak') ?? null),
            'nilai_add_kontrak' => trim($this->request->getPost('nilai_add_kontrak') ?? null),
            'status' => 'draft',
            'catatan' => trim($this->request->getPost('catatan') ?? ''),
            'updated_by' => session()->get('username') ?: session()->get('name') ?: 'system',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!$is_update) {
            $payload['created_by'] = session()->get('username') ?: session()->get('name') ?: 'system';
            $payload['created_at'] = date('Y-m-d H:i:s');
        }

        try {
            if ($is_update) {
                $this->simakModel->update($id, $payload);
                $message = 'SIMAK berhasil diubah.';
            } else {
                $id = $this->simakModel->insert($payload);
                $message = 'SIMAK berhasil ditambahkan.';
            }

            return redirect()->to(site_url('admin/paket/simak/' . $id))->with('success', $message);
        } catch (\Exception $e) {
            log_message('error', 'SIMAK Store Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    // UPLOAD FILE (HISTORICAL)
    public function uploadFile($id)
    {
        if (!$this->canManageSimak()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $simak = $this->simakModel->find($id);
        if (!$simak) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data SIMAK tidak ditemukan.']);
        }

        $file = $this->request->getFile('file');

        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak diunggah.']);
        }

        if (!$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak valid: ' . $file->getErrorString()]);
        }

        // Validasi tipe file (Excel)
        $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya file Excel (.xls, .xlsx) yang diperbolehkan.']);
        }

        // Generate nama file unik
        $fileName = 'simak_' . $id . '_' . time() . '.' . $file->getExtension();
        $uploadPath = 'uploads/simak/';

        try {
            // Buat direktori jika belum ada
            if (!is_dir(FCPATH . $uploadPath)) {
                mkdir(FCPATH . $uploadPath, 0755, true);
            }

            // Move file
            $file->move(FCPATH . $uploadPath, $fileName);

            // Simpan ke database
            $fileData = [
                'simak_id' => $id,
                'file_path' => $uploadPath . $fileName,
                'file_name' => $file->getClientName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => session()->get('username') ?: session()->get('name') ?: 'system',
                'uploaded_date' => date('Y-m-d H:i:s'),
            ];

            $this->simakFileModel->insert($fileData);

            // Update status SIMAK
            $this->simakModel->update($id, [
                'status' => 'submitted',
                'updated_by' => session()->get('username') ?: session()->get('name') ?: 'system',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'File berhasil diupload.',
                'file' => $fileData,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'SIMAK File Upload Error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat mengupload file.']);
        }
    }

    // DOWNLOAD FILE
    public function downloadFile($fileId)
    {
        if (!$this->canViewSimak()) {
            return redirect()->to(site_url('/admin'))->with('error', 'Akses ditolak.');
        }

        $file = $this->simakFileModel->find($fileId);
        if (!$file) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $filePath = FCPATH . $file['file_path'];
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        return $this->response->download($filePath, null);
    }

    // DELETE FILE
    public function deleteFile($fileId)
    {
        if (!$this->canManageSimak()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $file = $this->simakFileModel->find($fileId);
        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak ditemukan.']);
        }

        try {
            // Hapus file dari server
            $filePath = FCPATH . $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Hapus record dari database
            $this->simakFileModel->delete($fileId);

            return $this->response->setJSON(['success' => true, 'message' => 'File berhasil dihapus.']);
        } catch (\Exception $e) {
            log_message('error', 'SIMAK File Delete Error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus file.']);
        }
    }

    // DELETE SIMAK
    public function delete($id)
    {
        if (!$this->canManageSimak()) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Anda tidak memiliki akses untuk menghapus SIMAK.');
        }

        $simak = $this->simakModel->find($id);
        if (!$simak) {
            return redirect()->to(site_url('admin/paket/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        try {
            $this->simakModel->update($id, [
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => session()->get('username') ?: session()->get('name') ?: 'system',
            ]);

            return redirect()->to(site_url('admin/paket/simak'))->with('success', 'SIMAK berhasil dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'SIMAK Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus SIMAK.');
        }
    }

    // PERMISSION HELPERS
    private function canViewSimak(): bool
    {
        // Same permission as Kontrak
        return $this->canViewKontrak();
    }

    private function canManageSimak(): bool
    {
        // Same permission as Kontrak
        return $this->canManageKontrak();
    }

    private function canViewKontrak(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function canManageKontrak(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }
}
