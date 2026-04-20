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

        $fields = $this->menuAksesFields();
        $hasRoleId = in_array('role_id', $fields, true);
        $hasGroupId = in_array('group_id', $fields, true);

        // Keep both columns available to support historical migrations that still reference one of them.
        if ($hasRoleId && ! $hasGroupId) {
            $this->forge->addColumn('menu_akses', [
                'group_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'role_id',
                ],
            ]);
            $this->db->query('UPDATE menu_akses SET group_id = role_id WHERE group_id IS NULL');
            $hasGroupId = true;
        }

        if ($hasGroupId && ! $hasRoleId) {
            $this->forge->addColumn('menu_akses', [
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'group_id',
                ],
            ]);
            $this->db->query('UPDATE menu_akses SET role_id = group_id WHERE role_id IS NULL');
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

    private function menuAksesFields(): array
    {
        if (! $this->db->tableExists('menu_akses')) {
            return [];
        }

        $fields = [];
        $result = $this->db->query('SHOW COLUMNS FROM menu_akses')->getResultArray();
        foreach ($result as $row) {
            $name = strtolower((string) ($row['Field'] ?? ''));
            if ($name !== '') {
                $fields[] = $name;
            }
        }

        return $fields;
    }
}
