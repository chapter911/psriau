<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailRespondenToSimak extends Migration
{
    public function up()
    {
        // Add column to trn_kontrak_simak
        if (! $this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $fields = [
            'email_responden' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'default' => null,
                'after' => 'nilai_kontrak_jasa_konsultansi',
            ],
        ];

        $forge = \Config\Database::forge();
        $forge->addColumn('trn_kontrak_simak', $fields);

        // Add column to trn_kontrak_simak_konsultasi if exists
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi')) {
            $forge->addColumn('trn_kontrak_simak_konsultasi', $fields);
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();
        if ($this->db->tableExists('trn_kontrak_simak')) {
            $forge->dropColumn('trn_kontrak_simak', 'email_responden');
        }
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi')) {
            $forge->dropColumn('trn_kontrak_simak_konsultasi', 'email_responden');
        }
    }
}
