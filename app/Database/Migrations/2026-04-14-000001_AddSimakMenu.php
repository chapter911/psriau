<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimakMenu extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $simakId = $this->findOrCreateLv1Menu('Simak', 'fas fa-list');
        if ($simakId === null) {
            return;
        }

        $paketId = $this->upsertLv2ByLink(
            'admin/simak/paket',
            'Paket',
            'far fa-circle',
            $simakId
        );

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($simakId);
            $this->ensureAksesForMenu($paketId);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('menu_lv2')) {
            $this->db->table('menu_lv2')
                ->where('LOWER(link)', 'admin/simak/paket')
                ->delete();
        }

        if ($this->db->tableExists('menu_lv1')) {
            $this->db->table('menu_lv1')
                ->where('LOWER(label)', 'simak')
                ->delete();
        }

        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')
                ->where('menu_id', 'simak')
                ->orWhere('menu_id', 'simak-01')
                ->delete();
        }
    }

    private function findOrCreateLv1Menu(string $label, string $icon): ?string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            return (string) $existing['id'];
        }

        $menuId = $this->generateLv1Id();
        $maxOrdering = $this->db->table('menu_lv1')
            ->selectMax('ordering', 'max_ordering')
            ->get()
            ->getRowArray();

        $nextOrdering = ((int) ($maxOrdering['max_ordering'] ?? 0)) + 1;

        $this->db->table('menu_lv1')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => '#',
            'icon' => $icon,
            'old_icon' => null,
            'ordering' => $nextOrdering,
        ]);

        return $menuId;
    }

    private function upsertLv2ByLink(string $link, string $label, string $icon, string $header): string
    {
        $link = ltrim((string) $link, '/');
        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->orderBy('id', 'ASC')
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
        $maxSequence = 0;

        foreach ($rows as $row) {
            if (preg_match('/^(\d+)$/', (string) ($row['id'] ?? ''), $matches)) {
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
            $id = (string) ($row['id'] ?? '');
            if (preg_match('/^' . $header . '-(\d+)$/', $id, $matches)) {
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

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $roleRows = $this->db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($roleRows === []) {
            $roleRows = [[$roleColumn => 1]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            // Grant full access to all roles
            $this->db->table('menu_akses')->insert([
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => 1,
                'FiturEdit' => 1,
                'FiturDelete' => 1,
                'FiturExport' => 1,
                'FiturImport' => 1,
                'FiturApproval' => 0,
            ]);
        }
    }
}
