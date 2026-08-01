<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ResetMstTransportasi extends Migration
{
    public function up()
    {
        // Truncate the table to clear existing data and reset auto-increment
        $this->db->table('mst_transportasi')->truncate();

        // Insert new master data
        $items = [
            'Kendaraan Operasional',
            'Sewa Kendaraan',
            'Pesawat Udara',
            'Tiket Kapal',
            'Roro',
            'Toll',
            'Taksi',
            'Travel'
        ];

        $data = [];
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            $data[] = [
                'nama_transportasi' => $item,
                'created_by' => 'system',
                'created_date' => $now,
                'updated_by' => 'system',
                'updated_date' => $now
            ];
        }

        $this->db->table('mst_transportasi')->insertBatch($data);
    }

    public function down()
    {
        // Down migration can optionally empty the table, but since it's a reset,
        // it's generally fine to leave it as is or truncate it.
        // We'll leave it empty.
    }
}
