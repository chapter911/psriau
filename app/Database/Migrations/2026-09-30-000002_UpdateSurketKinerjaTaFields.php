<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSurketKinerjaTaFields extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('trn_surket_kinerja_ta')) {
            $fieldsToAdd = [];

            if (! $db->fieldExists('nomor_kontrak', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['nomor_kontrak'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'after'      => 'lokasi_pekerjaan',
                ];
            }

            if (! $db->fieldExists('tanggal_kontrak', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['tanggal_kontrak'] = [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'nomor_kontrak',
                ];
            }

            if (! $db->fieldExists('status_persen', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['status_persen'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 100,
                    'after'      => 'status_pekerjaan',
                ];
            }

            if (! $db->fieldExists('masa_penugasan_hari', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['masa_penugasan_hari'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'masa_penugasan',
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
            $cols = ['nomor_kontrak', 'tanggal_kontrak', 'status_persen', 'masa_penugasan_hari'];
            foreach ($cols as $col) {
                if ($db->fieldExists($col, 'trn_surket_kinerja_ta')) {
                    $this->forge->dropColumn('trn_surket_kinerja_ta', $col);
                }
            }
        }
    }
}
