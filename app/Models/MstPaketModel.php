<?php

namespace App\Models;

use CodeIgniter\Model;

class MstPaketModel extends Model
{
    protected $table         = 'mst_paket';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'nama_paket',
        'singkatan_paket',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps = false;
}
