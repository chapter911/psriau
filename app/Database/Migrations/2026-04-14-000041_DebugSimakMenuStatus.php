<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DebugSimakMenuStatus extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        $output = "=== SIMAK DEBUG ===\n";

        // 1. Check super_administrator role
        $superAdminRole = $this->db->table('access_roles')
            ->select('id, label, role_key')
            ->where('role_key', 'super_administrator')
            ->get()
            ->getRowArray();

        $output .= "\n1. Super Administrator Role:\n";
        if ($superAdminRole) {
            $output .= "   ID: " . $superAdminRole['id'] . "\n";
            $output .= "   Label: " . $superAdminRole['label'] . "\n";
            $superAdminId = (int) $superAdminRole['id'];
        } else {
            $output .= "   NOT FOUND\n";
            $superAdminId = null;
        }

        // 2. Check Paket menu
        $paketMenu = $this->db->table('menu_lv1')
            ->select('id, label')
            ->where('LOWER(label)', strtolower('Paket'))
            ->get()
            ->getRowArray();

        $output .= "\n2. Paket Menu (menu_lv1):\n";
        if ($paketMenu) {
            $output .= "   ID: " . $paketMenu['id'] . "\n";
            $paketId = (string) $paketMenu['id'];
        } else {
            $output .= "   NOT FOUND\n";
            $paketId = null;
        }

        // 3. Check SIMAK menu
        $simakMenu = $this->db->table('menu_lv2')
            ->select('id, label, header, link')
            ->where('LOWER(label)', strtolower('SIMAK'))
            ->get()
            ->getRowArray();

        $output .= "\n3. SIMAK Menu (menu_lv2):\n";
        if ($simakMenu) {
            $output .= "   ID: " . $simakMenu['id'] . "\n";
            $output .= "   Header: " . $simakMenu['header'] . "\n";
            $output .= "   Link: " . $simakMenu['link'] . "\n";
            $simakId = (string) $simakMenu['id'];
        } else {
            $output .= "   NOT FOUND\n";
            $simakId = null;
        }

        // 4. Check menu_akses for super_administrator
        if ($superAdminId && $simakId) {
            $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
            $akses = $this->db->table('menu_akses')
                ->select('*')
                ->where($roleColumn, $superAdminId)
                ->where('menu_id', $simakId)
                ->get()
                ->getRowArray();

            $output .= "\n4. Menu Access (menu_akses) - SIMAK + Super Admin (role_id=$superAdminId):\n";
            if ($akses) {
                $output .= "   EXISTS - FiturAdd: " . ($akses['FiturAdd'] ?? '-') . "\n";
            } else {
                $output .= "   NOT FOUND\n";

                // Show all menu_akses for this role
                $output .= "\n   All menu_akses for super_administrator (role_id=$superAdminId):\n";
                $allAkses = $this->db->table('menu_akses')
                    ->select('menu_id')
                    ->where($roleColumn, $superAdminId)
                    ->get()
                    ->getResultArray();

                if ($allAkses) {
                    foreach ($allAkses as $row) {
                        $output .= "      - " . $row['menu_id'] . "\n";
                    }
                } else {
                    $output .= "      (no entries found)\n";
                }
            }
        }

        // 5. Check all roles in access_roles
        $output .= "\n5. All Roles in access_roles table:\n";
        $allRoles = $this->db->table('access_roles')
            ->select('id, label, role_key')
            ->orderBy('id')
            ->get()
            ->getResultArray();

        if ($allRoles) {
            foreach ($allRoles as $role) {
                $output .= "   ID:" . str_pad((string) $role['id'], 3, ' ', STR_PAD_LEFT) . " | Key: " . str_pad($role['role_key'], 25, ' ') . " | Label: " . $role['label'] . "\n";
            }
        } else {
            $output .= "   (no roles found)\n";
        }

        // 6. Count menu_akses entries for SIMAK and Paket
        if ($paketId || $simakId) {
            $output .= "\n6. Menu Access entries for Paket/SIMAK menus:\n";
            $menuIds = [];
            if ($paketId) $menuIds[] = $paketId;
            if ($simakId) $menuIds[] = $simakId;

            $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
            $aksesEntries = $this->db->table('menu_akses')
                ->select($roleColumn . ' as role_id, menu_id, count(*) as cnt')
                ->whereIn('menu_id', $menuIds)
                ->groupBy('menu_id, ' . $roleColumn)
                ->get()
                ->getResultArray();

            if ($aksesEntries) {
                foreach ($aksesEntries as $entry) {
                    $output .= "   Role:" . str_pad((string) $entry['role_id'], 3, ' ', STR_PAD_LEFT) . " | Menu: " . str_pad($entry['menu_id'], 10, ' ') . " | Count: " . $entry['cnt'] . "\n";
                }
            } else {
                $output .= "   (no entries found)\n";
            }
        }

        $this->db->enableForeignKeyChecks();

        throw new \Exception($output);
    }

    public function down()
    {
        // No-op
    }
}
