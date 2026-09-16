<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisPinjamPakaiModel extends Model
{
    protected $table            = 'trn_inventaris_pinjam_pakai';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'inventaris_id',
        'renewed_from_id',
        'pegawai_id',
        'nama_peminjam',
        'nip_peminjam',
        'jabatan_peminjam',
        'kontak_peminjam',
        'no_surat',
        'kop_surat_id',
        'tgl_pinjam',
        'tgl_kembali_rencana',
        'tgl_kembali_realisasi',
        'keperluan',
        'kondisi_pinjam',
        'kondisi_kembali',
        'kelengkapan',
        'catatan',
        'file_surat',
        'status',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil seluruh item barang yang dipinjam dalam satu transaksi pinjam pakai
     */
    public function getItemsByPinjamId(int $pinjamId): array
    {
        if (! $this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            return [];
        }

        return $this->db->table('trn_inventaris_pinjam_pakai_item itm')
            ->select('itm.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.tahun_perolehan,
                      i.kondisi as kondisi_aset_sekarang, i.lokasi_ruangan, i.peruntukan')
            ->join('trn_inventaris_satker i', 'i.id = itm.inventaris_id', 'inner')
            ->where('itm.pinjam_pakai_id', $pinjamId)
            ->orderBy('itm.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil daftar tahun unik dari transaksi pinjam pakai untuk dropdown filter
     * Terurut secara menurun (DESC) dan memastikan tahun berjalan selalu tersedia.
     */
    public function getAvailableYears(): array
    {
        $rows = $this->db->table($this->table)
            ->select('DISTINCT(YEAR(tgl_pinjam)) AS tahun')
            ->where('tgl_pinjam IS NOT NULL')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();

        $years = [];
        foreach ($rows as $r) {
            if (! empty($r['tahun'])) {
                $years[] = (int) $r['tahun'];
            }
        }

        $curYear = (int) date('Y');
        if (! in_array($curYear, $years, true)) {
            $years[] = $curYear;
        }
        if (! in_array(2025, $years, true)) {
            $years[] = 2025;
        }

        rsort($years);
        return array_values(array_unique($years));
    }

    /**
     * Mengambil seluruh data pinjam pakai beserta relasi aset BMN, pegawai, dan child items
     */
    public function getPinjamWithRelations(?string $statusFilter = null, ?int $tahunFilter = null): array
    {
        $hasCfg = $this->db->tableExists('cfg_inventaris_kop_surat');
        $hasKs  = $this->db->tableExists('kop_surat');

        $kopTitleExpr = "''";
        $kopImgExpr   = "''";
        if ($hasCfg && $hasKs) {
            $kopTitleExpr = "COALESCE(cik.nama_kop, ks.title)";
            $kopImgExpr   = "COALESCE(cik.image_url, ks.image_url)";
        } elseif ($hasCfg) {
            $kopTitleExpr = "cik.nama_kop";
            $kopImgExpr   = "cik.image_url";
        } elseif ($hasKs) {
            $kopTitleExpr = "ks.title";
            $kopImgExpr   = "ks.image_url";
        }

        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang,
                      i.lokasi_ruangan, i.peruntukan,
                      peg.nama as pegawai_master_nama, peg.nip as pegawai_master_nip,
                      ju.jabatan as pegawai_master_jabatan, ' .
                      $kopTitleExpr . ' as kop_surat_title, ' .
                      $kopImgExpr . ' as kop_surat_image_url')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'left')
            ->join('mst_pegawai peg', 'peg.id = p.pegawai_id', 'left')
            ->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left');

        if ($hasCfg) {
            $builder->join('cfg_inventaris_kop_surat cik', 'cik.id = p.kop_surat_id', 'left');
        }
        if ($hasKs) {
            $builder->join('kop_surat ks', 'ks.id = p.kop_surat_id', 'left');
        }

        if ($statusFilter && in_array($statusFilter, ['dipinjam', 'dikembalikan', 'diperbaharui'], true)) {
            $builder->where('p.status', $statusFilter);
        }

        if (! empty($tahunFilter) && $tahunFilter >= 2000 && $tahunFilter <= 2100) {
            $builder->where('YEAR(p.tgl_pinjam)', $tahunFilter);
        }

        $loans = $builder->orderBy("CASE WHEN p.status = 'dipinjam' THEN 0 ELSE 1 END", 'ASC', false)
            ->orderBy('p.tgl_pinjam', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResultArray();

        if (! empty($loans) && $this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $loanIds = array_column($loans, 'id');
            $allItems = $this->db->table('trn_inventaris_pinjam_pakai_item itm')
                ->select('itm.*, 
                          i.kode_barang, i.nup, i.kode_register, i.nama_barang, 
                          i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang')
                ->join('trn_inventaris_satker i', 'i.id = itm.inventaris_id', 'inner')
                ->whereIn('itm.pinjam_pakai_id', $loanIds)
                ->orderBy('itm.id', 'ASC')
                ->get()
                ->getResultArray();

            $itemsByLoan = [];
            foreach ($allItems as $it) {
                $itemsByLoan[$it['pinjam_pakai_id']][] = $it;
            }

            foreach ($loans as &$ln) {
                $lid = (int) $ln['id'];
                $ln['items'] = $itemsByLoan[$lid] ?? [];
                if (empty($ln['items']) && ! empty($ln['inventaris_id'])) {
                    $ln['items'] = [[
                        'id'              => 0,
                        'pinjam_pakai_id' => $lid,
                        'inventaris_id'   => $ln['inventaris_id'],
                        'kode_barang'     => $ln['kode_barang'] ?? '',
                        'nup'             => $ln['nup'] ?? '',
                        'nama_barang'     => $ln['nama_barang'] ?? '',
                        'merk_tipe'       => $ln['merk_tipe'] ?? '',
                        'nilai_perolehan' => $ln['nilai_perolehan'] ?? 0,
                        'satuan'          => $ln['satuan'] ?? 'Unit',
                        'kondisi_pinjam'  => $ln['kondisi_pinjam'] ?? 'baik',
                        'kondisi_kembali' => $ln['kondisi_kembali'] ?? null,
                        'catatan'         => $ln['catatan'] ?? null,
                        'status'          => $ln['status'] ?? 'dipinjam',
                    ]];
                }
            }
            unset($ln);
        }

        return $loans;
    }

    /**
     * Mengambil satu data pinjam pakai beserta detail aset & pegawai serta array seluruh item barang yang dipinjam
     */
    public function getPinjamDetail(int $id): ?array
    {
        $hasCfg = $this->db->tableExists('cfg_inventaris_kop_surat');
        $hasKs  = $this->db->tableExists('kop_surat');

        $kopTitleExpr = "''";
        $kopImgExpr   = "''";
        if ($hasCfg && $hasKs) {
            $kopTitleExpr = "COALESCE(cik.nama_kop, ks.title)";
            $kopImgExpr   = "COALESCE(cik.image_url, ks.image_url)";
        } elseif ($hasCfg) {
            $kopTitleExpr = "cik.nama_kop";
            $kopImgExpr   = "cik.image_url";
        } elseif ($hasKs) {
            $kopTitleExpr = "ks.title";
            $kopImgExpr   = "ks.image_url";
        }

        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang,
                      i.tahun_perolehan, i.lokasi_ruangan, i.peruntukan,
                      peg.nama as pegawai_master_nama, peg.nip as pegawai_master_nip,
                      peg.jenis_pegawai as pegawai_master_jenis_pegawai,
                      ju.jabatan as pegawai_master_jabatan, ' .
                      $kopTitleExpr . ' as kop_surat_title, ' .
                      $kopImgExpr . ' as kop_surat_image_url')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'left')
            ->join('mst_pegawai peg', 'peg.id = p.pegawai_id', 'left')
            ->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left');

        if ($hasCfg) {
            $builder->join('cfg_inventaris_kop_surat cik', 'cik.id = p.kop_surat_id', 'left');
        }
        if ($hasKs) {
            $builder->join('kop_surat ks', 'ks.id = p.kop_surat_id', 'left');
        }

        $loan = $builder->where('p.id', $id)
            ->get()
            ->getRowArray();

        if (is_array($loan)) {
            $items = $this->getItemsByPinjamId($id);
            if (empty($items) && ! empty($loan['inventaris_id'])) {
                $items = [[
                    'id'              => 0,
                    'pinjam_pakai_id' => $id,
                    'inventaris_id'   => $loan['inventaris_id'],
                    'kode_barang'     => $loan['kode_barang'] ?? '',
                    'nup'             => $loan['nup'] ?? '',
                    'nama_barang'     => $loan['nama_barang'] ?? '',
                    'merk_tipe'       => $loan['merk_tipe'] ?? '',
                    'nilai_perolehan' => $loan['nilai_perolehan'] ?? 0,
                    'satuan'          => $loan['satuan'] ?? 'Unit',
                    'tahun_perolehan' => $loan['tahun_perolehan'] ?? '',
                    'kondisi_pinjam'  => $loan['kondisi_pinjam'] ?? 'baik',
                    'kondisi_kembali' => $loan['kondisi_kembali'] ?? null,
                    'catatan'         => $loan['catatan'] ?? null,
                    'status'          => $loan['status'] ?? 'dipinjam',
                ]];
            }
            $loan['items'] = $items;
        }

        return $loan;
    }

    /**
     * Mengambil daftar aset BMN yang tersedia untuk dipinjamkan (belum dialokasikan / tidak sedang dipinjam aktif)
     */
    public function getAvailableAssetsForLoan($currentInventarisIds = null): array
    {
        $excludedIds = [];

        // 1. Dari tabel child
        if ($this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $childActive = $this->db->table('trn_inventaris_pinjam_pakai_item')
                ->select('inventaris_id')
                ->where('status', 'dipinjam')
                ->get()
                ->getResultArray();
            $excludedIds = array_merge($excludedIds, array_column($childActive, 'inventaris_id'));
        }

        // 2. Dari tabel parent (fallback / backwards compatibility)
        $parentActive = $this->db->table($this->table)
            ->select('inventaris_id')
            ->where('status', 'dipinjam')
            ->get()
            ->getResultArray();
        $excludedIds = array_merge($excludedIds, array_column($parentActive, 'inventaris_id'));

        $excludedIds = array_unique(array_filter($excludedIds));

        // Jika sedang edit, jangan exclude current aset
        if (! empty($currentInventarisIds)) {
            $currentArr = is_array($currentInventarisIds) ? $currentInventarisIds : [(int) $currentInventarisIds];
            $excludedIds = array_diff($excludedIds, $currentArr);
        }

        $builder = $this->db->table('trn_inventaris_satker')
            ->select('id, kode_barang, nup, kode_register, nama_barang, merk_tipe, kondisi, peruntukan, nilai_perolehan, lokasi_ruangan');

        if (! empty($excludedIds)) {
            $builder->whereNotIn('id', array_values($excludedIds));
        }

        return $builder->orderBy('nama_barang', 'ASC')
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Menghitung statistik ringkasan pinjam pakai
     */
    /**
     * Menghitung statistik ringkasan pinjam pakai
     * Mendukung filter tahun anggaran tertentu atau agregat seluruh tahun (jika null)
     */
    public function getSummaryStats(?int $year = null): array
    {
        $db = $this->db;

        $baseBuilder = $db->table($this->table);
        if (! empty($year) && $year >= 2000 && $year <= 2100) {
            $baseBuilder->where('YEAR(tgl_pinjam)', $year);
        }

        // Total transaksi selesai, diperbaharui, dan peminjam unik
        $totalDikembalikan = (int) (clone $baseBuilder)->where('status', 'dikembalikan')->countAllResults();
        $totalDiperbaharui = (int) (clone $baseBuilder)->where('status', 'diperbaharui')->countAllResults();
        $totalPeminjamUnik = (int) (clone $baseBuilder)->where('status', 'dipinjam')->select('nama_peminjam')->distinct()->countAllResults();
        if ($totalPeminjamUnik === 0) {
            $totalPeminjamUnik = (int) (clone $baseBuilder)->select('nama_peminjam')->distinct()->countAllResults();
        }

        // Total unit aset yang sedang dipinjam & total nilainya
        $totalDipinjam = 0;
        $totalNilaiDipinjam = 0.0;

        if ($db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $builderItem = $db->table('trn_inventaris_pinjam_pakai_item itm')
                ->select('COUNT(itm.id) AS total_unit, COALESCE(SUM(i.nilai_perolehan), 0) AS total_nilai')
                ->join('trn_inventaris_satker i', 'i.id = itm.inventaris_id', 'inner')
                ->join($this->table . ' p', 'p.id = itm.pinjam_pakai_id', 'inner')
                ->where('itm.status', 'dipinjam');

            if (! empty($year) && $year >= 2000 && $year <= 2100) {
                $builderItem->where('YEAR(p.tgl_pinjam)', $year);
            }

            $rowStats = $builderItem->get()->getRowArray();

            $totalDipinjam = (int) ($rowStats['total_unit'] ?? 0);
            $totalNilaiDipinjam = (float) ($rowStats['total_nilai'] ?? 0);
        }

        // Fallback jika child table kosong
        if ($totalDipinjam === 0) {
            $builderFallback = $db->table($this->table . ' p')
                ->where('p.status', 'dipinjam');
            if (! empty($year) && $year >= 2000 && $year <= 2100) {
                $builderFallback->where('YEAR(p.tgl_pinjam)', $year);
            }
            $totalDipinjam = (int) (clone $builderFallback)->countAllResults();
            $rowNilai = $builderFallback
                ->select('COALESCE(SUM(i.nilai_perolehan), 0) AS total_nilai')
                ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'inner')
                ->get()
                ->getRowArray();
            $totalNilaiDipinjam = (float) ($rowNilai['total_nilai'] ?? 0);
        }

        return [
            'total_dipinjam'       => $totalDipinjam,
            'total_dikembalikan'   => $totalDikembalikan,
            'total_diperbaharui'   => $totalDiperbaharui,
            'total_peminjam'       => $totalPeminjamUnik,
            'total_peminjam_unik'  => $totalPeminjamUnik,
            'total_nilai_dipinjam' => $totalNilaiDipinjam,
        ];
    }

    /**
     * Generate nomor surat pinjam pakai auto increment per tahun
     * Format: PS.03.01/B/Gs7/{tahun}/{nomor auto increment 3 digit}
     * Contoh: PS.03.01/B/Gs7/2026/001
     */
    public function generateNextNoSurat(?int $tahun = null): string
    {
        $tahun = $tahun ?: (int) date('Y');
        $prefix = "PS.03.01/B/Gs7/{$tahun}/";

        $rows = $this->db->table($this->table)
            ->select('no_surat')
            ->like('no_surat', $prefix, 'after')
            ->get()
            ->getResultArray();

        $maxNum = 0;
        foreach ($rows as $row) {
            $val = trim((string) ($row['no_surat'] ?? ''));
            if (preg_match('/PS\.03\.01\/B\/Gs7\/' . $tahun . '\/(\d+)/i', $val, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Memperbaharui pinjam pakai aset BMN (Annual Renewal / Perpanjangan Awal Tahun)
     * - Membuat record transaksi baru dengan status 'dipinjam' dan nomor surat baru berjalan
     * - Menyalin seluruh child items ke transaksi baru
     * - Mengubah status transaksi lama menjadi 'diperbaharui'
     * - Memastikan status aset master di trn_inventaris_satker tetap 'Dipinjam Pakai'
     */
    public function perbaharuiPinjam(int $oldId, array $data, int $userId = 0): int
    {
        $oldLoan = $this->getPinjamDetail($oldId);
        if (! is_array($oldLoan) || ($oldLoan['status'] ?? '') !== 'dipinjam') {
            throw new \RuntimeException('Data pinjam pakai tidak ditemukan atau statusnya tidak sedang dipinjam.');
        }

        $items = ! empty($oldLoan['items']) ? $oldLoan['items'] : [];
        if (empty($items) && ! empty($oldLoan['inventaris_id'])) {
            $items = [['inventaris_id' => $oldLoan['inventaris_id'], 'kondisi_pinjam' => $oldLoan['kondisi_pinjam'] ?? 'baik']];
        }

        if (empty($items)) {
            throw new \RuntimeException('Tidak ada aset BMN pada transaksi pinjam pakai ini.');
        }

        $tglPinjamBaru = trim((string) ($data['tgl_pinjam'] ?? date('Y-m-d')));
        $tahunTarget = (int) date('Y', strtotime($tglPinjamBaru));
        $noSuratBaru = trim((string) ($data['no_surat'] ?? ''));
        if ($tahunTarget >= 2026 || empty($noSuratBaru)) {
            $noSuratBaru = $this->generateNextNoSurat($tahunTarget);
        }

        $this->db->transStart();

        $oldNoSuratText = ! empty($oldLoan['no_surat']) ? $oldLoan['no_surat'] : "ID #{$oldId}";
        $catatanBaru = trim((string) ($data['catatan'] ?? ''));
        $catatanPembaruan = "Pembaruan dari Surat " . $oldNoSuratText . ($catatanBaru !== '' ? " | " . $catatanBaru : '');

        // 1. Insert header pinjaman baru
        $newLoanData = [
            'inventaris_id'        => $items[0]['inventaris_id'],
            'renewed_from_id'      => $oldId,
            'pegawai_id'           => $oldLoan['pegawai_id'],
            'nama_peminjam'        => $oldLoan['nama_peminjam'],
            'nip_peminjam'         => $oldLoan['nip_peminjam'],
            'jabatan_peminjam'     => $oldLoan['jabatan_peminjam'],
            'kontak_peminjam'      => $oldLoan['kontak_peminjam'],
            'no_surat'             => $noSuratBaru,
            'kop_surat_id'         => ! empty($data['kop_surat_id']) ? (int) $data['kop_surat_id'] : ($oldLoan['kop_surat_id'] ?? null),
            'tgl_pinjam'           => $tglPinjamBaru,
            'tgl_kembali_rencana'  => ! empty($data['tgl_kembali_rencana']) ? trim((string) $data['tgl_kembali_rencana']) : null,
            'keperluan'            => ! empty($data['keperluan']) ? trim((string) $data['keperluan']) : $oldLoan['keperluan'],
            'kondisi_pinjam'       => ! empty($data['kondisi_pinjam']) ? trim((string) $data['kondisi_pinjam']) : ($oldLoan['kondisi_pinjam'] ?? 'baik'),
            'kelengkapan'          => isset($data['kelengkapan']) ? trim((string) $data['kelengkapan']) : ($oldLoan['kelengkapan'] ?? null),
            'catatan'              => $catatanPembaruan,
            'file_surat'           => ! empty($data['file_surat']) ? $data['file_surat'] : null,
            'status'               => 'dipinjam',
            'created_by'           => $userId ?: null,
            'updated_by'           => $userId ?: null,
        ];

        $newPinjamId = (int) $this->insert($newLoanData, true);
        if (! $newPinjamId) {
            $this->db->transRollback();
            throw new \RuntimeException('Gagal menyimpan transaksi pembaruan pinjam pakai.');
        }

        // 2. Insert items ke pinjaman baru
        $kondisiPinjamBaru = $newLoanData['kondisi_pinjam'];
        $now = date('Y-m-d H:i:s');
        $newItemRows = [];
        $inventarisIds = [];
        foreach ($items as $it) {
            $invId = (int) $it['inventaris_id'];
            $inventarisIds[] = $invId;
            $newItemRows[] = [
                'pinjam_pakai_id' => $newPinjamId,
                'inventaris_id'   => $invId,
                'kondisi_pinjam'  => $kondisiPinjamBaru,
                'kondisi_kembali' => null,
                'catatan'         => "Pembaruan dari Surat " . $oldNoSuratText,
                'status'          => 'dipinjam',
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if ($this->db->tableExists('trn_inventaris_pinjam_pakai_item') && ! empty($newItemRows)) {
            $this->db->table('trn_inventaris_pinjam_pakai_item')->insertBatch($newItemRows);
        }

        // 3. Update record lama
        $oldCatatan = (string) ($oldLoan['catatan'] ?? '');
        $updatedOldCatatan = ($oldCatatan !== '' ? $oldCatatan . " | " : '') . "Diperbaharui ke Surat No. " . $noSuratBaru . " (ID #{$newPinjamId})";
        $this->update($oldId, [
            'status'                => 'diperbaharui',
            'tgl_kembali_realisasi' => $tglPinjamBaru,
            'kondisi_kembali'       => $kondisiPinjamBaru,
            'catatan'               => $updatedOldCatatan,
            'updated_by'            => $userId ?: null,
        ]);

        // 4. Update child items di record lama menjadi 'dikembalikan'
        if ($this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $this->db->table('trn_inventaris_pinjam_pakai_item')
                ->where('pinjam_pakai_id', $oldId)
                ->update([
                    'status'          => 'dikembalikan',
                    'kondisi_kembali' => $kondisiPinjamBaru,
                    'catatan'         => "Diperbaharui ke Surat No. " . $noSuratBaru,
                    'updated_at'      => $now,
                ]);
        }

        // 5. Pastikan master inventaris satker tetap 'Dipinjam Pakai'
        if (! empty($inventarisIds)) {
            $satkerModel = new \App\Models\InventarisSatkerModel();
            $satkerModel->whereIn('id', array_unique($inventarisIds))->set([
                'status_bmn'     => 'Dipinjam Pakai',
                'lokasi_ruangan' => 'Pinjam Pakai: ' . $oldLoan['nama_peminjam'],
                'kondisi'        => $kondisiPinjamBaru,
                'updated_by'     => $userId ?: null,
            ])->update();
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Transaksi database gagal saat memperbaharui pinjam pakai.');
        }

        return $newPinjamId;
    }

    /**
     * Menghapus transaksi pinjam pakai dengan mekanisme Rollback otomatis:
     * - Jika transaksi yang dihapus adalah hasil pembaruan (renewed_from_id),
     *   maka transaksi surat sebelumnya otomatis dipulihkan ke status 'dipinjam',
     *   tgl_kembali_realisasi & kondisi_kembali dikosongkan kembali,
     *   seluruh item lama kembali ke status 'dipinjam',
     *   dan status master aset di trn_inventaris_satker tetap aman sebagai 'Dipinjam Pakai'.
     * - Jika bukan transaksi pembaruan (peminjaman biasa),
     *   status aset dikembalikan ke 'Digunakan Sendiri' (Belum berlokasi / Gudang).
     */
    public function hapusPinjamDenganRollback(int $id, int $userId = 0): array
    {
        $existing = $this->find($id);
        if (! is_array($existing)) {
            throw new \RuntimeException('Data transaksi pinjam pakai tidak ditemukan.');
        }

        $items = $this->getItemsByPinjamId($id);
        $itemIds = array_column($items, 'inventaris_id');
        if (empty($itemIds) && ! empty($existing['inventaris_id'])) {
            $itemIds = [(int) $existing['inventaris_id']];
        }
        $itemIds = array_unique(array_filter($itemIds));

        // Cari transaksi induk sebelumnya (jika ini hasil pembaruan)
        $parentLoan = null;
        $renewedFromId = ! empty($existing['renewed_from_id']) ? (int) $existing['renewed_from_id'] : null;

        if ($renewedFromId) {
            $parentLoan = $this->find($renewedFromId);
        }

        // Fallback pencarian transaksi asal via catatan jika renewed_from_id kosong
        if (! $parentLoan) {
            $parentCandidate = $this->where('status', 'diperbaharui')
                ->like('catatan', "(ID #{$id})")
                ->first();
            if (is_array($parentCandidate)) {
                $parentLoan = $parentCandidate;
                $renewedFromId = (int) $parentCandidate['id'];
            }
        }

        $isRollback = false;
        $parentNoSurat = '';

        $this->db->transStart();

        if ($parentLoan && is_array($parentLoan) && ($parentLoan['status'] ?? '') === 'diperbaharui') {
            $isRollback = true;
            $parentNoSurat = ! empty($parentLoan['no_surat']) ? $parentLoan['no_surat'] : "ID #{$parentLoan['id']}";

            // 1. Bersihkan catatan pembaruan dari transaksi lama
            $rawCatatan = (string) ($parentLoan['catatan'] ?? '');
            $cleanCatatan = preg_replace('/\s*\|\s*Diperbaharui ke Surat.*$/i', '', $rawCatatan);
            $cleanCatatan = preg_replace('/Diperbaharui ke Surat.*$/i', '', (string) $cleanCatatan);
            $cleanCatatan = trim((string) $cleanCatatan);

            // 2. Pulihkan transaksi induk ke 'dipinjam'
            $this->update($renewedFromId, [
                'status'                => 'dipinjam',
                'tgl_kembali_realisasi' => null,
                'kondisi_kembali'       => null,
                'catatan'               => $cleanCatatan !== '' ? $cleanCatatan : null,
                'updated_by'            => $userId ?: null,
            ]);

            // 3. Pulihkan status child items transaksi lama ke 'dipinjam'
            if ($this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
                $this->db->table('trn_inventaris_pinjam_pakai_item')
                    ->where('pinjam_pakai_id', $renewedFromId)
                    ->update([
                        'status'          => 'dipinjam',
                        'kondisi_kembali' => null,
                        'catatan'         => null,
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
            }

            // 4. Pastikan master aset BMN tetap 'Dipinjam Pakai' atas nama peminjam lama
            if (! empty($itemIds)) {
                $satkerModel = new \App\Models\InventarisSatkerModel();
                $satkerModel->whereIn('id', $itemIds)->set([
                    'status_bmn'     => 'Dipinjam Pakai',
                    'lokasi_ruangan' => 'Pinjam Pakai: ' . $parentLoan['nama_peminjam'],
                    'kondisi'        => $parentLoan['kondisi_pinjam'] ?? 'baik',
                    'updated_by'     => $userId ?: null,
                ])->update();
            }
        } else {
            // Bukan pembaruan: jika sedang dipinjam, kembalikan aset fisik ke Gudang
            if ($existing['status'] === 'dipinjam' && ! empty($itemIds)) {
                $satkerModel = new \App\Models\InventarisSatkerModel();
                $satkerModel->whereIn('id', $itemIds)->set([
                    'status_bmn'     => 'Digunakan Sendiri',
                    'lokasi_ruangan' => 'Belum berlokasi',
                    'updated_by'     => $userId ?: null,
                ])->update();
            }
        }

        // Hapus file fisik jika ada
        if (! empty($existing['file_surat']) && file_exists(FCPATH . $existing['file_surat'])) {
            @unlink(FCPATH . $existing['file_surat']);
        }

        // Hapus child items dari transaksi yang dihapus
        if ($this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $this->db->table('trn_inventaris_pinjam_pakai_item')->where('pinjam_pakai_id', $id)->delete();
        }

        // Hapus transaksi
        $this->delete($id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Gagal menghapus transaksi pinjam pakai.');
        }

        return [
            'is_rollback'     => $isRollback,
            'parent_no_surat' => $parentNoSurat,
            'parent_id'       => $renewedFromId,
        ];
    }
}

