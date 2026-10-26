<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarisPemeliharaanTables extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Tabel Utama: trn_inventaris_pemeliharaan
        if (! $db->tableExists('trn_inventaris_pemeliharaan')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'no_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => false,
                ],
                'judul_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                    'default'    => 'SURAT KETERANGAN PEMELIHARAAN PERALATAN & MESIN TIK',
                ],
                'jenis_pemeliharaan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                    'default'    => 'TIK',
                ],
                'tanggal_surat' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'tempat_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                    'default'    => 'Pekanbaru',
                ],
                'nama_kasatker' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'nip_kasatker' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ],
                'jabatan_kasatker' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'unit_kerja' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                    'default'    => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                ],
                'kop_surat_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'alasan_poin_a' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'alasan_poin_b' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'total_item' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                    'default'    => 0,
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
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey('no_surat');
            $forge->addKey('tanggal_surat');
            $forge->createTable('trn_inventaris_pemeliharaan', true);
        }

        // 2. Tabel Item Barang: trn_inventaris_pemeliharaan_item
        if (! $db->tableExists('trn_inventaris_pemeliharaan_item')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pemeliharaan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'inventaris_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'jenis_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                    'default'    => 'MESIN PERALATAN NON TIK',
                ],
                'kode_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ],
                'nama_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'merk_tipe' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'nup' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ],
                'kondisi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                    'default'    => 'Baik',
                ],
                'tahun_perolehan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                ],
                'kelompok_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
                'urutan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                    'default'    => 0,
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

            $forge->addKey('id', true);
            $forge->addKey('pemeliharaan_id');
            $forge->addKey('inventaris_id');
            $forge->addKey('kelompok_key');
            $forge->createTable('trn_inventaris_pemeliharaan_item', true);
        }

        // 3. Tabel Foto Lampiran: trn_inventaris_pemeliharaan_foto
        if (! $db->tableExists('trn_inventaris_pemeliharaan_foto')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pemeliharaan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'kelompok_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
                'nama_barang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'nup_gabungan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'foto_depan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_atas' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_bawah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_belakang' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'urutan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                    'default'    => 0,
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

            $forge->addKey('id', true);
            $forge->addKey('pemeliharaan_id');
            $forge->addKey('kelompok_key');
            $forge->createTable('trn_inventaris_pemeliharaan_foto', true);
        }

        // 4. Registrasi Submenu Lv2: 11-08 (Pemeliharaan Aset)
        if ($db->tableExists('menu_lv2')) {
            $this->upsertLv2Menu('11-08', '11', 'Pemeliharaan Aset', 'admin/inventaris/pemeliharaan', 'fas fa-wrench', 8);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pemeliharaan_foto')) {
            $forge->dropTable('trn_inventaris_pemeliharaan_foto', true);
        }

        if ($db->tableExists('trn_inventaris_pemeliharaan_item')) {
            $forge->dropTable('trn_inventaris_pemeliharaan_item', true);
        }

        if ($db->tableExists('trn_inventaris_pemeliharaan')) {
            $forge->dropTable('trn_inventaris_pemeliharaan', true);
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '11-08')->delete();
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '11-08')->delete();
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
