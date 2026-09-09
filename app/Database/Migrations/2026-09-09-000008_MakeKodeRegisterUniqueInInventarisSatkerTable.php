<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeKodeRegisterUniqueInInventarisSatkerTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_inventaris_satker')) {
            return;
        }

        if ($this->db->fieldExists('kode_register', 'trn_inventaris_satker')) {
            // Pastikan nilai kosong dikonversi menjadi NULL murni agar MySQL UNIQUE index mengizinkan multiple nulls
            $this->db->query("UPDATE `trn_inventaris_satker` SET `kode_register` = NULL WHERE `kode_register` = '' OR TRIM(`kode_register`) = ''");

            // Periksa index yang ada pada kolom kode_register
            $existingIndexes = $this->db->query("SHOW INDEX FROM `trn_inventaris_satker` WHERE Column_name = 'kode_register'")->getResultArray();
            $hasUniq = false;
            foreach ($existingIndexes as $idx) {
                if ($idx['Key_name'] === 'uniq_kode_register') {
                    $hasUniq = true;
                } elseif ($idx['Key_name'] === 'idx_kode_register') {
                    $this->db->query("ALTER TABLE `trn_inventaris_satker` DROP INDEX `idx_kode_register`");
                }
            }

            if (! $hasUniq) {
                $this->db->query("ALTER TABLE `trn_inventaris_satker` ADD UNIQUE INDEX `uniq_kode_register` (`kode_register`)");
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_inventaris_satker') && $this->db->fieldExists('kode_register', 'trn_inventaris_satker')) {
            $existingIndexes = $this->db->query("SHOW INDEX FROM `trn_inventaris_satker` WHERE Column_name = 'kode_register'")->getResultArray();
            foreach ($existingIndexes as $idx) {
                if ($idx['Key_name'] === 'uniq_kode_register') {
                    $this->db->query("ALTER TABLE `trn_inventaris_satker` DROP INDEX `uniq_kode_register`");
                }
            }
            $this->db->query("ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_kode_register` (`kode_register`)");
        }
    }
}
