<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureSimakMenuAccess extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $paketId = $this->findOrCreateLv1Menu('Paket', 'fas fa-box');
        if ($paketId === null) {
            return;
        }

        $simakId = $this->upsertLv2ByLink('admin/paket/simak', 'SIMAK', 'far fa-file-excel', $paketId);

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($paketId);
            $this->ensureAksesForMenu($simakId);
        }
    }

    public function down()
    {
        // No-op to avoid removing user-managed access/menu configuration.
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

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $roleIds = $this->resolveRoleIds($roleColumn);

        foreach ($roleIds as $roleId) {
            $existing = $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->get()
                ->getRowArray();

            $isAdminRole = $roleId === 1;

            if (! is_array($existing)) {
                $this->db->table('menu_akses')->insert([
                    $roleColumn => $roleId,
                    'menu_id' => $menuId,
                    'FiturAdd' => $isAdminRole ? 1 : 0,
                    'FiturEdit' => $isAdminRole ? 1 : 0,
                    'FiturDelete' => 0,
                    'FiturExport' => $isAdminRole ? 1 : 0,
                    'FiturImport' => $isAdminRole ? 1 : 0,
                    'FiturApproval' => 0,
                ]);

                continue;
            }

            $update = [];
            foreach (['FiturAdd', 'FiturEdit', 'FiturDelete', 'FiturExport', 'FiturImport', 'FiturApproval'] as $field) {
                if (! array_key_exists($field, $existing) || $existing[$field] === null) {
                    $update[$field] = 0;
                }
            }

            if ($update !== []) {
                $this->db->table('menu_akses')
                    ->where($roleColumn, $roleId)
                    ->where('menu_id', $menuId)
                    ->update($update);
            }
        }
    }

    /**
     * @return int[]
     */
    private function resolveRoleIds(string $roleColumn): array
    {
        $roleIds = [];

        if ($this->db->tableExists('access_roles')) {
            $roleRows = $this->db->table('access_roles')->select('id')->get()->getResultArray();
            foreach ($roleRows as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id > 0) {
                    $roleIds[] = $id;
                }
            }
        }

        if ($roleIds === []) {
            $roleRows = $this->db->table('menu_akses')
                ->select($roleColumn)
                ->distinct()
                ->get()
                ->getResultArray();

            foreach ($roleRows as $row) {
                $id = (int) ($row[$roleColumn] ?? 0);
                if ($id > 0) {
                    $roleIds[] = $id;
                }
            }
        }

        if ($roleIds === []) {
            $roleIds = [1];
        }

        $roleIds = array_values(array_unique($roleIds));
        sort($roleIds);

        return $roleIds;
    }
}
