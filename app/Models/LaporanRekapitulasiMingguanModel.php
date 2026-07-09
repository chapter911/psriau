<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanRekapitulasiMingguanModel extends Model
{
    protected $table            = 'laporan_rekapitulasi_mingguan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'paket_id',
        'minggu_ke',
        'judul',
        'file_path',
        'file_name',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
