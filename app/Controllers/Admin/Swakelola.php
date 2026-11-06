<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SwakelolaPaketModel;
use App\Models\SwakelolaDokumenModel;
use App\Models\SwakelolaRealisasiModel;
use App\Models\SwakelolaPerjadinPersonelModel;
use App\Models\Data\SwakelolaPerjadinData;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use CodeIgniter\HTTP\ResponseInterface;

class Swakelola extends BaseController
{
    protected SwakelolaPaketModel $paketModel;
    protected SwakelolaDokumenModel $dokumenModel;
    protected SwakelolaRealisasiModel $realisasiModel;
    protected SwakelolaPerjadinPersonelModel $perjadinPersonelModel;

    public function __construct()
    {
        $this->paketModel             = new SwakelolaPaketModel();
        $this->dokumenModel           = new SwakelolaDokumenModel();
        $this->realisasiModel         = new SwakelolaRealisasiModel();
        $this->perjadinPersonelModel = new SwakelolaPerjadinPersonelModel();
    }

    /**
     * Katalog Master 4 Paket Swakelola sesuai LHA No. 09 & POK Revisi 16 (23 Des 2025)
     */
    private function getPaketCatalog(): array
    {
        return [
            '06.691285.GM.7717.RBI.003.051.D' => [
                'lha_no'         => 1,
                'slug'           => 'phtc3',
                'pic_nama'       => 'Amsal Anwari, S.T.',
                'pic_label'      => 'Amsal',
                'kode_paket'     => '06.691285.GM.7717.RBI.003.051.D',
                'nama_kegiatan'  => 'Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3',
                'tahun_anggaran' => 2025,
                'pagu_anggaran'  => 90000000.00,
                'default_real'   => 87955000.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Hotel Royal Asnof, Kota Pekanbaru, Provinsi Riau',
                'jangka_waktu'   => 'November s/d Desember 2025 (Fullboard Meeting)',
                'rab_accounts'   => [
                    '521211' => [
                        'nama'   => 'Belanja Bahan',
                        'uraian' => 'Belanja Bahan (ATK, supplies, materi kit, spanduk, cetak laporan)',
                        'pagu'   => 19313000.00,
                    ],
                    '521213' => [
                        'nama'   => 'Belanja Honor Output Kegiatan',
                        'uraian' => 'Belanja Honor Output Kegiatan (Honorarium Narasumber Eselon III & Tim Swakelola 7 Orang)',
                        'pagu'   => 3900000.00,
                    ],
                    '524114' => [
                        'nama'   => 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota',
                        'uraian' => 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota (Paket Fullboard Hotel Royal Asnof, Uang Harian & Transport 33 Peserta)',
                        'pagu'   => 66787000.00,
                    ],
                ],
            ],
            '06.691285.GM.7717.CBI.001.900.A' => [
                'lha_no'         => 2,
                'slug'           => 'sppg',
                'pic_nama'       => 'Agung Kesuma',
                'pic_label'      => 'bg Agung',
                'kode_paket'     => '06.691285.GM.7717.CBI.001.900.A',
                'nama_kegiatan'  => 'Administrasi Kegiatan Pembangunan Gedung SPPG Provinsi Riau',
                'tahun_anggaran' => 2025,
                'pagu_anggaran'  => 413000000.00,
                'default_real'   => 141159100.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Provinsi Riau (Pekanbaru, Kampar, Dumai, Siak)',
                'jangka_waktu'   => 'September s/d Desember 2025 (4 bulan)',
                'rab_accounts'   => [
                    '521211' => [
                        'nama'   => 'Belanja Bahan',
                        'uraian' => 'Belanja Bahan (Dokumentasi & Pelaporan Rp 8,7 Jt, Konsumsi Rapat Rp 12 Jt, ATK Rp 26,7 Jt & Supplies Komputer Rp 26,7 Jt)',
                        'pagu'   => 74100000.00,
                    ],
                    '524111' => [
                        'nama'   => 'Belanja Perjalanan Dinas Biasa',
                        'uraian' => 'Belanja Perjalanan Dinas Biasa (Koordinasi Luar Provinsi Jakarta/Medan Rp 89,7 Jt & Tim PPK ke Lokasi SPPG Riau Rp 237,2 Jt)',
                        'pagu'   => 326900000.00,
                    ],
                    '524113' => [
                        'nama'   => 'Belanja Perjalanan Dinas Dalam Kota',
                        'uraian' => 'Belanja Perjalanan Dinas Dalam Kota (Uang Harian > 8 Jam Tim PPK Lapangan Riau)',
                        'pagu'   => 12000000.00,
                    ],
                ],
            ],
            '06.691285.GM.7717.RBI.003.900.AB' => [
                'lha_no'         => 3,
                'slug'           => 'madrasah_riau',
                'pic_nama'       => 'Jefri',
                'pic_label'      => 'bg Jefri',
                'kode_paket'     => '06.691285.GM.7717.RBI.003.900.AB',
                'nama_kegiatan'  => 'Administrasi Kegiatan Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau',
                'tahun_anggaran' => 2025,
                'pagu_anggaran'  => 569100000.00,
                'default_real'   => 322773000.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Provinsi Riau (12 Kabupaten / Kota)',
                'jangka_waktu'   => 'Juli s/d Desember 2025 (6 Bulan)',
                'rab_accounts'   => [
                    '521213' => [
                        'nama'   => 'Belanja Honor Output Kegiatan',
                        'uraian' => 'Belanja Honor Output Kegiatan (Honorarium Narasumber TP4D Kejati Riau 80 OJ)',
                        'pagu'   => 72000000.00,
                    ],
                    '521811' => [
                        'nama'   => 'Belanja Barang Persediaan Barang Konsumsi',
                        'uraian' => 'Belanja Barang Persediaan Barang Konsumsi (ATK & Bahan Habis Pakai Rp 15,735 Jt + Computer Supplies Rp 15,735 Jt)',
                        'pagu'   => 31470000.00,
                    ],
                    '522191' => [
                        'nama'   => 'Belanja Jasa Lainnya',
                        'uraian' => 'Belanja Jasa Lainnya (Tenaga Pendukung Teknis Administrasi Kegiatan 11 OB)',
                        'pagu'   => 66000000.00,
                    ],
                    '524111' => [
                        'nama'   => 'Belanja Perjalanan Dinas Biasa',
                        'uraian' => 'Belanja Perjalanan Dinas Biasa (Koordinasi ke Pusat Rp 145,7 Jt, Monitoring Evaluasi Rp 219,58 Jt & Pendampingan Kejaksaan Rp 12,6 Jt)',
                        'pagu'   => 377880000.00,
                    ],
                    '524113' => [
                        'nama'   => 'Belanja Perjalanan Dinas Dalam Kota',
                        'uraian' => 'Belanja Perjalanan Dinas Dalam Kota (Monitoring Evaluasi Dalam Kota Rp 19,2 Jt & Pendampingan Kejaksaan Rp 2,55 Jt)',
                        'pagu'   => 21750000.00,
                    ],
                ],
            ],
            '06.691285.GM.7717.RBI.003.900.G' => [
                'lha_no'         => 4,
                'slug'           => 'madrasah_riau5',
                'pic_nama'       => 'Muhammad Syahridwan, S.T.',
                'pic_label'      => 'bg Syahridwan',
                'kode_paket'     => '06.691285.GM.7717.RBI.003.900.G',
                'nama_kegiatan'  => 'Administrasi Kegiatan Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5',
                'tahun_anggaran' => 2026,
                'pagu_anggaran'  => 109828000.00,
                'default_real'   => 86533000.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Kabupaten Bengkalis dan Kota Dumai, Provinsi Riau',
                'jangka_waktu'   => 'Januari s/d Juni 2026 (6 Bulan)',
                'rab_accounts'   => [
                    '521811' => [
                        'nama'   => 'Belanja Barang Persediaan Barang Konsumsi',
                        'uraian' => 'Belanja Barang Persediaan Barang Konsumsi (ATK & Bahan Habis Pakai Pengawasan)',
                        'pagu'   => 24856000.00,
                    ],
                    '522191' => [
                        'nama'   => 'Belanja Jasa Lainnya',
                        'uraian' => 'Belanja Jasa Lainnya (Gaji Tenaga Pendukung Teknis Arsitektur Ade Putra, S.T. 5 Bulan)',
                        'pagu'   => 30000000.00,
                    ],
                    '524111' => [
                        'nama'   => 'Belanja Perjalanan Dinas Biasa',
                        'uraian' => 'Belanja Perjalanan Dinas Biasa (Monitoring Lapangan Madrasah Bengkalis & Dumai 6 SPM)',
                        'pagu'   => 54972000.00,
                    ],
                ],
            ],
        ];
    }

    /**
     * Master 28 Item Checklist Standar LHA No. 09
     */
    private function getStandardChecklistMaster(): array
    {
        return [
            ['A', 1, 'DIPA dan Revisi Anggaran (POK)'],
            ['A', 2, 'RUP/SIRUP paket Swakelola'],
            ['A', 3, 'KAK (Tujuan, Ruang Lingkup, Keluaran dan Spesifikasi)'],
            ['A', 4, 'RAB (Perkiraan Biaya dan Dasar Perhitungan)'],
            ['A', 5, 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola'],
            ['B', 6, 'Surat Keputusan Penyelenggara Swakelola oleh PA/KPA'],
            ['B', 7, 'Susunan Tim Persiapan, Tim Pelaksana dan Tim Pengawas beserta tugasnya'],
            ['B', 8, 'Rencana Kegiatan dan Jadwal Pelaksanaan'],
            ['B', 9, 'KAK final dan RAB rinci'],
            ['B', 10, 'Dokumen pembahasan atau hasil persiapan kegiatan'],
            ['B', 11, 'Rencana kebutuhan tenaga kerja, bahan, peralatan dan tenaga ahli'],
            ['C', 12, 'Surat Perintah atau Dokumen Penugasan'],
            ['C', 13, 'Daftar Hadir, Catatan Tenaga Kerja dan Bukti pelaksanaan Kegiatan'],
            ['C', 14, 'Laporan progres pekerjaan dan penggunaan anggaran secara berkala'],
            ['C', 15, 'Bukti pembelian bahan/barang dari Penyedia'],
            ['C', 16, 'Invoice/Kuitansi, Bukti Pembayaran, dan Dokumen Pajak sesuai ketentuan'],
            ['C', 17, 'Dokumentasi fisik kegiatan dan hasil pekerjaan'],
            ['C', 18, 'Bukti pengeluaran yang sah dan dapat ditelusuri'],
            ['D', 19, 'Laporan atau Catatan Pengawasan Tim Pengawas'],
            ['D', 20, 'Bukti Pemeriksaan kesesuaian volume, mutu, waktu dan keluaran'],
            ['D', 21, 'Rekonsiliasi realisasi fisik dan keuangan'],
            ['D', 22, 'Catatan penyimpangan dan bukti tindak lanjut'],
            ['E', 23, 'Laporan akhir pelaksanaan Swakelola'],
            ['E', 24, 'BAST hasil pekerjaan dari Tim Pelaksana kepada PPK'],
            ['E', 25, 'Dokumen pemeriksaan/verifikasi hasil pekerjaan'],
            ['E', 26, 'Rekapitulasi realisasi anggaran dibandingkan dengan RAB'],
            ['E', 27, 'Bukti penyelesaian pembayaran dan perpajakan yang relevan'],
            ['E', 28, 'Dokumen pencatatan aset/persediaan jika keluaran menghasilkan aset/persediaan'],
        ];
    }

    /**
     * Ambil dan perkaya daftar seluruh paket swakelola dengan metadata LHA No. 9 & progres dokumen
     */
    private function getEnrichedPaketList(): array
    {
        $catalog  = $this->getPaketCatalog();
        $rawList  = $this->paketModel->findAll();
        $db       = \Config\Database::connect();
        $docStats = [];

        if ($db->tableExists('trn_swakelola_dokumen')) {
            $rows = $db->table('trn_swakelola_dokumen')
                ->select('paket_id, status')
                ->get()
                ->getResultArray();
            foreach ($rows as $r) {
                $pid = (int) ($r['paket_id'] ?? 0);
                if (! isset($docStats[$pid])) {
                    $docStats[$pid] = ['ada' => 0, 'total' => 0];
                }
                $docStats[$pid]['total']++;
                if (($r['status'] ?? '') === 'Ada') {
                    $docStats[$pid]['ada']++;
                }
            }
        }

        $enriched = [];
        foreach ($rawList as $p) {
            $kode = (string) ($p['kode_paket'] ?? '');
            $meta = $catalog[$kode] ?? [
                'lha_no'       => 99,
                'slug'         => 'sppg',
                'pic_nama'     => '-',
                'pic_label'    => '-',
                'rab_accounts' => [],
            ];
            $pid = (int) $p['id'];
            $ada = $docStats[$pid]['ada'] ?? 0;
            $tot = $docStats[$pid]['total'] ?? 28;

            $p['lha_no']        = $meta['lha_no'];
            $p['slug']          = $meta['slug'];
            $p['pic_nama']      = $meta['pic_nama'];
            $p['pic_label']     = $meta['pic_label'];
            $p['rab_accounts']  = $meta['rab_accounts'];
            $p['dokumen_ada']   = $ada;
            $p['dokumen_total'] = $tot > 0 ? $tot : 28;
            $p['dokumen_pct']   = $p['dokumen_total'] > 0 ? round(($ada / $p['dokumen_total']) * 100, 1) : 0;
            $enriched[]         = $p;
        }

        usort($enriched, static fn(array $a, array $b): int => ($a['lha_no'] <=> $b['lha_no']) ?: ($a['id'] <=> $b['id']));

        return $enriched;
    }

    /**
     * Pilih paket aktif berdasarkan parameter ?paket_id= (default ke Paket 2 SPPG jika tidak ada parameter)
     */
    private function resolveSelectedPaket(int $requestedId, array $paketList): array
    {
        if ($requestedId > 0) {
            foreach ($paketList as $p) {
                if ((int) $p['id'] === $requestedId) {
                    return $p;
                }
            }
        }

        // Default ke Paket 2 (SPPG) jika belum memilih paket spesifik
        foreach ($paketList as $p) {
            if (($p['kode_paket'] ?? '') === '06.691285.GM.7717.CBI.001.900.A') {
                return $p;
            }
        }

        return $paketList[0] ?? [];
    }

    /**
     * Sub-Menu 1: Paket Kegiatan Swakelola
     */
    public function paketIndex()
    {
        $this->syncVerifiedSppgData();
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/paket');
        $paketList   = $this->getEnrichedPaketList();

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
        $paketList   = $this->getEnrichedPaketList();
        $requestedId = (int) ($this->request->getGet('paket_id') ?? 0);
        $paket       = $this->resolveSelectedPaket($requestedId, $paketList);
        $paketId     = (int) ($paket['id'] ?? 1);
        $slug        = (string) ($paket['slug'] ?? 'sppg');

        $dokumenList  = $this->dokumenModel->getByPaketId($paketId);
        $multiFileMap = $this->getMultiFilesMap($slug);

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
            $hasFile = ! empty($doc['file_name']) && $doc['status'] === 'Ada';
            if (isset($multiFileMap[$no]) && $hasFile) {
                $doc['sub_files'] = $multiFileMap[$no];
            } elseif (! empty($doc['file_name'])) {
                $doc['sub_files'] = [
                    [
                        'label'     => $doc['file_name'],
                        'file_name' => $doc['file_name'],
                    ],
                ];
            } else {
                $doc['sub_files'] = [];
            }

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
     * Unggah / Perbarui Berkas Dokumen Checklist (1 dari 28 Dokumen)
     */
    public function uploadDokumen(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/dokumen');
        if (empty($permissions['edit']) && empty($permissions['add'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengunggah/mengubah dokumen Swakelola.');
        }

        $doc = $this->dokumenModel->find($id);
        if (! $doc) {
            return redirect()->back()->with('error', 'Item dokumen checklist tidak ditemukan.');
        }

        $paketId    = (int) ($doc['paket_id'] ?? 0);
        $paketList  = $this->getEnrichedPaketList();
        $paket      = $this->resolveSelectedPaket($paketId, $paketList);
        $slug       = (string) ($paket['slug'] ?? 'sppg');
        $keterangan = trim((string) ($this->request->getPost('keterangan') ?? $doc['keterangan'] ?? ''));
        $statusPost = trim((string) ($this->request->getPost('status') ?? ''));

        $file       = $this->request->getFile('file_dokumen');
        $fileName   = (string) ($doc['file_name'] ?? '');
        $filePath   = (string) ($doc['file_path'] ?? '');
        $newStatus  = $statusPost !== '' ? $statusPost : (string) ($doc['status'] ?? 'Ada');

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, ['pdf', 'docx', 'xlsx', 'doc', 'xls'], true)) {
                return redirect()->to('/admin/swakelola/dokumen?paket_id=' . $paketId)
                    ->with('error', 'Format berkas harus berupa PDF, Word (.docx), atau Excel (.xlsx).');
            }

            $targetDir = FCPATH . 'uploads/swakelola/' . $slug;
            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $origName = $file->getClientName();
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.\s\(\)]/', '_', basename($origName));
            $prefix   = $doc['tahap'] . '. ' . $doc['nomor_urut'] . '. ';
            if (stripos($safeName, $doc['tahap'] . '.') !== 0) {
                $safeName = $prefix . $safeName;
            }

            $file->move($targetDir, $safeName, true);
            $fileName  = $safeName;
            $filePath  = 'public/uploads/swakelola/' . $slug . '/' . $safeName;
            $newStatus = 'Ada';
        }

        $this->dokumenModel->update($id, [
            'status'     => $newStatus,
            'file_name'  => $fileName !== '' ? $fileName : null,
            'file_path'  => $filePath !== '' ? $filePath : null,
            'keterangan' => $keterangan,
        ]);

        // Perbarui status paket otomatis jika 28/28 dokumen sudah Ada
        $this->refreshPaketStatus($paketId);

        return redirect()->to('/admin/swakelola/dokumen?paket_id=' . $paketId)
            ->with('success', 'Dokumen "' . $doc['nama_dokumen'] . '" berhasil diperbarui.');
    }

