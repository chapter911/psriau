<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddKopSuratModule extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('kop_surat')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                ],
                'year_start' => [
                    'type' => 'SMALLINT',
                    'constraint' => 4,
                ],
                'year_end' => [
                    'type' => 'SMALLINT',
                    'constraint' => 4,
                    'null' => true,
                ],
                'image_url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
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
            $this->forge->createTable('kop_surat');
        }

        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $masterId = $this->findOrCreateLv1Menu('Master', 'fas fa-boxes');
        if ($masterId === null) {
            return;
        }

        $menuId = $this->upsertLv2ByLink(
            'admin/master/kop-surat',
            'Kop Surat',
            'far fa-image',
            $masterId
        );

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($masterId);
            $this->ensureAksesForMenu($menuId);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('menu_lv2')) {
            $menuRow = $this->db->table('menu_lv2')
                ->select('id, header')
                ->where('LOWER(link)', 'admin/master/kop-surat')
                ->get()
                ->getRowArray();

            if (is_array($menuRow)) {
                $menuId = (string) $menuRow['id'];
                $headerId = (string) ($menuRow['header'] ?? '');

                if ($this->db->tableExists('menu_akses')) {
                    $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
                }

                $this->db->table('menu_lv2')->where('id', $menuId)->delete();

                if ($headerId !== '' && $this->db->tableExists('menu_lv1')) {
                    $remaining = $this->db->table('menu_lv2')
                        ->where('header', $headerId)
                        ->countAllResults();

                    if ($remaining === 0) {
                        $this->db->table('menu_lv1')->where('id', $headerId)->delete();
                    }
                }
            }
        }

        if ($this->db->tableExists('kop_surat')) {
            $this->forge->dropTable('kop_surat', true);
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
        $prefix = $header . '-';

        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
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
