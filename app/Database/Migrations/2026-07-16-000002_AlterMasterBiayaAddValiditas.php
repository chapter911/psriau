<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMasterBiayaAddValiditas extends Migration
{
    public function up()
    {
        $fields = [
            'berlaku_mulai'  => [
                'type'       => 'DATE',
                'null'       => false,
                'default'    => '2024-01-01',
                'after'      => 'provinsi_kode',
            ],
            'berlaku_hingga' => [
                'type'       => 'DATE',
                'null'       => true,
                'default'    => null,
                'after'      => 'berlaku_mulai',
            ],
            'is_active'      => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
                'after'      => 'berlaku_hingga',
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
