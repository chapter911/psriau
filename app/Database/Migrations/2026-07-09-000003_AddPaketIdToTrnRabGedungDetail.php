<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaketIdToTrnRabGedungDetail extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_rab_gedung_detail') && !$this->db->fieldExists('paket_id', 'trn_rab_gedung_detail')) {
            $this->forge->addColumn('trn_rab_gedung_detail', [
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'sekolah_npsn',
                ],
            ]);
            $this->db->query("ALTER TABLE trn_rab_gedung_detail ADD INDEX (paket_id)");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_rab_gedung_detail') && $this->db->fieldExists('paket_id', 'trn_rab_gedung_detail')) {
            $this->forge->dropColumn('trn_rab_gedung_detail', 'paket_id');
        }
    }
}
