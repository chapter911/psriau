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
        'kode_provinsi',
        'nama_provinsi',
        'level_pejabat',
        'tarif',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps    = false;
}
