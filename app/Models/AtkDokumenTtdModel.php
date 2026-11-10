<?php

namespace App\Models;

use CodeIgniter\Model;

class AtkDokumenTtdModel extends Model
{
    protected $table            = 'trn_atk_dokumen_ttd';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pengadaan_id',
        'jenis_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'catatan',
        'status_verifikasi',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'uploaded_by',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

