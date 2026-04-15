<?php

namespace App\Models;

use CodeIgniter\Model;

class MstPegawaiModel extends Model
{
    protected $table         = 'mst_pegawai';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'nip',
        'nama',
        'jenis_pegawai',
        'foto',
        'jabatan_utama_id',
        'jabatan_perbendaharaan_id',
        'eselon',
        'golongan',
        'masa_kerja',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
    protected $useTimestamps = false;
}