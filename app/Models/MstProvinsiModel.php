<?php

namespace App\Models;

use CodeIgniter\Model;

class MstProvinsiModel extends Model
{
    protected $table         = 'mst_provinsi';
    protected $primaryKey    = 'kode_provinsi';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode_provinsi', 'nama_provinsi'];
    protected $useTimestamps = false;
}
