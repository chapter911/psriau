<?php

namespace App\Models;

use CodeIgniter\Model;

class KontrakPekerjaanBaphpModel extends Model
{
    protected $table = 'trn_kontrak_ki_pekerjaan_baphp';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'id_kontrak_paket',
        'pekerjaan',
        'is_all_personel',
        'create_time',
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];
}
