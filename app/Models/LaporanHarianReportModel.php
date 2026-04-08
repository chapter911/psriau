<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanHarianReportModel extends Model
{
    protected $table         = 'laporan_harian_reports';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id', 'sekolah_id', 'report_date', 'sections_json',
        'personil_pekerja', 'personil_tukang', 'cuaca_cerah', 'cuaca_hujan',
        'latitude', 'longitude', 'input_device',
        'photo_paths_json', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;
}
