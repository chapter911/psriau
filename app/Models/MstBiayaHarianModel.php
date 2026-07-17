<?php

namespace App\Models;

use CodeIgniter\Model;

class MstBiayaHarianModel extends Model
{
    protected $table            = 'mst_biaya_harian';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'provinsi_kode',
        'berlaku_mulai',
        'berlaku_hingga',
        'is_active',
        'satuan',
        'luar_kota',
        'dalam_kota',
        'diklat',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps = false;
}
