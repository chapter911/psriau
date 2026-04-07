<?php

namespace App\Models;

use CodeIgniter\Model;

class HomeSettingModel extends Model
{
    protected $table         = 'home_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'hero_title',
        'hero_subtitle',
        'about_intro',
        'official_name',
        'logo_url',
        'contact_email',
        'contact_phone',
        'contact_address',
        'contact_map_url',
        'instagram_profile_url',
        'default_event_image',
        'default_article_image',
        'updated_at',
        'updated_by',
    ];
    protected $useTimestamps = false;
}
