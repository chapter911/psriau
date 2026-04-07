<?php

namespace App\Models;

use CodeIgniter\Model;

class HomeSlideModel extends Model
{
    protected $table         = 'home_slides';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['title', 'image_url', 'sort_order', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
