<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanLapanganProyekModel extends Model
{
    protected $table            = 'laporan_lapangan_proyek';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'paket_id',
        'sekolah_npsn',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'cuaca_json',
        'pengawas',
        'pelaksana',
        'mandor',
        'tukang',
        'pekerja',
        'nama_pelapor',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
