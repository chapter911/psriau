<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuLv3Model extends Model
{
    protected $table         = 'menu_lv3';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['id', 'label', 'icon', 'link', 'header', 'ordering'];
    protected $useTimestamps = false;
}
