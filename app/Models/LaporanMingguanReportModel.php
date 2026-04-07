<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanMingguanReportModel extends Model
{
    protected $table         = 'laporan_mingguan_reports';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id', 'sekolah_id', 'period_start', 'period_end', 'description',
        'file_path', 'file_name', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;
}
