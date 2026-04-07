<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsActiveToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldData('users');
        foreach ($fields as $field) {
            if ((string) $field->name === 'is_active') {
                return;
            }
        }

        $this->forge->addColumn('users', [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'role',
            ],
        ]);

        $this->db->table('users')->update(['is_active' => 1]);
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = $this->db->getFieldData('users');
        foreach ($fields as $field) {
            if ((string) $field->name === 'is_active') {
                $this->forge->dropColumn('users', 'is_active');
                return;
            }
        }
    }
}