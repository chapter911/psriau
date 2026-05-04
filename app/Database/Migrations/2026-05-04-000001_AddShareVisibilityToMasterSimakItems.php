<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShareVisibilityToMasterSimakItems extends Migration
{
    public function up()
    {
        foreach (['mst_simak_konstruksi_item', 'mst_simak_konsultasi_item'] as $table) {
            if (! $this->db->tableExists($table) || $this->db->fieldExists('is_hidden_share', $table)) {
                continue;
            }

            $this->forge->addColumn($table, [
                'is_hidden_share' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'is_active',
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (['mst_simak_konstruksi_item', 'mst_simak_konsultasi_item'] as $table) {
            if (! $this->db->tableExists($table) || ! $this->db->fieldExists('is_hidden_share', $table)) {
                continue;
            }

            $this->forge->dropColumn($table, 'is_hidden_share');
        }
    }
}
