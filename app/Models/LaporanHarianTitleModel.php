<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanHarianTitleModel extends Model
{
    protected $table         = 'laporan_sekolah';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['id', 'name', 'ordering', 'is_active'];
    protected $useTimestamps = false;
}
