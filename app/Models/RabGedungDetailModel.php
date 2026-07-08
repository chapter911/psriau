<?php

namespace App\Models;

use CodeIgniter\Model;

class RabGedungDetailModel extends Model
{
    protected $table            = 'trn_rab_gedung_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sekolah_npsn',
        'nama_sekolah',
        'pekerjaan_utama',
        'gedung',
        'kategori_1',
        'kategori_2',
        'no_urut',
        'uraian',
        'satuan',
        'kontrak_volume',
        'kontrak_harga_satuan',
        'kontrak_jumlah_harga',
        'tambah_volume',
        'tambah_jumlah_harga',
        'kurang_volume',
        'kurang_jumlah_harga',
        'mc_nol_volume',
        'mc_nol_jumlah_harga',
        'bobot_persen',
        'prestasi_persen',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
