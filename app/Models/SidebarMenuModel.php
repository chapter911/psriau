<?php

namespace App\Models;

use CodeIgniter\Model;

class SidebarMenuModel extends Model
{
    protected $table         = 'sidebar_menus';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'parent_id',
        'label',
        'url',
        'icon',
        'active_pattern',
        'sort_order',
        'is_active',
    ];
    protected $useTimestamps = true;
}
