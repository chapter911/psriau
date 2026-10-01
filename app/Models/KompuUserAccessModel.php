<?php

namespace App\Models;

use CodeIgniter\Model;

class KompuUserAccessModel extends Model
{
    protected $table            = 'cfg_kompu_user_access';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'user_id',
        'can_access',
        'can_view_log',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
