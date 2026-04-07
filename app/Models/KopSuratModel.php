<?php

namespace App\Models;

use CodeIgniter\Model;

class KopSuratModel extends Model
{
    protected $table         = 'kop_surat';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title',
        'year_start',
        'year_end',
        'image_url',
        'description',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
