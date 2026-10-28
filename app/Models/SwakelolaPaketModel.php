<?php

namespace App\Models;

use CodeIgniter\Model;

class SwakelolaPaketModel extends Model
{
    protected $table            = 'trn_swakelola_paket';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_paket',
        'nama_kegiatan',
        'tahun_anggaran',
        'pagu_anggaran',
        'realisasi_anggaran',
        'tipe_swakelola',
        'ppk_nama',
        'ppk_nip',
        'kasatker_nama',
        'kasatker_nip',
        'lokasi',
        'jangka_waktu',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

