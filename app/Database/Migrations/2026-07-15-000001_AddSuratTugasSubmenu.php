<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuratTugasSubmenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2') && $db->tableExists('menu_lv3')) {
            $exists = $db->table('menu_lv3')->where('id', '10-03-03')->countAllResults();
            if ($exists === 0) {
                $db->table('menu_lv3')->insert([
                    'id'       => '10-03-03',
                    'label'    => 'Surat Tugas',
                    'icon'     => 'far fa-circle',
                    'link'     => 'admin/surat/perjalanan-dinas/surat-tugas',
                    'header'   => '10-03',
                    'ordering' => 3,
                ]);
                $this->copyMenuAkses('10-03', '10-03-03');
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv3')) {
            $db->table('menu_lv3')->where('id', '10-03-03')->delete();
            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->where('menu_id', '10-03-03')->delete();
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
