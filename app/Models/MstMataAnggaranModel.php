<?php

namespace App\Models;

use CodeIgniter\Model;

class MstMataAnggaranModel extends Model
{
    protected $table            = 'mst_mata_anggaran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'mata_anggaran',
        'berlaku_dari',
        'berlaku_hingga',
        'status',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $useTimestamps = false;
}
