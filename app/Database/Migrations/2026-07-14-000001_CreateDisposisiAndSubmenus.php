<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisposisiAndSubmenus extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table 'disposisi_perjalanan_dinas'
        if (! $db->tableExists('disposisi_perjalanan_dinas')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pelaksana_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'periode_mulai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'periode_selesai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'kota_tujuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'tujuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'transportasi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'perihal' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'menyetujui_pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'diketahui_pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('periode_mulai');
            $this->forge->addKey('periode_selesai');
            $this->forge->createTable('disposisi_perjalanan_dinas', true);
        }

        // 2. Adjust menus if tables exist
        if ($db->tableExists('menu_lv2') && $db->tableExists('menu_lv3')) {
            // Find existing "Perjalanan Dinas" under menu_lv2 (ID 10-03)
            $existingPerjadin = $db->table('menu_lv2')
                ->where('id', '10-03')
                ->get()
                ->getRowArray();

            if ($existingPerjadin !== null) {
                // Update link of "Perjalanan Dinas" menu_lv2 to "#"
                $db->table('menu_lv2')
                    ->where('id', '10-03')
                    ->update(['link' => '#']);

                // Insert "Disposisi" into menu_lv3
                $existsDisposisi = $db->table('menu_lv3')->where('id', '10-03-01')->countAllResults();
                if ($existsDisposisi === 0) {
                    $db->table('menu_lv3')->insert([
                        'id'       => '10-03-01',
                        'label'    => 'Disposisi',
                        'icon'     => 'far fa-circle',
                        'link'     => 'admin/surat/perjalanan-dinas/disposisi',
                        'header'   => '10-03',
                        'ordering' => 1,
                    ]);
                    $this->copyMenuAkses('10-03', '10-03-01');
                }

                // Insert "Laporan Pelaksanaan" into menu_lv3
                $existsLaporan = $db->table('menu_lv3')->where('id', '10-03-02')->countAllResults();
                if ($existsLaporan === 0) {
                    $db->table('menu_lv3')->insert([
                        'id'       => '10-03-02',
                        'label'    => 'Laporan Pelaksanaan',
                        'icon'     => 'far fa-circle',
                        'link'     => 'admin/surat/perjalanan-dinas',
                        'header'   => '10-03',
                        'ordering' => 2,
                    ]);
                    $this->copyMenuAkses('10-03', '10-03-02');
                }
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        // 1. Drop table 'disposisi_perjalanan_dinas'
        if ($db->tableExists('disposisi_perjalanan_dinas')) {
            $this->forge->dropTable('disposisi_perjalanan_dinas', true);
        }

        // 2. Revert menus
        if ($db->tableExists('menu_lv2') && $db->tableExists('menu_lv3')) {
            // Delete menu_lv3 entries
            $db->table('menu_lv3')->whereIn('id', ['10-03-01', '10-03-02'])->delete();

            // Delete their menu_akses
            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->whereIn('menu_id', ['10-03-01', '10-03-02'])->delete();
            }

            // Restore link for "Perjalanan Dinas" menu_lv2
            $db->table('menu_lv2')
                ->where('id', '10-03')
                ->update(['link' => 'admin/surat/perjalanan-dinas']);
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
