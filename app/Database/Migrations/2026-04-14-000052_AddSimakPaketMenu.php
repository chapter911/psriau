<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimakPaketMenu extends Migration
{
    public function up()
    {
        // Directly insert using raw SQL
        $sql = "INSERT IGNORE INTO menu_lv2 (id, label, link, icon, header, ordering) VALUES ('10-01', 'Paket', 'admin/simak/paket', 'far fa-circle', '10', 1)";
        $this->db->query($sql);

        // Grant access to all roles
        if ($this->db->tableExists('menu_akses')) {
            $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

            $sql = "SELECT DISTINCT $roleColumn as role_id FROM menu_akses";
            $roles = $this->db->query($sql)->getResultArray();

            foreach ($roles as $role) {
                $roleId = (int)$role['role_id'];
                if ($roleId > 0) {
                    $insertSql = "INSERT IGNORE INTO menu_akses ($roleColumn, menu_id, FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval) 
                                  VALUES ($roleId, '10-01', 1, 1, 1, 1, 1, 0)";
                    $this->db->query($insertSql);
                }
            }
        }
    }

    public function down()
    {
        $this->db->query("DELETE FROM menu_akses WHERE menu_id = '10-01'");
        $this->db->query("DELETE FROM menu_lv2 WHERE id = '10-01'");
    }
}
