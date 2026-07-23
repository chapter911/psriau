<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailToMstPegawai extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('mst_pegawai') && ! $this->db->fieldExists('email', 'mst_pegawai')) {
            $this->forge->addColumn('mst_pegawai', [
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'nama',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('mst_pegawai') && $this->db->fieldExists('email', 'mst_pegawai')) {
            $this->forge->dropColumn('mst_pegawai', 'email');
        }
    }
}
