<?php

namespace App\Models;

use CodeIgniter\Model;

class MstBiayaTransportasiModel extends Model
{
    protected $table            = 'mst_biaya_transportasi';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'kode_provinsi',
        'kode_kabupaten',
        'asal',
        'tujuan',
        'besaran',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps    = false;
}
