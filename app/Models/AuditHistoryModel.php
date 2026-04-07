<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditHistoryModel extends Model
{
    protected $table = 'audit_histories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'action_type',
        'module_path',
        'table_name',
        'record_id',
        'user_id',
        'username',
        'role',
        'ip_address',
        'user_agent',
        'request_data_json',
        'before_data_json',
        'after_data_json',
        'happened_at',
        'created_at',
    ];
}
