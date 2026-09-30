<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKopSuratIdToSurketKinerjaTa extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('trn_surket_kinerja_ta')) {
            $fieldsToAdd = [];

            if (! $db->fieldExists('kop_surat_id', 'trn_surket_kinerja_ta')) {
                $fieldsToAdd['kop_surat_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'kota_surat',
                ];
            }

            if (! empty($fieldsToAdd)) {
                $this->forge->addColumn('trn_surket_kinerja_ta', $fieldsToAdd);
            }

            // Set default kop_surat_id to active kop surat if available
            try {
                if ($db->tableExists('kop_surat')) {
                    $activeKop = $db->table('kop_surat')
                        ->where('is_active', 1)
                        ->orderBy('id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->getRowArray();

                    if (! empty($activeKop['id'])) {
                        $db->table('trn_surket_kinerja_ta')
                            ->where('kop_surat_id IS NULL')
                            ->update(['kop_surat_id' => (int) $activeKop['id']]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if error occurs during backfill
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('trn_surket_kinerja_ta')) {
            if ($db->fieldExists('kop_surat_id', 'trn_surket_kinerja_ta')) {
                $this->forge->dropColumn('trn_surket_kinerja_ta', 'kop_surat_id');
            }
        }
    }
}
