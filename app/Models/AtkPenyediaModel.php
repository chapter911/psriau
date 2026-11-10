<?php

namespace App\Models;

use CodeIgniter\Model;

class AtkPenyediaModel extends Model
{
    protected $table            = 'mst_atk_penyedia';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_perusahaan',
        'alamat',
        'kota',
        'telepon',
        'email',
        'nama_direktur',
        'jabatan_pimpinan',
        'npwp',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

