<?php

namespace App\Models;

use CodeIgniter\Model;

class DisposisiPerjalananDinasModel extends Model
{
    protected $table            = 'disposisi_perjalanan_dinas';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'pelaksana_json',
        'periode_mulai',
        'periode_selesai',
        'kota_tujuan',
        'tujuan',
        'transportasi',
        'perihal',
        'menyetujui_pegawai_id',
        'diketahui_pegawai_id',
        'status',
        'approval_token',
        'catatan_penolakan',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false; // We will handle created_at / updated_at manually to match other controllers
}
