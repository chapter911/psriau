<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginHistoryModel extends Model
{
    protected $table = 'login_histories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'attempted_at',
        'is_success',
        'failure_reason',
        'username_input',
        'user_id',
        'full_name',
        'role',
        'account_active',
        'ip_address',
        'user_agent',
        'http_method',
        'request_path',
        'referer',
        'session_id',
        'request_payload_json',
        'server_context_json',
        'created_at',
    ];
}
