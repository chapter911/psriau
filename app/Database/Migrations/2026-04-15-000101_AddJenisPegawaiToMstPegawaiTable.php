<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisPegawaiToMstPegawaiTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mst_pegawai')) {
            return;
        }

        if ($this->db->fieldExists('jenis_pegawai', 'mst_pegawai')) {
            return;
        }

        $this->forge->addColumn('mst_pegawai', [
            'jenis_pegawai' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pns',
                'after' => 'nama',
            ],
        ]);

        $this->db->table('mst_pegawai')
            ->where('jenis_pegawai', null)
            ->set('jenis_pegawai', 'pns')
            ->update();
    }

    public function down()
    {
        if (! $this->db->tableExists('mst_pegawai')) {
            return;
        }

        if (! $this->db->fieldExists('jenis_pegawai', 'mst_pegawai')) {
            return;
        }

        $this->forge->dropColumn('mst_pegawai', 'jenis_pegawai');
    }
}
