<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSidebarMenusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'parent_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'active_pattern' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('parent_id');
        $this->forge->createTable('sidebar_menus');

        $now = date('Y-m-d H:i:s');

        $this->db->table('sidebar_menus')->insertBatch([
            [
                'id'             => 1,
                'parent_id'      => null,
                'label'          => 'Dashboard',
                'url'            => '/admin',
                'icon'           => 'fas fa-gauge-high',
                'active_pattern' => 'admin',
                'sort_order'     => 10,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 2,
                'parent_id'      => null,
                'label'          => 'Halaman Utama PPS',
                'url'            => null,
                'icon'           => 'fas fa-house',
                'active_pattern' => null,
                'sort_order'     => 20,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 3,
                'parent_id'      => 2,
                'label'          => 'Pengaturan Halaman',
                'url'            => '/admin/pengaturan-home',
                'icon'           => 'far fa-circle',
                'active_pattern' => 'admin/pengaturan-home',
                'sort_order'     => 10,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 4,
                'parent_id'      => 2,
                'label'          => 'Kelola Acara',
                'url'            => '/admin/acara',
                'icon'           => 'far fa-circle',
                'active_pattern' => 'admin/acara*',
                'sort_order'     => 20,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 5,
                'parent_id'      => 2,
                'label'          => 'Kelola Berita',
                'url'            => '/admin/berita',
                'icon'           => 'far fa-circle',
                'active_pattern' => 'admin/berita*',
                'sort_order'     => 30,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 6,
                'parent_id'      => null,
                'label'          => 'Lihat Website',
                'url'            => '/',
                'icon'           => 'fas fa-globe',
                'active_pattern' => null,
                'sort_order'     => 90,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('sidebar_menus');
    }
}
