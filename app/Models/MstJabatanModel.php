<?php

namespace App\Models;

use CodeIgniter\Model;

class MstJabatanModel extends Model
{
    protected $table         = 'mst_jabatan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'jabatan',
        'jenis_jabatan',
        'deskripsi_jabatan',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps = false;
}