<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertMasterPaketMenu extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // Find menu_lv1 id where label = 'master'
        $masterLv1 = $db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'master')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! is_array($masterLv1) || ! isset($masterLv1['id'])) {
            return;
        }

        $masterHeader = (string) $masterLv1['id'];

        // Check if menu already exists
        $existing = $db->table('menu_lv2')
            ->select('id')
            ->where('header', $masterHeader)
            ->where('LOWER(label)', 'paket')
            ->get()
            ->getRowArray();

        if (is_array($existing) && isset($existing['id'])) {
            return;
        }

        // Generate next ID under master-XX pattern
        $rows = $db->table('menu_lv2')
            ->select('id')
            ->where('header', $masterHeader)
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            $prefix = $masterHeader . '-';
            if (strpos($candidateId, $prefix) !== 0) {
                continue;
            }
            $suffix = substr($candidateId, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        $menuId = $masterHeader . '-' . str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);

        // Get next ordering
        $orderingRow = $db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $masterHeader)
            ->get()
            ->getRowArray();
        $ordering = ((int) ($orderingRow['max_ordering'] ?? 0)) + 1;

        // Insert menu
        $db->table('menu_lv2')->insert([
            'id' => $menuId,
            'label' => 'Paket',
            'link' => 'admin/master/paket',
            'icon' => 'far fa-circle',
            'header' => $masterHeader,
            'ordering' => $ordering,
        ]);

        // Insert menu access for Admin (role_id = 1)
        $db->table('menu_akses')->insert([
            'role_id' => 1,
            'menu_id' => $menuId,
            'FiturAdd' => 1,
            'FiturEdit' => 1,
            'FiturDelete' => 0,
            'FiturExport' => 0,
            'FiturImport' => 0,
            'FiturApproval' => 0,
        ]);
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_akses')) {
            return;
        }

        $row = $db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(label)', 'paket')
            ->where('LOWER(link)', 'admin/master/paket')
            ->get()
            ->getRowArray();

        if (is_array($row) && isset($row['id'])) {
            $menuId = (string) $row['id'];
            $db->table('menu_lv2')->where('id', $menuId)->delete();
            $db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }
    }
}
