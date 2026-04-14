<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSimakMenuStructure extends Migration
{
    public function up()
    {
        // Create simak_paket table
        if (! $this->db->tableExists('simak_paket')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'nama_paket' => ['type' => 'VARCHAR', 'constraint' => 255],
                'tahun_anggaran' => ['type' => 'INT'],
                'penyedia' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'nomor_kontrak' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'nilai_kontrak' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'null' => true],
                'add_kontrak' => ['type' => 'TEXT', 'null' => true],
                'tahapan_pekerjaan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'tanggal_pemeriksaan' => ['type' => 'DATE', 'null' => true],
                'satker' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'ppk' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'nip' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'created_by' => ['type' => 'VARCHAR', 'constraint' => 50],
                'created_date' => ['type' => 'DATETIME', 'default' => new \RawSql('CURRENT_TIMESTAMP')],
                'updated_by' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addPrimaryKey('id');
            $this->forge->addKey(['tahun_anggaran', 'created_date']);
            $this->forge->createTable('simak_paket', true);
        }

        // Create/update menus
        if ($this->db->tableExists('menu_lv1') && $this->db->tableExists('menu_lv2')) {
            // Get the max ordering for menu_lv1
            $maxOrdering = $this->db->table('menu_lv1')
                ->selectMax('ordering', 'max_ordering')
                ->get()
                ->getRowArray();
            $nextOrdering = ((int) ($maxOrdering['max_ordering'] ?? 0)) + 1;

            // Create Simak menu in menu_lv1 (use first available numeric ID)
            $simakId = $this->getNextMenuId();

            // Check if Simak menu already exists
            $existing = $this->db->table('menu_lv1')
                ->where('LOWER(label)', 'simak')
                ->countAllResults();

            if ($existing === 0) {
                $this->db->table('menu_lv1')->insert([
                    'id' => $simakId,
                    'label' => 'Simak',
                    'link' => '#',
                    'icon' => 'fas fa-list',
                    'old_icon' => null,
                    'ordering' => $nextOrdering,
                ]);
            } else {
                // Get existing ID for use in menu_lv2
                $existing = $this->db->table('menu_lv1')
                    ->select('id')
                    ->where('LOWER(label)', 'simak')
                    ->get()
                    ->getRowArray();
                $simakId = (string) ($existing['id'] ?? $simakId);
            }

            // Create Paket submenu in menu_lv2
            $paketId = $simakId . '-01';

            $existing = $this->db->table('menu_lv2')
                ->where('LOWER(link)', 'admin/simak/paket')
                ->countAllResults();

            if ($existing === 0) {
                $this->db->table('menu_lv2')->insert([
                    'id' => $paketId,
                    'label' => 'Paket',
                    'link' => 'admin/simak/paket',
                    'icon' => 'far fa-circle',
                    'header' => $simakId,
                    'ordering' => 1,
                ]);
            }
        }

        // Grant access to all roles
        if ($this->db->tableExists('menu_akses') && $this->db->tableExists('menu_lv2')) {
            $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

            // Get Paket menu_id
            $paketMenu = $this->db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/simak/paket')
                ->get()
                ->getRowArray();

            if ($paketMenu) {
                $paketId = (string) $paketMenu['id'];

                // Get all distinct roles
                $roleRows = $this->db->table('menu_akses')
                    ->select($roleColumn)
                    ->distinct()
                    ->get()
                    ->getResultArray();

                if ($roleRows !== []) {
                    foreach ($roleRows as $roleRow) {
                        $roleId = (int) ($roleRow[$roleColumn] ?? 0);
                        if ($roleId <= 0) {
                            continue;
                        }

                        // Insert menu access for Paket
                        $exists = $this->db->table('menu_akses')
                            ->where($roleColumn, $roleId)
                            ->where('menu_id', $paketId)
                            ->countAllResults();

                        if ($exists === 0) {
                            $this->db->table('menu_akses')->insert([
                                $roleColumn => $roleId,
                                'menu_id' => $paketId,
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
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')
                ->whereIn('menu_id', [
                    $this->db->table('menu_lv2')
                        ->select('id')
                        ->where('LOWER(link)', 'admin/simak/paket'),
                ])
                ->delete();
        }

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

        if ($this->db->tableExists('simak_paket')) {
            $this->forge->dropTable('simak_paket', true);
        }
    }

    private function getNextMenuId(): string
    {
        $rows = $this->db->table('menu_lv1')->select('id')->get()->getResultArray();
        $maxSequence = 0;

        foreach ($rows as $row) {
            if (preg_match('/^(\d+)$/', (string) ($row['id'] ?? ''), $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return (string) ($maxSequence + 1);
    }
}
