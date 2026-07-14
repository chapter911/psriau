<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstTransportasi extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table 'mst_transportasi'
        if (! $db->tableExists('mst_transportasi')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama_transportasi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
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
            $this->forge->addUniqueKey('nama_transportasi');
            $this->forge->createTable('mst_transportasi', true);

            // Seed default values
            $db->table('mst_transportasi')->insertBatch([
                [
                    'nama_transportasi' => 'Mobil Dinas',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Pesawat',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Kendaraan Pribadi',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Bus',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Kereta Api',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Sewa Kendaraan',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Kapal Laut',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Speedboat',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Kapal Cepat',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
                [
                    'nama_transportasi' => 'Kapal Feri',
                    'created_by'        => 'system',
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_by'        => 'system',
                    'updated_date'      => date('Y-m-d H:i:s'),
                ],
            ]);
        }

        // 2. Add menu "Transportasi" under "Master" (ID 05)
        if ($db->tableExists('menu_lv2')) {
            $existsMenu = $db->table('menu_lv2')->where('id', '05-09')->countAllResults();
            if ($existsMenu === 0) {
                $db->table('menu_lv2')->insert([
                    'id'       => '05-09',
                    'label'    => 'Transportasi',
                    'icon'     => 'far fa-circle',
                    'link'     => 'admin/master/transportasi',
                    'header'   => '05',
                    'ordering' => 9,
                ]);

                // Copy permissions from Dasar SPT (05-08) or Kop Surat (05-01)
                $this->copyMenuAkses('05-08', '05-09');
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        // 1. Drop table 'mst_transportasi'
        if ($db->tableExists('mst_transportasi')) {
            $this->forge->dropTable('mst_transportasi', true);
        }

        // 2. Delete menu and accesses
        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '05-09')->delete();

            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->where('menu_id', '05-09')->delete();
            }
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
