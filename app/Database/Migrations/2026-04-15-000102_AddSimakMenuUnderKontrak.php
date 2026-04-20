<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimakMenuUnderKontrak extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        // Find or create Kontrak menu
        $kontrakId = $this->findOrCreateLv1Menu('Kontrak', 'fas fa-file-signature');
        if ($kontrakId === null) {
            return;
        }

        // Add Simak submenu under Kontrak
        $simakId = $this->upsertLv2ByLink(
            'admin/kontrak/simak',
            'Simak',
            'far fa-circle',
            $kontrakId
        );

        // Grant access to all groups
        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($kontrakId);
            $this->ensureAksesForMenu($simakId);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        // Find and delete Simak menu
        $simakRow = $this->db->table('menu_lv2')
            ->select('id, header')
            ->where('LOWER(link)', 'admin/kontrak/simak')
            ->get()
            ->getRowArray();

        if (is_array($simakRow)) {
            $simakId = (string) $simakRow['id'];
            $headerId = (string) ($simakRow['header'] ?? '');

            $this->db->table('menu_lv2')->where('id', $simakId)->delete();

            if ($this->db->tableExists('menu_akses')) {
                $this->db->table('menu_akses')->where('menu_id', $simakId)->delete();
            }
        }
    }

    private function findOrCreateLv1Menu(string $label, string $icon): ?string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
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
            'label' => $label,
            'link' => '#',
            'icon' => $icon,
            'old_icon' => null,
            'ordering' => $nextOrdering,
        ]);

        return $newId;
    }

    private function upsertLv2ByLink(string $link, string $label, string $icon, string $header): string
    {
        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            $menuId = (string) $existing['id'];
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
        $rows = $this->db->table('menu_lv1')->select('id')->get()->getResultArray();
        $max = 0;

        foreach ($rows as $row) {
            if (preg_match('/^(\d{2})$/', (string) ($row['id'] ?? ''), $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    private function generateLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $max = 0;
        $prefix = $header . '-';

        foreach ($rows as $row) {
            $candidate = (string) ($row['id'] ?? '');
            if (strpos($candidate, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($candidate, strlen($prefix));
            if (preg_match('/^(\d{2})$/', $suffix, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $header . '-' . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ensureAksesForMenu(string $menuId): void
    {
        if ($menuId === '') {
            return;
        }

        // Determine which column to use (role_id or group_id)
        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $groupRows = $this->db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($groupRows === []) {
            $groupRows = [[$roleColumn => 1]];
        }

        foreach ($groupRows as $row) {
            $groupId = (int) ($row[$roleColumn] ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $existing = $this->db->table('menu_akses')
                ->where('menu_id', $menuId)
                ->where($roleColumn, $groupId)
                ->countAllResults();

            if ($existing === 0) {
                $this->db->table('menu_akses')->insert([
                    'menu_id' => $menuId,
                    $roleColumn => $groupId,
                ]);
            }
        }
    }
}
