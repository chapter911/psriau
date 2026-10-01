<?php

namespace App\Models;

use CodeIgniter\Model;

class KompuSosmedModel extends Model
{
    protected $table            = 'trn_kompu_sosmed';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'nama_sosmed',
        'kategori',
        'username',
        'email_login',
        'password',
        'url_profil',
        'metode_login',
        'keterangan',
        'icon',
        'ordering',
        'is_active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
