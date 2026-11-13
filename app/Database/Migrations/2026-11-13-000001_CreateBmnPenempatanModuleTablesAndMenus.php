<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBmnPenempatanModuleTablesAndMenus extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Tabel Master Gedung: mst_gedung
        if (! $db->tableExists('mst_gedung')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'sekolah_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'nama_gedung' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => false,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
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
            $forge->addKey('sekolah_id');
            $forge->addKey('nama_gedung');
            $forge->createTable('mst_gedung', true);
        }

        // 2. Tambah kolom sekolah_id dan gedung_id pada mst_ruangan jika belum ada
        if ($db->tableExists('mst_ruangan')) {
            $ruanganFields = [];
            if (! $db->fieldExists('sekolah_id', 'mst_ruangan')) {
                $ruanganFields['sekolah_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'id',
                ];
            }
            if (! $db->fieldExists('gedung_id', 'mst_ruangan')) {
                $ruanganFields['gedung_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'sekolah_id',
                ];
            }

            if (! empty($ruanganFields)) {
                $forge->addColumn('mst_ruangan', $ruanganFields);
                if (isset($ruanganFields['sekolah_id'])) {
                    $db->query("ALTER TABLE `mst_ruangan` ADD INDEX `idx_ruangan_sekolah` (`sekolah_id`)");
                }
                if (isset($ruanganFields['gedung_id'])) {
                    $db->query("ALTER TABLE `mst_ruangan` ADD INDEX `idx_ruangan_gedung` (`gedung_id`)");
                }
            }
        }

        // 3. Tambah kolom penempatan BMN pada trn_inventaris_satker jika belum ada
        if ($db->tableExists('trn_inventaris_satker')) {
            $invFields = [];
            if (! $db->fieldExists('sekolah_id', 'trn_inventaris_satker')) {
                $invFields['sekolah_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'peruntukan',
                ];
            }
            if (! $db->fieldExists('gedung_id', 'trn_inventaris_satker')) {
                $invFields['gedung_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'sekolah_id',
                ];
            }
            if (! $db->fieldExists('ruangan_id', 'trn_inventaris_satker')) {
                $invFields['ruangan_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'gedung_id',
                ];
            }
            if (! $db->fieldExists('status_penempatan', 'trn_inventaris_satker')) {
                $invFields['status_penempatan'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'belum_ditempatkan',
                    'null'       => false,
                    'after'      => 'ruangan_id',
                ];
            }
            if (! $db->fieldExists('tanggal_penempatan', 'trn_inventaris_satker')) {
                $invFields['tanggal_penempatan'] = [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'status_penempatan',
                ];
            }
            if (! $db->fieldExists('petugas_penempatan', 'trn_inventaris_satker')) {
                $invFields['petugas_penempatan'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'after'      => 'tanggal_penempatan',
                ];
            }

            if (! empty($invFields)) {
                $forge->addColumn('trn_inventaris_satker', $invFields);
                if (isset($invFields['sekolah_id'])) {
                    $db->query("ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_inv_sekolah` (`sekolah_id`)");
                }
                if (isset($invFields['gedung_id'])) {
                    $db->query("ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_inv_gedung` (`gedung_id`)");
                }
                if (isset($invFields['ruangan_id'])) {
                    $db->query("ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_inv_ruangan` (`ruangan_id`)");
                }
                if (isset($invFields['status_penempatan'])) {
                    $db->query("ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_inv_status_penempatan` (`status_penempatan`)");
                }
            }
        }

        // 4. Tabel Riwayat Penempatan & Foto: trn_bmn_penempatan
        if (! $db->tableExists('trn_bmn_penempatan')) {
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
                'sekolah_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'gedung_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'ruangan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'kondisi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'baik',
                    'null'       => false,
                ],
                'catatan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'petugas_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'tanggal_penempatan' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'aktif', // aktif, relokasi
                    'null'       => false,
                ],
                'foto_asli_1' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_asli_2' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_asli_3' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_asli_4' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_wm_1' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_wm_2' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_wm_3' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_wm_4' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
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
            $forge->addKey('sekolah_id');
            $forge->addKey('gedung_id');
            $forge->addKey('ruangan_id');
            $forge->addKey('status');
            $forge->createTable('trn_bmn_penempatan', true);
        }

        // 5. Registrasi Submenu Lv2: 11-09 (Penempatan BMN)
        if ($db->tableExists('menu_lv2')) {
            $this->upsertLv2Menu('11-09', '11', 'Penempatan BMN', 'admin/inventaris/penempatan', 'fas fa-map-marker-alt', 9);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_bmn_penempatan')) {
            $forge->dropTable('trn_bmn_penempatan', true);
        }

        if ($db->tableExists('mst_gedung')) {
            $forge->dropTable('mst_gedung', true);
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')->where('id', '11-09')->delete();
        }

        if ($db->tableExists('menu_akses')) {
            $db->table('menu_akses')->where('menu_id', '11-09')->delete();
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
