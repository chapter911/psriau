<?php

namespace App\Models;

use CodeIgniter\Model;

class AppSettingModel extends Model
{
    protected $table         = 'app_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'app_name',
        'primary_color',
        'sidebar_bg_color',
        'sidebar_text_color',
        'sidebar_active_bg_color',
        'sidebar_active_text_color',
        'app_logo_url',
        'login_background_url',
        'auto_logout_minutes',
        'preloader_duration_ms',
        'updated_at',
        'updated_by',
    ];
    protected $useTimestamps = false;
}
