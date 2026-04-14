<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuAksesModel extends Model
{
    protected $table         = 'menu_akses';
    protected $primaryKey    = '';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'role_id',
        'menu_id',
        'FiturAdd',
        'FiturEdit',
        'FiturDelete',
        'FiturExport',
        'FiturImport',
        'FiturApproval',
    ];
    protected $useTimestamps = false;
}
