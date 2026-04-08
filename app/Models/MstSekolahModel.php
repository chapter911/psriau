<?php

namespace App\Models;

use CodeIgniter\Model;

class MstSekolahModel extends Model
{
    protected $table         = 'mst_sekolah';
    protected $primaryKey    = 'npsn';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'npsn', 'nama', 'jenis', 'nsm', 'kabupaten', 'kecamatan',
        'latitude', 'longitude', 'created_by', 'created_date', 'updated_by', 'updated_date',
    ];
    protected $useTimestamps = false;
}
