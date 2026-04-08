<?php

namespace App\Models;

use CodeIgniter\Model;

class MstKabupatenModel extends Model
{
    protected $table         = 'mst_kabupaten';
    protected $primaryKey    = 'kode_kabupaten';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode_provinsi', 'kode_kabupaten', 'nama_kabupaten'];
    protected $useTimestamps = false;
}
