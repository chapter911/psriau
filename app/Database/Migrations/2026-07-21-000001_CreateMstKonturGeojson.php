<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstKonturGeojson extends Migration
{
    public function up()
    {
        // Create the table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kabupaten' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'detail_level' => [
                'type'       => 'ENUM',
                'constraint' => ['overview', 'medium', 'detail'],
                'default'    => 'detail',
            ],
            'contour_interval' => [
                'type'    => 'INT',
                'comment' => 'Interval kontur dalam meter (5, 25, 50)',
            ],
            'min_elevation' => [
                'type'    => 'DECIMAL',
                'constraint' => '8,1',
                'null'    => true,
            ],
            'max_elevation' => [
                'type'    => 'DECIMAL',
                'constraint' => '8,1',
                'null'    => true,
            ],
            'feature_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'bbox_min_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'bbox_min_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'bbox_max_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'bbox_max_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'geojson_data' => [
                'type'    => 'LONGTEXT',
                'comment' => 'GeoJSON FeatureCollection',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey(['kabupaten', 'detail_level'], false, false, 'idx_kabupaten_level');
        $this->forge->addKey(['bbox_min_lat', 'bbox_max_lat', 'bbox_min_lng', 'bbox_max_lng'], false, false, 'idx_bbox');

        $this->forge->createTable('mst_kontur_geojson', true);
    }

    public function down()
    {
        $this->forge->dropTable('mst_kontur_geojson', true);
    }
}
