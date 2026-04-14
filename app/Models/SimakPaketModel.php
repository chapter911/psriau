<?php

namespace App\Models;

use CodeIgniter\Model;

class SimakPaketModel extends Model
{
    protected $table         = 'simak_paket';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'nama_paket',
        'tahun_anggaran',
        'penyedia',
        'nomor_kontrak',
        'nilai_kontrak',
        'add_kontrak',
        'tahapan_pekerjaan',
        'tanggal_pemeriksaan',
        'satker',
        'ppk',
        'nip',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps = false;
    protected $createdField  = 'created_date';
    protected $updatedField  = 'updated_date';
}
