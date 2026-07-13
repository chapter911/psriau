<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaketIdToMstSekolah extends Migration
{
    public function up()
    {
        // 1. Add paket_id column to mst_sekolah if it does not exist
        if ($this->db->tableExists('mst_sekolah') && !$this->db->fieldExists('paket_id', 'mst_sekolah')) {
            $fields = [
                'paket_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'longitude',
                ],
            ];
            $this->forge->addColumn('mst_sekolah', $fields);

            // Add index for paket_id
            $this->db->query("ALTER TABLE mst_sekolah ADD INDEX (paket_id)");

            // 2. Backfill existing sekolah assignments from trn_rab_gedung_detail
            if ($this->db->tableExists('trn_rab_gedung_detail') && $this->db->fieldExists('paket_id', 'trn_rab_gedung_detail')) {
                $this->db->query("
                    UPDATE mst_sekolah s
                    INNER JOIN (
                        SELECT sekolah_npsn, MIN(paket_id) AS pid
                        FROM trn_rab_gedung_detail
                        WHERE paket_id IS NOT NULL
                        GROUP BY sekolah_npsn
                    ) r ON s.npsn = r.sekolah_npsn
                    SET s.paket_id = r.pid
                ");
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('mst_sekolah') && $this->db->fieldExists('paket_id', 'mst_sekolah')) {
            $this->forge->dropColumn('mst_sekolah', 'paket_id');
        }
    }
}
