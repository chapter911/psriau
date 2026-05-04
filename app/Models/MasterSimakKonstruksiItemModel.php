<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterSimakKonstruksiItemModel extends Model
{
    protected $table = 'mst_simak_konstruksi_item';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'parent_id',
        'row_no',
        'display_no',
        'uraian',
        'row_kind',
        'has_question',
        'ordering',
        'is_active',
        'is_hidden_share',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
