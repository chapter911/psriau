<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPeruntukanAndRestructureInventarisMenu extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Tambah kolom peruntukan di trn_inventaris_satker (default 'kantor')
        if ($db->tableExists('trn_inventaris_satker') && ! $db->fieldExists('peruntukan', 'trn_inventaris_satker')) {
            $forge->addColumn('trn_inventaris_satker', [
                'peruntukan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'kantor',
                    'null'       => false,
                    'after'      => 'kondisi',
                ],
            ]);

            // Buat index untuk pencarian/filtering cepat
            $db->query("ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_peruntukan` (`peruntukan`)");
        }

        // Pastikan seluruh data eksisting terisi 'kantor' jika ada yang NULL atau kosong
        if ($db->tableExists('trn_inventaris_satker')) {
            $db->table('trn_inventaris_satker')
                ->where('peruntukan IS NULL', null, false)
                ->orWhere('peruntukan', '')
                ->update(['peruntukan' => 'kantor']);
        }

        // 2. Restrukturisasi Menu di Bawah Inventarisasi (id: 11)
        if ($db->tableExists('menu_lv2') && $db->tableExists('menu_lv3')) {
            // Hapus Lv3 untuk 11-01
            $db->table('menu_lv3')->whereIn('id', ['11-01-01', '11-01-02'])->delete();
            if ($db->tableExists('menu_akses')) {
                $db->table('menu_akses')->whereIn('menu_id', ['11-01-01', '11-01-02'])->delete();
            }

            // Hapus menu lv2 11-03 lama jika ada bentrok
            $db->table('menu_lv2')->where('id', '11-03')->delete();

            // Menu 1: Daftar Barang (admin/inventaris/barang)
            $this->upsertLv2Menu('11-01', '11', 'Daftar Barang', 'admin/inventaris/barang', 'fas fa-boxes', 1);

            // Menu 2: Inventaris Kantor (admin/inventaris/dbr)
            $this->upsertLv2Menu('11-02', '11', 'Inventaris Kantor', 'admin/inventaris/dbr', 'fas fa-building', 2);

            // Menu 3: Inventaris Sekolah (admin/inventaris/sekolah)
            $this->upsertLv2Menu('11-03', '11', 'Inventaris Sekolah', 'admin/inventaris/sekolah', 'fas fa-school', 3);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // Drop kolom peruntukan
        if ($db->tableExists('trn_inventaris_satker') && $db->fieldExists('peruntukan', 'trn_inventaris_satker')) {
            $forge->dropColumn('trn_inventaris_satker', 'peruntukan');
        }

        // Kembalikan menu
        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '11-01')->update([
                'label'    => 'Inventaris Satker',
                'link'     => null,
                'icon'     => 'fas fa-building',
                'ordering' => 1,
            ]);

            $db->table('menu_lv2')->where('id', '11-02')->update([
                'label'    => 'Inventaris Sekolah',
                'link'     => 'admin/inventaris/sekolah',
                'icon'     => 'far fa-circle',
                'ordering' => 2,
            ]);

            $db->table('menu_lv2')->where('id', '11-03')->delete();
        }

        if ($db->tableExists('menu_lv3')) {
            $this->upsertLv3Menu('11-01-01', '11-01', 'Daftar Barang Inventaris', 'admin/inventaris/satker', 'far fa-circle', 1);
            $this->upsertLv3Menu('11-01-02', '11-01', 'Daftar Ruangan', 'admin/inventaris/dbr', 'far fa-circle', 2);
        }
    }

    private function upsertLv2Menu(string $id, string $header, string $label, string $link, string $icon, int $ordering): void
    {
        $db = $this->db;
        $existing = $db->table('menu_lv2')->where('id', $id)->get()->getRowArray();

        if ($existing) {
            $db->table('menu_lv2')->where('id', $id)->update([
                'header'   => $header,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        } else {
            $db->table('menu_lv2')->insert([
                'id'       => $id,
                'header'   => $header,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        }

        $this->ensureMenuAksesForMenuId($id);
    }

    private function upsertLv3Menu(string $id, string $header, string $label, string $link, string $icon, int $ordering): void
    {
        $db = $this->db;
        $existing = $db->table('menu_lv3')->where('id', $id)->get()->getRowArray();

        if ($existing) {
            $db->table('menu_lv3')->where('id', $id)->update([
                'header'   => $header,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        } else {
            $db->table('menu_lv3')->insert([
                'id'       => $id,
                'header'   => $header,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        }

        $this->ensureMenuAksesForMenuId($id);
    }

    private function ensureMenuAksesForMenuId(string $menuId): void
    {
        $db = $this->db;

        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $roleRows = $db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if (empty($roleRows)) {
            $roleRows = [[$roleColumn => 1], [$roleColumn => 2]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = (int) $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isSuperOrAdmin = in_array($roleId, [1, 2], true);

            $db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => $isSuperOrAdmin ? 1 : 0,
                'FiturEdit'     => $isSuperOrAdmin ? 1 : 0,
                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                'FiturExport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturApproval' => $isSuperOrAdmin ? 1 : 0,
            ]);
        }
    }
}
