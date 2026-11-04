<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class UpdateKegiatanMenuAndCategory extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = Database::forge();

        // 1. Tambah kolom kategori pada tabel kegiatan_lapangan jika belum ada
        if ($db->tableExists('kegiatan_lapangan')) {
            if (! $db->fieldExists('kategori', 'kegiatan_lapangan')) {
                $forge->addColumn('kegiatan_lapangan', [
                    'kategori' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'default'    => 'Kegiatan Lapangan',
                        'null'       => true,
                        'after'      => 'title',
                    ],
                ]);
            }

            // Update baris yang sudah ada agar memiliki kategori default 'Kegiatan Lapangan'
            $db->table('kegiatan_lapangan')
                ->groupStart()
                    ->where('kategori IS NULL', null, false)
                    ->orWhere('kategori', '')
                ->groupEnd()
                ->update(['kategori' => 'Kegiatan Lapangan']);
        }

        // 2. Perbarui label menu dari "Kegiatan Lapangan" menjadi "Kegiatan"
        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')
                ->where('link', 'admin/dokumentasi/kegiatan-lapangan')
                ->orWhere('link', '/admin/dokumentasi/kegiatan-lapangan')
                ->update(['label' => 'Kegiatan']);

            $db->table('menu_lv2')
                ->where('label', 'Kegiatan Lapangan')
                ->update(['label' => 'Kegiatan']);
        }

        if ($db->tableExists('menu_lv3')) {
            $db->table('menu_lv3')
                ->where('link', 'admin/dokumentasi/kegiatan-lapangan')
                ->orWhere('link', '/admin/dokumentasi/kegiatan-lapangan')
                ->update(['label' => 'Kegiatan']);

            $db->table('menu_lv3')
                ->where('label', 'Kegiatan Lapangan')
                ->update(['label' => 'Kegiatan']);
        }

        if ($db->tableExists('menu_lv1')) {
            $db->table('menu_lv1')
                ->where('link', 'admin/dokumentasi/kegiatan-lapangan')
                ->orWhere('link', '/admin/dokumentasi/kegiatan-lapangan')
                ->update(['label' => 'Kegiatan']);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = Database::forge();

        if ($db->tableExists('kegiatan_lapangan') && $db->fieldExists('kategori', 'kegiatan_lapangan')) {
            $forge->dropColumn('kegiatan_lapangan', 'kategori');
        }

        if ($db->tableExists('menu_lv2')) {
            $db->table('menu_lv2')
                ->where('link', 'admin/dokumentasi/kegiatan-lapangan')
                ->orWhere('link', '/admin/dokumentasi/kegiatan-lapangan')
                ->update(['label' => 'Kegiatan Lapangan']);
        }
    }
}

