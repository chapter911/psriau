<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeUtilityMenuLinks extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $builder = $this->db->table('menu_lv2');

        // Normalize old mixed-case links to lowercase admin routes.
        $builder->where('LOWER(link)', 'utility/user')->update(['link' => 'admin/utility/user']);
        $builder->where('LOWER(link)', 'admin/utility/user')->update(['link' => 'admin/utility/user']);

        $builder->where('LOWER(link)', 'utility/user-group')->update(['link' => 'admin/utility/user-group']);
        $builder->where('LOWER(link)', 'admin/utility/user-group')->update(['link' => 'admin/utility/user-group']);
    }

    public function down()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $builder = $this->db->table('menu_lv2');
        $builder->where('link', 'admin/utility/user')->update(['link' => 'utility/user']);
        $builder->where('link', 'admin/utility/user-group')->update(['link' => 'utility/user-group']);
    }
}
