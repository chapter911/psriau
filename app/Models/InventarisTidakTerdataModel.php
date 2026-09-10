<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisTidakTerdataModel extends Model
{
    protected $table            = 'trn_inventaris_tidak_terdata';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_barang',
        'jumlah',
        'satuan',
        'merk_tipe',
        'tahun_perolehan',
        'ruangan_id',
        'lokasi_penempatan',
        'kondisi',
        'keterangan',
        'petugas_nama',
        'petugas_nip',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua data aset tidak terdata beserta detail nama ruangan jika terhubung
     */
    public function getWithRelations(array $filters = []): array
    {
        $builder = $this->db->table($this->table . ' t')
            ->select('t.*, r.kode_ruangan, r.nama_ruangan, r.penanggung_jawab_nama, r.lokasi_lantai')
            ->join('mst_ruangan r', 'r.id = t.ruangan_id', 'left');

        if (! empty($filters['ruangan_id'])) {
            $builder->where('t.ruangan_id', (int) $filters['ruangan_id']);
        }

        if (! empty($filters['kondisi'])) {
            $builder->where('t.kondisi', $filters['kondisi']);
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $builder->groupStart()
                ->like('t.nama_barang', $q)
                ->orLike('t.merk_tipe', $q)
                ->orLike('t.lokasi_penempatan', $q)
                ->orLike('t.tahun_perolehan', $q)
                ->orLike('r.nama_ruangan', $q)
                ->groupEnd();
        }

        return $builder->orderBy('t.id', 'ASC')->get()->getResultArray();
    }

    /**
     * Hitung ringkasan statistik
     */
    public function getSummaryStats(): array
    {
        $row = $this->db->table($this->table)
            ->select('
                COUNT(id) AS total_item,
                COALESCE(SUM(jumlah), 0) AS total_buah,
                SUM(CASE WHEN kondisi = "baik" THEN jumlah ELSE 0 END) AS total_baik,
                SUM(CASE WHEN kondisi != "baik" THEN jumlah ELSE 0 END) AS total_rusak
            ')
            ->get()
            ->getRowArray();

        return [
            'total_item'  => (int) ($row['total_item'] ?? 0),
            'total_buah'  => (int) ($row['total_buah'] ?? 0),
            'total_baik'  => (int) ($row['total_baik'] ?? 0),
            'total_rusak' => (int) ($row['total_rusak'] ?? 0),
        ];
    }
}
