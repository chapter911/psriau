<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakKonsultasiShareTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi_share')) {
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
            'share_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
                'constraint' => 255,
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
        $this->forge->addKey('simak_id');
        $this->forge->addUniqueKey('share_token');
        $this->forge->createTable('trn_kontrak_simak_konsultasi_share', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak_konsultasi_share', true);
    }
}
