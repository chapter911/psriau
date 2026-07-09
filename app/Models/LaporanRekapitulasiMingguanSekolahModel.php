<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanRekapitulasiMingguanSekolahModel extends Model
{
    protected $table            = 'laporan_rekapitulasi_mingguan_sekolah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'rekapitulasi_mingguan_id',
        'no_urut',
        'nama_sekolah',
        'jumlah_harga',
        'bobot',
        'progres_minggu_lalu',
        'progres_minggu_ini',
        'progres_sampai_minggu_ini',
        'rencana',
        'deviasi',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
