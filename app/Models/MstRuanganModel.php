<?php

namespace App\Models;

use CodeIgniter\Model;

class MstRuanganModel extends Model
{
    protected $table            = 'mst_ruangan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'kode_ruangan',
        'nama_ruangan',
        'pegawai_id',
        'penanggung_jawab_nama',
        'penanggung_jawab_nip',
        'lokasi_lantai',
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
     * Mengambil seluruh ruangan beserta jumlah aset dan unit barang di dalamnya.
     */
    public function getRuanganWithStats(): array
    {
        return $this->db->table($this->table . ' r')
            ->select('r.*, COUNT(s.id) AS total_aset, COALESCE(SUM(s.jumlah), 0) AS total_unit')
            ->join('trn_inventaris_satker s', 's.ruangan_id = r.id', 'left')
            ->groupBy('r.id')
            ->orderBy('r.kode_ruangan', 'ASC')
            ->orderBy('r.nama_ruangan', 'ASC')
            ->get()
            ->getResultArray();
    }
}
