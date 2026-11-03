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
                'default_real'   => 0.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Kota Pekanbaru, Provinsi Riau',
                'jangka_waktu'   => 'TA 2025 (Fullboard Meeting 3 Hari)',
                'rab_accounts'   => [
                    '521211' => [
                        'nama'   => 'Belanja Bahan',
                        'uraian' => 'Belanja Bahan (ATK Rp 3 Jt, Computer Supplies Rp 1,5 Jt, Spanduk Rp 563 Rb, Materi Kit Rp 12,25 Jt & Penggandaan Laporan Rp 2 Jt)',
                        'pagu'   => 19313000.00,
                    ],
                    '521213' => [
                        'nama'   => 'Belanja Honor Output Kegiatan',
                        'uraian' => 'Belanja Honor Output Kegiatan (Tim Pelaksana Kegiatan & Honorarium Narasumber Eselon III)',
                        'pagu'   => 3900000.00,
                    ],
                    '524111' => [
                        'nama'   => 'Belanja Perjalanan Dinas Biasa',
                        'uraian' => 'Belanja Perjalanan Dinas Biasa (Perjalanan Dinas Narasumber Pusat Jakarta - Riau)',
                        'pagu'   => 6692000.00,
                    ],
                    '524114' => [
                        'nama'   => 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota',
                        'uraian' => 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota (Paket Fullboard, Uang Harian & Transport Peserta Rakor)',
                        'pagu'   => 60095000.00,
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
                'default_real'   => 0.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Provinsi Riau',
                'jangka_waktu'   => 'TA 2025 (6 Bulan)',
                'rab_accounts'   => [
                    '521213' => [
                        'nama'   => 'Belanja Honor Output Kegiatan',
                        'uraian' => 'Belanja Honor Output Kegiatan (Honorarium Narasumber Eselon III ke Bawah / yang Disetarakan 80 OJ)',
                        'pagu'   => 72000000.00,
                    ],
                    '521811' => [
                        'nama'   => 'Belanja Barang Persediaan Barang Konsumsi',
                        'uraian' => 'Belanja Barang Persediaan Barang Konsumsi (ATK & Bahan Habis Pakai Rp 15,735 Jt + Computer Supplies Rp 15,735 Jt)',
                        'pagu'   => 31470000.00,
                    ],
                    '522191' => [
                        'nama'   => 'Belanja Jasa Lainnya',
                        'uraian' => 'Belanja Jasa Lainnya (Tenaga Pendukung Kegiatan Administrasi Teknis 11 OB)',
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
                'tahun_anggaran' => 2025,
                'pagu_anggaran'  => 192100000.00,
                'default_real'   => 0.00,
                'tipe_swakelola' => 'Tipe I',
                'ppk_nama'       => 'Nurhidayat Nugroho, S.Ars.',
                'ppk_nip'        => '19901221 201802 1 001',
                'kasatker_nama'  => 'Muhammad Yudi Prasetya, S.T.',
                'kasatker_nip'   => '19800626 200604 1 016',
                'lokasi'         => 'Provinsi Riau',
                'jangka_waktu'   => 'TA 2025 (3 Bulan)',
                'rab_accounts'   => [
                    '521213' => [
                        'nama'   => 'Belanja Honor Output Kegiatan',
                        'uraian' => 'Belanja Honor Output Kegiatan (Pendampingan dengan Kejaksaan — Pejabat Eselon III ke Bawah 16 OJ)',
                        'pagu'   => 14400000.00,
                    ],
                    '521811' => [
                        'nama'   => 'Belanja Barang Persediaan Barang Konsumsi',
                        'uraian' => 'Belanja Barang Persediaan Barang Konsumsi (ATK & Bahan Habis Pakai Rp 4,514 Jt + Computer Supplies Rp 4,514 Jt)',
                        'pagu'   => 9028000.00,
                    ],
                    '524111' => [
                        'nama'   => 'Belanja Perjalanan Dinas Biasa',
                        'uraian' => 'Belanja Perjalanan Dinas Biasa (Koordinasi ke Pusat Rp 23,312 Jt & Monitoring Evaluasi Lapangan Rp 145,36 Jt)',
                        'pagu'   => 168672000.00,
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
                9 => [
                    ['label' => 'B. 4. a. KAK_Final_Rakor_Survei_Perencanaan_Teknis_Madrasah_PHTC_Tahap_3.docx', 'file_name' => 'B. 4. a. KAK_Final_Rakor_Survei_Perencanaan_Teknis_Madrasah_PHTC_Tahap_3.docx'],
                    ['label' => 'B. 4. b. RAB Final_Rakor_Madrasah_PHTC_Tahap_3.docx', 'file_name' => 'B. 4. b. RAB Final_Rakor_Madrasah_PHTC_Tahap_3.docx'],
                ],
            ];
        }

        if ($slug !== 'sppg') {
            return [];
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

        // Fallback untuk nama lama item B.4 SPPG
        if ($slug === 'sppg' && strpos($safeFileName, 'B. 4.') === 0) {
            $candidates[] = FCPATH . 'uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/B. 4. KAK Final dan RAB Rinci Administrasi SPPG.docx';
            $candidates[] = FCPATH . 'uploads/swakelola/sppg/B. 4. a. KAK Final Administrasi SPPG.docx';
            $candidates[] = ROOTPATH . 'public/uploads/swakelola/sppg/B. 4. a. KAK Final Administrasi SPPG.docx';
        }

        // 4. Fallback do_not_upload/audit
        $candidates[] = ROOTPATH . 'do_not_upload/audit/hasil_sppg/' . $safeFileName;
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
                $initialStatus = ($kodePaket === '06.691285.GM.7717.CBI.001.900.A') ? 'Selesai' : 'Dalam Penyusunan';
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
                if (($paket['kasatker_nip'] ?? '') !== '19800626 200604 1 016' || (float) ($paket['pagu_anggaran'] ?? 0) !== (float) $meta['pagu_anggaran']) {
                    $updateFields = [
                        'nama_kegiatan' => $meta['nama_kegiatan'],
                        'pagu_anggaran' => $meta['pagu_anggaran'],
                        'ppk_nama'      => $meta['ppk_nama'],
                        'ppk_nip'       => $meta['ppk_nip'],
                        'kasatker_nama' => $meta['kasatker_nama'],
                        'kasatker_nip'  => $meta['kasatker_nip'],
                        'updated_at'    => $now,
                    ];
                    if ($kodePaket === '06.691285.GM.7717.CBI.001.900.A') {
                        $updateFields['realisasi_anggaran'] = 141159100.00;
                    }
                    $db->table('trn_swakelola_paket')->where('id', $paketId)->update($updateFields);
                }
            }

            // Pastikan setiap paket memiliki 28 baris dokumen checklist
            $existingDocs = $db->table('trn_swakelola_dokumen')
                ->where('paket_id', $paketId)
                ->countAllResults();

            if ($existingDocs < 28) {
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

            // Pastikan Paket 2 (SPPG) memiliki 16 transaksi SPM/SP2D/PTUP asli jika tabel masih kosong
            if ($kodePaket === '06.691285.GM.7717.CBI.001.900.A' && $db->tableExists('trn_swakelola_realisasi')) {
                $realCount = $db->table('trn_swakelola_realisasi')->where('paket_id', $paketId)->countAllResults();
                if ($realCount === 0) {
                    $this->seedVerifiedSppgRealisasi($db, $paketId, $now);
                }
            }
        }
    }

    /**
     * Inisialisasi 28 item checklist awal untuk paket berdasarkan slug ('phtc3', 'sppg', 'madrasah_riau', 'madrasah_riau5')
     */
    private function seedChecklistForPackage($db, int $paketId, string $slug, array $masterChecklist, string $now): void
    {
        $db->table('trn_swakelola_dokumen')->where('paket_id', $paketId)->delete();

        $phtc3Prepopulated = [
            1  => ['A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf', 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025 — Pagu Rp 90.000.000,-'],
            3  => ['A. 3. KAK_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'KAK Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3'],
            4  => ['A. 4. RAB_RAKOR Survei dan Perencanaan Teknik Madrasah PHTC Riau Thp 3_2025.pdf', 'RAB Rapat Koordinasi Survey dan Perencanaan Teknis Madrasah PHTC Tahap 3 (Rp 90.000.000,-)'],
            5  => ['A. 5. DOKUMEN IDENTIFIKASI KEBUTUHAN DAN DASAR PEMILIHAN SWAKELOLA.docx', 'Dokumen Identifikasi Kebutuhan dan Dasar Pemilihan Swakelola Tipe I'],
            8  => ['B. 3. Rencana_Kegiatan_dan_Jadwal_Rakor_Madrasah_PHTC_Tahap_3.docx', 'Rencana Kegiatan dan Jadwal Pelaksanaan Rakor Madrasah PHTC Tahap 3'],
            9  => ['B. 4. a. KAK_Final_Rakor_Survei_Perencanaan_Teknis_Madrasah_PHTC_Tahap_3.docx', 'KAK Final & RAB Final Rakor Survei Perencanaan Teknis Madrasah PHTC Tahap 3'],
            10 => ['B. 5. BA Pembahasan Persiapan Swakelola_Rakor_Madrasah_PHTC_Tahap_3.docx', 'Berita Acara Pembahasan Persiapan Swakelola Rakor Madrasah PHTC Tahap 3'],
            11 => ['B. 6. Rencana_Kebutuhan_Rakor_PHTC_Tahap_3.docx', 'Rencana Kebutuhan Tenaga Kerja, Bahan dan Peralatan Rakor PHTC Tahap 3'],
            13 => ['C. 2. Laporan Pelaksanaan dan Daftar Hadir.pdf', 'Laporan Pelaksanaan Kegiatan dan Daftar Hadir Peserta Rakor PHTC Tahap 3'],
            17 => ['C. 6. Dokumentasi Kegiatan Rapat dan Survei  PHTC Tahap 3.pdf', 'Dokumentasi Fisik Kegiatan Rapat dan Survei Madrasah PHTC Tahap 3'],
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
            } elseif ($slug === 'phtc3' && isset($phtc3Prepopulated[$noUrut])) {
                $status     = 'Ada';
                $fileName   = $phtc3Prepopulated[$noUrut][0];
                $filePath   = 'public/uploads/swakelola/phtc3/' . $fileName;
                $keterangan = $phtc3Prepopulated[$noUrut][1];
            } elseif (in_array($slug, ['madrasah_riau', 'madrasah_riau5'], true) && $noUrut === 1) {
                $status     = 'Ada';
                $fileName   = 'A. 1. 16_2025_RINCIAN KERTAS KERJA SATKER PPS RIAU_Rev 16 POK.pdf';
                $filePath   = 'public/uploads/swakelola/' . $slug . '/' . $fileName;
                $keterangan = 'Rincian Kertas Kerja Satker (POK) Revisi 16 Tgl 23 Des 2025';
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
