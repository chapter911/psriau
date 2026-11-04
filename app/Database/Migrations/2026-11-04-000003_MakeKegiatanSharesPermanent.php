<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeKegiatanSharesPermanent extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('kegiatan_lapangan_shares')) {
            // Ubah seluruh link berbagi yang sudah ada menjadi permanen (tanpa tanggal kedaluwarsa)
            $db->table('kegiatan_lapangan_shares')
                ->where('expires_at IS NOT NULL', null, false)
                ->update(['expires_at' => null]);
        }
    }

    public function down()
    {
        // Tetap dibiarkan nullable
    }
}

