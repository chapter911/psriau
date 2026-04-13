<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanLapanganShareModel extends Model
{
    protected $table         = 'kegiatan_lapangan_shares';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'activity_id',
        'share_token',
        'expires_at',
        'created_by_user_id',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
