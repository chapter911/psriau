<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLokasiRuanganToInventarisPemeliharaanItem extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('trn_inventaris_pemeliharaan_item')) {
            if (! $db->fieldExists('lokasi_ruangan', 'trn_inventaris_pemeliharaan_item')) {
                $this->forge->addColumn('trn_inventaris_pemeliharaan_item', [
                    'lokasi_ruangan' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 150,
                        'null'       => true,
                        'after'      => 'tahun_perolehan',
                    ],
                ]);
            }

            // Backfill data lokasi_ruangan dari trn_inventaris_satker jika tersedia
            if ($db->tableExists('trn_inventaris_satker')) {
                $db->query("
                    UPDATE trn_inventaris_pemeliharaan_item pi
                    JOIN trn_inventaris_satker s 
                      ON (pi.inventaris_id IS NOT NULL AND pi.inventaris_id = s.id)
                      OR (pi.kode_barang = s.kode_barang AND pi.nup = s.nup)
                    SET pi.lokasi_ruangan = s.lokasi_ruangan
                    WHERE (pi.lokasi_ruangan IS NULL OR pi.lokasi_ruangan = '' OR pi.lokasi_ruangan = '-')
                      AND s.lokasi_ruangan IS NOT NULL
                      AND s.lokasi_ruangan != ''
                ");
            }

            // Set default 'Belum berlokasi' jika masih kosong
            $db->query("
                UPDATE trn_inventaris_pemeliharaan_item
                SET lokasi_ruangan = 'Belum berlokasi'
                WHERE lokasi_ruangan IS NULL OR lokasi_ruangan = '' OR lokasi_ruangan = '-'
            ");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('trn_inventaris_pemeliharaan_item')) {
            if ($db->fieldExists('lokasi_ruangan', 'trn_inventaris_pemeliharaan_item')) {
                $this->forge->dropColumn('trn_inventaris_pemeliharaan_item', 'lokasi_ruangan');
            }
        }
    }
}
