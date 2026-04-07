<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSyaratUmumModalColumns extends Migration
{
    public function up()
    {
        $fields = [
            'laporan_modal' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'laporan',
            ],
            'hasil_modal' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'hasil',
            ],
            'tugas_tanggung_jawab_modal' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'tugas_tanggung_jawab',
            ],
        ];

        $this->forge->addColumn('trn_syarat_umum_kontrak_ki', $fields);

        $this->db->query('
            UPDATE trn_syarat_umum_kontrak_ki
            SET
                laporan_modal = laporan,
                hasil_modal = hasil,
                tugas_tanggung_jawab_modal = tugas_tanggung_jawab
            WHERE laporan_modal IS NULL
               OR hasil_modal IS NULL
               OR tugas_tanggung_jawab_modal IS NULL
        ');
    }

    public function down()
    {
        $this->forge->dropColumn('trn_syarat_umum_kontrak_ki', [
            'laporan_modal',
            'hasil_modal',
            'tugas_tanggung_jawab_modal',
        ]);
    }
}