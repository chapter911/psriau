<?php

namespace App\Models;

use CodeIgniter\Model;

class SwakelolaDokumenModel extends Model
{
    protected $table            = 'trn_swakelola_dokumen';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'paket_id',
        'tahap',
        'nomor_urut',
        'nama_dokumen',
        'status',
        'file_name',
        'file_path',
        'keterangan',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByPaketId(int $paketId): array
    {
        return $this->where('paket_id', $paketId)
                    ->orderBy('nomor_urut', 'ASC')
                    ->findAll();
    }
}

