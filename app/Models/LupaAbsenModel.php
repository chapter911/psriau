<?php

namespace App\Models;

use CodeIgniter\Model;

class LupaAbsenModel extends Model
{
    protected $table = 'lupa_absen';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nip',
        'nama',
        'tanggal_absen',
        'jenis_absen',
        'jam_absen',
        'keterangan',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;
}
