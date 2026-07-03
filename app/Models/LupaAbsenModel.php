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
        'tanggal_absen',
        'jenis_absen',
        'alasan_detail',
        'nomor_surat',
        'alasan_kategori',
        'entries_json',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'created_at',
        'updated_at',
        'kop_surat_id',
    ];
    protected $useTimestamps = false;
}
