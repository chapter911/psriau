<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanLapanganPhotoModel extends Model
{
    protected $table         = 'kegiatan_lapangan_photos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'activity_id',
        'photo_path',
        'photo_name',
        'sort_order',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}