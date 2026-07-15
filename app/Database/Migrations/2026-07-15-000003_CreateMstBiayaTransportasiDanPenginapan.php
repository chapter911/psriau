<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstBiayaTransportasiDanPenginapan extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table 'mst_biaya_transportasi'
        if (! $db->tableExists('mst_biaya_transportasi')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'kode_provinsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                ],
                'kode_kabupaten' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                ],
                'asal' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'tujuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'besaran' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => false,
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
            $this->forge->addUniqueKey(['asal', 'tujuan']);
            $this->forge->createTable('mst_biaya_transportasi', true);

            // Seed default values for Riau (Asal Pekanbaru)
            $riau = $db->table('mst_provinsi')->like('nama_provinsi', 'Riau')->get()->getRowArray();
            $kodeProv = $riau['kode_provinsi'] ?? '14';

            $kabData = [
                'Indragiri Hilir'  => 380000,
                'Indragiri Hulu'   => 315000,
                'Kampar'           => 200000,
                'Kuantan Singingi' => 300000,
                'Pelalawan'        => 225000,
                'Rokan Hilir'      => 350000,
                'Rokan Hulu'       => 322000,
                'Siak'             => 350000,
                'Dumai'            => 400000,
            ];

            $now = date('Y-m-d H:i:s');
            $transportSeeds = [];

            foreach ($kabData as $name => $amount) {
                // Find matching kabupaten code
                $kab = $db->table('mst_kabupaten')
                    ->where('kode_provinsi', $kodeProv)
                    ->like('nama_kabupaten', $name)
                    ->get()
                    ->getRowArray();

                $kodeKab = $kab['kode_kabupaten'] ?? null;
                $fullname = $kab['nama_kabupaten'] ?? $name;

                $transportSeeds[] = [
                    'kode_provinsi'  => $kodeProv,
                    'kode_kabupaten' => $kodeKab,
                    'asal'           => 'Pekanbaru',
                    'tujuan'         => $fullname,
                    'besaran'        => $amount,
                    'created_by'     => 'system',
                    'created_date'   => $now,
                    'updated_by'     => 'system',
                    'updated_date'   => $now,
                ];
            }

            if (! empty($transportSeeds)) {
                $db->table('mst_biaya_transportasi')->insertBatch($transportSeeds);
            }
        }

        // 2. Create table 'mst_biaya_penginapan'
        if (! $db->tableExists('mst_biaya_penginapan')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'kode_provinsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                ],
                'nama_provinsi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'level_pejabat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'tarif' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => false,
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
            $this->forge->addUniqueKey(['nama_provinsi', 'level_pejabat']);
            $this->forge->createTable('mst_biaya_penginapan', true);

            // Seed default values for Riau Hotel rates (PMK 32 Halaman 19)
            $riau = $db->table('mst_provinsi')->like('nama_provinsi', 'Riau')->get()->getRowArray();
            $kodeProv = $riau['kode_provinsi'] ?? '14';
            $namaProv = $riau['nama_provinsi'] ?? 'Riau';

            $now = date('Y-m-d H:i:s');
            $db->table('mst_biaya_penginapan')->insertBatch([
                [
                    'kode_provinsi'  => $kodeProv,
                    'nama_provinsi'  => $namaProv,
                    'level_pejabat'  => 'Pejabat Negara/Wakil Menteri/Eselon I',
                    'tarif'          => 3820000,
                    'created_by'     => 'system',
                    'created_date'   => $now,
                    'updated_by'     => 'system',
                    'updated_date'   => $now,
                ],
                [
                    'kode_provinsi'  => $kodeProv,
                    'nama_provinsi'  => $namaProv,
                    'level_pejabat'  => 'Pejabat Negara Lainnya/Eselon II',
                    'tarif'          => 3119000,
                    'created_by'     => 'system',
                    'created_date'   => $now,
                    'updated_by'     => 'system',
                    'updated_date'   => $now,
                ],
                [
                    'kode_provinsi'  => $kodeProv,
                    'nama_provinsi'  => $namaProv,
                    'level_pejabat'  => 'Pejabat Eselon III/Golongan IV',
                    'tarif'          => 1650000,
                    'created_by'     => 'system',
                    'created_date'   => $now,
                    'updated_by'     => 'system',
                    'updated_date'   => $now,
                ],
                [
                    'kode_provinsi'  => $kodeProv,
                    'nama_provinsi'  => $namaProv,
                    'level_pejabat'  => 'Pejabat Eselon IV/Golongan III/II/I',
                    'tarif'          => 852000,
                    'created_by'     => 'system',
                    'created_date'   => $now,
                    'updated_by'     => 'system',
                    'updated_date'   => $now,
                ],
            ]);
        }

        // 3. Set up menus
        if ($db->tableExists('menu_lv2')) {
            // Find parent Master menu (ID 05)
            $masterLv1Id = '05';

            // Check if Level 2 menu "Biaya" (05-10) exists
            $existsBiayaLv2 = $db->table('menu_lv2')->where('id', '05-10')->countAllResults();
            if ($existsBiayaLv2 === 0) {
                $db->table('menu_lv2')->insert([
                    'id'       => '05-10',
                    'label'    => 'Biaya',
                    'icon'     => 'fas fa-money-bill-wave',
                    'link'     => '#',
                    'header'   => $masterLv1Id,
                    'ordering' => 10,
                ]);

                // Copy permissions from Dasar SPT (05-08) or similar
                $this->copyMenuAkses('05-08', '05-10');
            }

            // Check if Level 3 submenus exist
            if ($db->tableExists('menu_lv3')) {
                // Submenu: Transportasi
                $existsTransLv3 = $db->table('menu_lv3')->where('id', '05-10-01')->countAllResults();
                if ($existsTransLv3 === 0) {
                    $db->table('menu_lv3')->insert([
                        'id'       => '05-10-01',
                        'label'    => 'Transportasi',
                        'icon'     => 'far fa-circle',
                        'link'     => 'admin/master/biaya/transportasi',
                        'header'   => '05-10',
                        'ordering' => 1,
                    ]);
                    $this->copyMenuAkses('05-08', '05-10-01');
                }

                // Submenu: Penginapan
                $existsHotelLv3 = $db->table('menu_lv3')->where('id', '05-10-02')->countAllResults();
                if ($existsHotelLv3 === 0) {
                    $db->table('menu_lv3')->insert([
                        'id'       => '05-10-02',
                        'label'    => 'Penginapan',
                        'icon'     => 'far fa-circle',
                        'link'     => 'admin/master/biaya/penginapan',
                        'header'   => '05-10',
                        'ordering' => 2,
                    ]);
                    $this->copyMenuAkses('05-08', '05-10-02');
                }
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        // Drop tables
        if ($db->tableExists('mst_biaya_transportasi')) {
            $this->forge->dropTable('mst_biaya_transportasi', true);
        }
        if ($db->tableExists('mst_biaya_penginapan')) {
            $this->forge->dropTable('mst_biaya_penginapan', true);
        }

        // Delete menus and access
        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '05-10')->delete();
        }

        if ($db->tableExists('menu_lv3')) {
            $db->table('menu_lv3')->where('header', '05-10')->delete();
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '05-10')->delete();
            $db->table('menu_akses')->where('menu_id', '05-10-01')->delete();
            $db->table('menu_akses')->where('menu_id', '05-10-02')->delete();
        }
    }

    private function copyMenuAkses(string $srcMenuId, string $destMenuId): void
    {
        $db = $this->db;
        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $rows = $db->table('menu_akses')->where('menu_id', $srcMenuId)->get()->getResultArray();
        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        foreach ($rows as $row) {
            $roleId = $row[$roleColumn];

            $exists = $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $destMenuId)
                ->countAllResults();

            if ($exists === 0) {
                $db->table('menu_akses')->insert([
                    $roleColumn     => $roleId,
                    'menu_id'       => $destMenuId,
                    'FiturAdd'      => $row['FiturAdd'] ?? 0,
                    'FiturEdit'     => $row['FiturEdit'] ?? 0,
                    'FiturDelete'   => $row['FiturDelete'] ?? 0,
                    'FiturExport'   => $row['FiturExport'] ?? 0,
                    'FiturImport'   => $row['FiturImport'] ?? 0,
                    'FiturApproval' => $row['FiturApproval'] ?? 0,
                ]);
            }
        }
    }
}
