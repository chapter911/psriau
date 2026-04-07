<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveYearFromKopSurat extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('kop_surat')) {
            $this->forge->dropColumn('kop_surat', ['year_start', 'year_end']);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('kop_surat')) {
            $this->forge->addColumn('kop_surat', [
                'year_start' => [
                    'type' => 'SMALLINT',
                    'constraint' => 4,
                    'after' => 'title',
                ],
                'year_end' => [
                    'type' => 'SMALLINT',
                    'constraint' => 4,
                    'null' => true,
                    'after' => 'year_start',
                ],
            ]);
        }
    }
}
