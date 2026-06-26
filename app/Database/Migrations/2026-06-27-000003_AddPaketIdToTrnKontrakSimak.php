<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaketIdToTrnKontrakSimak extends Migration
{
    public function up()
    {
        // Add paket_id to trn_kontrak_simak
        if ($this->db->tableExists('trn_kontrak_simak') && !$this->db->fieldExists('paket_id', 'trn_kontrak_simak')) {
            $this->forge->addColumn('trn_kontrak_simak', [
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'satker',
                ],
            ]);
            $this->forge->addKey('paket_id');
        }

        // Add paket_id to trn_kontrak_simak_konsultasi
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi') && !$this->db->fieldExists('paket_id', 'trn_kontrak_simak_konsultasi')) {
            $this->forge->addColumn('trn_kontrak_simak_konsultasi', [
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'satker',
                ],
            ]);
            $this->forge->addKey('paket_id');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_kontrak_simak') && $this->db->fieldExists('paket_id', 'trn_kontrak_simak')) {
            $this->forge->dropColumn('trn_kontrak_simak', 'paket_id');
        }

        if ($this->db->tableExists('trn_kontrak_simak_konsultasi') && $this->db->fieldExists('paket_id', 'trn_kontrak_simak_konsultasi')) {
            $this->forge->dropColumn('trn_kontrak_simak_konsultasi', 'paket_id');
        }
    }
}
