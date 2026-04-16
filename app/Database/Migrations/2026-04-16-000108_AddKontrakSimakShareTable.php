<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKontrakSimakShareTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_share')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'simak_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'share_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'updated_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('simak_id', 'uniq_trn_kontrak_simak_share_simak_id');
        $this->forge->addUniqueKey('share_token', 'uniq_trn_kontrak_simak_share_token');
        $this->forge->addForeignKey('simak_id', 'trn_kontrak_simak', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('trn_kontrak_simak_share', true);
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_simak_share')) {
            return;
        }

        $this->forge->dropTable('trn_kontrak_simak_share', true);
    }
}
