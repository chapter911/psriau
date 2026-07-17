<?php

namespace App\Models;

use CodeIgniter\Model;

class MstBiayaPenginapanModel extends Model
{
    protected $table            = 'mst_biaya_penginapan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'provinsi_kode',
        'berlaku_mulai',
        'berlaku_hingga',
        'is_active',
        'satuan',
        'tarif_eselon1',
        'tarif_eselon2',
        'tarif_eselon3',
        'tarif_eselon4',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps = false;
}
