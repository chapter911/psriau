<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LaporanLapanganProyekModel;
use App\Models\LaporanLapanganPekerjaanModel;
use CodeIgniter\HTTP\ResponseInterface;

class LaporanHarian extends BaseController
{
    /**
     * URL: POST /api/laporan-harian/proyek
     */
    public function proyek()
    {
        $rules = [
            'paket_id'     => 'required',
            'sekolah_npsn' => 'required',
            'tanggal'      => 'required|valid_date[Y-m-d]',
            'jam_mulai'    => 'required',
            'jam_selesai'  => 'required',
            'cuaca_json'   => 'required',
            'nama_pelapor' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'paket_id'     => $this->request->getVar('paket_id'),
            'sekolah_npsn' => $this->request->getVar('sekolah_npsn'),
            'tanggal'      => $this->request->getVar('tanggal'),
            'jam_mulai'    => $this->request->getVar('jam_mulai'),
            'jam_selesai'  => $this->request->getVar('jam_selesai'),
            'cuaca_json'   => $this->request->getVar('cuaca_json'),
            'pengawas'     => (int) ($this->request->getVar('pengawas') ?? 0),
            'pelaksana'    => (int) ($this->request->getVar('pelaksana') ?? 0),
            'mandor'       => (int) ($this->request->getVar('mandor') ?? 0),
            'tukang'       => (int) ($this->request->getVar('tukang') ?? 0),
            'pekerja'      => (int) ($this->request->getVar('pekerja') ?? 0),
            'nama_pelapor' => $this->request->getVar('nama_pelapor'),
        ];

        $proyekModel = new LaporanLapanganProyekModel();

        // Check if report already exists for school & date to prevent duplicates (upsert behavior)
        $existing = $proyekModel->where('sekolah_npsn', $data['sekolah_npsn'])
                               ->where('tanggal', $data['tanggal'])
                               ->first();

        if ($existing) {
            $proyekModel->update($existing['id'], $data);
            $message = 'Laporan proyek berhasil diperbarui';
        } else {
            $proyekModel->insert($data);
            $message = 'Laporan proyek berhasil disimpan';
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $message
        ]);
    }

    /**
     * URL: POST /api/laporan-harian/pekerjaan
     */
    public function pekerjaan()
    {
        $rules = [
            'rab_detail_id'  => 'required|integer',
            'tanggal'        => 'required|valid_date[Y-m-d]',
            'status_selesai' => 'required|in_list[0,1]',
            'progres_persen' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $rabDetailId = (int) $this->request->getVar('rab_detail_id');
        $tanggal     = $this->request->getVar('tanggal');

        // Handle File Uploads
        $photoPaths = [];
        $files = $this->request->getFileMultiple('foto');
        if (!is_array($files) || $files === []) {
            $files = $this->request->getFileMultiple('foto[]');
        }

        if (is_array($files) && $files !== []) {
            $flatFiles = [];
            array_walk_recursive($files, static function ($file) use (&$flatFiles): void {
                $flatFiles[] = $file;
            });
            $flatFiles = array_values(array_filter($flatFiles, static fn ($file): bool => $file !== null));

            $uploadDir = FCPATH . 'uploads/laporan-lapangan/' . date('Y/m');
            if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal membuat folder upload di server.'
                ]);
            }

            foreach ($flatFiles as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move($uploadDir, $newName);
                    $photoPaths[] = '/uploads/laporan-lapangan/' . date('Y/m') . '/' . $newName;
                }
            }
        }

        $pekerjaanModel = new LaporanLapanganPekerjaanModel();

        // Check if report already exists for this RAB detail & date
        $existing = $pekerjaanModel->where('rab_detail_id', $rabDetailId)
                                   ->where('tanggal', $tanggal)
                                   ->first();

        // If new photos uploaded, use them. If no new photos uploaded but record exists, retain old photos.
        if (empty($photoPaths) && $existing) {
            $photoPaths = json_decode($existing['foto_paths_json'] ?? '[]', true) ?: [];
        }

        $data = [
            'rab_detail_id'      => $rabDetailId,
            'tanggal'            => $tanggal,
            'status_selesai'     => (int) $this->request->getVar('status_selesai'),
            'progres_persen'     => (float) $this->request->getVar('progres_persen'),
            'keterangan_progres' => $this->request->getVar('keterangan_progres'),
            'kendala'            => $this->request->getVar('kendala'),
            'foto_paths_json'    => json_encode($photoPaths),
        ];

        if ($existing) {
            $pekerjaanModel->update($existing['id'], $data);
            $message = 'Laporan pekerjaan berhasil diperbarui';
        } else {
            $pekerjaanModel->insert($data);
            $message = 'Laporan pekerjaan berhasil disimpan';
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $message
        ]);
    }
}
