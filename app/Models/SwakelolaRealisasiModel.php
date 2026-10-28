<?php

namespace App\Models;

use CodeIgniter\Model;

class SwakelolaRealisasiModel extends Model
{
    protected $table            = 'trn_swakelola_realisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'paket_id',
        'kode_akun',
        'uraian_belanja',
        'nomor_spm',
        'tanggal_spm',
        'nomor_sp2d',
        'jumlah_bayar',
        'file_bukti',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByPaketId(int $paketId): array
    {
        return $this->where('paket_id', $paketId)
                    ->orderBy('tanggal_spm', 'ASC')
                    ->findAll();
    }
}

