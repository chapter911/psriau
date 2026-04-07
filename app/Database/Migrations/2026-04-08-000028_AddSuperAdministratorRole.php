<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuperAdministratorRole extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('access_roles')) {
            return;
        }

        $roleTable = $this->db->table('access_roles');
        $exists = (int) $roleTable->where('role_key', 'super_administrator')->countAllResults();

        if ($exists === 0) {
            $roleTable->insert([
                'label' => 'Super Administrator',
                'role_key' => 'super_administrator',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('access_roles')) {
            return;
        }

        $this->db->table('access_roles')
            ->where('role_key', 'super_administrator')
            ->delete();
    }
}
