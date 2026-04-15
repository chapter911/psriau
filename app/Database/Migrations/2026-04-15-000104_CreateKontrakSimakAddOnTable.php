<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakAddOnTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_add_on')) {
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
            'urutan' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'default' => 1,
            ],
            'nilai_add_on' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
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
            'deleted_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('simak_id');
        $this->forge->addUniqueKey(['simak_id', 'urutan']);
        $this->forge->addForeignKey('simak_id', 'trn_kontrak_simak', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('trn_kontrak_simak_add_on', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak_add_on', true);
    }
}
