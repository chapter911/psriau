<?php

namespace App\Models;

use CodeIgniter\Model;

class MstKecamatanModel extends Model
{
    protected $table         = 'mst_kecamatan';
    protected $primaryKey    = 'kode_kecamatan';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode_provinsi', 'kode_kabupaten', 'kode_kecamatan', 'nama_kecamatan', 'kategori_konflik'];
    protected $useTimestamps = false;
}
