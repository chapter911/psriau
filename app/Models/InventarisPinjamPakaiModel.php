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
     * Mengambil seluruh data pinjam pakai beserta relasi aset BMN dan pegawai
     */
    public function getPinjamWithRelations(?string $statusFilter = null): array
    {
        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang,
                      i.lokasi_ruangan, i.peruntukan,
                      peg.nama as pegawai_master_nama, peg.nip as pegawai_master_nip,
                      ju.jabatan as pegawai_master_jabatan')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'left')
            ->join('mst_pegawai peg', 'peg.id = p.pegawai_id', 'left')
            ->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left');

        if ($statusFilter && in_array($statusFilter, ['dipinjam', 'dikembalikan'], true)) {
            $builder->where('p.status', $statusFilter);
        }

        return $builder->orderBy("CASE WHEN p.status = 'dipinjam' THEN 0 ELSE 1 END", 'ASC', false)
            ->orderBy('p.tgl_pinjam', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil satu data pinjam pakai beserta detail aset & pegawai
     */
    public function getPinjamDetail(int $id): ?array
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, 
                      i.kode_barang, i.nup, i.kode_register, i.nama_barang, i.kategori, 
                      i.merk_tipe, i.nilai_perolehan, i.satuan, i.kondisi as kondisi_aset_sekarang,
                      i.tahun_perolehan, i.lokasi_ruangan, i.peruntukan,
                      peg.nama as pegawai_master_nama, peg.nip as pegawai_master_nip,
                      ju.jabatan as pegawai_master_jabatan')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'left')
            ->join('mst_pegawai peg', 'peg.id = p.pegawai_id', 'left')
            ->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Mengambil daftar aset BMN yang tersedia untuk dipinjamkan (belum dialokasikan / tidak sedang dipinjam aktif)
     */
    public function getAvailableAssetsForLoan(?int $currentInventarisId = null): array
    {
        // ID aset yang sedang dipinjam aktif
        $activeLoanAssetIds = $this->db->table($this->table)
            ->select('inventaris_id')
            ->where('status', 'dipinjam')
            ->get()
            ->getResultArray();

        $excludedIds = array_filter(array_column($activeLoanAssetIds, 'inventaris_id'));

        // Jika sedang edit, kecualikan current aset agar tetap muncul
        if ($currentInventarisId !== null && ($key = array_search($currentInventarisId, $excludedIds, true)) !== false) {
            unset($excludedIds[$key]);
        }

        $builder = $this->db->table('trn_inventaris_satker')
            ->select('id, kode_barang, nup, kode_register, nama_barang, merk_tipe, kondisi, peruntukan, nilai_perolehan, lokasi_ruangan');

        if (! empty($excludedIds)) {
            $builder->whereNotIn('id', $excludedIds);
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

        $totalDipinjam = (int) $db->table($this->table)->where('status', 'dipinjam')->countAllResults();
        $totalDikembalikan = (int) $db->table($this->table)->where('status', 'dikembalikan')->countAllResults();
        $totalPeminjamUnik = (int) $db->table($this->table)->select('nama_peminjam')->distinct()->countAllResults();

        $rowNilai = $db->table($this->table . ' p')
            ->select('COALESCE(SUM(i.nilai_perolehan), 0) AS total_nilai')
            ->join('trn_inventaris_satker i', 'i.id = p.inventaris_id', 'inner')
            ->where('p.status', 'dipinjam')
            ->get()
            ->getRowArray();

        $totalNilaiDipinjam = (float) ($rowNilai['total_nilai'] ?? 0);

        return [
            'total_dipinjam'       => $totalDipinjam,
            'total_dikembalikan'   => $totalDikembalikan,
            'total_peminjam_unik'  => $totalPeminjamUnik,
            'total_nilai_dipinjam' => $totalNilaiDipinjam,
        ];
    }
}
