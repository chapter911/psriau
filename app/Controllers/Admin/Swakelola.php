<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SwakelolaPaketModel;
use App\Models\SwakelolaDokumenModel;
use App\Models\SwakelolaRealisasiModel;
use CodeIgniter\HTTP\ResponseInterface;

class Swakelola extends BaseController
{
    protected SwakelolaPaketModel $paketModel;
    protected SwakelolaDokumenModel $dokumenModel;
    protected SwakelolaRealisasiModel $realisasiModel;

    public function __construct()
    {
        $this->paketModel     = new SwakelolaPaketModel();
        $this->dokumenModel   = new SwakelolaDokumenModel();
        $this->realisasiModel = new SwakelolaRealisasiModel();
    }

    /**
     * Sub-Menu 1: Paket Kegiatan Swakelola
     */
    public function paketIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/paket');
        $paketList   = $this->paketModel->orderBy('tahun_anggaran', 'DESC')->findAll();

        return view('admin/swakelola/paket', [
            'title'       => 'Daftar Paket Kegiatan Swakelola',
            'paketList'   => $paketList,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Sub-Menu 2: Checklist & Administrasi Dokumen
     */
    public function dokumenIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/dokumen');
        $paketId     = (int) ($this->request->getGet('paket_id') ?? 0);

        if ($paketId <= 0) {
            $firstPaket = $this->paketModel->first();
            $paketId    = $firstPaket ? (int) $firstPaket['id'] : 1;
        }

        $paket     = $this->paketModel->find($paketId);
        $paketList = $this->paketModel->findAll();

        // Auto-heal nomor_urut 9 (hindari karakter & pada nama file di database lama)
        $db = \Config\Database::connect();
        if ($db->tableExists('trn_swakelola_dokumen')) {
            $db->table('trn_swakelola_dokumen')
                ->where('nomor_urut', 9)
                ->where('file_name', 'B. 4. a. KAK Final & B. 4. b. RAB Final Administrasi SPPG.docx')
                ->update([
                    'file_name' => 'B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx',
                    'file_path' => 'public/uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx',
                ]);
        }

        $dokumenList = $this->dokumenModel->getByPaketId($paketId);
        $multiFileMap = $this->getMultiFilesMap();

        // Group by Tahapan
        $grouped = [
            'A' => ['label' => 'A. Perencanaan Swakelola', 'items' => []],
            'B' => ['label' => 'B. Persiapan Swakelola', 'items' => []],
            'C' => ['label' => 'C. Pelaksanaan Swakelola', 'items' => []],
            'D' => ['label' => 'D. Pengawasan dan Pengendalian Swakelola', 'items' => []],
            'E' => ['label' => 'E. Serah Terima dan Pertanggungjawaban Swakelola', 'items' => []],
        ];

        $totalAda = 0;
        foreach ($dokumenList as $doc) {
            $no = (int) ($doc['nomor_urut'] ?? 0);
            $doc['sub_files'] = $multiFileMap[$no] ?? [
                [
                    'label'     => $doc['file_name'],
                    'file_name' => $doc['file_name'],
                ],
            ];
            $t = $doc['tahap'];
            if (isset($grouped[$t])) {
                $grouped[$t]['items'][] = $doc;
            }
            if ($doc['status'] === 'Ada') {
                $totalAda++;
            }
        }

        $totalCount = count($dokumenList);
        $persenAda  = $totalCount > 0 ? round(($totalAda / $totalCount) * 100, 1) : 0;

        return view('admin/swakelola/dokumen', [
            'title'       => 'Checklist Dokumen Swakelola',
            'paket'       => $paket,
            'paketList'   => $paketList,
            'grouped'     => $grouped,
            'totalAda'    => $totalAda,
            'totalCount'  => $totalCount,
            'persenAda'   => $persenAda,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Daftar rincian sub-berkas untuk item checklist yang memiliki lebih dari 1 dokumen pendukung
     */
    private function getMultiFilesMap(): array
    {
        return [
            9 => [
                ['label' => 'B. 4. a. KAK Final Administrasi SPPG.docx', 'file_name' => 'B. 4. a. KAK Final Administrasi SPPG.docx'],
                ['label' => 'B. 4. b. RAB Final Administrasi SPPG.docx', 'file_name' => 'B. 4. b. RAB Final Administrasi SPPG.docx'],
                ['label' => 'B. 4. Gabungan KAK Final & RAB Rinci SPPG.docx', 'file_name' => 'B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx'],
            ],
            15 => [
                ['label' => 'C. 4. Bukti Pembelian Bahan ATK Bulan November (SPM 00415A).pdf', 'file_name' => 'ATK BULAN NOVEMBER (SPM 00415A).pdf'],
                ['label' => 'C. 4. Bukti Pembelian Bahan ATK Bulan Desember (SPM 00498A).pdf', 'file_name' => 'ATK SPPG DESEMBER (SPM 00498A) OK.pdf'],
                ['label' => 'C. 4. Bukti Kuitansi Dokumentasi & Pelaporan (TUP 1).pdf', 'file_name' => 'TUP 1 (KWI DOKUMENTASI DAN PELAPORAN).pdf'],
            ],
            16 => [
                ['label' => 'C. 5. Invoice, Kuitansi & Pajak Supplies Komputer Nov (SPM 00414A).pdf', 'file_name' => 'SUPLIES KOMPUTER BULAN NOVEMBER (SPM 00414A) (1).pdf'],
                ['label' => 'C. 5. Invoice, Kuitansi & Pajak Supplies Komputer Des (SPM 00497A).pdf', 'file_name' => 'SUPLIES KOMPUTER BULAN DESEMBER (SPM 00497A).pdf'],
            ],
            18 => [
                ['label' => 'C. 7. 1. SPPD Monitoring Kampar Oktober (SPM 00445A).pdf', 'file_name' => 'SPPD SPPG KAMPAR OKTOBER (SPM 00445A).pdf'],
                ['label' => 'C. 7. 2. SPPD Koordinasi Jakarta November (SPM 00449A).pdf', 'file_name' => 'SPPD JAKARTA NOVEMBER (SPM 00449A).pdf'],
                ['label' => 'C. 7. 3. SPPD Dalam Kota Pekanbaru November (SPM 00450A).pdf', 'file_name' => '1. SPPD NOVEMBER SPPG (SPM 00450A).pdf'],
                ['label' => 'C. 7. 4. SPPD Monitoring Dumai & Siak November (SPM 00483A).pdf', 'file_name' => 'SPPD DUMAI SIAK SPPG NOV (SPM 00483A).pdf'],
                ['label' => 'C. 7. 5. SPPD Koordinasi Jakarta November Thp 2 (SPM 00485A).pdf', 'file_name' => 'SPPD JAKARTA NOVEMBER (SPM 00485A).pdf'],
                ['label' => 'C. 7. 6. SPPD Monitoring Dumai Desember (SPM 00489A).pdf', 'file_name' => 'SPPD DUMAI DESEMBER (SPM 00489A).pdf'],
                ['label' => 'C. 7. 7. SPPD Koordinasi Wilayah Medan Desember (SPM 00535A).pdf', 'file_name' => 'SPPD MEDAN DESEMBER (SPM 00535A) OK.pdf'],
                ['label' => 'C. 7. 8. SPPD Monitoring SPPG Desember (SPM 00536A).pdf', 'file_name' => 'SPPD SPPG DESEMBER (SPM 00536A).pdf'],
                ['label' => 'C. 7. 9. SPPD Monitoring Kampar Desember (SPM 00556A).pdf', 'file_name' => 'SPPD SPPG KAMPAR (SPM 00556A).pdf'],
                ['label' => 'C. 7. 10. SPPD Monitoring Siak Desember (SPM 00557A).pdf', 'file_name' => 'SPPD SPPG SIAK (SPM 00557A).pdf'],
                ['label' => 'C. 7. 11. Kuitansi & Bukti Pengeluaran Perjadin (TUP 1).pdf', 'file_name' => 'TUP 1 (KWI PERJADIN).pdf'],
            ],
        ];
    }

    /**
     * Helper cari lokasi berkas fisik (mendukung public/uploads dan fallback audit)
     */
    private function resolveDocumentPath(string $fileName, ?string $dbPath = null): ?string
    {
        $safeFileName = basename(trim($fileName));
        $candidates = [];

        // 1. Lokasi permanen di public/uploads
        $candidates[] = FCPATH . 'uploads/swakelola/sppg/' . $safeFileName;
        $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/' . $safeFileName;

        // Fallback untuk nama lama item B.4
        if (strpos($safeFileName, 'B. 4.') === 0) {
            $candidates[] = FCPATH . 'uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx';
            $candidates[] = FCPATH . 'uploads/swakelola/sppg/B. 4. a. KAK Final Administrasi SPPG.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/B. 4. a. KAK Final Administrasi SPPG.docx';
        }

        // 2. Berdasarkan database path jika ada
        if (! empty($dbPath)) {
            $candidates[] = ROOTPATH . ltrim($dbPath, '/');
            $candidates[] = FCPATH . ltrim($dbPath, '/');
        }

        // 3. Fallback do_not_upload/audit
        $candidates[] = ROOTPATH . 'do_not_upload/audit/hasil_sppg/' . $safeFileName;

        foreach ($candidates as $path) {
            if (file_exists($path) && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Deteksi MIME type secara aman untuk PDF, DOCX, XLSX
     */
    private function detectSafeMimeType(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => (function_exists('mime_content_type') ? (@mime_content_type($filePath) ?: 'application/octet-stream') : 'application/octet-stream'),
        };
    }

    /**
     * Download Berkas Dokumen (mendukung parameter opsional ?file=...)
     */
    public function downloadDokumen(int $id)
    {
        $requestedFile = trim((string) ($this->request->getGet('file') ?? ''));
        $doc = $id > 0 ? $this->dokumenModel->find($id) : null;

        if (! $doc && $requestedFile === '') {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $targetFileName = $requestedFile !== '' ? basename($requestedFile) : (string) ($doc['file_name'] ?? '');
        $filePath = $this->resolveDocumentPath($targetFileName, $requestedFile === '' ? ($doc['file_path'] ?? null) : null);

        if (! $filePath) {
            return redirect()->back()->with('error', 'Berkas fisik/digital dokumen belum tersedia di server.');
        }

        $mimeType = $this->detectSafeMimeType($filePath);
        return $this->response->download($filePath, null)->setContentType($mimeType);
    }

    /**
     * Stream Berkas Inline untuk Iframe / Preview Langsung
     */
    public function viewInlineDokumen(int $id)
    {
        $requestedFile = trim((string) ($this->request->getGet('file') ?? ''));
        $doc = $id > 0 ? $this->dokumenModel->find($id) : null;

        if (! $doc && $requestedFile === '') {
            return $this->response->setStatusCode(404, 'Dokumen tidak ditemukan');
        }

        $targetFileName = $requestedFile !== '' ? basename($requestedFile) : (string) ($doc['file_name'] ?? '');
        $filePath = $this->resolveDocumentPath($targetFileName, $requestedFile === '' ? ($doc['file_path'] ?? null) : null);
        if (! $filePath) {
            return $this->response->setStatusCode(404, 'Berkas fisik tidak ditemukan di server');
        }

        $mimeType = $this->detectSafeMimeType($filePath);
        $fileContent = file_get_contents($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes(basename($filePath)) . '"')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody($fileContent);
    }

    /**
     * AJAX Info Dokumen untuk Modal Preview
     */
    public function ajaxGetDokumenInfo(int $id)
    {
        $requestedFile = trim((string) ($this->request->getGet('file') ?? ''));
        $customTitle   = trim((string) ($this->request->getGet('title') ?? ''));
        $doc = $id > 0 ? $this->dokumenModel->find($id) : null;

        if (! $doc && $requestedFile === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Dokumen tidak ditemukan.']);
        }

        $targetFileName = $requestedFile !== '' ? basename($requestedFile) : (string) ($doc['file_name'] ?? '');
        $filePath = $this->resolveDocumentPath($targetFileName, $requestedFile === '' ? ($doc['file_path'] ?? null) : null);
        $fileExists = ($filePath !== null);
        $resolvedFileName = $fileExists ? basename($filePath) : $targetFileName;
        $ext = strtolower(pathinfo($resolvedFileName, PATHINFO_EXTENSION));

        $querySuffix = $requestedFile !== '' ? ('?file=' . rawurlencode($resolvedFileName)) : '';
        $publicUrl   = base_url('uploads/swakelola/sppg/' . rawurlencode($resolvedFileName));
        $inlineUrl   = base_url('admin/swakelola/dokumen/view/' . $id) . $querySuffix;
        $downloadUrl = base_url('admin/swakelola/dokumen/download/' . $id) . $querySuffix;

        return $this->response->setJSON([
            'status'       => 'success',
            'id'           => $doc['id'] ?? 0,
            'nomor_urut'   => $doc['nomor_urut'] ?? '-',
            'tahap'        => $doc['tahap'] ?? '-',
            'nama_dokumen' => $customTitle !== '' ? $customTitle : ($doc['nama_dokumen'] ?? $resolvedFileName),
            'file_name'    => $resolvedFileName,
            'file_exists'  => $fileExists,
            'ext'          => $ext,
            'file_url'     => $publicUrl,
            'inline_url'   => $inlineUrl,
            'download_url' => $downloadUrl,
            'keterangan'   => $doc['keterangan'] ?? '',
            'file_size'    => $fileExists ? filesize($filePath) : 0,
        ]);
    }

    /**
     * Sub-Menu 3: Realisasi Anggaran & SPM
     */
    public function realisasiIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/realisasi');
        $paketId     = (int) ($this->request->getGet('paket_id') ?? 0);

        if ($paketId <= 0) {
            $firstPaket = $this->paketModel->first();
            $paketId    = $firstPaket ? (int) $firstPaket['id'] : 1;
        }

        $paket     = $this->paketModel->find($paketId);
        $paketList = $this->paketModel->findAll();
        $realisasi = $this->realisasiModel->getByPaketId($paketId);

        $totalRealisasi = 0;
        $perAkun = [];
        foreach ($realisasi as $r) {
            $totalRealisasi += (float) $r['jumlah_bayar'];
            $akun = $r['kode_akun'];
            if (! isset($perAkun[$akun])) {
                $perAkun[$akun] = [
                    'nama'  => match ($akun) {
                        '521211' => 'Belanja Bahan',
                        '524111' => 'Belanja Perjalanan Dinas Biasa',
                        '524113' => 'Belanja Perjalanan Dinas Dalam Kota',
                        default  => 'Belanja Operasional',
                    },
                    'total' => 0,
                    'count' => 0,
                ];
            }
            $perAkun[$akun]['total'] += (float) $r['jumlah_bayar'];
            $perAkun[$akun]['count']++;
        }

        return view('admin/swakelola/realisasi', [
            'title'          => 'Realisasi Anggaran Swakelola',
            'paket'          => $paket,
            'paketList'      => $paketList,
            'realisasi'      => $realisasi,
            'totalRealisasi' => $totalRealisasi,
            'perAkun'        => $perAkun,
            'permissions'    => $permissions,
        ]);
    }

    /**
     * Sub-Menu 4: Rekapitulasi & Laporan Akhir
     */
    public function laporanIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/laporan');
        $paketId     = (int) ($this->request->getGet('paket_id') ?? 0);

        if ($paketId <= 0) {
            $firstPaket = $this->paketModel->first();
            $paketId    = $firstPaket ? (int) $firstPaket['id'] : 1;
        }

        $paket     = $this->paketModel->find($paketId);
        $paketList = $this->paketModel->findAll();

        $rabComparison = [
            [
                'akun'      => '521211',
                'uraian'    => 'Belanja Bahan (ATK, Supplies Komputer & Dokumentasi/Pelaporan)',
                'pagu'      => 44700000.00,
                'realisasi' => 61088500.00,
                'sisa'      => -16388500.00,
                'persen'    => 136.66,
            ],
            [
                'akun'      => '521811',
                'uraian'    => 'Belanja Barang Persediaan (Konsumsi Rapat Koordinasi)',
                'pagu'      => 12000000.00,
                'realisasi' => 0.00,
                'sisa'      => 12000000.00,
                'persen'    => 0.00,
            ],
            [
                'akun'      => '522131',
                'uraian'    => 'Belanja Jasa Profesi / Konsultan Individual',
                'pagu'      => 15000000.00,
                'realisasi' => 0.00,
                'sisa'      => 15000000.00,
                'persen'    => 0.00,
            ],
            [
                'akun'      => '522115',
                'uraian'    => 'Belanja Jasa Pendampingan Hukum Kejaksaan',
                'pagu'      => 14400000.00,
                'realisasi' => 0.00,
                'sisa'      => 14400000.00,
                'persen'    => 0.00,
            ],
            [
                'akun'      => '524111',
                'uraian'    => 'Belanja Perjalanan Dinas Biasa (Monitoring Wilayah Kampar/Siak/Dumai & Jakarta/Medan)',
                'pagu'      => 314900000.00,
                'realisasi' => 77070600.00,
                'sisa'      => 237829400.00,
                'persen'    => 24.47,
            ],
            [
                'akun'      => '524113',
                'uraian'    => 'Belanja Perjalanan Dinas Dalam Kota (Tim PPK Lapangan)',
                'pagu'      => 12000000.00,
                'realisasi' => 3000000.00,
                'sisa'      => 9000000.00,
                'persen'    => 25.00,
            ],
        ];

        return view('admin/swakelola/laporan', [
            'title'         => 'Rekapitulasi & Laporan Swakelola',
            'paket'         => $paket,
            'paketList'     => $paketList,
            'rabComparison' => $rabComparison,
            'permissions'   => $permissions,
        ]);
    }

    /**
     * Download Checklist Excel
     */
    public function exportChecklistExcel()
    {
        $filePath = FCPATH . 'uploads/swakelola/sppg/Checklist_Lengkap_Swakelola_SPPG.xlsx';
        if (! file_exists($filePath)) {
            $filePath = ROOTPATH . 'do_not_upload/audit/hasil_sppg/Checklist_Lengkap_Swakelola_SPPG.xlsx';
        }

        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'File Excel checklist belum tersedia.');
        }

        return $this->response->download($filePath, null)
            ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Helper resolve permissions sesuai menu_akses
     */
    private function resolveMenuAksesPermissions(string $link): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => true,
            'import'   => false,
            'approval' => false,
        ];

        $role = strtolower(trim((string) (session()->get('role') ?? '')));
        $db   = db_connect();

        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = null;
        if ($db->tableExists('access_roles')) {
            $roleRow = $db->table('access_roles')
                ->select('id')
                ->where('LOWER(role_key)', $role)
                ->where('is_active', 1)
                ->get()
                ->getRowArray();
            if ($roleRow && isset($roleRow['id'])) {
                $roleId = (int) $roleRow['id'];
            }
        }

        if ($roleId === null) {
            $roleId = match ($role) {
                'admin'               => 2,
                'editor'              => 3,
                'keuangan'            => 5,
                'admin_tata_usaha'    => 6,
                default               => 1,
            };
        }

        $row = $db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (! $row || ! isset($row['id'])) {
            return $default;
        }

        $menuId = (string) $row['id'];
        $akses = $db->table('menu_akses')
            ->where('role_id', $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! $akses) {
            return $default;
        }

        return [
            'add'      => ((int) ($akses['FiturAdd'] ?? 0)) === 1,
            'edit'     => ((int) ($akses['FiturEdit'] ?? 0)) === 1,
            'delete'   => ((int) ($akses['FiturDelete'] ?? 0)) === 1,
            'export'   => ((int) ($akses['FiturExport'] ?? 0)) === 1,
            'import'   => ((int) ($akses['FiturImport'] ?? 0)) === 1,
            'approval' => ((int) ($akses['FiturApproval'] ?? 0)) === 1,
        ];
    }
}
