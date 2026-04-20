<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameGroupToRoleAccess extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        if ($this->db->fieldExists('group_id', 'menu_akses') && ! $this->db->fieldExists('role_id', 'menu_akses')) {
            $this->forge->modifyColumn('menu_akses', [
                'group_id' => [
                    'name' => 'role_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);
        }

        if (! $this->db->tableExists('access_roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'label' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'role_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
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
            $this->forge->addUniqueKey('role_key');
            $this->forge->createTable('access_roles');
        }

        $roleTable = $this->db->table('access_roles');
        $adminExists = (int) $roleTable->where('id', 1)->countAllResults();
        if ($adminExists === 0) {
            $roleTable->insert([
                'id' => 1,
                'label' => 'Admin',
                'role_key' => 'admin',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $editorExists = (int) $roleTable->where('id', 2)->countAllResults();
        if ($editorExists === 0) {
            $roleTable->insert([
                'id' => 2,
                'label' => 'Editor',
                'role_key' => 'editor',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('menu_akses') && $this->db->fieldExists('role_id', 'menu_akses') && ! $this->db->fieldExists('group_id', 'menu_akses')) {
            $this->forge->modifyColumn('menu_akses', [
                'role_id' => [
                    'name' => 'group_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);
        }

        if ($this->db->tableExists('access_roles')) {
            $this->forge->dropTable('access_roles', true);
        }
    }
}
