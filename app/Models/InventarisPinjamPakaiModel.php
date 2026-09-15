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
     * Mengambil seluruh data pinjam pakai beserta relasi aset BMN, pegawai, dan child items
     */
    public function getPinjamWithRelations(?string $statusFilter = null): array
    {
        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang,
                      i.lokasi_ruangan, i.peruntukan,
                      peg.nama as pegawai_master_nama, peg.nip as pegawai_master_nip,
                      ju.jabatan as pegawai_master_jabatan,
                      ks.title as kop_surat_title')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'left')
            ->join('mst_pegawai peg', 'peg.id = p.pegawai_id', 'left')
            ->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left')
            ->join('kop_surat ks', 'ks.id = p.kop_surat_id', 'left');

        if ($statusFilter && in_array($statusFilter, ['dipinjam', 'dikembalikan', 'diperbaharui'], true)) {
            $builder->where('p.status', $statusFilter);
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
        $loan = $this->db->table($this->table . ' p')
            ->select('p.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang,
                      i.tahun_perolehan, i.lokasi_ruangan, i.peruntukan,
                      peg.nama as pegawai_master_nama, peg.nip as pegawai_master_nip,
                      peg.jenis_pegawai as pegawai_master_jenis_pegawai,
                      ju.jabatan as pegawai_master_jabatan,
                      ks.title as kop_surat_title, ks.image_url as kop_surat_image_url')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'left')
            ->join('mst_pegawai peg', 'peg.id = p.pegawai_id', 'left')
            ->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left')
            ->join('kop_surat ks', 'ks.id = p.kop_surat_id', 'left')
            ->where('p.id', $id)
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
    public function getSummaryStats(): array
    {
        $db = $this->db;

        // Total transaksi selesai, diperbaharui, dan peminjam unik
        $totalDikembalikan = (int) $db->table($this->table)->where('status', 'dikembalikan')->countAllResults();
        $totalDiperbaharui = (int) $db->table($this->table)->where('status', 'diperbaharui')->countAllResults();
        $totalPeminjamUnik = (int) $db->table($this->table)->where('status', 'dipinjam')->select('nama_peminjam')->distinct()->countAllResults();
        if ($totalPeminjamUnik === 0) {
            $totalPeminjamUnik = (int) $db->table($this->table)->select('nama_peminjam')->distinct()->countAllResults();
        }

        // Total unit aset yang sedang dipinjam & total nilainya
        $totalDipinjam = 0;
        $totalNilaiDipinjam = 0.0;

        if ($db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $rowStats = $db->table('trn_inventaris_pinjam_pakai_item itm')
                ->select('COUNT(itm.id) AS total_unit, COALESCE(SUM(i.nilai_perolehan), 0) AS total_nilai')
                ->join('trn_inventaris_satker i', 'i.id = itm.inventaris_id', 'inner')
                ->where('itm.status', 'dipinjam')
                ->get()
                ->getRowArray();

            $totalDipinjam = (int) ($rowStats['total_unit'] ?? 0);
            $totalNilaiDipinjam = (float) ($rowStats['total_nilai'] ?? 0);
        }

        // Fallback jika child table kosong
        if ($totalDipinjam === 0) {
            $totalDipinjam = (int) $db->table($this->table)->where('status', 'dipinjam')->countAllResults();
            $rowNilai = $db->table($this->table . ' p')
                ->select('COALESCE(SUM(i.nilai_perolehan), 0) AS total_nilai')
                ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'inner')
                ->where('p.status', 'dipinjam')
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
}

