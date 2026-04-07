<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveLihatWebsiteMenu extends Migration
{
    public function up()
    {
        $builder = $this->db->table('sidebar_menus');

        $builder
            ->groupStart()
                ->where('id', 9)
                ->orGroupStart()
                    ->where('label', 'Lihat Website')
                    ->where('url', '/')
                ->groupEnd()
            ->groupEnd()
            ->delete();
    }

    public function down()
    {
        $builder = $this->db->table('sidebar_menus');
        $exists  = $builder->where('id', 9)->get()->getRowArray();

        if (! $exists) {
            $builder->insert([
                'id'             => 9,
                'parent_id'      => null,
                'label'          => 'Lihat Website',
                'url'            => '/',
                'icon'           => 'fas fa-globe',
                'active_pattern' => null,
                'sort_order'     => 90,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
