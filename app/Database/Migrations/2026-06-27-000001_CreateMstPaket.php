<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstPaket extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment'  => true,
            ],
            'nama_paket' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'singkatan_paket' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'updated_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('mst_paket');
    }

    public function down()
    {
        $this->forge->dropTable('mst_paket');
    }
}
