<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisSatkerModel extends Model
{
    protected $table            = 'trn_inventaris_satker';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'kode_barang',
        'nup',
        'kode_register',
        'nama_barang',
        'kategori',
        'merk',
        'tipe',
        'merk_tipe',
        'jumlah',
        'satuan',
        'kondisi',
        'lokasi_ruangan',
        'ruangan_id',
        'nilai_perolehan',
        'nilai_buku',
        'status_bmn',
        'no_psp',
        'tahun_perolehan',
        'tgl_perolehan',
        'keterangan',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil daftar barang dalam ruangan yang diagregasi per jenis barang/merk untuk DBR.
     */
    public function getBarangByRuanganAgregat(int $ruanganId): array
    {
        return $this->db->table($this->table)
            ->select('kode_barang, nama_barang, merk_tipe, satuan, kondisi,
                      MIN(tahun_perolehan) AS tahun_perolehan,
                      MIN(nup) AS min_nup,
                      MAX(nup) AS max_nup,
                      COALESCE(SUM(jumlah), 0) AS total_jumlah,
                      COUNT(id) AS total_nup,
                      GROUP_CONCAT(nup ORDER BY CAST(NULLIF(nup, "") AS UNSIGNED) ASC SEPARATOR ", ") AS daftar_nup,
                      SUM(nilai_perolehan) AS total_nilai')
            ->where('ruangan_id', $ruanganId)
            ->groupBy(['kode_barang', 'nama_barang', 'merk_tipe', 'satuan', 'kondisi'])
            ->orderBy('nama_barang', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil ringkasan statistik aset kantor satker.
     */
    public function getSummaryStats(): array
    {
        $builder = $this->db->table($this->table);

        $totalBarang = (int) $this->countAll();
        $kondisiBaik = (int) $this->where('kondisi', 'Baik')->countAllResults(true);
        $rusakRingan = (int) $this->where('kondisi', 'Rusak Ringan')->countAllResults(true);
        $rusakBerat  = (int) $this->where('kondisi', 'Rusak Berat')->countAllResults(true);

        $terdistribusi = (int) $this->where('ruangan_id IS NOT NULL', null, false)
            ->where('ruangan_id >', 0)
            ->countAllResults(true);

        $belumTerdistribusi = (int) $this->groupStart()
                ->where('ruangan_id IS NULL', null, false)
                ->orWhere('ruangan_id', 0)
            ->groupEnd()
            ->countAllResults(true);

        return [
            'total_barang'       => $totalBarang,
            'kondisi_baik'       => $kondisiBaik,
            'rusak_ringan'       => $rusakRingan,
            'rusak_berat'        => $rusakBerat,
            'terdistribusi'      => $terdistribusi,
            'belum_terdistribusi'=> $belumTerdistribusi,
        ];
    }
}
