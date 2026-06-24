<?php

namespace App\Models;

use CodeIgniter\Model;

class LupaAbsenModel extends Model
{
    protected $table = 'lupa_absen';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nama',
        'nip',
        'jabatan_id',
        'jabatan',
        'unit_kerja',
        'tanggal_surat',
        'nomor_surat',
        'alasan_kategori',
        'alasan_detail',
        'entries_json',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;
}
