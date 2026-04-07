<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuLv2Model extends Model
{
    protected $table         = 'menu_lv2';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['id', 'label', 'link', 'icon', 'header', 'ordering'];
    protected $useTimestamps = false;
}
