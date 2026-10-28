<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotaDinasPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table 'nota_dinas_perjalanan_dinas'
        if (! $db->tableExists('nota_dinas_perjalanan_dinas')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nomor_nota_dinas' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'kepada_yth' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                ],
                'dari' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau',
                ],
                'hal' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan',
                ],
                'tanggal_nota_dinas' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'isi_nota_dinas' => [
                    'type' => 'TEXT',
                    'null' => true,
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
                'tujuan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ppk_pegawai_id' => [
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
            $this->forge->createTable('nota_dinas_perjalanan_dinas', true);
        }

        // 2. Add 'Nota Dinas' submenu under 'Perjalanan Dinas' (10-03) at the last ordering (4)
        if ($db->tableExists('menu_lv2') && $db->tableExists('menu_lv3')) {
            $exists = $db->table('menu_lv3')->where('id', '10-03-04')->countAllResults();
            if ($exists === 0) {
                $db->table('menu_lv3')->insert([
                    'id'       => '10-03-04',
                    'label'    => 'Nota Dinas',
                    'icon'     => 'far fa-circle',
                    'link'     => 'admin/surat/perjalanan-dinas/nota-dinas',
                    'header'   => '10-03',
                    'ordering' => 4,
                ]);
                $this->copyMenuAkses('10-03-01', '10-03-04');
                $this->copyMenuAkses('10-03', '10-03-04');
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('nota_dinas_perjalanan_dinas')) {
            $this->forge->dropTable('nota_dinas_perjalanan_dinas', true);
        }

        if ($db->tableExists('menu_lv3')) {
            $db->table('menu_lv3')->where('id', '10-03-04')->delete();
            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->where('menu_id', '10-03-04')->delete();
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

