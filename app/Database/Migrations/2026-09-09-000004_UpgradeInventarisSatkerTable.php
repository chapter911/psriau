<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeInventarisSatkerTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_inventaris_satker')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('ruangan_id', 'trn_inventaris_satker')) {
            $fields['ruangan_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ];
        }

        if (! $this->db->fieldExists('nilai_perolehan', 'trn_inventaris_satker')) {
            $fields['nilai_perolehan'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'jumlah',
            ];
        }

        if (! $this->db->fieldExists('nilai_buku', 'trn_inventaris_satker')) {
            $fields['nilai_buku'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'nilai_perolehan',
            ];
        }

        if (! $this->db->fieldExists('status_bmn', 'trn_inventaris_satker')) {
            $fields['status_bmn'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Aktif',
                'after'      => 'kondisi',
            ];
        }

        if (! $this->db->fieldExists('no_psp', 'trn_inventaris_satker')) {
            $fields['no_psp'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'status_bmn',
            ];
        }

        if (! $this->db->fieldExists('tgl_perolehan', 'trn_inventaris_satker')) {
            $fields['tgl_perolehan'] = [
                'type' => 'DATE',
                'null' => true,
                'after' => 'tahun_perolehan',
            ];
        }

        if (! $this->db->fieldExists('merk', 'trn_inventaris_satker')) {
            $fields['merk'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'kategori',
            ];
        }

        if (! $this->db->fieldExists('tipe', 'trn_inventaris_satker')) {
            $fields['tipe'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'merk',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('trn_inventaris_satker', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_inventaris_satker')) {
            return;
        }

        $dropColumns = ['ruangan_id', 'nilai_perolehan', 'nilai_buku', 'status_bmn', 'no_psp', 'tgl_perolehan', 'merk', 'tipe'];
        foreach ($dropColumns as $col) {
            if ($this->db->fieldExists($col, 'trn_inventaris_satker')) {
                $this->forge->dropColumn('trn_inventaris_satker', $col);
            }
        }
    }
}
