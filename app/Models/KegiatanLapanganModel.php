<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanLapanganModel extends Model
{
    protected $table         = 'kegiatan_lapangan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title',
        'activity_date',
        'location',
        'created_by',
        'created_by_user_id',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}