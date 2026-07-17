<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterBiayaTables extends Migration
{
    public function up()
    {
        $db = $this->db;

        // -------------------------------------------------------
        // 1. Table: mst_biaya_transportasi
        // -------------------------------------------------------
        if (! $db->tableExists('mst_biaya_transportasi')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'provinsi_kode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => false,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                    'default'    => 'Orang/Kali',
                ],
                'besaran' => [
                    'type'       => 'BIGINT',
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'updated_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('provinsi_kode');
            $this->forge->createTable('mst_biaya_transportasi', true);

            $now = date('Y-m-d H:i:s');
            $db->table('mst_biaya_transportasi')->insertBatch([
                ['provinsi_kode' => '11', 'satuan' => 'Orang/Kali', 'besaran' => 123000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '12', 'satuan' => 'Orang/Kali', 'besaran' => 278000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '13', 'satuan' => 'Orang/Kali', 'besaran' => 171000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '14', 'satuan' => 'Orang/Kali', 'besaran' => 99000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '15', 'satuan' => 'Orang/Kali', 'besaran' => 133000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '16', 'satuan' => 'Orang/Kali', 'besaran' => 162000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '17', 'satuan' => 'Orang/Kali', 'besaran' => 106000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '18', 'satuan' => 'Orang/Kali', 'besaran' => 162000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '19', 'satuan' => 'Orang/Kali', 'besaran' => 94000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '21', 'satuan' => 'Orang/Kali', 'besaran' => 159000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '31', 'satuan' => 'Orang/Kali', 'besaran' => 250000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '32', 'satuan' => 'Orang/Kali', 'besaran' => 180000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '33', 'satuan' => 'Orang/Kali', 'besaran' => 105000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '34', 'satuan' => 'Orang/Kali', 'besaran' => 258000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '35', 'satuan' => 'Orang/Kali', 'besaran' => 225000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '36', 'satuan' => 'Orang/Kali', 'besaran' => 300000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '51', 'satuan' => 'Orang/Kali', 'besaran' => 219000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '52', 'satuan' => 'Orang/Kali', 'besaran' => 224000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '53', 'satuan' => 'Orang/Kali', 'besaran' => 105000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '61', 'satuan' => 'Orang/Kali', 'besaran' => 165000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '62', 'satuan' => 'Orang/Kali', 'besaran' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '63', 'satuan' => 'Orang/Kali', 'besaran' => 174000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '64', 'satuan' => 'Orang/Kali', 'besaran' => 300000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '65', 'satuan' => 'Orang/Kali', 'besaran' => 211000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '71', 'satuan' => 'Orang/Kali', 'besaran' => 134000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '72', 'satuan' => 'Orang/Kali', 'besaran' => 149000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '73', 'satuan' => 'Orang/Kali', 'besaran' => 181000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '74', 'satuan' => 'Orang/Kali', 'besaran' => 154000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '75', 'satuan' => 'Orang/Kali', 'besaran' => 256000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '76', 'satuan' => 'Orang/Kali', 'besaran' => 283000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '81', 'satuan' => 'Orang/Kali', 'besaran' => 279000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '82', 'satuan' => 'Orang/Kali', 'besaran' => 208000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '91', 'satuan' => 'Orang/Kali', 'besaran' => 462000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '92', 'satuan' => 'Orang/Kali', 'besaran' => 228000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '93', 'satuan' => 'Orang/Kali', 'besaran' => 0,      'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '94', 'satuan' => 'Orang/Kali', 'besaran' => 0,      'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '95', 'satuan' => 'Orang/Kali', 'besaran' => 0,      'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '96', 'satuan' => 'Orang/Kali', 'besaran' => 0,      'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
            ]);
        }

        // -------------------------------------------------------
        // 2. Table: mst_biaya_penginapan
        // -------------------------------------------------------
        if (! $db->tableExists('mst_biaya_penginapan')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'provinsi_kode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => false,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                    'default'    => 'OH',
                ],
                // Pejabat Negara/Wakil Menteri/Pejabat Eselon I
                'tarif_eselon1' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                // Pejabat Negara Lainnya/Pejabat Eselon II
                'tarif_eselon2' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                // Pejabat Eselon III/Golongan IV
                'tarif_eselon3' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                // Pejabat Eselon IV/Golongan III/II/I
                'tarif_eselon4' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'updated_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('provinsi_kode');
            $this->forge->createTable('mst_biaya_penginapan', true);

            $now = date('Y-m-d H:i:s');
            $db->table('mst_biaya_penginapan')->insertBatch([
                ['provinsi_kode' => '11', 'satuan' => 'OH', 'tarif_eselon1' => 5109000, 'tarif_eselon2' => 3526000, 'tarif_eselon3' => 1578000, 'tarif_eselon4' => 770000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '12', 'satuan' => 'OH', 'tarif_eselon1' => 4960000, 'tarif_eselon2' => 2195000, 'tarif_eselon3' => 1188000, 'tarif_eselon4' => 699000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '13', 'satuan' => 'OH', 'tarif_eselon1' => 5603000, 'tarif_eselon2' => 3373000, 'tarif_eselon3' => 1353000, 'tarif_eselon4' => 701000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '14', 'satuan' => 'OH', 'tarif_eselon1' => 3820000, 'tarif_eselon2' => 3119000, 'tarif_eselon3' => 1650000, 'tarif_eselon4' => 852000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '15', 'satuan' => 'OH', 'tarif_eselon1' => 5004000, 'tarif_eselon2' => 4102000, 'tarif_eselon3' => 1252000, 'tarif_eselon4' => 580000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '16', 'satuan' => 'OH', 'tarif_eselon1' => 6298000, 'tarif_eselon2' => 3134000, 'tarif_eselon3' => 1966000, 'tarif_eselon4' => 861000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '17', 'satuan' => 'OH', 'tarif_eselon1' => 2140000, 'tarif_eselon2' => 1628000, 'tarif_eselon3' => 1546000, 'tarif_eselon4' => 692000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '18', 'satuan' => 'OH', 'tarif_eselon1' => 4806000, 'tarif_eselon2' => 2663000, 'tarif_eselon3' => 1539000, 'tarif_eselon4' => 621000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '19', 'satuan' => 'OH', 'tarif_eselon1' => 4424000, 'tarif_eselon2' => 2838000, 'tarif_eselon3' => 1957000, 'tarif_eselon4' => 724000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '21', 'satuan' => 'OH', 'tarif_eselon1' => 6177000, 'tarif_eselon2' => 2481000, 'tarif_eselon3' => 1388000, 'tarif_eselon4' => 792000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '31', 'satuan' => 'OH', 'tarif_eselon1' => 9331000, 'tarif_eselon2' => 2084000, 'tarif_eselon3' => 1062000, 'tarif_eselon4' => 730000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '32', 'satuan' => 'OH', 'tarif_eselon1' => 5812000, 'tarif_eselon2' => 2755000, 'tarif_eselon3' => 1366000, 'tarif_eselon4' => 735000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '33', 'satuan' => 'OH', 'tarif_eselon1' => 6129000, 'tarif_eselon2' => 2138000, 'tarif_eselon3' => 1286000, 'tarif_eselon4' => 810000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '34', 'satuan' => 'OH', 'tarif_eselon1' => 5100000, 'tarif_eselon2' => 2695000, 'tarif_eselon3' => 1600000, 'tarif_eselon4' => 845000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '35', 'satuan' => 'OH', 'tarif_eselon1' => 4449000, 'tarif_eselon2' => 2007000, 'tarif_eselon3' => 1234000, 'tarif_eselon4' => 814000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '36', 'satuan' => 'OH', 'tarif_eselon1' => 5725000, 'tarif_eselon2' => 2373000, 'tarif_eselon3' => 1301000, 'tarif_eselon4' => 775000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '51', 'satuan' => 'OH', 'tarif_eselon1' => 7328000, 'tarif_eselon2' => 2433000, 'tarif_eselon3' => 1754000, 'tarif_eselon4' => 1138000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '52', 'satuan' => 'OH', 'tarif_eselon1' => 4682000, 'tarif_eselon2' => 2648000, 'tarif_eselon3' => 1418000, 'tarif_eselon4' => 907000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '53', 'satuan' => 'OH', 'tarif_eselon1' => 4013000, 'tarif_eselon2' => 2283000, 'tarif_eselon3' => 1450000, 'tarif_eselon4' => 737000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '61', 'satuan' => 'OH', 'tarif_eselon1' => 2654000, 'tarif_eselon2' => 1923000, 'tarif_eselon3' => 1125000, 'tarif_eselon4' => 576000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '62', 'satuan' => 'OH', 'tarif_eselon1' => 4901000, 'tarif_eselon2' => 3391000, 'tarif_eselon3' => 1189000, 'tarif_eselon4' => 706000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '63', 'satuan' => 'OH', 'tarif_eselon1' => 4797000, 'tarif_eselon2' => 3316000, 'tarif_eselon3' => 1500000, 'tarif_eselon4' => 746000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '64', 'satuan' => 'OH', 'tarif_eselon1' => 4000000, 'tarif_eselon2' => 2342000, 'tarif_eselon3' => 1507000, 'tarif_eselon4' => 804000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '65', 'satuan' => 'OH', 'tarif_eselon1' => 4000000, 'tarif_eselon2' => 2854000, 'tarif_eselon3' => 1507000, 'tarif_eselon4' => 904000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '71', 'satuan' => 'OH', 'tarif_eselon1' => 5264000, 'tarif_eselon2' => 2290000, 'tarif_eselon3' => 1270000, 'tarif_eselon4' => 978000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '72', 'satuan' => 'OH', 'tarif_eselon1' => 2309000, 'tarif_eselon2' => 2166000, 'tarif_eselon3' => 1679000, 'tarif_eselon4' => 951000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '73', 'satuan' => 'OH', 'tarif_eselon1' => 4820000, 'tarif_eselon2' => 1938000, 'tarif_eselon3' => 1423000, 'tarif_eselon4' => 745000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '74', 'satuan' => 'OH', 'tarif_eselon1' => 3089000, 'tarif_eselon2' => 2755000, 'tarif_eselon3' => 1297000, 'tarif_eselon4' => 786000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '75', 'satuan' => 'OH', 'tarif_eselon1' => 4168000, 'tarif_eselon2' => 3107000, 'tarif_eselon3' => 1606000, 'tarif_eselon4' => 955000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '76', 'satuan' => 'OH', 'tarif_eselon1' => 4076000, 'tarif_eselon2' => 3098000, 'tarif_eselon3' => 1344000, 'tarif_eselon4' => 704000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '81', 'satuan' => 'OH', 'tarif_eselon1' => 3467000, 'tarif_eselon2' => 3240000, 'tarif_eselon3' => 1059000, 'tarif_eselon4' => 667000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '82', 'satuan' => 'OH', 'tarif_eselon1' => 4612000, 'tarif_eselon2' => 3843000, 'tarif_eselon3' => 1160000, 'tarif_eselon4' => 654000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '91', 'satuan' => 'OH', 'tarif_eselon1' => 3859000, 'tarif_eselon2' => 3318000, 'tarif_eselon3' => 2521000, 'tarif_eselon4' => 1038000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '92', 'satuan' => 'OH', 'tarif_eselon1' => 3872000, 'tarif_eselon2' => 3575000, 'tarif_eselon3' => 2056000, 'tarif_eselon4' => 967000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '93', 'satuan' => 'OH', 'tarif_eselon1' => 5673000, 'tarif_eselon2' => 4877000, 'tarif_eselon3' => 3706000, 'tarif_eselon4' => 1526000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '94', 'satuan' => 'OH', 'tarif_eselon1' => 3859000, 'tarif_eselon2' => 3318000, 'tarif_eselon3' => 2521000, 'tarif_eselon4' => 1038000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '95', 'satuan' => 'OH', 'tarif_eselon1' => 5711000, 'tarif_eselon2' => 4911000, 'tarif_eselon3' => 3731000, 'tarif_eselon4' => 1536000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '96', 'satuan' => 'OH', 'tarif_eselon1' => 3872000, 'tarif_eselon2' => 3575000, 'tarif_eselon3' => 2056000, 'tarif_eselon4' => 967000,  'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
            ]);
        }

        // -------------------------------------------------------
        // 3. Table: mst_biaya_harian
        // -------------------------------------------------------
        if (! $db->tableExists('mst_biaya_harian')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'provinsi_kode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => false,
                ],
                'satuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                    'default'    => 'OH',
                ],
                'luar_kota' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                'dalam_kota' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                'diklat' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'updated_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('provinsi_kode');
            $this->forge->createTable('mst_biaya_harian', true);

            $now = date('Y-m-d H:i:s');
            $db->table('mst_biaya_harian')->insertBatch([
                ['provinsi_kode' => '11', 'satuan' => 'OH', 'luar_kota' => 360000, 'dalam_kota' => 140000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '12', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '13', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '14', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '15', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '16', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '17', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '18', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '19', 'satuan' => 'OH', 'luar_kota' => 410000, 'dalam_kota' => 160000, 'diklat' => 120000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '21', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '31', 'satuan' => 'OH', 'luar_kota' => 530000, 'dalam_kota' => 210000, 'diklat' => 160000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '32', 'satuan' => 'OH', 'luar_kota' => 430000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '33', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '34', 'satuan' => 'OH', 'luar_kota' => 420000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '35', 'satuan' => 'OH', 'luar_kota' => 410000, 'dalam_kota' => 160000, 'diklat' => 120000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '36', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '51', 'satuan' => 'OH', 'luar_kota' => 480000, 'dalam_kota' => 190000, 'diklat' => 140000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '52', 'satuan' => 'OH', 'luar_kota' => 440000, 'dalam_kota' => 180000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '53', 'satuan' => 'OH', 'luar_kota' => 430000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '61', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '62', 'satuan' => 'OH', 'luar_kota' => 360000, 'dalam_kota' => 140000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '63', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '64', 'satuan' => 'OH', 'luar_kota' => 430000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '65', 'satuan' => 'OH', 'luar_kota' => 430000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '71', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '72', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '73', 'satuan' => 'OH', 'luar_kota' => 430000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '74', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '75', 'satuan' => 'OH', 'luar_kota' => 370000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '76', 'satuan' => 'OH', 'luar_kota' => 410000, 'dalam_kota' => 160000, 'diklat' => 120000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '81', 'satuan' => 'OH', 'luar_kota' => 380000, 'dalam_kota' => 150000, 'diklat' => 110000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '82', 'satuan' => 'OH', 'luar_kota' => 430000, 'dalam_kota' => 170000, 'diklat' => 130000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '91', 'satuan' => 'OH', 'luar_kota' => 580000, 'dalam_kota' => 230000, 'diklat' => 170000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '92', 'satuan' => 'OH', 'luar_kota' => 480000, 'dalam_kota' => 190000, 'diklat' => 140000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '93', 'satuan' => 'OH', 'luar_kota' => 580000, 'dalam_kota' => 230000, 'diklat' => 170000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '94', 'satuan' => 'OH', 'luar_kota' => 580000, 'dalam_kota' => 230000, 'diklat' => 170000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '95', 'satuan' => 'OH', 'luar_kota' => 580000, 'dalam_kota' => 230000, 'diklat' => 170000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
                ['provinsi_kode' => '96', 'satuan' => 'OH', 'luar_kota' => 480000, 'dalam_kota' => 190000, 'diklat' => 140000, 'created_by' => 'system', 'created_date' => $now, 'updated_by' => 'system', 'updated_date' => $now],
            ]);
        }

        // -------------------------------------------------------
        // 4. Add menu "Biaya" (lv2: 05-10) under Master (05)
        // -------------------------------------------------------
        if ($db->tableExists('menu_lv2')) {
            $existsBiaya = $db->table('menu_lv2')->where('id', '05-10')->countAllResults();
            if ($existsBiaya === 0) {
                $db->table('menu_lv2')->insert([
                    'id'       => '05-10',
                    'label'    => 'Biaya',
                    'icon'     => 'fas fa-money-bill-wave',
                    'link'     => '#',
                    'header'   => '05',
                    'ordering' => 10,
                ]);
                $this->copyMenuAkses('05-09', '05-10');
            }
        }

        // -------------------------------------------------------
        // 5. Add lv3 sub-menus under "Biaya" (05-10)
        // -------------------------------------------------------
        if ($db->tableExists('menu_lv3')) {
            $menus = [
                ['id' => '05-10-01', 'label' => 'Transportasi',    'icon' => 'far fa-circle', 'link' => 'admin/master/biaya/transportasi', 'header' => '05-10', 'ordering' => 1],
                ['id' => '05-10-02', 'label' => 'Penginapan',       'icon' => 'far fa-circle', 'link' => 'admin/master/biaya/penginapan',   'header' => '05-10', 'ordering' => 2],
                ['id' => '05-10-03', 'label' => 'Harian Personel',  'icon' => 'far fa-circle', 'link' => 'admin/master/biaya/harian',       'header' => '05-10', 'ordering' => 3],
            ];

            foreach ($menus as $menu) {
                $exists = $db->table('menu_lv3')->where('id', $menu['id'])->countAllResults();
                if ($exists === 0) {
                    $db->table('menu_lv3')->insert($menu);
                    $this->copyMenuAkses('05-09', $menu['id']);
                }
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        // Drop tables
        foreach (['mst_biaya_transportasi', 'mst_biaya_penginapan', 'mst_biaya_harian'] as $table) {
            if ($db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        // Remove menus
        if ($db->tableExists('menu_lv3')) {
            $db->table('menu_lv3')->whereIn('id', ['05-10-01', '05-10-02', '05-10-03'])->delete();
            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->whereIn('menu_id', ['05-10-01', '05-10-02', '05-10-03'])->delete();
            }
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '05-10')->delete();
            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->where('menu_id', '05-10')->delete();
            }
        }
    }

    private function copyMenuAkses(string $srcMenuId, string $destMenuId): void
    {
        $db = $this->db;
        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $rows = $db->table('menu_akses')->where('menu_id', $srcMenuId)->get()->getResultArray();

        foreach ($rows as $row) {
            $roleId = $row[$roleColumn];
            $exists = $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $destMenuId)
                ->countAllResults();

            if ($exists === 0) {
                $db->table('menu_akses')->insert([
                    $roleColumn      => $roleId,
                    'menu_id'        => $destMenuId,
                    'FiturAdd'       => $row['FiturAdd'] ?? 0,
                    'FiturEdit'      => $row['FiturEdit'] ?? 0,
                    'FiturDelete'    => $row['FiturDelete'] ?? 0,
                    'FiturExport'    => $row['FiturExport'] ?? 0,
                    'FiturImport'    => $row['FiturImport'] ?? 0,
                    'FiturApproval'  => $row['FiturApproval'] ?? 0,
                ]);
            }
        }
    }
}
