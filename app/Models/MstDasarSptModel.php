<?php

namespace App\Models;

use CodeIgniter\Model;

class MstDasarSptModel extends Model
{
    protected $table = 'mst_dasar_spt';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'uraian', 'created_by', 'created_date', 'updated_by', 'updated_date'
    ];
    protected $useTimestamps = false;
}
