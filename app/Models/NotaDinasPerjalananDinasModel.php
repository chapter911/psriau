<?php

namespace App\Models;

use CodeIgniter\Model;

class NotaDinasPerjalananDinasModel extends Model
{
    protected $table            = 'nota_dinas_perjalanan_dinas';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nomor_nota_dinas',
        'kepada_yth',
        'dari',
        'hal',
        'tanggal_nota_dinas',
        'isi_nota_dinas',
        'pelaksana_json',
        'periode_mulai',
        'periode_selesai',
        'tujuan',
        'ppk_pegawai_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}

