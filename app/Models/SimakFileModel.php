<?php

namespace App\Models;

use CodeIgniter\Model;

class SimakFileModel extends Model
{
    protected $table = 'trn_simak_file';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'simak_id',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
        'uploaded_date',
    ];
    protected $useTimestamps = false;
}
