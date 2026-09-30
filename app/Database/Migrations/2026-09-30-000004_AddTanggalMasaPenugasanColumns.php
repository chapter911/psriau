<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggalMasaPenugasanColumns extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('trn_surket_kinerja_ta')) {
            $fieldsToAdd = [];

            if (! $db->fieldExists('tanggal_mulai_penugasan', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['tanggal_mulai_penugasan'] = [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'sumber_dana',
                ];
            }

            if (! $db->fieldExists('tanggal_selesai_penugasan', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['tanggal_selesai_penugasan'] = [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'tanggal_mulai_penugasan',
                ];
            }

            if (! empty($fieldsToAdd)) {
                $this->forge->addColumn('trn_surket_kinerja_ta', $fieldsToAdd);
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('trn_surket_kinerja_ta')) {
            $cols = ['tanggal_mulai_penugasan', 'tanggal_selesai_penugasan'];
            foreach ($cols as $col) {
                if ($db->fieldExists($col, 'trn_surket_kinerja_ta')) {
                    $this->forge->dropColumn('trn_surket_kinerja_ta', $col);
                }
            }
        }
    }
}