    /**
     * Daftar rincian sub-berkas untuk item checklist yang memiliki lebih dari 1 dokumen pendukung
     */
    private function getMultiFilesMap(string $slug = 'sppg'): array
    {
        if ($slug === 'phtc3') {
            return [
                1 => [
                    ['label' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'file_name' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf'],
                    ['label' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK Rakor Madrasah PHTC Tahap 3 TA 2025.docx', 'file_name' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK Rakor Madrasah PHTC Tahap 3 TA 2025.docx'],
                ],
                3 => [
                    ['label' => 'A. 3. KAK_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'file_name' => 'A. 3. KAK_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf'],
                    ['label' => 'A. 3. KAK Final Rakor Survei dan Perencanaan Teknis.docx', 'file_name' => 'B. 4. a. KAK_Final_Rakor_Survei_Perencanaan_Teknis_Madrasah_PHTC_Tahap_3.docx'],
                ],
                4 => [
                    ['label' => 'A. 4. RAB_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'file_name' => 'A. 4. RAB_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf'],
                    ['label' => 'A. 4. RAB Final Rakor Madrasah PHTC Tahap 3.docx', 'file_name' => 'B. 4. b. RAB Final_Rakor_Madrasah_PHTC_Tahap_3.docx'],
                ],
                6 => [
                    ['label' => 'B. 1. SK Kasatker No. 27/2025 Pembentukan Tim Penyelenggara Swakelola.pdf', 'file_name' => 'PEMBENTUKAN TIM PENYELENGGARA SWAKELOLA KEGIATAN RAPAT KOORDINASI.pdf'],
                    ['label' => 'B. 1. Salinan SK Penetapan Penyelenggara Swakelola Rakor Madrasah PHTC Tahap 3.docx', 'file_name' => 'B. 1. SK Penetapan Penyelenggara Swakelola Rakor Madrasah PHTC Tahap 3.docx'],
                ],
                9 => [
                    ['label' => 'B. 4. a. KAK_Final_Rakor_Survei_Perencanaan_Teknis_Madrasah_PHTC_Tahap_3.docx', 'file_name' => 'B. 4. a. KAK_Final_Rakor_Survei_Perencanaan_Teknis_Madrasah_PHTC_Tahap_3.docx'],
                    ['label' => 'B. 4. b. RAB Final_Rakor_Madrasah_PHTC_Tahap_3.docx', 'file_name' => 'B. 4. b. RAB Final_Rakor_Madrasah_PHTC_Tahap_3.docx'],
                    ['label' => 'B. 4. KAK Final dan RAB Rinci Rakor Madrasah PHTC Tahap 3.docx', 'file_name' => 'B. 4. KAK Final dan RAB Rinci Rakor Madrasah PHTC Tahap 3.docx'],
                ],
                13 => [
                    ['label' => 'C. 2. Laporan Pelaksanaan dan Presensi Daftar Hadir Peserta Rakor (33 Org).pdf', 'file_name' => 'C. 2. Laporan Pelaksanaan dan Daftar Hadir.pdf'],
                    ['label' => 'C. 2. Catatan Pelaksanaan dan Daftar Hadir Rakor Madrasah PHTC Tahap 3.docx', 'file_name' => 'C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx'],
                ],
                15 => [
                    ['label' => 'C. 4. 1. Belanja Bahan - ATK Bulan November (SPM 00417A).pdf', 'file_name' => 'ATK BULAN NOVEMBER (SPM 00417A).pdf'],
                    ['label' => 'C. 4. 2. Belanja Bahan - Supplies Komputer Bulan November (SPM 00416A).pdf', 'file_name' => 'SUPLIES KOMPUTER NOVEMBER (SPM 00416A) OK.pdf'],
                    ['label' => 'C. 4. 3. Belanja Bahan - Pengadaan Materi Kit Rakor (SPM 00392A).pdf', 'file_name' => 'MATERI KIT (SPM 00392A).pdf'],
                    ['label' => 'C. 4. 4. Belanja Bahan - Spanduk Rakor GUP (SPM 00442A).pdf', 'file_name' => 'GUP 02 DES (SPM 00442A) SPANDUK.pdf'],
                    ['label' => 'C. 4. 5. Belanja Bahan - Cetak Laporan Kegiatan Rakor (SPM 00462A).pdf', 'file_name' => 'CETAK LAPORAN RAKOR PHTC 3 (SPM 00462 A).pdf'],
                    ['label' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan dan Barang dari Penyedia.docx', 'file_name' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan dan Barang dari Penyedia.docx'],
                ],
                16 => [
                    ['label' => 'C. 5. 1. Belanja Honor - Honorarium Narasumber Eselon III (SPM 00423A).pdf', 'file_name' => 'HONORARIUM NARASUMBER (SPM  00423A).pdf'],
                    ['label' => 'C. 5. 2. Belanja Honor - Honorarium Tim Penyelenggara Swakelola 7 Org (SPM 00467A).pdf', 'file_name' => 'HONOR TIM SWAKELOLA (SPM 00467A) OKE.pdf'],
                    ['label' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Dokumen Pajak Honor.docx', 'file_name' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Bukti Setor Pajak.docx'],
                ],
                17 => [
                    ['label' => 'C. 6. Dokumentasi Foto Kegiatan Rapat & Survey Lapangan Madrasah PHTC 3.pdf', 'file_name' => 'C. 6. Dokumentasi Kegiatan Rapat dan Survei  PHTC Tahap 3.pdf'],
                    ['label' => 'C. 6. Album Dokumentasi Fisik dan Hasil Pekerjaan Rakor PHTC 3.docx', 'file_name' => 'C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan Rakor PHTC 3.docx'],
                ],
                18 => [
                    ['label' => 'C. 7. 1. Paket Meeting Fullboard Hotel Royal Asnof Pekanbaru (SPM 00446A).pdf', 'file_name' => 'PAKET MEETING RAKOR (SPM 00446A) OK.pdf'],
                    ['label' => 'C. 7. 2. Perjadin Dlm Kota - Uang Harian Peserta Rakor 33 Org (SPM 00425A).pdf', 'file_name' => 'UANG HARIAN PESERTA (SPM 00425A).pdf'],
                    ['label' => 'C. 7. 3. Perjadin Dlm Kota - Uang Transport Peserta Rakor 33 Org (SPM 00424A).pdf', 'file_name' => 'UANG TRANSPORT PESERTA (SPM 00424A).pdf'],
                    ['label' => 'C. 7. b. Daftar Nominatif dan Rincian Pengeluaran Sah (Hotel & Perjadin).docx', 'file_name' => 'C. 7. b. Daftar Nominatif dan Rincian Bukti Pengeluaran Sah.docx'],
                ],
            ];
        }

        if ($slug === 'madrasah_riau') {
            return [
                1 => [
                    ['label' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'file_name' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf'],
                    ['label' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK Madrasah PHTC Riau TA 2025.docx', 'file_name' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK Madrasah PHTC Riau TA 2025.docx'],
                ],
                9 => [
                    ['label' => 'B. 4. a. KAK Final Administrasi Madrasah PHTC Riau.docx', 'file_name' => 'B. 4. a. KAK Final Administrasi Madrasah PHTC Riau.docx'],
                    ['label' => 'B. 4. b. RAB Final Administrasi Madrasah PHTC Riau.docx', 'file_name' => 'B. 4. b. RAB Final Administrasi Madrasah PHTC Riau.docx'],
                    ['label' => 'B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau.docx', 'file_name' => 'B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau.docx'],
                ],
                15 => [
                    ['label' => 'C. 4. 1. Kuitansi & BAST Pembelian ATK Juli (SPM 00279A GUP).pdf', 'file_name' => '1. GUP SEPTEMBER (SPM 00279A) ATK JULI OKE.pdf'],
                    ['label' => 'C. 4. 2. Kuitansi & Faktur ATK & Supplies Agustus (SPM 00255A).pdf', 'file_name' => '2. ATK DAN SUPPLIES AGUSTUS (SPM 00255A) OK.pdf'],
                    ['label' => 'C. 4. 3. Kuitansi & Faktur ATK & Supplies September (SPM 00294A).pdf', 'file_name' => '3. ATK DAN SUPPLIES SEPTEMBER (SPM 00294A) OK.pdf'],
                    ['label' => 'C. 4. 4. Kuitansi & Faktur ATK & Supplies Oktober (SPM 00321A).pdf', 'file_name' => '4. ATK DAN SUPPLIES  OKTOBER (SPM 00321 A) OK .pdf'],
                    ['label' => 'C. 4. 5. Kuitansi & Faktur ATK & Supplies November (SPM 00371A).pdf', 'file_name' => '5. ATK DAN SUPLIES NOVEMBER (SPM 00371A) OK.pdf'],
                    ['label' => 'C. 4. 6. Kuitansi & Faktur ATK & Supplies Desember (SPM 00499A).pdf', 'file_name' => '6. ATK SUPLIES DESEMBER (SPM 00499A) OK.pdf'],
                    ['label' => 'C. 4. b. Rekapitulasi Pembelian Bahan & Barang CV. Sinar Cahaya Lestari.docx', 'file_name' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan dan Barang dari Penyedia.docx'],
                ],
                16 => [
                    ['label' => 'C. 5. 1. Honorarium Narasumber TP4D Kejati Riau Okt (SPM 00350A).pdf', 'file_name' => '1. HONOR NARSUM TP4D OKTOBER (SPM 00350A).pdf'],
                    ['label' => 'C. 5. 2. Honorarium Narasumber TP4D Kejati Riau Nov (SPM 00464A).pdf', 'file_name' => '2. HONOR TP4D NOVEMBER (SPM 00464A).pdf'],
                    ['label' => 'C. 5. 3. Gaji Tenaga Pendukung Juli-Ags a.n. Revano (SPM 00241A).pdf', 'file_name' => '1. GAJI BULAN JULI DAN AGUSTUS (SPM 00241A) OKE.pdf'],
                    ['label' => 'C. 5. 4. Gaji Tenaga Pendukung Agustus a.n. Ade Putra (SPM 00242A).pdf', 'file_name' => '2. GAJI BULAN AGUSTUS (SPM 00242A).pdf'],
                    ['label' => 'C. 5. 5. Gaji Tenaga Pendukung September a.n. Ade Putra (SPM 00256A).pdf', 'file_name' => '3. GAJI BULAN SEPTEMBER (SPM 00256A) OKE.pdf'],
                    ['label' => 'C. 5. 6. Gaji Tenaga Pendukung September a.n. Revano (SPM 00257A).pdf', 'file_name' => '4. GAJI BULAN SEPTEMBER (SPM 00257A) OKE.pdf'],
                    ['label' => 'C. 5. 7. Gaji Tenaga Pendukung Oktober a.n. Revano (SPM 00338A).pdf', 'file_name' => '5. GAJI BULAN OKTOBER (SPM 00338A) OK.pdf'],
                    ['label' => 'C. 5. 8. Gaji Tenaga Pendukung Oktober a.n. Ade Putra (SPM 00337A).pdf', 'file_name' => '6. GAJI BULAN OKTOBER (SPM 00337A).pdf'],
                    ['label' => 'C. 5. 9. Gaji Tenaga Pendukung November a.n. Ade Putra (SPM 00429A).pdf', 'file_name' => '7. GAJI BULAN NOVEMBER (SPM 00429).pdf'],
                    ['label' => 'C. 5. 10. Gaji Tenaga Pendukung November a.n. Revano (SPM 00428A).pdf', 'file_name' => '8. GAJI BULAN NOVEMBER (SPM 00428A).pdf'],
                    ['label' => 'C. 5. 11. Gaji Tenaga Pendukung Desember a.n. Ade Putra (SPM 00481A).pdf', 'file_name' => '9. GAJI DESEMBER (SPM 00481A) OK.pdf'],
                    ['label' => 'C. 5. 12. Gaji Tenaga Pendukung Desember a.n. Revano (SPM 00480A).pdf', 'file_name' => '10. GAJI DESEMBER (SPM 00480A).pdf'],
                    ['label' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Bukti Setor Pajak (PPN/PPh).docx', 'file_name' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Bukti Setor Pajak.docx'],
                ],
                18 => [
                    ['label' => 'C. 7. 1. SPPD MC-0 Siak MAN 1 - 4 Pegawai (SPM 00270A).pdf', 'file_name' => 'SPPD SIAK PHTC 1 SEPTEMBER (SPM 00270A) OK.pdf'],
                    ['label' => 'C. 7. 2. SPPD Workshop Pengendalian Banten - 3 Pegawai (SPM 00287A).pdf', 'file_name' => 'SPPD OKTOBER BANTEN (SPM 00287A) OK.pdf'],
                    ['label' => 'C. 7. 3. SPPD SIPBJ V2 & SPSE 4.5 Medan - 3 Pegawai (SPM 00310A).pdf', 'file_name' => 'SPPD MEDAN OKTOBER (SPM 00310A).pdf'],
                    ['label' => 'C. 7. 4. SPPD Monev Siak PHTC 1 Oktober - 4 Pegawai (SPM 00311A).pdf', 'file_name' => 'SPPD SIAK PHTC 1 OKTOBER (SPM 00311A) OK.pdf'],
                    ['label' => 'C. 7. 5. SPPD Monitoring Siak Oktober - 3 Pegawai (SPM 00313A).pdf', 'file_name' => 'SPPD SIAK OKTOBER (SPM 00313A).pdf'],
                    ['label' => 'C. 7. 6. SPPD Koordinasi Madrasah ke Aceh - 1 Pegawai (SPM 00315A).pdf', 'file_name' => 'SPPD ACEH OKTOBER (SPM 00315A).pdf'],
                    ['label' => 'C. 7. 7. SPPD Koordinasi Banten November - 2 Pegawai (SPM 00388A).pdf', 'file_name' => 'SPPD BANTEN NOVEMBER (SPM 00388A).pdf'],
                    ['label' => 'C. 7. 8. SPPD Monev Siak PHTC 1 November - 4 Pegawai (SPM 00444A).pdf', 'file_name' => 'SPPD SIAK PHTC 1 NOVEMBER (SPM 00444A).pdf'],
                    ['label' => 'C. 7. 9. SPPD Koordinasi Jakarta November - 3 Pegawai (SPM 00447A).pdf', 'file_name' => 'SPPD JAKARTA NOVEMBER (SPM 00447A).pdf'],
                    ['label' => 'C. 7. 10. SPPD Monev Siak Desember - 2 Pegawai (SPM 00532A).pdf', 'file_name' => 'SPPD SIAK DESEMBER (SPM 00532A).pdf'],
                    ['label' => 'C. 7. 11. SPPD Koordinasi Banten Desember - 1 Pegawai (SPM 00544A).pdf', 'file_name' => 'SPPD BANTEN DESMBER (SPM 00544A).pdf'],
                    ['label' => 'C. 7. 12. SPPD Koordinasi Banten & Semarang - 1 Pegawai (SPM 00558A).pdf', 'file_name' => 'SPPD BANTEN SEMARANG DESEMBER (SPM 00558A).pdf'],
                    ['label' => 'C. 7. 13. SPPD Koordinasi Akhir Banten - 3 Pegawai (SPM 00562A).pdf', 'file_name' => 'SPPD DESEMBER BANTEN (SPM 00562A).pdf'],
                    ['label' => 'C. 7. 14. PTUP SPPD Monev Lapangan Madrasah Subkomp AB (SPM 00599A).pdf', 'file_name' => 'TUP 3 SPPD PHTC 1 (SPM 00599A).pdf'],
                    ['label' => 'C. 7. 15. SPPD Dalam Kota Pekanbaru Sep - 7 Pegawai (SPM 00271A).pdf', 'file_name' => 'SPPD PKU SEPTEMBER PHTC 1  (SPM 00271A).pdf'],
                    ['label' => 'C. 7. 16. SPPD Dalam Kota Pekanbaru Okt Thp 1 - 4 Pegawai (SPM 00309A).pdf', 'file_name' => 'SPPD PKU OKTOBER PHTC 1 (SPM 00309A) OK.pdf'],
                    ['label' => 'C. 7. 17. SPPD Dalam Kota Pekanbaru Okt Thp 2 - 5 Pegawai (SPM 00312A).pdf', 'file_name' => 'SPPD PEKANBARU OKTOBER PHTC 1 (SPM 00312A).pdf'],
                    ['label' => 'C. 7. 18. SPPD Dalam Kota Pekanbaru Okt Thp 3 - 5 Pegawai (SPM 00349A).pdf', 'file_name' => 'SPPD PKU OKTOBER PHTC 1  (SPM 00349A) OK.pdf'],
                    ['label' => 'C. 7. 19. SPPD Dalam Kota Pekanbaru Nov Thp 1 - 4 Pegawai (SPM 00405A).pdf', 'file_name' => 'SPPD PKU NOVEMBER PHTC 1 (SPM 00405A) OK.pdf'],
                    ['label' => 'C. 7. 20. SPPD Dalam Kota Pekanbaru Nov Thp 2 - 2 Pegawai (SPM 00406A).pdf', 'file_name' => 'SPPD PKU NOVEMBER PHTC 1 (SPM 00406A).pdf'],
                    ['label' => 'C. 7. 21. SPPD Dalam Kota Pekanbaru Nov Thp 3 - 3 Pegawai (SPM 00443A).pdf', 'file_name' => 'SPPD PKU NOVEMBER PHTC 1 (SPM 00443A).pdf'],
                    ['label' => 'C. 7. 22. SPPD Dalam Kota Pekanbaru Des Thp 1 - 2 Pegawai (SPM 00491A).pdf', 'file_name' => 'SPPD PKU DESEMBER PHTC 1 (SPM 00491A).pdf'],
                    ['label' => 'C. 7. 23. SPPD Dalam Kota Pekanbaru Des Thp 2 - 4 Pegawai (SPM 00493A).pdf', 'file_name' => 'SPPD PKU DESEMBER PHTC 1 (SPM 00493A).pdf'],
                    ['label' => 'C. 7. 24. SPPD Dalam Kota Pekanbaru Des Thp 3 - 2 Pegawai (SPM 00559A).pdf', 'file_name' => 'SPPD PKU DESEMBER PHTC 1 (SPM 00559A) OK.pdf'],
                    ['label' => 'C. 7. 25. SPPD Dalam Kota Pekanbaru Des Thp 4 - 7 Pegawai (SPM 00561A).pdf', 'file_name' => 'SPPD PEKANBARU  DESEMBER (SPM 00561A).pdf'],
                    ['label' => 'C. 7. b. Daftar Nominatif & Rincian Bukti Pengeluaran Sah.docx', 'file_name' => 'C. 7. b. Daftar Nominatif dan Rincian Bukti Pengeluaran Sah.docx'],
                ],
            ];
        }

        if ($slug === 'madrasah_riau5') {
            return [
                1 => [
                    ['label' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'file_name' => 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf'],
                    ['label' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK Madrasah PHTC Riau 5 TA 2025-2026.docx', 'file_name' => 'A. 1. Petikan DIPA dan Revisi Anggaran POK Madrasah PHTC Riau 5 TA 2025-2026.docx'],
                ],
                3 => [
                    ['label' => 'A. 3. KAK Administrasi PHTC Provinsi Riau 5 (Revisi).pdf', 'file_name' => 'KAK ADMINISTRASI PHTC PROVINSI RIAU 5 rev.pdf'],
                    ['label' => 'A. 3. Kerangka Acuan Kerja Administrasi Madrasah PHTC 5.pdf', 'file_name' => 'Kerangka Acuan Kerja.pdf'],
                    ['label' => 'A. 3. KAK Administrasi Madrasah PHTC Provinsi Riau 5.docx', 'file_name' => 'A. 3. KAK Administrasi Madrasah PHTC Provinsi Riau 5.docx'],
                ],
                4 => [
                    ['label' => 'A. 4. RAB Administrasi PHTC Provinsi Riau 5 Definitif.pdf', 'file_name' => 'RAB ADMINISTRASI PHTC PROVINSI RIAU 5 ok.pdf'],
                    ['label' => 'A. 4. RAB Administrasi Madrasah PHTC Provinsi Riau 5.docx', 'file_name' => 'A. 4. RAB Administrasi Madrasah PHTC Provinsi Riau 5.docx'],
                ],
                6 => [
                    ['label' => 'B. 1. SK Penetapan Penyelenggara Swakelola Madrasah PHTC Riau 5 oleh KPA.docx', 'file_name' => 'B. 1. SK Penetapan Penyelenggara Swakelola Madrasah PHTC Riau 5 oleh KPA.docx'],
                ],
                9 => [
                    ['label' => 'B. 4. a. KAK Final Administrasi Madrasah PHTC Riau 5.docx', 'file_name' => 'B. 4. a. KAK Final Administrasi Madrasah PHTC Riau 5.docx'],
                    ['label' => 'B. 4. b. RAB Final Administrasi Madrasah PHTC Riau 5.docx', 'file_name' => 'B. 4. b. RAB Final Administrasi Madrasah PHTC Riau 5.docx'],
                    ['label' => 'B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau 5.docx', 'file_name' => 'B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau 5.docx'],
                ],
                12 => [
                    ['label' => 'C. 1. Surat Perjanjian Kerja (SPK) Tenaga Ahli Ade Putra No. 01/2026.pdf', 'file_name' => 'Surat Perjanjian Kerja.pdf'],
                    ['label' => 'C. 1. Dokumen Proses Pengadaan Tenaga Ahli Ade Putra.pdf', 'file_name' => 'Dokumen Proses Pengadaan.pdf'],
                    ['label' => 'C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'file_name' => 'C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx'],
                ],
                15 => [
                    ['label' => 'C. 4. 1. Belanja Bahan - ATK PHTC 5 GUP III Maret (SPM 00086A).pdf', 'file_name' => '86. GUP III MARET (SPM 00086A) atk phtc 5.pdf'],
                    ['label' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan dan Barang dari Penyedia.docx', 'file_name' => 'C. 4. b. Rekapitulasi Bukti Pembelian Bahan dan Barang dari Penyedia.docx'],
                ],
                16 => [
                    ['label' => 'C. 5. 1. Gaji Tenaga Teknis Ade Putra Bulan Januari (SPM 00048A).pdf', 'file_name' => '1. GAJI JAN ADE (SPM 00048A).pdf'],
                    ['label' => 'C. 5. 2. Gaji Tenaga Teknis Ade Putra Bulan Februari (SPM 00059A).pdf', 'file_name' => '2. GAJI FEB ADE (SPM 00059A).pdf'],
                    ['label' => 'C. 5. 3. Gaji Tenaga Teknis Ade Putra Bulan Maret (SPM 00112A).pdf', 'file_name' => '3. GAJI MARET ADE (SPM 00112A).pdf'],
                    ['label' => 'C. 5. 4. Gaji Tenaga Teknis Ade Putra Bulan April (SPM 00147A).pdf', 'file_name' => '4. GAJI APRIL ADE (SPM 00147A).pdf'],
                    ['label' => 'C. 5. 5. Gaji Tenaga Teknis Ade Putra Bulan Mei (SPM 00200A).pdf', 'file_name' => '5. GAJI MEI ADE (SPM 00200A).pdf'],
                    ['label' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Bukti Setor Pajak.docx', 'file_name' => 'C. 5. b. Rekapitulasi Invoice Kuitansi dan Bukti Setor Pajak.docx'],
                ],
                18 => [
                    ['label' => 'C. 7. 1. SPPD Januari Bengkalis Tahap I (SPM 00029A).pdf', 'file_name' => '1. SPPD JANUARI BENGKALIS (SPM 00029A).pdf'],
                    ['label' => 'C. 7. 2. SPPD Januari Bengkalis Tahap II (SPM 00030A).pdf', 'file_name' => '2. SPPD JANUARI BENGKALIS (SPM 00030A).pdf'],
                    ['label' => 'C. 7. 3. SPPD Februari Bengkalis (SPM 00075A).pdf', 'file_name' => '3. SPPD FEBRUARI BENGKALIS (SPM 00075A).pdf'],
                    ['label' => 'C. 7. 4. SPPD Maret Bengkalis (SPM 00106A).pdf', 'file_name' => '4. SPPD MARET BENGKALIS (SPM 00106A).pdf'],
                    ['label' => 'C. 7. 5. SPPD April Bengkalis (SPM 00140A).pdf', 'file_name' => '5. SPPD APRIL BENGKALIS (SPM 00140A).pdf'],
                    ['label' => 'C. 7. 6. SPPD Dumai & Bengkalis Juni (SPM 00226A).pdf', 'file_name' => '6. SPPD DUMAI BENGKALIS JUNI (SPM 00226A).pdf'],
                    ['label' => 'C. 7. b. Daftar Nominatif dan Rincian Bukti Pengeluaran Sah (SPPD).docx', 'file_name' => 'C. 7. b. Daftar Nominatif dan Rincian Bukti Pengeluaran Sah.docx'],
                ],
            ];
        }

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
     * Helper cari lokasi berkas fisik (mendukung multi-paket public/uploads/swakelola/<slug>/ dan fallback audit)
     */
    private function resolveDocumentPath(string $fileName, ?string $dbPath = null, string $slug = 'sppg'): ?string
    {
        $safeFileName = basename(trim($fileName));
        if ($safeFileName === '') {
            return null;
        }

        $candidates = [];

        // 1. Berdasarkan database path jika ada
        if (! empty($dbPath)) {
            $candidates[] = ROOTPATH . ltrim($dbPath, '/');
            $candidates[] = FCPATH . ltrim($dbPath, '/');
        }

        // 2. Lokasi paket aktif di public/uploads/swakelola/<slug>/
        $candidates[] = FCPATH . 'uploads/swakelola/' . $slug . '/' . $safeFileName;
        $candidates[] = ROOTPATH . 'public/uploads/swakelola/' . $slug . '/' . $safeFileName;

        // 3. Cek semua folder paket (sppg, phtc3, madrasah_riau, madrasah_riau5)
        foreach (['sppg', 'phtc3', 'madrasah_riau', 'madrasah_riau5'] as $folder) {
            $candidates[] = FCPATH . 'uploads/swakelola/' . $folder . '/' . $safeFileName;
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/' . $folder . '/' . $safeFileName;
        }

        // Fallback untuk nama lama item B.4 SPPG & Madrasah Riau
        if ($slug === 'sppg' && strpos($safeFileName, 'B. 4.') === 0) {
            $candidates[] = FCPATH . 'uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx';
            $candidates[] = FCPATH . 'uploads/swakelola/sppg/B. 4. a. KAK Final Administrasi SPPG.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/B. 4. a. KAK Final Administrasi SPPG.docx';
        }
        if ($slug === 'madrasah_riau' && strpos($safeFileName, 'B. 4.') === 0) {
            $candidates[] = FCPATH . 'uploads/swakelola/madrasah_riau/B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/madrasah_riau/B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau.docx';
        }

        // 4. Fallback do_not_upload/audit
        $candidates[] = ROOTPATH . 'do_not_upload/audit/hasil_sppg/' . $safeFileName;
        $candidates[] = ROOTPATH . 'do_not_upload/audit/hasil_madrasah_riau/' . $safeFileName;
        $candidates[] = ROOTPATH . 'do_not_upload/audit/contoh/' . $safeFileName;

        foreach ($candidates as $path) {
            if (file_exists($path) && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Cari slug folder paket berdasarkan paket_id
     */
    private function getSlugByPaketId(int $paketId): string
    {
        if ($paketId <= 0) {
            return 'sppg';
        }
        $paket = $this->paketModel->find($paketId);
        if (! $paket) {
            return 'sppg';
        }
        $catalog = $this->getPaketCatalog();
        $kode    = (string) ($paket['kode_paket'] ?? '');
        return $catalog[$kode]['slug'] ?? 'sppg';
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

        $slug = $doc ? $this->getSlugByPaketId((int) ($doc['paket_id'] ?? 0)) : 'sppg';
        $targetFileName = $requestedFile !== '' ? basename($requestedFile) : (string) ($doc['file_name'] ?? '');
        $filePath = $this->resolveDocumentPath($targetFileName, $requestedFile === '' ? ($doc['file_path'] ?? null) : null, $slug);

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

        $slug = $doc ? $this->getSlugByPaketId((int) ($doc['paket_id'] ?? 0)) : 'sppg';
        $targetFileName = $requestedFile !== '' ? basename($requestedFile) : (string) ($doc['file_name'] ?? '');
        $filePath = $this->resolveDocumentPath($targetFileName, $requestedFile === '' ? ($doc['file_path'] ?? null) : null, $slug);
        if (! $filePath) {
            return $this->response->setStatusCode(404, 'Berkas fisik tidak ditemukan di server');
        }

        $mimeType = $this->detectSafeMimeType($filePath);
        $fileContent = file_get_contents($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes(basename($filePath)) . '"')
            ->setHeader('Cache-Control', 'no-cache, must-revalidate')
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

        $slug = $doc ? $this->getSlugByPaketId((int) ($doc['paket_id'] ?? 0)) : 'sppg';
        $targetFileName = $requestedFile !== '' ? basename($requestedFile) : (string) ($doc['file_name'] ?? '');
        $filePath = $this->resolveDocumentPath($targetFileName, $requestedFile === '' ? ($doc['file_path'] ?? null) : null, $slug);
        $fileExists = ($filePath !== null);
        $resolvedFileName = $fileExists ? basename($filePath) : $targetFileName;
        $ext = strtolower(pathinfo($resolvedFileName, PATHINFO_EXTENSION));
        $ver = $fileExists ? (string) @filemtime($filePath) : (string) time();

        $querySuffix = $requestedFile !== ''
            ? ('?file=' . rawurlencode($resolvedFileName) . '&v=' . $ver)
            : ('?v=' . $ver);
        $publicUrl   = base_url('uploads/swakelola/' . $slug . '/' . rawurlencode($resolvedFileName)) . '?v=' . $ver;
        $inlineUrl   = base_url('admin/swakelola/dokumen/view/' . $id) . $querySuffix;
        $downloadUrl = base_url('admin/swakelola/dokumen/download/' . $id) . ($requestedFile !== '' ? ('?file=' . rawurlencode($resolvedFileName)) : '');

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
        $paketList   = $this->getEnrichedPaketList();
        $requestedId = (int) ($this->request->getGet('paket_id') ?? 0);
        $paket       = $this->resolveSelectedPaket($requestedId, $paketList);
        $paketId     = (int) ($paket['id'] ?? 1);
        $rabAccounts = $paket['rab_accounts'] ?? [];

        $realisasi = $this->realisasiModel->getByPaketId($paketId);

        $totalRealisasi = 0.0;
        $perAkun = [];
        foreach ($rabAccounts as $kodeAkun => $accMeta) {
            $perAkun[$kodeAkun] = [
                'nama'  => $accMeta['nama'],
                'pagu'  => (float) $accMeta['pagu'],
                'total' => 0.0,
                'count' => 0,
            ];
        }

        foreach ($realisasi as $r) {
            $totalRealisasi += (float) $r['jumlah_bayar'];
            $akun = (string) $r['kode_akun'];
            if (! isset($perAkun[$akun])) {
                $perAkun[$akun] = [
                    'nama'  => $this->getAccountName($akun),
                    'pagu'  => 0.0,
                    'total' => 0.0,
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
            'rabAccounts'    => $rabAccounts,
            'permissions'    => $permissions,
        ]);
    }

    /**
     * Tambah / Edit Realisasi SPM & Bukti Pengeluaran
     */
    public function saveRealisasi()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/realisasi');
        $id          = (int) ($this->request->getPost('id') ?? 0);

        if ($id > 0 && empty($permissions['edit'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses ubah (edit) pada menu Realisasi Anggaran.');
        }
        if ($id <= 0 && empty($permissions['add'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses tambah (add) pada menu Realisasi Anggaran.');
        }

        $paketId       = (int) ($this->request->getPost('paket_id') ?? 0);
        $kodeAkun      = trim((string) ($this->request->getPost('kode_akun') ?? '521211'));
        $uraianBelanja = trim((string) ($this->request->getPost('uraian_belanja') ?? ''));
        $nomorSpm      = trim((string) ($this->request->getPost('nomor_spm') ?? ''));
        $tanggalSpm    = trim((string) ($this->request->getPost('tanggal_spm') ?? date('Y-m-d')));
        $nomorSp2d     = trim((string) ($this->request->getPost('nomor_sp2d') ?? ''));
        $rawJumlah     = preg_replace('/[^0-9,\.]/', '', (string) ($this->request->getPost('jumlah_bayar') ?? '0'));
        $jumlahBayar   = (float) str_replace(',', '.', str_replace('.', '', $rawJumlah));

        if ($paketId <= 0 || $uraianBelanja === '') {
            return redirect()->back()->with('error', 'Mohon lengkapi paket dan uraian pengeluaran belanja.');
        }

        $slug     = $this->getSlugByPaketId($paketId);
        $existing = $id > 0 ? $this->realisasiModel->find($id) : null;
        $fileBukti = $existing['file_bukti'] ?? null;

        $file = $this->request->getFile('file_bukti');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, ['pdf', 'docx', 'xlsx'], true)) {
                return redirect()->to('/admin/swakelola/realisasi?paket_id=' . $paketId)
                    ->with('error', 'Berkas bukti SPM/Kuitansi harus berformat PDF, Word, atau Excel.');
            }

            $targetDir = FCPATH . 'uploads/swakelola/' . $slug;
            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.\s\(\)]/', '_', basename($file->getClientName()));
            $file->move($targetDir, $safeName, true);
            $fileBukti = $safeName;
        }

        $payload = [
            'paket_id'       => $paketId,
            'kode_akun'      => $kodeAkun,
            'uraian_belanja' => $uraianBelanja,
            'nomor_spm'      => $nomorSpm,
            'tanggal_spm'    => $tanggalSpm !== '' ? $tanggalSpm : null,
            'nomor_sp2d'     => $nomorSp2d,
            'jumlah_bayar'   => $jumlahBayar,
            'file_bukti'     => $fileBukti,
        ];

        if ($id > 0 && $existing) {
            $this->realisasiModel->update($id, $payload);
            $msg = 'Data realisasi SPM berhasil diperbarui.';
        } else {
            $this->realisasiModel->insert($payload);
            $msg = 'Data realisasi SPM baru berhasil ditambahkan.';
        }

        $this->recalculatePaketRealisasi($paketId);

        return redirect()->to('/admin/swakelola/realisasi?paket_id=' . $paketId)
            ->with('success', $msg);
    }

    /**
     * Hapus Realisasi SPM
     */
    public function deleteRealisasi(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/realisasi');
        if (empty($permissions['delete'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses hapus (delete) pada menu Realisasi Anggaran.');
        }

        $row = $this->realisasiModel->find($id);
        if (! $row) {
            return redirect()->back()->with('error', 'Data realisasi tidak ditemukan.');
        }

        $paketId = (int) ($row['paket_id'] ?? 0);
        $this->realisasiModel->delete($id);
        $this->recalculatePaketRealisasi($paketId);

        return redirect()->to('/admin/swakelola/realisasi?paket_id=' . $paketId)
            ->with('success', 'Data realisasi SPM berhasil dihapus.');
    }

    /**
     * Hitung ulang total realisasi_anggaran pada tabel trn_swakelola_paket
     */
    private function recalculatePaketRealisasi(int $paketId): void
    {
        if ($paketId <= 0) {
            return;
        }
        $rows = $this->realisasiModel->getByPaketId($paketId);
        $sum  = 0.0;
        foreach ($rows as $r) {
            $sum += (float) ($r['jumlah_bayar'] ?? 0);
        }
        $this->paketModel->update($paketId, ['realisasi_anggaran' => $sum]);
    }

    /**
     * Perbarui status paket berdasarkan kelengkapan 28 dokumen
     */
    private function refreshPaketStatus(int $paketId): void
    {
        if ($paketId <= 0) {
            return;
        }
        $docs = $this->dokumenModel->getByPaketId($paketId);
        $ada  = 0;
        foreach ($docs as $d) {
            if (($d['status'] ?? '') === 'Ada') {
                $ada++;
            }
        }
        $status = ($ada >= 28 && count($docs) >= 28) ? 'Selesai' : 'Dalam Penyusunan';
        $this->paketModel->update($paketId, ['status' => $status]);
    }

    /**
     * Helper nama akun belanja standar
     */
    private function getAccountName(string $akun): string
    {
        return match ($akun) {
            '521211' => 'Belanja Bahan',
            '521213' => 'Belanja Honor Output Kegiatan',
            '521811' => 'Belanja Barang Persediaan Barang Konsumsi',
            '522191' => 'Belanja Jasa Lainnya',
            '524111' => 'Belanja Perjalanan Dinas Biasa',
            '524113' => 'Belanja Perjalanan Dinas Dalam Kota',
            '524114' => 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota',
            default  => 'Belanja Operasional',
        };
    }

    /**
     * Sub-Menu 4: Rekapitulasi & Laporan Akhir
     */
    public function laporanIndex()
    {
        $this->syncVerifiedSppgData();
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/laporan');
        $paketList   = $this->getEnrichedPaketList();
        $requestedId = (int) ($this->request->getGet('paket_id') ?? 0);
        $paket       = $this->resolveSelectedPaket($requestedId, $paketList);
        $paketId     = (int) ($paket['id'] ?? 1);
        $rabAccounts = $paket['rab_accounts'] ?? [];

        $realisasiRows = $this->realisasiModel->getByPaketId($paketId);
        $realByAkun    = [];
        foreach ($realisasiRows as $r) {
            $ak = (string) ($r['kode_akun'] ?? '');
            $realByAkun[$ak] = ($realByAkun[$ak] ?? 0.0) + (float) ($r['jumlah_bayar'] ?? 0);
        }

        $rabComparison = [];
        foreach ($rabAccounts as $akun => $meta) {
            $pagu   = (float) $meta['pagu'];
            $real   = (float) ($realByAkun[$akun] ?? 0.0);
            $sisa   = $pagu - $real;
            $persen = $pagu > 0 ? round(($real / $pagu) * 100, 2) : 0.0;
            $rabComparison[] = [
                'akun'      => $akun,
                'uraian'    => $meta['uraian'],
                'pagu'      => $pagu,
                'realisasi' => $real,
                'sisa'      => $sisa,
                'persen'    => $persen,
            ];
            unset($realByAkun[$akun]);
        }

        // Jika ada akun realisasi di luar komponen awal POK
        foreach ($realByAkun as $akun => $real) {
            $rabComparison[] = [
                'akun'      => $akun,
                'uraian'    => $this->getAccountName($akun),
                'pagu'      => 0.0,
                'realisasi' => $real,
                'sisa'      => -$real,
                'persen'    => 100.0,
            ];
        }

        return view('admin/swakelola/laporan', [
            'title'         => 'Rekapitulasi & Laporan Swakelola',
            'paket'         => $paket,
            'paketList'     => $paketList,
            'rabComparison' => $rabComparison,
            'realisasiCount'=> count($realisasiRows),
            'permissions'   => $permissions,
        ]);
    }

    /**
     * Download Checklist Excel (mendukung semua 4 paket via ?paket_id=)
     */
    public function exportChecklistExcel()
    {
        $this->syncVerifiedSppgData();
        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/laporan');
        if (empty($permissions['export'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses export pada menu ini.');
        }

        $paketList   = $this->getEnrichedPaketList();
        $requestedId = (int) ($this->request->getGet('paket_id') ?? 0);
        $paket       = $this->resolveSelectedPaket($requestedId, $paketList);
        $slug        = (string) ($paket['slug'] ?? 'sppg');

        if ($slug === 'phtc3') {
            $filePath = FCPATH . 'uploads/swakelola/phtc3/Checklist_Lengkap_Swakelola_PHTC3.xlsx';
            if (! file_exists($filePath)) {
                $filePath = ROOTPATH . 'do_not_upload/audit/hasil_phtc3/Checklist_Lengkap_Swakelola_PHTC3.xlsx';
            }
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)
                    ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }
        }

        if ($slug === 'sppg') {
            $filePath = FCPATH . 'uploads/swakelola/sppg/Checklist_Lengkap_Swakelola_SPPG.xlsx';
            if (! file_exists($filePath)) {
                $filePath = ROOTPATH . 'do_not_upload/audit/hasil_sppg/Checklist_Lengkap_Swakelola_SPPG.xlsx';
            }
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)
                    ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }
        }

        if ($slug === 'madrasah_riau') {
            $filePath = FCPATH . 'uploads/swakelola/madrasah_riau/Checklist_Lengkap_Swakelola_Madrasah_Riau.xlsx';
            if (! file_exists($filePath)) {
                $filePath = ROOTPATH . 'do_not_upload/audit/hasil_madrasah_riau/Checklist_Lengkap_Swakelola_Madrasah_Riau.xlsx';
            }
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)
                    ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }
        }

        if ($slug === 'madrasah_riau5') {
            $filePath = FCPATH . 'uploads/swakelola/madrasah_riau5/Checklist_Lengkap_Swakelola_Madrasah_Riau5.xlsx';
            if (! file_exists($filePath)) {
                $filePath = ROOTPATH . 'do_not_upload/audit/hasil_madrasah_riau5/Checklist_Lengkap_Swakelola_Madrasah_Riau5.xlsx';
            }
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)
                    ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }
        }

        // Generate file Excel (.xlsx) dinamis untuk Paket 1, 3, 4 (atau fallback)
        $xlsxBinary = $this->buildDynamicChecklistXlsx($paket);
        $downloadName = 'Checklist_28_Dokumen_Swakelola_Paket_' . ($paket['lha_no'] ?? 1) . '_' . strtoupper($slug) . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $downloadName . '"')
            ->setBody($xlsxBinary);
    }

    /**
     * Builder file .xlsx (OpenXML) tanpa dependensi eksternal untuk paket manapun
     */
    private function buildDynamicChecklistXlsx(array $paket): string
    {
        $paketId = (int) ($paket['id'] ?? 0);
        $docs    = $this->dokumenModel->getByPaketId($paketId);
        $escXml  = static fn(string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $rowsXml = '';
        $rIdx = 1;

        $addRow = static function (array $cells) use (&$rowsXml, &$rIdx, $escXml): void {
            $rowsXml .= '<row r="' . $rIdx . '">';
            foreach ($cells as $cIdx => $val) {
                $col = chr(65 + $cIdx) . $rIdx;
                $rowsXml .= '<c r="' . $col . '" t="inlineStr"><is><t>' . $escXml((string) $val) . '</t></is></c>';
            }
            $rowsXml .= '</row>';
            $rIdx++;
        };

        $addRow(['CHECKLIST 28 DOKUMEN ADMINISTRASI SWAKELOLA (LHA NO. 9)']);
        $addRow(['Nama Paket Kegiatan', (string) ($paket['nama_kegiatan'] ?? '-')]);
        $addRow(['Kode MAK / POK Rev 16', (string) ($paket['kode_paket'] ?? '-')]);
        $addRow(['PIC Penanggung Jawab', (string) ($paket['pic_nama'] ?? '-')]);
        $addRow(['Pagu Anggaran', 'Rp ' . number_format((float) ($paket['pagu_anggaran'] ?? 0), 0, ',', '.')]);
        $addRow(['Realisasi SP2D', 'Rp ' . number_format((float) ($paket['realisasi_anggaran'] ?? 0), 0, ',', '.')]);
        $addRow([]);
        $addRow(['No', 'Tahap', 'Nama Dokumen Kelengkapan', 'Status', 'Nama Berkas Digital', 'Keterangan']);

        foreach ($docs as $d) {
            $addRow([
                (string) ($d['nomor_urut'] ?? ''),
                (string) ($d['tahap'] ?? ''),
                (string) ($d['nama_dokumen'] ?? ''),
                (string) ($d['status'] ?? ''),
                (string) ($d['file_name'] ?? '-'),
                (string) ($d['keterangan'] ?? ''),
            ]);
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . '</worksheet>';

        $tmpFile = tempnam(sys_get_temp_dir(), 'swk_xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Checklist 28 Dokumen" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = (string) file_get_contents($tmpFile);
        @unlink($tmpFile);

        return $binary;
    }

    /**
     * Auto-heal / sinkronisasi otomatis seluruh 4 Paket Swakelola LHA No. 09
     * agar sesuai dengan POK Revisi 16 (23 Des 2025) dan berkas pada masing-masing folder.
     */
    private function syncVerifiedSppgData(): void
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('trn_swakelola_paket') || ! $db->tableExists('trn_swakelola_dokumen')) {
            return;
        }

        $now     = date('Y-m-d H:i:s');
        $catalog = $this->getPaketCatalog();
        $masterChecklist = $this->getStandardChecklistMaster();

        foreach ($catalog as $kodePaket => $meta) {
            $paket = $db->table('trn_swakelola_paket')
                ->where('kode_paket', $kodePaket)
                ->get()
                ->getRowArray();

            if (! $paket) {
                $initialStatus = in_array($kodePaket, ['06.691285.GM.7717.CBI.001.900.A', '06.691285.GM.7717.RBI.003.900.AB'], true) ? 'Selesai' : 'Dalam Penyusunan';
                $db->table('trn_swakelola_paket')->insert([
                    'kode_paket'         => $meta['kode_paket'],
                    'nama_kegiatan'      => $meta['nama_kegiatan'],
                    'tahun_anggaran'     => $meta['tahun_anggaran'],
                    'pagu_anggaran'      => $meta['pagu_anggaran'],
                    'realisasi_anggaran' => $meta['default_real'],
                    'tipe_swakelola'     => $meta['tipe_swakelola'],
                    'ppk_nama'           => $meta['ppk_nama'],
                    'ppk_nip'            => $meta['ppk_nip'],
                    'kasatker_nama'      => $meta['kasatker_nama'],
                    'kasatker_nip'       => $meta['kasatker_nip'],
                    'lokasi'             => $meta['lokasi'],
                    'jangka_waktu'       => $meta['jangka_waktu'],
                    'status'             => $initialStatus,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $paketId = (int) $db->insertID();
            } else {
                $paketId = (int) $paket['id'];
                $updateFields = [];
                if (($paket['kasatker_nip'] ?? '') !== '19800626 200604 1 016' || (float) ($paket['pagu_anggaran'] ?? 0) !== (float) $meta['pagu_anggaran']) {
                    $updateFields = [
                        'nama_kegiatan' => $meta['nama_kegiatan'],
                        'pagu_anggaran' => $meta['pagu_anggaran'],
                        'ppk_nama'      => $meta['ppk_nama'],
                        'ppk_nip'       => $meta['ppk_nip'],
                        'kasatker_nama' => $meta['kasatker_nama'],
                        'kasatker_nip'  => $meta['kasatker_nip'],
                    ];
                }
                if ($kodePaket === '06.691285.GM.7717.RBI.003.051.D') {
                    $updateFields['pagu_anggaran'] = 90000000.00;
                    $updateFields['realisasi_anggaran'] = 87955000.00;
                    $updateFields['status'] = 'Selesai';
                    $updateFields['lokasi'] = 'Hotel Royal Asnof, Kota Pekanbaru, Provinsi Riau';
                    $updateFields['jangka_waktu'] = 'November s/d Desember 2025 (Fullboard Meeting)';
                }
                if ($kodePaket === '06.691285.GM.7717.CBI.001.900.A') {
                    $updateFields['realisasi_anggaran'] = 141159100.00;
                    $updateFields['status'] = 'Selesai';
                }
                if ($kodePaket === '06.691285.GM.7717.RBI.003.900.AB') {
                    $updateFields['pagu_anggaran'] = 569100000.00;
                    $updateFields['realisasi_anggaran'] = 322773000.00;
                    $updateFields['status'] = 'Selesai';
                    $updateFields['jangka_waktu'] = 'Juli s/d Desember 2025 (6 Bulan)';
                    $updateFields['lokasi'] = 'Provinsi Riau (12 Kabupaten / Kota)';
                }
                if ($kodePaket === '06.691285.GM.7717.RBI.003.900.G') {
                    $updateFields['pagu_anggaran'] = 109828000.00;
                    $updateFields['realisasi_anggaran'] = 86533000.00;
                    $updateFields['status'] = 'Selesai';
                    $updateFields['tahun_anggaran'] = 2026;
                    $updateFields['lokasi'] = 'Kabupaten Bengkalis dan Kota Dumai, Provinsi Riau';
                    $updateFields['jangka_waktu'] = 'Januari s/d Juni 2026 (6 Bulan)';
                }
                if (! empty($updateFields)) {
                    $updateFields['updated_at'] = $now;
                    $db->table('trn_swakelola_paket')->where('id', $paketId)->update($updateFields);
                }
            }

            // Pastikan setiap paket memiliki 28 baris dokumen checklist berstatus Ada
            $existingDocs = $db->table('trn_swakelola_dokumen')
                ->where('paket_id', $paketId)
                ->countAllResults();

            $adaCount = $db->table('trn_swakelola_dokumen')
                ->where('paket_id', $paketId)
                ->where('status', 'Ada')
                ->countAllResults();

            if ($existingDocs < 28 || $adaCount < 28) {
                $this->seedChecklistForPackage($db, $paketId, $meta['slug'], $masterChecklist, $now);
            } elseif ($kodePaket === '06.691285.GM.7717.CBI.001.900.A') {
                // Pastikan dokumen nomor_urut 1 (A.1) dan 9 (B.4) Paket 2 SPPG merujuk ke berkas terverifikasi
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

            // Pastikan Paket 1 (PHTC 3) memiliki 10 transaksi SPM/SP2D asli
            if ($kodePaket === '06.691285.GM.7717.RBI.003.051.D' && $db->tableExists('trn_swakelola_realisasi')) {
                $realCount = $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->countAllResults();
                if ($realCount < 10) {
                    $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->delete();
                    $this->seedVerifiedPhtc3Realisasi($db, $paketId, $now);
                }
            }

            // Pastikan Paket 2 (SPPG) memiliki 16 transaksi SPM/SP2D/PTUP asli
            if ($kodePaket === '06.691285.GM.7717.CBI.001.900.A' && $db->tableExists('trn_swakelola_realisasi')) {
                $realCount = $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->countAllResults();
                if ($realCount < 16) {
                    $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->delete();
                    $this->seedVerifiedSppgRealisasi($db, $paketId, $now);
                }
            }

            // Pastikan Paket 3 (Madrasah Riau) memiliki 43 transaksi SPM/SP2D/PTUP asli
            if ($kodePaket === '06.691285.GM.7717.RBI.003.900.AB' && $db->tableExists('trn_swakelola_realisasi')) {
                $realCount = $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->countAllResults();
                if ($realCount < 43) {
                    $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->delete();
                    $this->seedVerifiedMadrasahRiauRealisasi($db, $paketId, $now);
                }
            }

            // Pastikan Paket 4 (Madrasah Riau 5) memiliki 12 transaksi SPM/SP2D asli
            if ($kodePaket === '06.691285.GM.7717.RBI.003.900.G' && $db->tableExists('trn_swakelola_realisasi')) {
                $realCount = $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->countAllResults();
                if ($realCount < 12) {
                    $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->delete();
                    $this->seedVerifiedMadrasahRiau5Realisasi($db, $paketId, $now);
                }
            }

            // Perbarui status paket berdasarkan kelengkapan 28 dokumen checklist
            $this->refreshPaketStatus($paketId);
        }
    }

    /**
     * Inisialisasi 28 item checklist awal untuk paket berdasarkan slug ('phtc3', 'sppg', 'madrasah_riau', 'madrasah_riau5')
     */
    private function seedChecklistForPackage($db, int $paketId, string $slug, array $masterChecklist, string $now): void
    {
        $db->table('trn_swakelola_dokumen')->where('paket_id', $paketId)->delete();

        $phtc3Prepopulated = [
            1  => ['A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK Rakor PHTC 3 Pagu Rp 90.000.000,-'],
            2  => ['A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola Rakor Madrasah PHTC Tahap 3.docx', 'Lembar Penetapan RUP & Pengumuman SiRUP LKPP Swakelola Rakor PHTC Tahap 3'],
            3  => ['A. 3. KAK_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'KAK Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3'],
            4  => ['A. 4. RAB_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'RAB Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3 (Rp 90.000.000,-)'],
            5  => ['A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola Tipe I'],
            6  => ['PEMBENTUKAN TIM PENYELENGGARA SWAKELOLA KEGIATAN RAPAT KOORDINASI.pdf', 'SK Kasatker No. 27/KPTS/SATKER/PPS-RIAU/2025 Penetapan Tim Penyelenggara Swakelola'],
            7  => ['B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Susunan Tim Persiapan, Pelaksana, Pengawas beserta Uraian Tugas 7 Orang'],
            8  => ['B. 3. Rencana_Kegiatan_dan_Jadwal_Rakor_Madrasah_PHTC_Tahap_3.docx', 'Rencana Kegiatan dan Jadwal Pelaksanaan Rakor Madrasah PHTC Tahap 3'],
            9  => ['B. 4. KAK Final dan RAB Rinci Rakor Madrasah PHTC Tahap 3.docx', 'Disahkan PPK dan Tim Persiapan Rakor Madrasah PHTC Tahap 3'],
            10 => ['B. 5. BA Pembahasan Persiapan Swakelola_Rakor_Madrasah_PHTC_Tahap_3.docx', 'Berita Acara Pembahasan Persiapan Swakelola Rakor Madrasah PHTC Tahap 3'],
            11 => ['B. 6. Rencana_Kebutuhan_Rakor_PHTC_Tahap_3.docx', 'Rencana Kebutuhan Tenaga Kerja, Bahan dan Peralatan Rakor PHTC Tahap 3'],
            12 => ['C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah Penugasan Tim Pelaksana Swakelola Rakor PHTC Tahap 3'],
            13 => ['C. 2. Laporan Pelaksanaan dan Daftar Hadir.pdf', 'Laporan Pelaksanaan Kegiatan dan Presensi Daftar Hadir Peserta Rakor (33 Org)'],
            14 => ['C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan Progres Fisik dan Penggunaan Anggaran Berkala Rakor PHTC 3'],
            15 => ['ATK BULAN NOVEMBER (SPM 00417A).pdf', '5 Berkas SPM/Kuitansi/Faktur Pembelian Bahan (ATK, Supplies, Materi Kit, Spanduk, Cetak)'],
            16 => ['HONORARIUM NARASUMBER (SPM  00423A).pdf', '2 Berkas SPM/Kuitansi/SSP Pajak Honorarium Narasumber & Tim Swakelola 7 Orang'],
            17 => ['C. 6. Dokumentasi Kegiatan Rapat dan Survei  PHTC Tahap 3.pdf', 'Dokumentasi Foto Fisik Kegiatan Rapat & Survey Lapangan Madrasah PHTC 3'],
            18 => ['PAKET MEETING RAKOR (SPM 00446A) OK.pdf', '3 Berkas SPM/Kuitansi Paket Meeting Hotel Royal Asnof & Perjadin Uang Harian/Transport 33 Peserta'],
            19 => ['D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola Rakor PHTC 3.docx', 'Laporan Pengawasan Tim Pengawas Rakor Madrasah PHTC Tahap 3'],
            20 => ['D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar Checklist Verifikasi Kesesuaian Output dan Spesifikasi Teknis'],
            21 => ['D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekonsiliasi Fisik 100% & Realisasi Keuangan Rp 87.955.000,- (97,73%)'],
            22 => ['D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi Efisiensi Anggaran Penghematan Sisa Pagu Rp 2.045.000,-'],
            23 => ['E. 1. Laporan Akhir Pelaksanaan Swakelola Rakor Madrasah PHTC Tahap 3.docx', 'Laporan Akhir Komprehensif Pelaksanaan Swakelola Rakor Madrasah PHTC Tahap 3'],
            24 => ['E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 04/BAST-SWK/PPK-PPS/2025 Hasil Pekerjaan Rakor PHTC Tahap 3'],
            25 => ['E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas Rakor Madrasah PHTC Tahap 3'],
            26 => ['E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Rekapitulasi Realisasi 3 Akun Anggaran vs RAB/POK Rev 16 (Rp 87.955.000,-)'],
            27 => ['E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA Penyelesaian Pembayaran 10 SP2D dan Pelunasan Seluruh Perpajakan'],
            28 => ['E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA Pencatatan Barang Persediaan Habis Pakai ATK dan Materi Kit Rakor'],
        ];

        $sppgPrepopulated = [
            1  => ['A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK SPPG Pagu Rp 413.000.000,-'],
            2  => ['A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola SPPG.docx', 'Format pengumuman SiRUP LKPP Swakelola'],
            3  => ['A. 3. KAK Administrasi SPPG Provinsi Riau.pdf', 'Disahkan PPK dan Kasatker'],
            4  => ['A. 4. RAB Administrasi SPPG Provinsi Riau.pdf', 'Pagu Rp 413.000.000,-'],
            5  => ['A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dasar hukum pemilihan Swakelola Tipe I'],
            6  => ['B. 1. SK Penetapan Penyelenggara Swakelola SPPG oleh KPA.docx', 'SK KPA No. 28/KPTS/SATKER-PPS.RIAU/2025'],
            7  => ['B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK rincian tugas tim'],
            8  => ['B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi SPPG.docx', 'Matriks jadwal pelaksanaan kurva waktu'],
            9  => ['B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx', 'Disahkan PPK dan Tim Persiapan'],
            10 => ['B. 5. BA Pembahasan Persiapan Swakelola Administrasi SPPG.docx', 'BA Pembahasan No. 01/BA-PERSIAPAN/2025'],
            11 => ['B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rincian kebutuhan personil & bahan operasional'],
            12 => ['C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah PPK No. 15/SPP-SWK/2025'],
            13 => ['C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi logbook & penugasan staf'],
            14 => ['C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan progres bulanan Okt, Nov, Des 2025'],
            15 => ['C. 4. Bukti Pembelian Bahan dan Barang dari Penyedia (ATK SPM 00415A).pdf', 'Kuitansi & faktur belanja bahan CV. Sinar Cahaya Lestari'],
            16 => ['C. 5. Invoice Kuitansi dan Dokumen Pajak (Supplies SPM 00414A).pdf', 'SPM, SP2D, bukti setor pajak PPN/PPh Pasal 22'],
            17 => ['C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan SPPG.docx', 'Album foto survei Kampar, Dumai, Siak, Pekanbaru, Jakarta & Medan'],
            18 => ['C. 7. Bukti Pengeluaran Sah yang Dapat Ditelusuri (SPPD SPM 00445A).pdf', '11 Berkas SPPD, SPT, Nominatif & Rincian Biaya Riil'],
            19 => ['D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola SPPG.docx', 'Laporan berkala hasil pengawasan Tim Was'],
            20 => ['D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar checklist pemeriksaan kesesuaian'],
            21 => ['D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekon Fisik 100% & Keu Rp 141.159.100,-'],
            22 => ['D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi efisiensi sisa pagu Rp 271,84 jt'],
            23 => ['E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi SPPG.docx', 'Laporan akhir komprehensif Bab I s/d Bab IV'],
            24 => ['E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 01/BAST-SWK/PPK-PPS/2025'],
            25 => ['E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas sebelum BAST'],
            26 => ['E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks perbandingan RAB/POK Rev 16 vs Realisasi'],
            27 => ['E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA pelunasan 16 SP2D dan pajak PPN/PPh22 lunas'],
            28 => ['E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA pencatatan barang persediaan habis pakai'],
        ];

        $madrasahPrepopulated = [
            1  => ['A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK Madrasah PHTC Riau Pagu Rp 569.100.000,-'],
            2  => ['A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola Madrasah PHTC Riau.docx', 'Lembar Penetapan RUP & Pengumuman SiRUP LKPP Swakelola'],
            3  => ['A. 3. KAK Administrasi Madrasah PHTC Provinsi Riau.pdf', 'Disahkan PPK dan Kasatker'],
            4  => ['A. 4. RAB Administrasi Madrasah PHTC Provinsi Riau.pdf', 'Pagu Rp 569.100.000,- (5 Akun Belanja POK Rev 16)'],
            5  => ['A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dasar hukum pemilihan Swakelola Tipe I'],
            6  => ['B. 1. SK Penetapan Penyelenggara Swakelola Madrasah PHTC Riau oleh KPA.docx', 'SK KPA No. 22/KPTS/SATKER-PPS.RIAU/2025'],
            7  => ['B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK Penetapan Tim Swakelola & Uraian Tugas'],
            8  => ['B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi Madrasah PHTC Riau.docx', 'Matriks jadwal kurva waktu Juli s.d. Desember 2025 (6 Bulan)'],
            9  => ['B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau.docx', 'Disahkan PPK dan Tim Persiapan'],
            10 => ['B. 5. BA Pembahasan Persiapan Swakelola Administrasi Madrasah PHTC Riau.docx', 'BA Pembahasan No. 02/BA-PERSIAPAN/SWK-MDR/2025'],
            11 => ['B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rencana kebutuhan SDM, ATK, supplies, perjadin & narsum'],
            12 => ['C. 1. Surat Perintah Penugasan Tim Pelaksana Swakelola.docx', 'Surat Perintah PPK No. 12/SPP-SWK/PPK-PPS/2025'],
            13 => ['C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi logbook bulanan dan presensi tim pelaksana'],
            14 => ['C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan progres bulanan Juli s.d. Desember 2025'],
            15 => ['C. 4. Bukti Pembelian Bahan dan Barang dari Penyedia (ATK SPM 00255A).pdf', '6 Berkas SPM/Kuitansi/BAST/Faktur Pembelian ATK & Supplies CV. Sinar Cahaya Lestari'],
            16 => ['C. 5. Invoice Kuitansi dan Dokumen Pajak (Honor Narsum SPM 00350A).pdf', '12 Berkas SPM/Kuitansi/SSP Pajak (2 SPM Honor Narsum TP4D + 10 SPM Gaji Staf Pendukung)'],
            17 => ['C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan Madrasah PHTC Riau.docx', 'Album foto survei & monitoring lapangan madrasah di 12 Kabupaten/Kota Provinsi Riau'],
            18 => ['C. 7. Bukti Pengeluaran Sah yang Dapat Ditelusuri (SPPD SPM 00270A).pdf', '25 Berkas Lengkap SPPD, SPT, Nominatif & Bukti Riil Perjadin Biasa & Dalam Kota'],
            19 => ['D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola Madrasah PHTC Riau.docx', 'Laporan berkala hasil audit dan pengawasan fisik/keuangan Tim Was'],
            20 => ['D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar checklist verifikasi kesesuaian output & spesifikasi teknis'],
            21 => ['D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekonsiliasi Fisik 100% & Keuangan Rp 322.773.000,- (56,72%)'],
            22 => ['D. 4. Catatan Penyimpangan Evaluasi dan Bukti Tindak Lanjut.docx', 'Evaluasi efisiensi anggaran penghematan sisa pagu Rp 246.327.000,-'],
            23 => ['E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi Madrasah PHTC Riau.docx', 'Laporan akhir komprehensif Bab I s/d Bab IV kegiatan rehabilitasi madrasah'],
            24 => ['E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 03/BAST-SWK/PPK-PPS/2025'],
            25 => ['E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP No. 03/BAPHP-SWK/WAS-PPS/2025 Tim Pengawas'],
            26 => ['E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks rekonsiliasi 5 akun POK Rev 16 vs 43 SPM realisasi definitif'],
            27 => ['E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA penyelesaian pembayaran 43 SP2D dan pelunasan seluruh kewajiban perpajakan'],
            28 => ['E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA pencatatan barang persediaan habis pakai ATK dan supplies komputer'],
        ];

        $madrasahRiau5Prepopulated = [
            1  => ['A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 & Petikan POK Madrasah PHTC Riau 5'],
            2  => ['A. 2. Lembar RUP dan Pengumuman SiRUP Paket Swakelola Madrasah PHTC Riau 5.docx', 'Lembar Penetapan RUP & Pengumuman SiRUP LKPP Swakelola Madrasah PHTC Riau 5'],
            3  => ['KAK ADMINISTRASI PHTC PROVINSI RIAU 5 rev.pdf', 'Disahkan PPK dan Kasatker Administrasi Rehabilitasi dan Renovasi Madrasah PHTC Provinsi Riau 5'],
            4  => ['RAB ADMINISTRASI PHTC PROVINSI RIAU 5 ok.pdf', 'RAB Definitif Alokasi Rp 109.828.000,- (3 Akun Belanja POK Rev 16 / TA 2026)'],
            5  => ['A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola Tipe I'],
            6  => ['B. 1. SK Penetapan Penyelenggara Swakelola Madrasah PHTC Riau 5 oleh KPA.docx', 'SK Penetapan Penyelenggara Swakelola No. 18/KPTS/SATKER-PPS.RIAU/2026'],
            7  => ['B. 2. Susunan Tim Persiapan, Pelaksana, Pengawas dan Uraian Tugas.docx', 'Lampiran SK Penetapan Tim Swakelola Madrasah PHTC Riau 5 & Uraian Tugas'],
            8  => ['B. 3. Rencana Kegiatan dan Jadwal Pelaksanaan Administrasi Madrasah PHTC Riau 5.docx', 'Matriks Jadwal Pelaksanaan Kurva Waktu Januari s.d. Juni 2026 (6 Bulan)'],
            9  => ['B. 4. KAK Final dan RAB Rinci Administrasi Madrasah PHTC Riau 5.docx', 'Disahkan PPK dan Tim Persiapan Administrasi Madrasah PHTC Riau 5'],
            10 => ['B. 5. BA Pembahasan Persiapan Swakelola Administrasi Madrasah PHTC Riau 5.docx', 'BA Pembahasan Persiapan Swakelola Madrasah PHTC Riau 5'],
            11 => ['B. 6. Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan, dan Tenaga Ahli.docx', 'Rencana Kebutuhan Tenaga Kerja, Bahan, Peralatan dan Tenaga Ahli Ade Putra, S.T.'],
            12 => ['Surat Perjanjian Kerja.pdf', 'Surat Perjanjian Kerja (SPK) Tenaga Ahli Ade Putra, S.T. No. 01/2026 & Surat Penugasan Tim'],
            13 => ['C. 2. Laporan Pelaksanaan, Daftar Hadir dan Catatan Tenaga Kerja.docx', 'Rekapitulasi Logbook Bulanan dan Presensi Tim Pelaksana Jan-Jun 2026'],
            14 => ['C. 3. Laporan Progres Fisik dan Penggunaan Anggaran Berkala.docx', 'Laporan Progres Fisik dan Penggunaan Anggaran Berkala Jan s.d. Jun 2026'],
            15 => ['86. GUP III MARET (SPM 00086A) atk phtc 5.pdf', 'Bukti Pembelian Bahan ATK PHTC 5 Penggantian Uang Persediaan GUP III Maret 2026 (SPM 00086A)'],
            16 => ['1. GAJI JAN ADE (SPM 00048A).pdf', '5 Berkas SPM/Kuitansi/SSP Pajak Gaji Tenaga Teknis Ade Putra, S.T. Jan s.d. Mei 2026 (Rp 30.000.000,-)'],
            17 => ['C. 6. Dokumentasi Fisik Kegiatan dan Hasil Pekerjaan Madrasah PHTC Riau 5.docx', 'Dokumentasi Foto Fisik Lapangan Rehabilitasi Madrasah di Bengkalis dan Dumai'],
            18 => ['1. SPPD JANUARI BENGKALIS (SPM 00029A).pdf', '6 Berkas Lengkap SPPD, SPT, Nominatif & Bukti Riil Perjadin Bengkalis & Dumai (Rp 54.972.000,-)'],
            19 => ['D. 1. Laporan Catatan Pengawasan Tim Pengawas Swakelola Madrasah PHTC Riau 5.docx', 'Laporan Berkala Hasil Audit dan Pengawasan Fisik/Keuangan Tim Was'],
            20 => ['D. 2. Bukti Pemeriksaan Kesesuaian Volume, Mutu, Waktu dan Keluaran.docx', 'Lembar Checklist Verifikasi Kesesuaian Output dan Spesifikasi Teknis Lapangan'],
            21 => ['D. 3. Berita Acara Rekonsiliasi Realisasi Fisik dan Keuangan.docx', 'BA Rekonsiliasi Fisik 100% & Realisasi Keuangan Rp 86.533.000,- (78,79%)'],
            22 => ['D. 4. Catatan Penyimpangan Evaluasi dan Rekomendasi Efisiensi Anggaran.docx', 'Evaluasi Efisiensi Anggaran Penghematan Sisa RAB Rp 23.295.000,-'],
            23 => ['E. 1. Laporan Akhir Pelaksanaan Swakelola Administrasi Madrasah PHTC Riau 5.docx', 'Laporan Akhir Komprehensif Bab I s/d Bab IV Administrasi Madrasah PHTC Riau 5'],
            24 => ['E. 2. Berita Acara Serah Terima (BAST) Hasil Pekerjaan dari Tim Pelaksana kepada PPK.docx', 'BAST No. 05/BAST-SWK/PPK-PPS/2026 Hasil Pekerjaan Administrasi Madrasah PHTC Riau 5'],
            25 => ['E. 3. Berita Acara Pemeriksaan Hasil Pekerjaan (BAPHP).docx', 'BAPHP Tim Pengawas Swakelola Madrasah PHTC Riau 5'],
            26 => ['E. 4. Rekapitulasi Realisasi Anggaran Dibandingkan dengan RAB.docx', 'Matriks Rekonsiliasi 3 Akun Belanja vs 12 SPM Realisasi Definitif (Rp 86.533.000,-)'],
            27 => ['E. 5. Bukti Penyelesaian Pembayaran dan Perpajakan.docx', 'BA Penyelesaian Pembayaran 12 SP2D dan Pelunasan Seluruh Kewajiban Perpajakan'],
            28 => ['E. 6. Dokumen Pencatatan Persediaan Barang Habis Pakai dan Aset.docx', 'BA Pencatatan Barang Persediaan Habis Pakai ATK Madrasah PHTC 5'],
        ];

        foreach ($masterChecklist as $item) {
            [$tahap, $noUrut, $namaDok] = $item;
            $status     = 'Belum Ada';
            $fileName   = null;
            $filePath   = null;
            $keterangan = 'Menunggu unggahan berkas dokumen oleh PIC paket';

            if ($slug === 'sppg' && isset($sppgPrepopulated[$noUrut])) {
                $status     = 'Ada';
                $fileName   = $sppgPrepopulated[$noUrut][0];
                $filePath   = 'public/uploads/swakelola/sppg/' . $fileName;
                $keterangan = $sppgPrepopulated[$noUrut][1];
            } elseif ($slug === 'madrasah_riau' && isset($madrasahPrepopulated[$noUrut])) {
                $status     = 'Ada';
                $fileName   = $madrasahPrepopulated[$noUrut][0];
                $filePath   = 'public/uploads/swakelola/madrasah_riau/' . $fileName;
                $keterangan = $madrasahPrepopulated[$noUrut][1];
            } elseif ($slug === 'phtc3' && isset($phtc3Prepopulated[$noUrut])) {
                $status     = 'Ada';
                $fileName   = $phtc3Prepopulated[$noUrut][0];
                $filePath   = 'public/uploads/swakelola/phtc3/' . $fileName;
                $keterangan = $phtc3Prepopulated[$noUrut][1];
            } elseif ($slug === 'madrasah_riau5' && isset($madrasahRiau5Prepopulated[$noUrut])) {
                $status     = 'Ada';
                $fileName   = $madrasahRiau5Prepopulated[$noUrut][0];
                $filePath   = 'public/uploads/swakelola/madrasah_riau5/' . $fileName;
                $keterangan = $madrasahRiau5Prepopulated[$noUrut][1];
            }

            $db->table('trn_swakelola_dokumen')->insert([
                'paket_id'     => $paketId,
                'tahap'        => $tahap,
                'nomor_urut'   => $noUrut,
                'nama_dokumen' => $namaDok,
                'status'       => $status,
                'file_name'    => $fileName,
                'file_path'    => $filePath,
                'keterangan'   => $keterangan,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    /**
     * Seed 16 transaksi realisasi SPM/SP2D/PTUP asli Paket 2 (SPPG)
     */
    private function seedVerifiedSppgRealisasi($db, int $paketId, string $now): void
    {
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

    /**
     * Seed 43 transaksi realisasi SPM/SP2D/PTUP asli Paket 3 (Madrasah PHTC Riau)
     * Total Realisasi Definitif: Rp 322.773.000,00
     */
    private function seedVerifiedMadrasahRiauRealisasi($db, int $paketId, string $now): void
    {
        $verifiedRealisasi = [
            // 521213 - Belanja Honor Output Kegiatan (2 SPM = Rp 72.000.000,00)
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Narasumber TP4D Kejati Riau Bulan Oktober 2025 (50 OJ)', '00350A', '2025-10-31', '259991330369850', 45000000.00, '1. HONOR NARSUM TP4D OKTOBER (SPM 00350A).pdf'],
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Narasumber TP4D Kejati Riau Bulan November 2025 (30 OJ)', '00464A', '2025-12-04', '259991330462001', 27000000.00, '2. HONOR TP4D NOVEMBER (SPM 00464A).pdf'],

            // 521811 - Belanja Barang Persediaan Konsumsi (6 SPM = Rp 31.366.000,00)
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK Juli 2025 Penggantian Uang Persediaan (GUP September)', '00279A (GUP)', '2025-09-24', '259991110599182', 1545000.00, '1. GUP SEPTEMBER (SPM 00279A) ATK JULI OKE.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan Agustus 2025 (CV. Sinar Cahaya Lestari)', '00255A', '2025-09-17', '259991310986701', 5760000.00, '2. ATK DAN SUPPLIES AGUSTUS (SPM 00255A) OK.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan September 2025 (CV. Sinar Cahaya Lestari)', '00294A', '2025-10-06', '259991311046210', 5760000.00, '3. ATK DAN SUPPLIES SEPTEMBER (SPM 00294A) OK.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan Oktober 2025 (CV. Sinar Cahaya Lestari)', '00321A', '2025-10-21', '259991311100412', 5760000.00, '4. ATK DAN SUPPLIES  OKTOBER (SPM 00321 A) OK .pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan November 2025 (CV. Sinar Cahaya Lestari)', '00371A', '2025-11-13', '259991311195670', 5760000.00, '5. ATK DAN SUPLIES NOVEMBER (SPM 00371A) OK.pdf'],
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Pembelian ATK & Computer Supplies Bulan Desember 2025 (CV. Sinar Cahaya Lestari)', '00499A', '2025-12-09', '259991311369738', 6781000.00, '6. ATK SUPLIES DESEMBER (SPM 00499A) OK.pdf'],

            // 522191 - Belanja Jasa Lainnya (10 SPM = Rp 66.000.000,00)
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Juli & Agustus 2025 a.n. Revano (2 OB @ Rp 6.000.000)', '00241A', '2025-09-09', '259991320492100', 12000000.00, '1. GAJI BULAN JULI DAN AGUSTUS (SPM 00241A) OKE.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Agustus 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00242A', '2025-09-09', '259991320492101', 6000000.00, '2. GAJI BULAN AGUSTUS (SPM 00242A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan September 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00256A', '2025-09-17', '259991320509822', 6000000.00, '3. GAJI BULAN SEPTEMBER (SPM 00256A) OKE.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan September 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00257A', '2025-09-17', '259991320509823', 6000000.00, '4. GAJI BULAN SEPTEMBER (SPM 00257A) OKE.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Oktober 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00338A', '2025-10-27', '259991320584711', 6000000.00, '5. GAJI BULAN OKTOBER (SPM 00338A) OK.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Oktober 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00337A', '2025-10-27', '259991320584710', 6000000.00, '6. GAJI BULAN OKTOBER (SPM 00337A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan November 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00429A', '2025-12-01', '259991320674890', 6000000.00, '7. GAJI BULAN NOVEMBER (SPM 00429).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan November 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00428A', '2025-12-01', '259991320674889', 6000000.00, '8. GAJI BULAN NOVEMBER (SPM 00428A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Desember 2025 a.n. Ade Putra (1 OB @ Rp 6.000.000)', '00481A', '2025-12-09', '259991320700543', 6000000.00, '9. GAJI DESEMBER (SPM 00481A) OK.pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Pendukung Teknis Bulan Desember 2025 a.n. Revano (1 OB @ Rp 6.000.000)', '00480A', '2025-12-09', '259991320700542', 6000000.00, '10. GAJI DESEMBER (SPM 00480A).pdf'],

            // 524111 - Belanja Perjalanan Dinas Biasa (14 SPM = Rp 139.007.000,00)
            ['524111', 'Perjadin Biasa - Rekon MC-0 Madrasah PHTC Kab. Siak untuk 4 Pegawai (MAN 1 Siak Tgl 18-19 Sep 2025)', '00270A', '2025-09-22', '259991330286411', 5090000.00, 'SPPD SIAK PHTC 1 SEPTEMBER (SPM 00270A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Workshop Pengendalian Pelaksanaan Program Madrasah ke Provinsi Banten untuk 3 Pegawai', '00287A', '2025-10-02', '259991330302145', 13910000.00, 'SPPD OKTOBER BANTEN (SPM 00287A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Bimtek Implementasi SIPBJ V2 & SPSE 4.5 Madrasah ke Kota Medan untuk 3 Pegawai', '00310A', '2025-10-15', '259991330331002', 14860000.00, 'SPPD MEDAN OKTOBER (SPM 00310A).pdf'],
            ['524111', 'Perjadin Biasa - Monev Lapangan Progres Fisik Madrasah PHTC Kab. Siak Tahap 1 untuk 4 Pegawai', '00311A', '2025-10-15', '259991330331003', 4900000.00, 'SPPD SIAK PHTC 1 OKTOBER (SPM 00311A) OK.pdf'],
            ['524111', 'Perjadin Biasa - Monitoring Mutu Konstruksi Madrasah Kab. Siak untuk 3 Pegawai (PPK, Pengawas, Pelaksana)', '00313A', '2025-10-15', '259991330331005', 3960000.00, 'SPPD SIAK OKTOBER (SPM 00313A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Pelaksanaan Kegiatan Madrasah PHTC ke Banda Aceh untuk 1 Pegawai', '00315A', '2025-10-16', '259991330333420', 5560000.00, 'SPPD ACEH OKTOBER (SPM 00315A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Teknis Program Madrasah ke Provinsi Banten Bulan November untuk 2 Pegawai', '00388A', '2025-11-19', '259991330419812', 11230000.00, 'SPPD BANTEN NOVEMBER (SPM 00388A).pdf'],
            ['524111', 'Perjadin Biasa - Monev Lapangan Progres Fisik Madrasah PHTC Kab. Siak Tahap 2 untuk 4 Pegawai', '00444A', '2025-12-03', '259991330451845', 7550000.00, 'SPPD SIAK PHTC 1 NOVEMBER (SPM 00444A).pdf'],
            ['524111', 'Perjadin Biasa - Konsultasi & Evaluasi Teknis Progres Madrasah ke Kementerian PUPR Jakarta untuk 3 Pegawai', '00447A', '2025-12-03', '259991330451772', 18250000.00, 'SPPD JAKARTA NOVEMBER (SPM 00447A).pdf'],
            ['524111', 'Perjadin Biasa - Monev Akhir & Rekonsiliasi Lapangan Madrasah PHTC Kab. Siak Bulan Desember untuk 2 Pegawai', '00532A', '2025-12-16', '259991330513923', 3030000.00, 'SPPD SIAK DESEMBER (SPM 00532A).pdf'],
            ['524111', 'Perjadin Biasa - Rapat Koordinasi Evaluasi Akhir Kegiatan Madrasah ke Provinsi Banten untuk 1 Pegawai', '00544A', '2025-12-17', '259991330521098', 5610000.00, 'SPPD BANTEN DESMBER (SPM 00544A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Pelaporan dan Audit Teknis Madrasah ke Banten & Semarang untuk 1 Pegawai', '00558A', '2025-12-22', '259991330532061', 6130000.00, 'SPPD BANTEN SEMARANG DESEMBER (SPM 00558A).pdf'],
            ['524111', 'Perjadin Biasa - Koordinasi Finalisasi Serah Terima Hasil Pekerjaan Madrasah ke Banten untuk 3 Pegawai', '00562A', '2025-12-22', '259991330532065', 16837000.00, 'SPPD DESEMBER BANTEN (SPM 00562A).pdf'],
            ['524111', 'Perjadin Biasa - Pertanggungjawaban TUP (PTUP) Monev Lapangan Tim PPK & Was Madrasah PHTC Subkomponen AB', '00599A (PTUP)', '2025-12-31', '259991702155735', 22090000.00, 'TUP 3 SPPD PHTC 1 (SPM 00599A).pdf'],

            // 524113 - Belanja Perjalanan Dinas Dalam Kota (11 SPM = Rp 14.400.000,00)
            ['524113', 'Perjadin Dalam Kota - Monitoring & Koordinasi Lapangan Kota Pekanbaru Bulan September untuk 7 Pegawai', '00271A', '2025-09-22', '259991330286412', 1050000.00, 'SPPD PKU SEPTEMBER PHTC 1  (SPM 00271A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Oktober Tahap 1 untuk 4 Pegawai', '00309A', '2025-10-15', '259991330331001', 600000.00, 'SPPD PKU OKTOBER PHTC 1 (SPM 00309A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Oktober Tahap 2 untuk 5 Pegawai', '00312A', '2025-10-15', '259991330331004', 750000.00, 'SPPD PEKANBARU OKTOBER PHTC 1 (SPM 00312A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Oktober Tahap 3 untuk 5 Pegawai', '00349A', '2025-10-31', '259991330369849', 750000.00, 'SPPD PKU OKTOBER PHTC 1  (SPM 00349A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan November Tahap 1 untuk 4 Pegawai', '00405A', '2025-11-26', '259991330438101', 600000.00, 'SPPD PKU NOVEMBER PHTC 1 (SPM 00405A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan November Tahap 2 untuk 2 Pegawai', '00406A', '2025-11-26', '259991330438102', 300000.00, 'SPPD PKU NOVEMBER PHTC 1 (SPM 00406A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan November Tahap 3 untuk 3 Pegawai', '00443A', '2025-12-03', '259991330451844', 450000.00, 'SPPD PKU NOVEMBER PHTC 1 (SPM 00443A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Desember Tahap 1 untuk 2 Pegawai', '00491A', '2025-12-09', '259991330478413', 300000.00, 'SPPD PKU DESEMBER PHTC 1 (SPM 00491A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Desember Tahap 2 untuk 4 Pegawai', '00493A', '2025-12-09', '259991330478415', 600000.00, 'SPPD PKU DESEMBER PHTC 1 (SPM 00493A).pdf'],
            ['524113', 'Perjadin Dalam Kota - Monitoring Lapangan Madrasah Kota Pekanbaru Bulan Desember Tahap 3 untuk 2 Pegawai', '00559A', '2025-12-22', '259991330532062', 300000.00, 'SPPD PKU DESEMBER PHTC 1 (SPM 00559A) OK.pdf'],
            ['524113', 'Perjadin Dalam Kota - Pelaksanaan Monitoring Evaluasi Akhir Lapangan Kota Pekanbaru untuk 7 Pegawai & Pendamping Kejaksaan', '00561A', '2025-12-22', '259991330532064', 8700000.00, 'SPPD PEKANBARU  DESEMBER (SPM 00561A).pdf'],
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

    /**
     * Seed 10 transaksi realisasi SPM/SP2D asli Paket 1 (Rakor Madrasah PHTC Tahap 3 - Amsal Anwari, S.T.)
     * Total Realisasi: Rp 87.955.000,00 (Pagu: Rp 90.000.000,00 - Efisiensi: Rp 2.045.000,00)
     */
    private function seedVerifiedPhtc3Realisasi($db, int $paketId, string $now): void
    {
        $verifiedRealisasi = [
            // 521211 - Belanja Bahan (5 SPM = Rp 19.260.000,00)
            ['521211', 'Belanja Bahan - Pembelian ATK Rakor Survey & Perencanaan Teknis Madrasah PHTC Tahap 3 Bulan November 2025 (CV. Sinar Cahaya Lestari)', '00417A', '2025-12-01', '259991311235906', 2990000.00, 'ATK BULAN NOVEMBER (SPM 00417A).pdf'],
            ['521211', 'Belanja Bahan - Pembelian Supplies Komputer Rakor Madrasah PHTC Tahap 3 Bulan November 2025 (CV. Sinar Cahaya Lestari)', '00416A', '2025-12-01', '259991311235907', 1460000.00, 'SUPLIES KOMPUTER NOVEMBER (SPM 00416A) OK.pdf'],
            ['521211', 'Belanja Bahan - Pengadaan Materi Kit Rakor Survey & Perencanaan Teknis Madrasah PHTC Tahap 3 (33 Peserta + Narasumber)', '00392A', '2025-11-20', '259991311210450', 12250000.00, 'MATERI KIT (SPM 00392A).pdf'],
            ['521211', 'Belanja Bahan - Pengadaan Spanduk / Backdrop Rakor Madrasah PHTC Tahap 3 Penggantian Uang Persediaan (GUP 02 Desember)', '00442A (GUP)', '2025-12-02', '259991110645120', 560000.00, 'GUP 02 DES (SPM 00442A) SPANDUK.pdf'],
            ['521211', 'Belanja Bahan - Cetak dan Penggandaan Laporan Hasil Pelaksanaan Kegiatan Rakor PHTC Tahap 3 (CV. Riau Grafika Mandiri)', '00462A', '2025-12-04', '259991311289654', 2000000.00, 'CETAK LAPORAN RAKOR PHTC 3 (SPM 00462 A).pdf'],

            // 521213 - Belanja Honor Output Kegiatan (2 SPM = Rp 3.900.000,00)
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Narasumber Rakor Teknis Madrasah Pejabat Eselon III (2 Jam Pelajaran @ Rp 900.000)', '00423A', '2025-12-01', '259991330441205', 1800000.00, 'HONORARIUM NARASUMBER (SPM  00423A).pdf'],
            ['521213', 'Belanja Honor Output Kegiatan - Honorarium Tim Penyelenggara Swakelola Rakor Madrasah PHTC Tahap 3 (7 Orang Sesuai SK No. 27/2025)', '00467A', '2025-12-04', '259991330462110', 2100000.00, 'HONOR TIM SWAKELOLA (SPM 00467A) OKE.pdf'],

            // 524114 - Belanja Perjalanan Dinas Paket Meeting (3 SPM = Rp 64.795.000,00)
            ['524114', 'Belanja Perjalanan Dinas Paket Meeting - Pembayaran Paket Fullboard Meeting Hotel Royal Asnof Pekanbaru (33 Peserta + Tim Satker)', '00446A', '2025-12-03', '259991330451850', 49000000.00, 'PAKET MEETING RAKOR (SPM 00446A) OK.pdf'],
            ['524114', 'Belanja Perjalanan Dinas Paket Meeting - Uang Harian Paket Meeting Dalam Kota Peserta Rakor Madrasah PHTC Tahap 3 (33 Orang)', '00425A', '2025-12-01', '259991330441208', 8580000.00, 'UANG HARIAN PESERTA (SPM 00425A).pdf'],
            ['524114', 'Belanja Perjalanan Dinas Paket Meeting - Biaya Transportasi Lokal Peserta Rakor Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3 (33 Orang)', '00424A', '2025-12-01', '259991330441207', 7215000.00, 'UANG TRANSPORT PESERTA (SPM 00424A).pdf'],
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

    /**
     * Seed 12 transaksi realisasi SPM/SP2D asli Paket 4 (Administrasi Madrasah PHTC Riau 5 - Muhammad Syahridwan, S.T.)
     * Total Realisasi Definitif: Rp 86.533.000,00 (Alokasi RAB: Rp 109.828.000,00 - Efisiensi: Rp 23.295.000,00)
     */
    private function seedVerifiedMadrasahRiau5Realisasi($db, int $paketId, string $now): void
    {
        $verifiedRealisasi = [
            // 521811 - Belanja Bahan Persediaan Konsumsi (1 SPM = Rp 1.561.000,00)
            ['521811', 'Belanja Bahan Persediaan Konsumsi - Belanja Bahan ATK PHTC 5 Penggantian Uang Persediaan GUP III Maret 2026', '00086A (GUP)', '2026-03-25', '269991110125890', 1561000.00, '86. GUP III MARET (SPM 00086A) atk phtc 5.pdf'],

            // 522191 - Belanja Jasa Lainnya (5 SPM = Rp 30.000.000,00 - 100% Alokasi RAB 522191)
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Januari 2026 (SPK No. 01/2026)', '00048A', '2026-02-05', '269991320102145', 6000000.00, '1. GAJI JAN ADE (SPM 00048A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Februari 2026 (SPK No. 01/2026)', '00059A', '2026-03-02', '269991320125891', 6000000.00, '2. GAJI FEB ADE (SPM 00059A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Maret 2026 (SPK No. 01/2026)', '00112A', '2026-04-06', '269991320148902', 6000000.00, '3. GAJI MARET ADE (SPM 00112A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan April 2026 (SPK No. 01/2026)', '00147A', '2026-05-04', '269991320172314', 6000000.00, '4. GAJI APRIL ADE (SPM 00147A).pdf'],
            ['522191', 'Belanja Jasa Lainnya - Gaji Tenaga Teknis Pengawasan Ade Putra, S.T. Bulan Mei 2026 (SPK No. 01/2026)', '00200A', '2026-06-03', '269991320198450', 6000000.00, '5. GAJI MEI ADE (SPM 00200A).pdf'],

            // 524111 - Belanja Perjalanan Dinas Biasa (6 SPM = Rp 54.972.000,00 - 100% Alokasi RAB 524111)
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Madrasah PHTC Kab. Bengkalis Tahap I Bulan Januari 2026', '00029A', '2026-01-20', '269991330089123', 8432000.00, '1. SPPD JANUARI BENGKALIS (SPM 00029A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Madrasah PHTC Kab. Bengkalis Tahap II Bulan Januari 2026', '00030A', '2026-01-22', '269991330089124', 6454000.00, '2. SPPD JANUARI BENGKALIS (SPM 00030A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Evaluasi Lapangan Madrasah PHTC Kab. Bengkalis Bulan Februari 2026', '00075A', '2026-03-12', '269991330112340', 11144000.00, '3. SPPD FEBRUARI BENGKALIS (SPM 00075A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Evaluasi Lapangan Madrasah PHTC Kab. Bengkalis Bulan Maret 2026', '00106A', '2026-04-02', '269991330134567', 10364000.00, '4. SPPD MARET BENGKALIS (SPM 00106A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monitoring Evaluasi Lapangan Madrasah PHTC Kab. Bengkalis Bulan April 2026', '00140A', '2026-05-02', '269991330167890', 10364000.00, '5. SPPD APRIL BENGKALIS (SPM 00140A).pdf'],
            ['524111', 'Belanja Perjalanan Dinas Biasa - SPPD Monev Lapangan Madrasah PHTC Kota Dumai & Bengkalis Bulan Juni 2026', '00226A', '2026-06-18', '269991330214560', 8214000.00, '6. SPPD DUMAI BENGKALIS JUNI (SPM 00226A).pdf'],
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

    /**
     * Sub-Menu 5: Daftar Personel Perjalanan Dinas Swakelola
     */
    public function perjadinIndex()
    {
        $this->syncVerifiedSppgData();
        $this->ensurePerjadinModuleReady();

        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/perjadin');
        $paketList   = $this->getEnrichedPaketList();

        $filters = [
            'paket_id'      => (int) ($this->request->getGet('paket_id') ?? 0),
            'nama_personel' => (string) ($this->request->getGet('nama_personel') ?? ''),
            'kota_tujuan'   => (string) ($this->request->getGet('kota_tujuan') ?? ''),
            'status_match'  => (string) ($this->request->getGet('status_match') ?? ''),
            'spm'           => (string) ($this->request->getGet('spm') ?? ''),
            'kode_akun'     => (string) ($this->request->getGet('kode_akun') ?? ''),
        ];

        $records            = $this->perjadinPersonelModel->getFilteredPersonel($filters);
        $stats              = $this->perjadinPersonelModel->getSummaryStats($filters);
        $uniquePersonel     = $this->perjadinPersonelModel->getUniquePersonel();
        $uniqueDestinations = $this->perjadinPersonelModel->getUniqueDestinations();

        return view('admin/swakelola/perjadin', [
            'title'              => 'Daftar Personel Perjalanan Dinas Swakelola',
            'records'            => $records,
            'stats'              => $stats,
            'filters'            => $filters,
            'paketList'          => $paketList,
            'uniquePersonel'     => $uniquePersonel,
            'uniqueDestinations' => $uniqueDestinations,
            'permissions'        => $permissions,
        ]);
    }

    /**
     * Export Daftar Personel Perjadin ke Excel (.xlsx)
     */
    public function exportPerjadinExcel()
    {
        $this->syncVerifiedSppgData();
        $this->ensurePerjadinModuleReady();

        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/perjadin');
        if (empty($permissions['export'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengekspor data ini.');
        }

        $filters = [
            'paket_id'      => (int) ($this->request->getGet('paket_id') ?? 0),
            'nama_personel' => (string) ($this->request->getGet('nama_personel') ?? ''),
            'kota_tujuan'   => (string) ($this->request->getGet('kota_tujuan') ?? ''),
            'status_match'  => (string) ($this->request->getGet('status_match') ?? ''),
            'spm'           => (string) ($this->request->getGet('spm') ?? ''),
            'kode_akun'     => (string) ($this->request->getGet('kode_akun') ?? ''),
        ];

        $records = $this->perjadinPersonelModel->getFilteredPersonel($filters);
        $stats   = $this->perjadinPersonelModel->getSummaryStats($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Perjadin Swakelola');

        // Satker Header
        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');
        $sheet->mergeCells('A3:P3');
        $sheet->mergeCells('A4:P4');

        $sheet->setCellValue('A1', 'DAFTAR PERSONEL PERJALANAN DINAS (PERJADIN) SWAKELOLA');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU');
        $sheet->setCellValue('A3', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS — KEMENTERIAN PEKERJAAN UMUM');
        $sheet->setCellValue('A4', 'Tahun Anggaran 2025 / 2026 (Audit LHA No. 09 Multi-Paket Swakelola)');

        $sheet->getStyle('A1:P4')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2:A4')->getFont()->setSize(10)->getColor()->setARGB('FF334155');
        $sheet->getStyle('A1:P4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // KPI Ringkasan
        $sheet->mergeCells('A6:P6');
        $kpiText = sprintf(
            'Total Perjalanan: %d Penugasan  |  Personel Unik: %d Orang  |  Cocok Master Pegawai: %d  |  Non-Master / Eksternal: %d  |  Total Biaya: Rp %s',
            $stats['total_perjalanan'],
            $stats['personel_unik'],
            $stats['matched'],
            $stats['unmatched'],
            number_format($stats['total_biaya'], 0, ',', '.')
        );
        $sheet->setCellValue('A6', $kpiText);
        $sheet->getStyle('A6:P6')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FF1E3A8A');
        $sheet->getStyle('A6:P6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:P6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF6FF');

        // Headers
        $headers = [
            'A8' => 'NO.',
            'B8' => 'NAMA PAKET KEGIATAN',
            'C8' => 'AKUN MAK',
            'D8' => 'NAMA PERSONEL',
            'E8' => 'STATUS MASTER',
            'F8' => 'NIP',
            'G8' => 'GOLONGAN',
            'H8' => 'JABATAN / PERAN',
            'I8' => 'INSTANSI',
            'J8' => 'KOTA TUJUAN',
            'K8' => 'TGL BERANGKAT',
            'L8' => 'TGL KEMBALI',
            'M8' => 'LAMA (HARI)',
            'N8' => 'NO. SPM & TGL',
            'O8' => 'NO. SP2D',
            'P8' => 'BIAYA (RP)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']],
        ];
        $sheet->getStyle('A8:P8')->applyFromArray($headerStyle);
        $sheet->getRowDimension(8)->setRowHeight(28);

        $rowNum = 9;
        $no = 1;
        foreach ($records as $r) {
            $isMatched = ((int) ($r['is_matched_master'] ?? 0) === 1);
            $statusLabel = $isMatched ? 'Master Pegawai' : 'Non-Master / Eksternal';
            $nipDisplay = ! empty($r['master_nip']) ? $r['master_nip'] : ($r['nip'] ?? '-');
            $golDisplay = ! empty($r['master_golongan']) ? $r['master_golongan'] : ($r['golongan'] ?? '-');
            $jabDisplay = ! empty($r['master_jabatan']) ? $r['master_jabatan'] : ($r['jabatan'] ?? '-');
            $spmInfo    = trim(($r['nomor_spm'] ?? '') . ' ' . (! empty($r['tanggal_spm']) ? '(' . date('d/m/Y', strtotime($r['tanggal_spm'])) . ')' : ''));

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $r['nama_paket'] ?? '-');
            $sheet->setCellValue('C' . $rowNum, $r['kode_akun'] ?? '-');
            $sheet->setCellValue('D' . $rowNum, $r['nama_personel'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $statusLabel);
            $sheet->setCellValue('F' . $rowNum, $nipDisplay);
            $sheet->setCellValue('G' . $rowNum, $golDisplay);
            $sheet->setCellValue('H' . $rowNum, $jabDisplay);
            $sheet->setCellValue('I' . $rowNum, $r['instansi'] ?? 'Satker PPS Riau');
            $sheet->setCellValue('J' . $rowNum, $r['kota_tujuan'] ?? '-');
            $sheet->setCellValue('K' . $rowNum, ! empty($r['tanggal_berangkat']) ? date('d/m/Y', strtotime($r['tanggal_berangkat'])) : '-');
            $sheet->setCellValue('L' . $rowNum, ! empty($r['tanggal_kembali']) ? date('d/m/Y', strtotime($r['tanggal_kembali'])) : '-');
            $sheet->setCellValue('M' . $rowNum, (int) ($r['lama_hari'] ?? 1));
            $sheet->setCellValue('N' . $rowNum, $spmInfo);
            $sheet->setCellValue('O' . $rowNum, $r['nomor_sp2d'] ?? '-');
            $sheet->setCellValue('P' . $rowNum, (float) ($r['nilai_biaya'] ?? 0));

            // Alignments
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $rowNum . ':M' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');

            if ($rowNum % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':P' . $rowNum)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $rowNum++;
        }

        // Summary row
        $sheet->mergeCells('A' . $rowNum . ':O' . $rowNum);
        $sheet->setCellValue('A' . $rowNum, 'TOTAL BIAYA PERJALANAN DINAS');
        $sheet->setCellValue('P' . $rowNum, "=SUM(P8:P" . ($rowNum - 1) . ")");
        $sheet->getStyle('A' . $rowNum . ':P' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('P' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $rowNum . ':P' . $rowNum)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFEF3C7');

        // Borders
        $sheet->getStyle('A8:P' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        // Auto width
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Daftar_Personel_Perjadin_Swakelola_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Tampilan cetak / PDF Daftar Personel Perjadin
     */
    public function exportPerjadinPdf()
    {
        $this->syncVerifiedSppgData();
        $this->ensurePerjadinModuleReady();

        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/perjadin');
        if (empty($permissions['export'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mencetak laporan ini.');
        }

        $filters = [
            'paket_id'      => (int) ($this->request->getGet('paket_id') ?? 0),
            'nama_personel' => (string) ($this->request->getGet('nama_personel') ?? ''),
            'kota_tujuan'   => (string) ($this->request->getGet('kota_tujuan') ?? ''),
            'status_match'  => (string) ($this->request->getGet('status_match') ?? ''),
            'spm'           => (string) ($this->request->getGet('spm') ?? ''),
            'kode_akun'     => (string) ($this->request->getGet('kode_akun') ?? ''),
        ];

        $records = $this->perjadinPersonelModel->getFilteredPersonel($filters);
        $stats   = $this->perjadinPersonelModel->getSummaryStats($filters);

        return view('admin/swakelola/perjadin_pdf', [
            'title'   => 'Cetak Daftar Personel Perjalanan Dinas Swakelola',
            'records' => $records,
            'stats'   => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Trigger sinkronisasi pencocokan Personel Perjadin dengan Master Pegawai
     */
    public function syncPerjadinMasterPegawai()
    {
        $this->ensurePerjadinModuleReady();

        $permissions = $this->resolveMenuAksesPermissions('admin/swakelola/perjadin');
        if (empty($permissions['edit']) && empty($permissions['add'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menyinkronkan data.');
        }

        $result = $this->perjadinPersonelModel->syncWithMasterPegawai();

        // Note Rule 1: Single notification, no duplication
        $msg = sprintf(
            'Sinkronisasi Master Pegawai selesai: %d data berhasil dicocokkan dengan Master Pegawai, %d personel non-master/eksternal tetap dicatat dalam daftar.',
            $result['matched'],
            $result['unmatched']
        );

        return redirect()->to('/admin/swakelola/perjadin')->with('success', $msg);
    }

    /**
     * Memastikan tabel trn_swakelola_perjadin_personel, menu_lv2, dan menu_akses telah siap
     */
    private function ensurePerjadinModuleReady(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        // 1. Ensure Table trn_swakelola_perjadin_personel
        if (! $db->tableExists('trn_swakelola_perjadin_personel')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'realisasi_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'kode_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'nama_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'kode_akun' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'nomor_spt' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'tanggal_spt' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'nomor_spd' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'nama_personel' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'jabatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'instansi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'default'    => 'Satker Pelaksanaan Prasarana Strategis Riau',
                ],
                'golongan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'is_matched_master' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'kota_tujuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'tanggal_berangkat' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'tanggal_kembali' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'lama_hari' => [
                    'type'       => 'INT',
                    'constraint' => 4,
                    'default'    => 1,
                ],
                'maksud_tugas' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nomor_spm' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'tanggal_spm' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'nomor_sp2d' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'nilai_biaya' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'file_bukti' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('paket_id');
            $forge->addKey('pegawai_id');
            $forge->addKey('kode_akun');
            $forge->addKey('nomor_spm');
            $forge->createTable('trn_swakelola_perjadin_personel', true);
        }

        // 2. Ensure Menu Lv2 'Daftar Perjadin'
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $swakelolaRow = $db->table('menu_lv1')
                ->where('LOWER(label)', 'swakelola')
                ->get()
                ->getRowArray();
            $swakelolaId = $swakelolaRow['id'] ?? null;

            if ($swakelolaId !== null) {
                $menuLv2 = $db->table('menu_lv2')
                    ->where('LOWER(link)', 'admin/swakelola/perjadin')
                    ->get()
                    ->getRowArray();

                if (! $menuLv2) {
                    $nextId = $this->generateNextChildMenuId('menu_lv2', (string) $swakelolaId);
                    $db->table('menu_lv2')->insert([
                        'id'        => $nextId,
                        'header'    => (string) $swakelolaId,
                        'label'     => 'Daftar Perjadin',
                        'link'      => 'admin/swakelola/perjadin',
                        'icon'      => 'fas fa-plane-departure',
                        'ordering'  => 5,
                        'is_active' => 1,
                    ]);
                    $menuId = $nextId;
                } else {
                    $menuId = (string) $menuLv2['id'];
                }

                // 3. Ensure menu_akses entries
                if ($db->tableExists('menu_akses')) {
                    $roles = [1, 2, 3, 5, 6];
                    foreach ($roles as $rId) {
                        $hasAkses = $db->table('menu_akses')
                            ->where('role_id', $rId)
                            ->where('menu_id', $menuId)
                            ->countAllResults();

                        if ($hasAkses === 0) {
                            $isSuperOrAdmin = in_array($rId, [1, 2], true);
                            $isKeuangan     = ($rId === 5);
                            $isTataUsaha    = in_array($rId, [3, 6], true);

                            $db->table('menu_akses')->insert([
                                'role_id'       => $rId,
                                'menu_id'       => $menuId,
                                'FiturAdd'      => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                                'FiturEdit'     => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                                'FiturExport'   => 1,
                                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                                'FiturApproval' => ($isSuperOrAdmin || $isKeuangan) ? 1 : 0,
                            ]);
                        }
                    }
                }
            }
        }

        // 4. Seed Verified Perjadin Dataset if empty
        $currentCount = $db->table('trn_swakelola_perjadin_personel')->countAllResults();
        if ($currentCount === 0) {
            $dataset = SwakelolaPerjadinData::all();

            $paketMap = [];
            if ($db->tableExists('trn_swakelola_paket')) {
                $pRows = $db->table('trn_swakelola_paket')->get()->getResultArray();
                foreach ($pRows as $p) {
                    $paketMap[$p['kode_paket']] = (int) $p['id'];
                }
            }

            $realisasiMap = [];
            if ($db->tableExists('trn_swakelola_realisasi')) {
                $rRows = $db->table('trn_swakelola_realisasi')->get()->getResultArray();
                foreach ($rRows as $r) {
                    $key = ($r['nomor_spm'] ?? '') . '_' . ($r['kode_akun'] ?? '');
                    $realisasiMap[$key] = (int) $r['id'];
                }
            }

            $now = date('Y-m-d H:i:s');
            foreach ($dataset as $row) {
                $kodePaket   = $row['kode_paket'] ?? '';
                $paketId     = $paketMap[$kodePaket] ?? 1;
                $spmKey      = ($row['nomor_spm'] ?? '') . '_' . ($row['kode_akun'] ?? '');
                $realisasiId = $realisasiMap[$spmKey] ?? null;

                $db->table('trn_swakelola_perjadin_personel')->insert([
                    'paket_id'          => $paketId,
                    'realisasi_id'      => $realisasiId,
                    'kode_paket'        => $kodePaket,
                    'nama_paket'        => $row['nama_paket'] ?? '',
                    'kode_akun'         => $row['kode_akun'] ?? '',
                    'nomor_spt'         => $row['nomor_spt'] ?? null,
                    'tanggal_spt'       => $row['tanggal_spt'] ?? null,
                    'nomor_spd'         => $row['nomor_spd'] ?? null,
                    'nama_personel'     => $row['nama_personel'] ?? '',
                    'nip'               => $row['nip'] ?? null,
                    'jabatan'           => $row['jabatan'] ?? null,
                    'instansi'          => $row['instansi'] ?? 'Satker Pelaksanaan Prasarana Strategis Riau',
                    'golongan'          => $row['golongan'] ?? null,
                    'pegawai_id'        => null,
                    'is_matched_master' => 0,
                    'kota_tujuan'       => $row['kota_tujuan'] ?? 'Kota Pekanbaru',
                    'tanggal_berangkat' => $row['tanggal_berangkat'] ?? null,
                    'tanggal_kembali'   => $row['tanggal_kembali'] ?? null,
                    'lama_hari'         => $row['lama_hari'] ?? 1,
                    'maksud_tugas'      => $row['maksud_tugas'] ?? null,
                    'nomor_spm'         => $row['nomor_spm'] ?? null,
                    'tanggal_spm'       => $row['tanggal_spm'] ?? null,
                    'nomor_sp2d'        => $row['nomor_sp2d'] ?? null,
                    'nilai_biaya'       => $row['nilai_biaya'] ?? 0.00,
                    'file_bukti'        => $row['file_bukti'] ?? null,
                    'keterangan'        => $row['keterangan'] ?? null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }

            // Run initial sync with master pegawai
            $this->perjadinPersonelModel->syncWithMasterPegawai();
        }
    }

    /**
     * Helper generate child menu id for menu_lv2
     */
    private function generateNextChildMenuId(string $table, string $headerId): string
    {
        $db = \Config\Database::connect();
        $prefix = rtrim($headerId, '-') . '-';
        $rows = $db->table($table)
            ->select('id')
            ->like('id', $prefix, 'after')
            ->get()
            ->getResultArray();

        $max = 0;
        foreach ($rows as $r) {
            $val = substr((string) $r['id'], strlen($prefix));
            if (is_numeric($val) && (int) $val > $max) {
                $max = (int) $val;
            }
        }

        return $prefix . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }
}
