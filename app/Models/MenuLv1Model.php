<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuLv1Model extends Model
{
    protected $table         = 'menu_lv1';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['id', 'label', 'link', 'icon', 'old_icon', 'ordering'];
    protected $useTimestamps = false;
}
