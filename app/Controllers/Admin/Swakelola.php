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
        $this->syncVerifiedSppgData();
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
        $this->syncVerifiedSppgData();
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/dokumen');
        $paketId     = (int) ($this->request->getGet('paket_id') ?? 0);

        if ($paketId <= 0) {
            $firstPaket = $this->paketModel->first();
            $paketId    = $firstPaket ? (int) $firstPaket['id'] : 1;
        }

        $paket     = $this->paketModel->find($paketId);
        $paketList = $this->paketModel->findAll();

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
            1 => [
                ['label' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'file_name' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf'],
                ['label' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK SPPG TA 2025.docx', 'file_name' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK SPPG TA 2025.docx'],
            ],
            9 => [
                ['label' => 'B. 4. a. KAK Final Administrasi SPPG.docx', 'file_name' => 'B. 4. a. KAK Final Administrasi SPPG.docx'],
                ['label' => 'B. 4. b. RAB Final Administrasi SPPG.docx', 'file_name' => 'B. 4. b. RAB Final Administrasi SPPG.docx'],
                ['label' => 'B. 4. Gabungan KAK Final & RAB Rinci SPPG.docx', 'file_name' => 'B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx'],
            ],
            15 => [
                ['label' => 'C. 4. Bukti Pembelian Bahan ATK Bulan November (SPM 00415A).pdf', 'file_name' => 'ATK BULAN NOVEMBER (SPM 00415A).pdf'],
                ['label' => 'C. 4. Bukti Pembelian Bahan ATK Bulan Desember (SPM 00498A).pdf', 'file_name' => 'ATK SPPG DESEMBER (SPM 00498A) OK.pdf'],
                ['label' => 'C. 4. Bukti Kuitansi Dokumentasi & Pelaporan (PTUP SPM 00597A).pdf', 'file_name' => 'TUP 1 (KWI DOKUMENTASI DAN PELAPORAN).pdf'],
                ['label' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan & Barang Penyedia.docx', 'file_name' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan dan Barang dari Penyedia.docx'],
            ],
            16 => [
                ['label' => 'C. 5. Invoice, Kuitansi & Pajak Supplies Komputer Nov (SPM 00414A).pdf', 'file_name' => 'SUPLIES KOMPUTER BULAN NOVEMBER (SPM 00414A) (1).pdf'],
                ['label' => 'C. 5. Invoice, Kuitansi & Pajak Supplies Komputer Des (SPM 00497A).pdf', 'file_name' => 'SUPLIES KOMPUTER BULAN DESEMBER (SPM 00497A).pdf'],
                ['label' => 'C. 5. b. Rekapitulasi Invoice, Kuitansi & Bukti Setor Pajak.docx', 'file_name' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Bukti Setor Pajak.docx'],
            ],
            18 => [
                ['label' => 'C. 7. 1. SPPD Monitoring Kampar 4 Pegawai (SPM 00445A).pdf', 'file_name' => 'SPPD SPPG KAMPAR OKTOBER (SPM 00445A).pdf'],
                ['label' => 'C. 7. 2. SPPD Koordinasi Jakarta Thp 1 - 3 Pegawai (SPM 00449A).pdf', 'file_name' => 'SPPD JAKARTA NOVEMBER (SPM 00449A).pdf'],
                ['label' => 'C. 7. 3. SPPD Dalam Kota Pekanbaru 2 Pegawai (SPM 00450A).pdf', 'file_name' => '1. SPPD NOVEMBER SPPG (SPM 00450A).pdf'],
                ['label' => 'C. 7. 4. SPPD Monitoring Dumai & Siak 11 Pegawai (SPM 00483A).pdf', 'file_name' => 'SPPD DUMAI SIAK SPPG NOV (SPM 00483A).pdf'],
                ['label' => 'C. 7. 5. SPPD Koordinasi Jakarta Thp 2 - 4 Pegawai (SPM 00485A).pdf', 'file_name' => 'SPPD JAKARTA NOVEMBER (SPM 00485A).pdf'],
                ['label' => 'C. 7. 6. SPPD Monitoring Dumai 3 Pegawai (SPM 00489A).pdf', 'file_name' => 'SPPD DUMAI DESEMBER (SPM 00489A).pdf'],
                ['label' => 'C. 7. 7. SPPD Koordinasi Wilayah Medan 2 Pegawai (SPM 00535A).pdf', 'file_name' => 'SPPD MEDAN DESEMBER (SPM 00535A) OK.pdf'],
                ['label' => 'C. 7. 8. SPPD Monitoring SPPG Desember 4 Pegawai (SPM 00536A).pdf', 'file_name' => 'SPPD SPPG DESEMBER (SPM 00536A).pdf'],
                ['label' => 'C. 7. 9. SPPD Monitoring Kampar Desember 3 Pegawai (SPM 00556A).pdf', 'file_name' => 'SPPD SPPG KAMPAR (SPM 00556A).pdf'],
                ['label' => 'C. 7. 10. SPPD Monitoring Siak Desember 3 Pegawai (SPM 00557A).pdf', 'file_name' => 'SPPD SPPG SIAK (SPM 00557A).pdf'],
                ['label' => 'C. 7. 11. Kuitansi & Bukti Pengeluaran Perjadin (PTUP SPM 00597A).pdf', 'file_name' => 'TUP 1 (KWI PERJADIN).pdf'],
                ['label' => 'C. 7. b. Daftar Nominatif & Rincian Bukti Pengeluaran Sah.docx', 'file_name' => 'C. 7. b. Daftar Nominatif dan Rincian Bukti Pengeluaran Sah.docx'],
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
        $this->syncVerifiedSppgData();
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
        $this->syncVerifiedSppgData();
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
                'uraian'    => 'Belanja Bahan (Dokumentasi & Pelaporan Rp 8,7 Jt, Konsumsi Rapat Rp 12 Jt, ATK Rp 26,7 Jt & Supplies Komputer Rp 26,7 Jt)',
                'pagu'      => 74100000.00,
                'realisasi' => 28283000.00,
                'sisa'      => 45817000.00,
                'persen'    => 38.17,
            ],
            [
                'akun'      => '524111',
                'uraian'    => 'Belanja Perjalanan Dinas Biasa (Koordinasi Luar Provinsi Jakarta/Medan Rp 89,7 Jt & Tim PPK ke Lokasi SPPG Riau Rp 237,2 Jt)',
                'pagu'      => 326900000.00,
                'realisasi' => 112576100.00,
                'sisa'      => 214323900.00,
                'persen'    => 34.44,
            ],
            [
                'akun'      => '524113',
                'uraian'    => 'Belanja Perjalanan Dinas Dalam Kota (Uang Harian > 8 Jam Tim PPK Lapangan Riau)',
                'pagu'      => 12000000.00,
                'realisasi' => 300000.00,
                'sisa'      => 11700000.00,
                'persen'    => 2.50,
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
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/laporan');
        if (empty($permissions['export'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses export pada menu ini.');
        }

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
     * Auto-heal / sinkronisasi otomatis data SPPG pada database agar 100% sesuai
     * dengan POK Revisi 16 (23 Des 2025) dan 16 berkas SPM/SP2D/PTUP asli.
     */
    private function syncVerifiedSppgData(): void
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('trn_swakelola_paket')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $paket = $db->table('trn_swakelola_paket')
            ->where('kode_paket', '06.691285.GM.7717.CBI.001.900.A')
            ->get()
            ->getRowArray();

        if (! $paket) {
            return;
        }

        $paketId = (int) $paket['id'];

        // 1. Pastikan NIP Kasatker dan nilai pagu/realisasi sesuai POK Rev 16 & LHA No. 9
        if (($paket['kasatker_nip'] ?? '') !== '19800626 200604 1 016' || (float) ($paket['realisasi_anggaran'] ?? 0) !== 141159100.00) {
            $db->table('trn_swakelola_paket')
                ->where('id', $paketId)
                ->update([
                    'pagu_anggaran'      => 413000000.00,
                    'realisasi_anggaran' => 141159100.00,
                    'ppk_nama'           => 'Nurhidayat Nugroho, S.Ars.',
                    'ppk_nip'            => '19901221 201802 1 001',
                    'kasatker_nama'      => 'Muhammad Yudi Prasetya, S.T.',
                    'kasatker_nip'       => '19800626 200604 1 016',
                    'updated_at'         => $now,
                ]);
        }

        // 2. Pastikan dokumen nomor_urut 1 (A.1) dan 9 (B.4) merujuk ke berkas terverifikasi
        if ($db->tableExists('trn_swakelola_dokumen')) {
            $db->table('trn_swakelola_dokumen')
                ->where('paket_id', $paketId)
                ->where('nomor_urut', 1)
                ->update([
                    'file_name'  => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf',
                    'file_path'  => 'public/uploads/swakelola/sppg/A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf',
                    'keterangan' => 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK SPPG Pagu Rp 413.000.000,-',
                ]);

            $db->table('trn_swakelola_dokumen')
                ->where('paket_id', $paketId)
                ->where('nomor_urut', 9)
                ->where('file_name', 'B. 4. a. KAK Final & B. 4. b. RAB Final Administrasi SPPG.docx')
                ->update([
                    'file_name' => 'B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx',
                    'file_path' => 'public/uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx',
                ]);
        }

        // 3. Pastikan trn_swakelola_realisasi memuat tepat 16 transaksi SPM/SP2D/PTUP asli senilai Rp 141.159.100,-
        if ($db->tableExists('trn_swakelola_realisasi')) {
            $rows = $db->table('trn_swakelola_realisasi')
                ->where('paket_id', $paketId)
                ->get()
                ->getResultArray();

            $sumRealisasi = 0.0;
            foreach ($rows as $r) {
                $sumRealisasi += (float) ($r['jumlah_bayar'] ?? 0);
            }

            if (count($rows) !== 16 || abs($sumRealisasi - 141159100.00) > 1.0) {
                $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->delete();

                $verifiedRealisasi = [
                    ['521211', 'Belanja Bahan - Pembelian ATK Kegiatan Pembangunan Gedung SPPG Bulan November 2025 (CV. Sinar Cahaya Lestari, Kwi No. 128 Tgl 20/11/2025)', '00415A', '2025-12-01', '259991311235904', 4943000.00, 'ATK BULAN NOVEMBER (SPM 00415A).pdf'],
                    ['521211', 'Belanja Bahan - Pembelian Supplies Komputer Kegiatan Pembangunan Gedung SPPG Bulan November 2025 (CV. Sinar Cahaya Lestari, Kwi No. 129 Tgl 20/11/2025)', '00414A', '2025-12-01', '259991311235905', 3843000.00, 'SUPLIES KOMPUTER BULAN NOVEMBER (SPM 00414A) (1).pdf'],
                    ['521211', 'Belanja Bahan - Pembelian Supplies Komputer Kegiatan Pembangunan Gedung SPPG Bulan Desember 2025 (CV. Sinar Cahaya Lestari, Kwi No. 164 Tgl 08/12/2025)', '00497A', '2025-12-09', '259991311369736', 5850000.00, 'SUPLIES KOMPUTER BULAN DESEMBER (SPM 00497A).pdf'],
                    ['521211', 'Belanja Bahan - Pembelian ATK Kegiatan Pembangunan Gedung SPPG Bulan Desember 2025 (CV. Sinar Cahaya Lestari, Kwi No. 163 Tgl 08/12/2025)', '00498A', '2025-12-09', '259991311369737', 4947000.00, 'ATK SPPG DESEMBER (SPM 00498A) OK.pdf'],
                    ['521211', 'Belanja Bahan - Pertanggungjawaban TUP (PTUP) Biaya Dokumentasi dan Pelaporan Kegiatan SPPG (Item POK 000875)', '00597A (PTUP)', '2025-12-31', '259991702155733', 8700000.00, 'TUP 1 (KWI DOKUMENTASI DAN PELAPORAN).pdf'],
                    ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kab. Kampar untuk 4 Pegawai (ST No. 290/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00445A', '2025-12-03', '259991330451846', 6240000.00, 'SPPD SPPG KAMPAR OKTOBER (SPM 00445A).pdf'],
                    ['524111', 'Perjadin Biasa - Koordinasi & Konsultasi Kegiatan SPPG ke Jakarta untuk 3 Pegawai (ST No. 285/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00449A', '2025-12-03', '259991330451774', 18264400.00, 'SPPD JAKARTA NOVEMBER (SPM 00449A).pdf'],
                    ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kota Dumai & Kab. Siak untuk 11 Pegawai (ST No. 295/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00483A', '2025-12-09', '259991330477567', 9236000.00, 'SPPD DUMAI SIAK SPPG NOV (SPM 00483A).pdf'],
                    ['524111', 'Perjadin Biasa - Koordinasi & Konsultasi Kegiatan SPPG ke Jakarta Tahap II untuk 4 Pegawai (ST No. 299/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 28/11/2025)', '00485A', '2025-12-09', '259991330477566', 26466600.00, 'SPPD JAKARTA NOVEMBER (SPM 00485A).pdf'],
                    ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kota Dumai untuk 3 Pegawai (ST No. 303/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 01/12/2025)', '00489A', '2025-12-09', '259991330478411', 7473000.00, 'SPPD DUMAI DESEMBER (SPM 00489A).pdf'],
                    ['524111', 'Perjadin Biasa - Koordinasi Regional Kegiatan SPPG ke Medan untuk 2 Pegawai (ST No. 313/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 03/12/2025)', '00535A', '2025-12-16', '259991330513926', 8900100.00, 'SPPD MEDAN DESEMBER (SPM 00535A) OK.pdf'],
                    ['524111', 'Perjadin Biasa - Monitoring Lapangan Gedung SPPG untuk 4 Pegawai (ST No. 311/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 03/12/2025)', '00536A', '2025-12-16', '259991330513968', 4160000.00, 'SPPD SPPG DESEMBER (SPM 00536A).pdf'],
                    ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kab. Kampar untuk 3 Pegawai (ST No. 335/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 09/12/2025)', '00556A', '2025-12-22', '259991330531985', 6670000.00, 'SPPD SPPG KAMPAR (SPM 00556A).pdf'],
                    ['524111', 'Perjadin Biasa - Monitoring Lokasi SPPG Kab. Siak untuk 3 Pegawai (ST No. 334/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 09/12/2025)', '00557A', '2025-12-22', '259991330532060', 6890000.00, 'SPPD SPPG SIAK (SPM 00557A).pdf'],
                    ['524111', 'Perjadin Biasa - Pertanggungjawaban TUP (PTUP) Tim PPK ke Lokasi SPPG Riau (Sewa Kendaraan Rp 4.286.000 + UH Rp 9.990.000 + Penginapan Rp 4.000.000)', '00597A (PTUP)', '2025-12-31', '259991702155733', 18276000.00, 'TUP 1 (KWI PERJADIN).pdf'],
                    ['524113', 'Perjadin Dalam Kota - Uang Harian > 8 Jam Tim PPK Lapangan Riau untuk 2 Pegawai (ST No. 291/SPT/SATKER/PPS-RIAU/Gs7/2025 Tgl 25/11/2025)', '00450A', '2025-12-03', '259991330451775', 300000.00, '1. SPPD NOVEMBER SPPG (SPM 00450A).pdf'],
                ];

                foreach ($verifiedRealisasi as $r) {
                    $db->table('trn_swakelola_realisasi')->insert([
                        'paket_id'       => $paketId,
                        'kode_akun'      => $r[0],
                        'uraian_belanja' => $r[1],
                        'nomor_spm'      => $r[2],
                        'tanggal_spm'    => $r[3],
                        'nomor_sp2d'     => $r[4],
                        'jumlah_bayar'   => $r[5],
                        'file_bukti'     => $r[6],
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
            }
        }
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
