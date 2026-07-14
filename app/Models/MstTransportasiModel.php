<?php

namespace App\Models;

use CodeIgniter\Model;

class MstTransportasiModel extends Model
{
    protected $table            = 'mst_transportasi';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nama_transportasi',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps    = false;
}
