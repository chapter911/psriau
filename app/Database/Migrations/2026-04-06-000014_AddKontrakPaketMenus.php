<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKontrakPaketMenus extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $kontrakId = $this->findOrCreateLv1Menu('Kontrak', 'fas fa-file-signature');
        if ($kontrakId === null) {
            return;
        }

        $paketId = $this->upsertLv2ByLink(
            'admin/kontrak/paket',
            'Paket',
            'far fa-circle',
            $kontrakId
        );

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($kontrakId);
            $this->ensureAksesForMenu($paketId);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $paketRow = $this->db->table('menu_lv2')
            ->select('id, header')
            ->where('LOWER(link)', 'admin/kontrak/paket')
            ->get()
            ->getRowArray();

        if (is_array($paketRow)) {
            $paketId = (string) $paketRow['id'];
            $headerId = (string) ($paketRow['header'] ?? '');

            $this->db->table('menu_lv2')->where('id', $paketId)->delete();

            if ($this->db->tableExists('menu_akses')) {
                $this->db->table('menu_akses')->where('menu_id', $paketId)->delete();
            }

            if ($headerId !== '') {
                $remainingChildren = $this->db->table('menu_lv2')
                    ->where('header', $headerId)
                    ->countAllResults();

                if ($remainingChildren === 0) {
                    $this->db->table('menu_lv1')->where('id', $headerId)->delete();

                    if ($this->db->tableExists('menu_akses')) {
                        $this->db->table('menu_akses')->where('menu_id', $headerId)->delete();
                    }
                }
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

            $isAdmin = $groupId === 1;
            $this->db->table('menu_akses')->insert([
                'group_id' => $groupId,
                'menu_id' => $menuId,
                'FiturAdd' => $isAdmin ? 1 : 0,
                'FiturEdit' => $isAdmin ? 1 : 0,
                'FiturDelete' => $isAdmin ? 1 : 0,
                'FiturExport' => $isAdmin ? 1 : 0,
                'FiturImport' => $isAdmin ? 1 : 0,
                'FiturApproval' => $isAdmin ? 1 : 0,
            ]);
        }
    }
}
