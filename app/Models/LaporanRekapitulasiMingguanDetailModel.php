<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanRekapitulasiMingguanDetailModel extends Model
{
    protected $table            = 'laporan_rekapitulasi_mingguan_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'rekapitulasi_mingguan_id',
        'nama_sekolah',
        'no_urut',
        'uraian',
        'volume',
        'satuan',
        'harga_satuan',
        'jumlah_harga',
        'bobot',
        'progres_minggu_lalu_vol',
        'progres_minggu_lalu_bobot',
        'progres_minggu_ini_vol',
        'progres_minggu_ini_bobot',
        'progres_sampai_minggu_ini_vol',
        'progres_sampai_minggu_ini_bobot',
        'progres_pekerjaan_persen',
        'deviasi_progres',
        'sisa_progres',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
