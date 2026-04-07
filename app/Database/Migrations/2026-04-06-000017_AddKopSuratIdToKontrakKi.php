<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKopSuratIdToKontrakKi extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_ki')) {
            $this->forge->addColumn('trn_kontrak_ki', [
                'kop_surat_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'paket',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_kontrak_ki')) {
            $this->forge->dropColumn('trn_kontrak_ki', 'kop_surat_id');
        }
    }
}
