<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMasterBiayaAddValiditas extends Migration
{
    public function up()
    {
        $tables = [
            'mst_biaya_transportasi',
            'mst_biaya_penginapan',
            'mst_biaya_harian'
        ];

        // Ensure provinsi_kode exists (in case production had malformed tables before this migration)
        foreach ($tables as $table) {
            if ($this->db->tableExists($table) && !$this->db->fieldExists('provinsi_kode', $table)) {
                $this->forge->addColumn($table, [
                    'provinsi_kode' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 10,
                        'null'       => false,
                        'after'      => 'id',
                    ],
                ]);
            }
        }

        $fields = [
            'berlaku_mulai'  => [
                'type'       => 'DATE',
                'null'       => false,
                'default'    => '2024-01-01',
            ],
            'berlaku_hingga' => [
                'type'       => 'DATE',
                'null'       => true,
                'default'    => null,
            ],
            'is_active'      => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
        ];

        $tables = [
            'mst_biaya_transportasi',
            'mst_biaya_penginapan',
            'mst_biaya_harian'
        ];

        foreach ($tables as $table) {
            $this->forge->addColumn($table, $fields);
        }
    }

    public function down()
    {
        $fields = [
            'berlaku_mulai',
            'berlaku_hingga',
            'is_active',
        ];

        $tables = [
            'mst_biaya_transportasi',
            'mst_biaya_penginapan',
            'mst_biaya_harian'
        ];

        foreach ($tables as $table) {
            foreach ($fields as $field) {
                if ($this->db->fieldExists($field, $table)) {
                    $this->forge->dropColumn($table, $field);
                }
            }
        }
    }
}
