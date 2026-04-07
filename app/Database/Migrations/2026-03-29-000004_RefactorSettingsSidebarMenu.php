<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorSettingsSidebarMenu extends Migration
{
    public function up()
    {
        $builder = $this->db->table('sidebar_menus');

        $menuSix = $builder->where('id', 6)->get()->getRowArray();
        if ($menuSix) {
            $builder->where('id', 6)->update([
                'label'          => 'Pengaturan',
                'url'            => null,
                'icon'           => 'fas fa-gear',
                'active_pattern' => null,
                'sort_order'     => 80,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        $applicationMenu = $builder->where('id', 7)->get()->getRowArray();
        if (! $applicationMenu) {
            $builder->insert([
                'id'             => 7,
                'parent_id'      => 6,
                'label'          => 'Application',
                'url'            => '/admin/pengaturan/application',
                'icon'           => 'far fa-circle',
                'active_pattern' => 'admin/pengaturan/application',
                'sort_order'     => 10,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        $menusMenu = $builder->where('id', 8)->get()->getRowArray();
        if (! $menusMenu) {
            $builder->insert([
                'id'             => 8,
                'parent_id'      => 6,
                'label'          => 'Menus',
                'url'            => '/admin/pengaturan/menus',
                'icon'           => 'far fa-circle',
                'active_pattern' => 'admin/pengaturan/menus*',
                'sort_order'     => 20,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        $websiteMenu = $builder->where('id', 9)->get()->getRowArray();
        if (! $websiteMenu) {
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

    public function down()
    {
        $builder = $this->db->table('sidebar_menus');

        $builder->whereIn('id', [7, 8, 9])->delete();

        $menuSix = $builder->where('id', 6)->get()->getRowArray();
        if ($menuSix) {
            $builder->where('id', 6)->update([
                'label'          => 'Lihat Website',
                'url'            => '/',
                'icon'           => 'fas fa-globe',
                'active_pattern' => null,
                'sort_order'     => 90,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
