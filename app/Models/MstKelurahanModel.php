<?php

namespace App\Models;

use CodeIgniter\Model;

class MstKelurahanModel extends Model
{
    protected $table         = 'mst_kelurahan';
    protected $primaryKey    = 'kode_kelurahan';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode_provinsi', 'kode_kabupaten', 'kode_kecamatan', 'kode_kelurahan', 'nama_kelurahan', 'kategori_konflik'];
    protected $useTimestamps = false;
}
