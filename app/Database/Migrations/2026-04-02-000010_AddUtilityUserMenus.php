<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUtilityUserMenus extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $utilityId = $this->findOrCreateUtilityMenu();
        if ($utilityId === null) {
            return;
        }

        $userMenuId = $this->upsertLv2ByLink(
            'admin/utility/user',
            'User',
            'fas fa-user',
            $utilityId
        );

        $userRoleMenuId = $this->upsertLv2ByLink(
            'admin/utility/user-group',
            'User Role',
            'fas fa-user-shield',
            $utilityId
        );

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($userMenuId);
            $this->ensureAksesForMenu($userRoleMenuId);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $targets = ['admin/utility/user', 'admin/utility/user-group'];

        foreach ($targets as $link) {
            $row = $this->db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', strtolower($link))
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                continue;
            }

            $menuId = (string) $row['id'];
            $this->db->table('menu_lv2')->where('id', $menuId)->delete();

            if ($this->db->tableExists('menu_akses')) {
                $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
            }
        }
    }

    private function findOrCreateUtilityMenu(): ?string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', 'utility')
            ->orderBy('ordering', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            return (string) $existing['id'];
        }

        $newId = $this->generateLv1Id();

        $maxRow = $this->db->table('menu_lv1')->selectMax('ordering', 'max_ordering')->get()->getRowArray();
        $nextOrdering = ((int) ($maxRow['max_ordering'] ?? 0)) + 1;

        $this->db->table('menu_lv1')->insert([
            'id' => $newId,
            'label' => 'Utility',
            'link' => '#',
            'icon' => 'fas fa-tools',
            'old_icon' => null,
            'ordering' => $nextOrdering,
        ]);

        return $newId;
    }

    private function upsertLv2ByLink(string $link, string $label, string $icon, string $header): string
    {
        $byLink = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($byLink)) {
            $menuId = (string) $byLink['id'];

            if (strncmp($menuId, $header . '-', strlen($header) + 1) !== 0) {
                $newId = $this->generateLv2Id($header);
                $this->db->table('menu_lv2')->where('id', $menuId)->update([
                    'id' => $newId,
                    'label' => $label,
                    'icon' => $icon,
                    'header' => $header,
                ]);

                if ($this->db->tableExists('menu_akses')) {
                    $this->db->table('menu_akses')->where('menu_id', $menuId)->update(['menu_id' => $newId]);
                }

                return $newId;
            }

            $this->db->table('menu_lv2')->where('id', $menuId)->update([
                'label' => $label,
                'icon' => $icon,
                'header' => $header,
            ]);

            return $menuId;
        }

        $menuId = $this->generateLv2Id($header);

        $maxRow = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();

        $nextOrdering = ((int) ($maxRow['max_ordering'] ?? 0)) + 1;

        $this->db->table('menu_lv2')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'header' => $header,
            'ordering' => $nextOrdering,
        ]);

        return $menuId;
    }

    private function generateLv1Id(): string
    {
        $rows = $this->db->table('menu_lv1')
            ->select('id')
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            if (preg_match('/(\d+)$/', (string) ($row['id'] ?? ''), $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function generateLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            $prefix = $header . '-';
            if (strpos($candidateId, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($candidateId, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return $header . '-' . str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ensureAksesForMenu(string $menuId): void
    {
        if ($menuId === '') {
            return;
        }

        $groupRows = $this->db->table('menu_akses')
            ->select('group_id')
            ->distinct()
            ->get()
            ->getResultArray();

        if ($groupRows === []) {
            $groupRows = [['group_id' => 1]];
        }

        foreach ($groupRows as $row) {
            $groupId = (int) ($row['group_id'] ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $exists = $this->db->table('menu_akses')
                ->where('group_id', $groupId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isAdminGroup = $groupId === 1;
            $this->db->table('menu_akses')->insert([
                'group_id' => $groupId,
                'menu_id' => $menuId,
                'FiturAdd' => $isAdminGroup ? 1 : 0,
                'FiturEdit' => $isAdminGroup ? 1 : 0,
                'FiturDelete' => $isAdminGroup ? 1 : 0,
                'FiturExport' => $isAdminGroup ? 1 : 0,
                'FiturImport' => $isAdminGroup ? 1 : 0,
                'FiturApproval' => $isAdminGroup ? 1 : 0,
            ]);
        }
    }
}
