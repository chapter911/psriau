<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarisPinjamPakaiTable extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Buat tabel trn_inventaris_pinjam_pakai jika belum ada
        if (! $db->tableExists('trn_inventaris_pinjam_pakai')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'inventaris_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'nama_peminjam' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'nip_peminjam' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'jabatan_peminjam' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'kontak_peminjam' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'no_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
                'tgl_pinjam' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'tgl_kembali_rencana' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'tgl_kembali_realisasi' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'keperluan' => [
                    'type' => 'TEXT',
                    'null' => false,
                ],
                'kondisi_pinjam' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'baik',
                    'null'       => false,
                ],
                'kondisi_kembali' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                ],
                'kelengkapan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'catatan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'file_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'dipinjam',
                    'null'       => false,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey('inventaris_id');
            $forge->addKey('pegawai_id');
            $forge->addKey('status');
            $forge->createTable('trn_inventaris_pinjam_pakai', true);
        }

        // 2. Registrasi Menu Lv2 "3. Pinjam Pakai Aset" (11-04) & urutkan sekolah jadi ke-4
        if ($db->tableExists('menu_lv2')) {
            // Update urutan menu sekolah
            $db->table('menu_lv2')->where('id', '11-03')->update(['ordering' => 4]);

            // Upsert menu pinjam pakai
            $this->upsertLv2Menu('11-04', '11', 'Pinjam Pakai Aset', 'admin/inventaris/pinjam-pakai', 'fas fa-file-signature', 3);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
            $forge->dropTable('trn_inventaris_pinjam_pakai', true);
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '11-04')->delete();
            $db->table('menu_lv2')->where('id', '11-03')->update(['ordering' => 3]);
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '11-04')->delete();
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
