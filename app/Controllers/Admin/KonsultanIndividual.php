<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KonsultanKontrakModel;
use App\Models\KonsultanLaporanBulananModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\ResponseInterface;

class KonsultanIndividual extends BaseController
{
    protected KonsultanKontrakModel $kontrakModel;
    protected KonsultanLaporanBulananModel $laporanModel;

    public function __construct()
    {
        $this->kontrakModel = new KonsultanKontrakModel();
        $this->laporanModel = new KonsultanLaporanBulananModel();
    }

    // ==========================================
    // SUBMENU 1: KONTRAK
    // ==========================================

    public function kontrak()
    {
        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        $isKonsultan      = ($currentKonsultan !== null);
        $permissions       = $this->resolveMenuAksesPermissions('admin/konsultan-individual/kontrak');
        $konsultanList    = $this->getAllKonsultanPegawai();

        return view('admin/konsultan_individual/kontrak', [
            'title'             => 'Konsultan Individual - Kontrak',
            'current_konsultan' => $currentKonsultan,
            'is_konsultan'      => $isKonsultan,
            'permissions'       => $permissions,
            'konsultan_list'    => $konsultanList,
        ]);
    }

    public function kontrakData()
    {
        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        $isKonsultan      = ($currentKonsultan !== null);
        $permissions       = $this->resolveMenuAksesPermissions('admin/konsultan-individual/kontrak');

        $filterPegawaiId = null;
        if ($isKonsultan) {
            $filterPegawaiId = (int) $currentKonsultan['id'];
        } else {
            $requestedPegawai = (int) $this->request->getGet('pegawai_id');
            if ($requestedPegawai > 0) {
                $filterPegawaiId = $requestedPegawai;
            }
        }

        $search = trim((string) ($this->request->getGet('search')['value'] ?? ''));
        $data   = $this->kontrakModel->getWithPegawai($filterPegawaiId, $search);

        $result = [];
        $no     = (int) ($this->request->getGet('start') ?? 0) + 1;
        $today  = date('Y-m-d');

        foreach ($data as $row) {
            $tglMulai   = $row['tanggal_mulai'] ?? '';
            $tglSelesai = $row['tanggal_selesai'] ?? '';

            // Compute status badge
            $statusBadge = '<span class="badge badge-secondary">Tidak Diketahui</span>';
            if ($tglMulai && $tglSelesai) {
                if ($today < $tglMulai) {
                    $statusBadge = '<span class="badge badge-info"><i class="fas fa-clock mr-1"></i>Akan Datang</span>';
                } elseif ($today >= $tglMulai && $today <= $tglSelesai) {
                    $statusBadge = '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Aktif</span>';
                } else {
                    $statusBadge = '<span class="badge badge-secondary"><i class="fas fa-history mr-1"></i>Berakhir</span>';
                }
            }

            // Durasi
            $durasiText = '-';
            if ($tglMulai && $tglSelesai) {
                $d1 = new \DateTime($tglMulai);
                $d2 = new \DateTime($tglSelesai);
                $diff = $d1->diff($d2);
                $parts = [];
                if ($diff->y > 0) $parts[] = $diff->y . ' thn';
                if ($diff->m > 0) $parts[] = $diff->m . ' bln';
                if ($diff->d > 0 && empty($parts)) $parts[] = $diff->d . ' hari';
                $durasiText = !empty($parts) ? implode(' ', $parts) : '1 hari';
            }

            // PDF info
            $fileUrl = base_url(ltrim($row['file_pdf'] ?? '', '/'));
            $previewUrl = site_url('admin/konsultan-individual/kontrak/' . $row['id'] . '/preview');
            $downloadUrl = site_url('admin/konsultan-individual/kontrak/' . $row['id'] . '/download');

            $fileBadge = '<div class="btn-group btn-group-sm">
                <a href="' . esc($previewUrl) . '" target="_blank" class="btn btn-outline-danger btn-sm" title="Preview PDF">
                    <i class="fas fa-file-pdf mr-1"></i> Lihat
                </a>
                <a href="' . esc($downloadUrl) . '" class="btn btn-outline-secondary btn-sm" title="Unduh PDF">
                    <i class="fas fa-download"></i>
                </a>
            </div>';

            // Actions
            $canDelete = false;
            if ($permissions['delete']) {
                if ($isKonsultan && (int) $row['pegawai_id'] === (int) $currentKonsultan['id']) {
                    $canDelete = true;
                } elseif (! $isKonsultan && $this->isCurrentUserAdmin()) {
                    $canDelete = true;
                }
            }

            $actions = '<div class="btn-group btn-group-sm">';
            $actions .= '<a href="' . esc($previewUrl) . '" target="_blank" class="btn btn-info btn-sm" title="Lihat Dokumen"><i class="fas fa-eye"></i></a> ';
            $actions .= '<a href="' . esc($downloadUrl) . '" class="btn btn-secondary btn-sm" title="Unduh Dokumen"><i class="fas fa-download"></i></a> ';
            if ($canDelete) {
                $deleteUrl = site_url('admin/konsultan-individual/kontrak/' . $row['id'] . '/hapus');
                $actions .= '<button type="button" class="btn btn-danger btn-sm btn-delete-kontrak" data-id="' . $row['id'] . '" data-url="' . esc($deleteUrl) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
            }
            $actions .= '</div>';

            $result[] = [
                'no'               => $no++,
                'nama_pegawai'     => esc($row['nama_pegawai'] ?? '-'),
                'nip_pegawai'      => esc($row['nip_pegawai'] ?? '-'),
                'periode_kontrak'  => $this->formatIndonesianDate($tglMulai) . '<br><small class="text-muted">s/d ' . $this->formatIndonesianDate($tglSelesai) . '</small>',
                'status_badge'     => $statusBadge,
                'durasi'           => $durasiText,
                'file_badge'       => $fileBadge,
                'keterangan'       => esc($row['keterangan'] ?? '-'),
                'created_at'       => $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-',
                'action'           => $actions,
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getGet('draw') ?? 1),
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
            'data'            => $result,
        ]);
    }

    public function kontrakUpload()
    {
        // 1. Strict access check: only logged-in user who is a Konsultan Individual can upload
        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        if ($currentKonsultan === null) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak. Pengunggahan berkas kontrak hanya diizinkan untuk pegawai berjenis Konsultan Individual.',
            ]);
        }

        // 2. Validate menu_akses FiturAdd
        $permissions = $this->resolveMenuAksesPermissions('admin/konsultan-individual/kontrak');
        if (! $permissions['add']) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses (FiturAdd) untuk mengunggah kontrak.',
            ]);
        }

        // 3. Validation rules
        $rules = [
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
            'keterangan'      => 'permit_empty|max_length[1000]',
            'file_pdf'        => [
                'label' => 'Berkas PDF Kontrak',
                'rules' => 'uploaded[file_pdf]|mime_in[file_pdf,application/pdf]|ext_in[file_pdf,pdf]|max_size[file_pdf,20480]',
                'errors' => [
                    'uploaded' => 'Berkas PDF kontrak wajib diunggah.',
                    'mime_in'  => 'Format berkas harus berupa dokumen PDF.',
                    'ext_in'   => 'Ekstensi berkas harus .pdf.',
                    'max_size' => 'Ukuran berkas maksimal 20 MB.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $tanggalMulai   = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        if ($tanggalSelesai < $tanggalMulai) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => [
                    'tanggal_selesai' => 'Tanggal selesai kontrak tidak boleh lebih awal dari tanggal mulai.',
                ],
            ]);
        }

        $file = $this->request->getFile('file_pdf');
        if (! $file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal membaca berkas yang diunggah: ' . $file->getErrorString(),
            ]);
        }

        $targetDir = FCPATH . 'uploads/konsultan/kontrak';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $originalName = $file->getClientName();
        $fileSize = $file->getSize();

        $file->move($targetDir, $newName);
        $relativePath = '/uploads/konsultan/kontrak/' . $newName;

        $insertId = $this->kontrakModel->insert([
            'pegawai_id'         => (int) $currentKonsultan['id'],
            'tanggal_mulai'      => $tanggalMulai,
            'tanggal_selesai'    => $tanggalSelesai,
            'file_pdf'           => $relativePath,
            'file_size'          => $fileSize,
            'file_original_name' => $originalName,
            'keterangan'         => trim((string) $this->request->getPost('keterangan')),
            'created_by'         => (int) (session()->get('userId') ?? 0),
        ]);

        if (! $insertId) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data kontrak ke database.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Berkas kontrak berhasil diunggah.',
        ]);
    }

    public function kontrakDelete(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/konsultan-individual/kontrak');
        if (! $permissions['delete']) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus data kontrak.',
            ]);
        }

        $row = $this->kontrakModel->find($id);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data kontrak tidak ditemukan.',
            ]);
        }

        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        if ($currentKonsultan !== null) {
            if ((int) $row['pegawai_id'] !== (int) $currentKonsultan['id']) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda hanya berhak menghapus berkas kontrak milik Anda sendiri.',
                ]);
            }
        } elseif (! $this->isCurrentUserAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak.',
            ]);
        }

        // Delete file on disk
        $filePath = FCPATH . ltrim($row['file_pdf'] ?? '', '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }

        $this->kontrakModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data kontrak berhasil dihapus.',
        ]);
    }

    public function kontrakPreview(int $id)
    {
        $row = $this->kontrakModel->find($id);
        if (! $row || empty($row['file_pdf'])) {
            return $this->response->setStatusCode(404)->setBody('Dokumen tidak ditemukan.');
        }

        $filePath = FCPATH . ltrim($row['file_pdf'], '/');
        if (! is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('Berkas fisik tidak ditemukan di server.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($row['file_original_name'] ?: 'kontrak.pdf') . '"')
            ->setBody(file_get_contents($filePath));
    }

    public function kontrakDownload(int $id)
    {
        $row = $this->kontrakModel->find($id);
        if (! $row || empty($row['file_pdf'])) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $filePath = FCPATH . ltrim($row['file_pdf'], '/');
        if (! is_file($filePath)) {
            return redirect()->back()->with('error', 'Berkas fisik tidak ditemukan di server.');
        }

        $fileName = $row['file_original_name'] ?: basename($filePath);
        return $this->response->download($filePath, null)->setFileName($fileName);
    }

    // ==========================================
    // SUBMENU 2: LAPORAN BULANAN
    // ==========================================

    public function laporanBulanan()
    {
        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        $isKonsultan      = ($currentKonsultan !== null);
        $permissions       = $this->resolveMenuAksesPermissions('admin/konsultan-individual/laporan-bulanan');
        $konsultanList    = $this->getAllKonsultanPegawai();

        // Month and Year choices
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentYear = (int) date('Y');
        $years = range($currentYear - 2, $currentYear + 2);

        return view('admin/konsultan_individual/laporan_bulanan', [
            'title'             => 'Konsultan Individual - Laporan Bulanan',
            'current_konsultan' => $currentKonsultan,
            'is_konsultan'      => $isKonsultan,
            'permissions'       => $permissions,
            'konsultan_list'    => $konsultanList,
            'months'            => $months,
            'years'             => $years,
            'current_month'     => (int) date('n'),
            'current_year'      => $currentYear,
        ]);
    }

    public function laporanBulananData()
    {
        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        $isKonsultan      = ($currentKonsultan !== null);
        $permissions       = $this->resolveMenuAksesPermissions('admin/konsultan-individual/laporan-bulanan');

        $filterPegawaiId = null;
        if ($isKonsultan) {
            $filterPegawaiId = (int) $currentKonsultan['id'];
        } else {
            $requestedPegawai = (int) $this->request->getGet('pegawai_id');
            if ($requestedPegawai > 0) {
                $filterPegawaiId = $requestedPegawai;
            }
        }

        $filterTahun = (int) $this->request->getGet('tahun');
        $filterBulan = (int) $this->request->getGet('bulan');
        $search      = trim((string) ($this->request->getGet('search')['value'] ?? ''));

        $data = $this->laporanModel->getWithPegawai(
            $filterPegawaiId,
            $filterTahun > 0 ? $filterTahun : null,
            $filterBulan > 0 ? $filterBulan : null,
            $search
        );

        $result = [];
        $no     = (int) ($this->request->getGet('start') ?? 0) + 1;
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($data as $row) {
            $bulanNum = (int) ($row['bulan'] ?? 0);
            $tahunNum = (int) ($row['tahun'] ?? 0);
            $namaBulan = $monthNames[$bulanNum] ?? ('Bulan ' . $bulanNum);
            $periodeText = $namaBulan . ' ' . $tahunNum;

            $previewUrl = site_url('admin/konsultan-individual/laporan-bulanan/' . $row['id'] . '/preview');
            $downloadUrl = site_url('admin/konsultan-individual/laporan-bulanan/' . $row['id'] . '/download');

            $fileBadge = '<div class="btn-group btn-group-sm">
                <a href="' . esc($previewUrl) . '" target="_blank" class="btn btn-outline-danger btn-sm" title="Preview PDF">
                    <i class="fas fa-file-pdf mr-1"></i> Lihat
                </a>
                <a href="' . esc($downloadUrl) . '" class="btn btn-outline-secondary btn-sm" title="Unduh PDF">
                    <i class="fas fa-download"></i>
                </a>
            </div>';

            // Delete action
            $canDelete = false;
            if ($permissions['delete']) {
                if ($isKonsultan && (int) $row['pegawai_id'] === (int) $currentKonsultan['id']) {
                    $canDelete = true;
                } elseif (! $isKonsultan && $this->isCurrentUserAdmin()) {
                    $canDelete = true;
                }
            }

            $actions = '<div class="btn-group btn-group-sm">';
            $actions .= '<a href="' . esc($previewUrl) . '" target="_blank" class="btn btn-info btn-sm" title="Lihat Laporan"><i class="fas fa-eye"></i></a> ';
            $actions .= '<a href="' . esc($downloadUrl) . '" class="btn btn-secondary btn-sm" title="Unduh Laporan"><i class="fas fa-download"></i></a> ';
            if ($canDelete) {
                $deleteUrl = site_url('admin/konsultan-individual/laporan-bulanan/' . $row['id'] . '/hapus');
                $actions .= '<button type="button" class="btn btn-danger btn-sm btn-delete-laporan" data-id="' . $row['id'] . '" data-url="' . esc($deleteUrl) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
            }
            $actions .= '</div>';

            $result[] = [
                'no'           => $no++,
                'nama_pegawai' => esc($row['nama_pegawai'] ?? '-'),
                'nip_pegawai'  => esc($row['nip_pegawai'] ?? '-'),
                'periode'      => '<span class="font-weight-bold text-primary"><i class="far fa-calendar-alt mr-1"></i>' . esc($periodeText) . '</span>',
                'file_badge'   => $fileBadge,
                'keterangan'   => esc($row['keterangan'] ?? '-'),
                'created_at'   => $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-',
                'action'       => $actions,
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getGet('draw') ?? 1),
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
            'data'            => $result,
        ]);
    }

    public function laporanBulananUpload()
    {
        // 1. Strict access check: only logged-in user who is a Konsultan Individual can upload
        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        if ($currentKonsultan === null) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak. Pengunggahan laporan bulanan hanya diizinkan untuk pegawai berjenis Konsultan Individual.',
            ]);
        }

        // 2. Validate menu_akses FiturAdd
        $permissions = $this->resolveMenuAksesPermissions('admin/konsultan-individual/laporan-bulanan');
        if (! $permissions['add']) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses (FiturAdd) untuk mengunggah laporan bulanan.',
            ]);
        }

        // 3. Validation rules - Note: NO title field required!
        $rules = [
            'bulan'      => 'required|in_list[1,2,3,4,5,6,7,8,9,10,11,12]',
            'tahun'      => 'required|numeric|greater_than_equal_to[2020]|less_than_equal_to[2035]',
            'keterangan' => 'permit_empty|max_length[1000]',
            'file_pdf'   => [
                'label' => 'Berkas PDF Laporan Bulanan',
                'rules' => 'uploaded[file_pdf]|mime_in[file_pdf,application/pdf]|ext_in[file_pdf,pdf]|max_size[file_pdf,20480]',
                'errors' => [
                    'uploaded' => 'Berkas PDF laporan bulanan wajib diunggah.',
                    'mime_in'  => 'Format berkas harus berupa dokumen PDF.',
                    'ext_in'   => 'Ekstensi berkas harus .pdf.',
                    'max_size' => 'Ukuran berkas maksimal 20 MB.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $bulan = (int) $this->request->getPost('bulan');
        $tahun = (int) $this->request->getPost('tahun');
        $periodeBulan = sprintf('%04d-%02d', $tahun, $bulan);

        $file = $this->request->getFile('file_pdf');
        if (! $file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal membaca berkas yang diunggah: ' . $file->getErrorString(),
            ]);
        }

        $targetDir = FCPATH . 'uploads/konsultan/laporan';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName = $file->getRandomName();
        $originalName = $file->getClientName();
        $fileSize = $file->getSize();

        $file->move($targetDir, $newName);
        $relativePath = '/uploads/konsultan/laporan/' . $newName;

        $insertId = $this->laporanModel->insert([
            'pegawai_id'         => (int) $currentKonsultan['id'],
            'periode_bulan'      => $periodeBulan,
            'tahun'              => $tahun,
            'bulan'              => $bulan,
            'file_pdf'           => $relativePath,
            'file_size'          => $fileSize,
            'file_original_name' => $originalName,
            'keterangan'         => trim((string) $this->request->getPost('keterangan')),
            'created_by'         => (int) (session()->get('userId') ?? 0),
        ]);

        if (! $insertId) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data laporan bulanan ke database.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Laporan bulanan periode ' . $periodeBulan . ' berhasil diunggah.',
        ]);
    }

    public function laporanBulananDelete(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/konsultan-individual/laporan-bulanan');
        if (! $permissions['delete']) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus laporan bulanan.',
            ]);
        }

        $row = $this->laporanModel->find($id);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data laporan bulanan tidak ditemukan.',
            ]);
        }

        $currentKonsultan = $this->getCurrentKonsultanPegawai();
        if ($currentKonsultan !== null) {
            if ((int) $row['pegawai_id'] !== (int) $currentKonsultan['id']) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda hanya berhak menghapus laporan bulanan milik Anda sendiri.',
                ]);
            }
        } elseif (! $this->isCurrentUserAdmin()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak.',
            ]);
        }

        // Delete file on disk
        $filePath = FCPATH . ltrim($row['file_pdf'] ?? '', '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }

        $this->laporanModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Laporan bulanan berhasil dihapus.',
        ]);
    }

    public function laporanBulananPreview(int $id)
    {
        $row = $this->laporanModel->find($id);
        if (! $row || empty($row['file_pdf'])) {
            return $this->response->setStatusCode(404)->setBody('Dokumen tidak ditemukan.');
        }

        $filePath = FCPATH . ltrim($row['file_pdf'], '/');
        if (! is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('Berkas fisik tidak ditemukan di server.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($row['file_original_name'] ?: 'laporan.pdf') . '"')
            ->setBody(file_get_contents($filePath));
    }

    public function laporanBulananDownload(int $id)
    {
        $row = $this->laporanModel->find($id);
        if (! $row || empty($row['file_pdf'])) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $filePath = FCPATH . ltrim($row['file_pdf'], '/');
        if (! is_file($filePath)) {
            return redirect()->back()->with('error', 'Berkas fisik tidak ditemukan di server.');
        }

        $fileName = $row['file_original_name'] ?: basename($filePath);
        return $this->response->download($filePath, null)->setFileName($fileName);
    }

    // ==========================================
    // INTERNAL HELPERS
    // ==========================================

    /**
     * Resolves currently logged-in user to an employee whose jenis_pegawai is 'konsultan'
     */
    private function getCurrentKonsultanPegawai(): ?array
    {
        $username = trim((string) (session()->get('username') ?? ''));
        $fullName = trim((string) (session()->get('fullName') ?? ''));

        if ($username === '' && $fullName === '') {
            return null;
        }

        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return null;
        }

        $cleanUsername = preg_replace('/^nip/i', '', $username);

        $builder = $db->table('mst_pegawai');
        $builder->select('mst_pegawai.*, ju.jabatan AS jabatan_label');
        $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
        $builder->where('mst_pegawai.is_active', 1);

        $builder->groupStart();
        $builder->where('LOWER(mst_pegawai.nip)', strtolower($username));
        $builder->orWhere('LOWER(mst_pegawai.nip)', strtolower('nip' . $username));
        if ($cleanUsername !== '') {
            $builder->orWhere('LOWER(mst_pegawai.nip)', strtolower('nip' . $cleanUsername));
            $builder->orWhere('LOWER(mst_pegawai.nip)', strtolower($cleanUsername));
        }
        if ($fullName !== '') {
            $builder->orWhere('LOWER(mst_pegawai.nama)', strtolower($fullName));
            $builder->orLike('LOWER(mst_pegawai.nama)', strtolower($fullName));
        }
        $builder->groupEnd();

        $rows = $builder->get()->getResultArray();
        foreach ($rows as $row) {
            $jenis = strtolower(trim((string) ($row['jenis_pegawai'] ?? '')));
            if ($jenis === 'konsultan' || strpos($jenis, 'konsultan') !== false) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Get all active employees who are konsultan individual
     */
    private function getAllKonsultanPegawai(): array
    {
        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return [];
        }

        $builder = $db->table('mst_pegawai');
        $builder->select('mst_pegawai.id, mst_pegawai.nama, mst_pegawai.nip, ju.jabatan AS jabatan_label');
        $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
        $builder->where('mst_pegawai.is_active', 1);
        $builder->groupStart();
        $builder->where('LOWER(mst_pegawai.jenis_pegawai)', 'konsultan');
        $builder->orLike('LOWER(mst_pegawai.jenis_pegawai)', 'konsultan');
        $builder->groupEnd();
        $builder->orderBy('mst_pegawai.nama', 'ASC');

        return $builder->get()->getResultArray();
    }

    private function isCurrentUserAdmin(): bool
    {
        $role = strtolower(trim((string) (session()->get('role') ?? '')));
        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    /**
     * Resolve permissions from menu_akses for a given link
     */
    private function resolveMenuAksesPermissions(string $link): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $role = strtolower(trim((string) (session()->get('role') ?? '')));
        $db   = db_connect();

        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        // Find roleId
        $roleId = null;
        if ($db->tableExists('access_roles')) {
            $variants = [$role];
            if (in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
                $variants = ['super administrator', 'super_administrator', 'super-admin', 'superadmin'];
            }
            $roleRow = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', $variants)
                ->where('is_active', 1)
                ->get()
                ->getRowArray();
            if ($roleRow && isset($roleRow['id'])) {
                $roleId = (int) $roleRow['id'];
            }
        }

        if ($roleId === null) {
            $roleId = match ($role) {
                'admin'  => 2,
                'editor' => 3,
                default  => 1,
            };
        }

        // Find menu ID
        $menuId = null;
        foreach (['menu_lv2', 'menu_lv1', 'menu_lv3'] as $table) {
            if (! $db->tableExists($table)) continue;
            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower(trim($link)))
                ->get()
                ->getRowArray();
            if ($row && isset($row['id'])) {
                $menuId = (string) $row['id'];
                break;
            }
        }

        if (! $menuId) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! $row) {
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

    private function formatIndonesianDate(?string $date): string
    {
        if (! $date || $date === '0000-00-00') {
            return '-';
        }

        $timestamp = strtotime($date);
        if (! $timestamp) {
            return (string) $date;
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $day   = date('d', $timestamp);
        $month = $months[(int) date('n', $timestamp)] ?? date('m', $timestamp);
        $year  = date('Y', $timestamp);

        return $day . ' ' . $month . ' ' . $year;
    }
}
