<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanLapanganPekerjaanModel extends Model
{
    protected $table            = 'laporan_lapangan_pekerjaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'rab_detail_id',
        'tanggal',
        'status_selesai',
        'progres_persen',
        'keterangan_progres',
        'kendala',
        'foto_paths_json',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
