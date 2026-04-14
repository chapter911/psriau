<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstJabatanTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('mst_jabatan')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'jabatan' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'jenis_jabatan' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'deskripsi_jabatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'updated_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('jabatan');
        $this->forge->addKey('is_active');
        $this->forge->createTable('mst_jabatan', true);
    }

    public function down()
    {
        $this->forge->dropTable('mst_jabatan', true);
    }
}