<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMstMapTypeTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mst_map_type')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'map_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'map_script' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('mst_map_type', true);
        }

        $rows = [
            [
                'id' => 1,
                'map_name' => 'LeafLet Map',
                'map_script' => "L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {\n    attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'\n}).addTo(map);\n\nL.control.scale().addTo(map);",
            ],
            [
                'id' => 2,
                'map_name' => 'Google Hybrid',
                'map_script' => "L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {\n    maxZoom: 20,\n    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']\n}).addTo(map);",
            ],
            [
                'id' => 3,
                'map_name' => 'Google RoadMap',
                'map_script' => "L.tileLayer('http://{s}.google.com/vt/lyrs=r&hl,en&x={x}&y={y}&z={z}', {\n    maxZoom: 20,\n    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']\n}).addTo(map);",
            ],
            [
                'id' => 4,
                'map_name' => 'Google Terrain',
                'map_script' => "L.tileLayer('http://{s}.google.com/vt/lyrs=p&hl,en&x={x}&y={y}&z={z}', {\n    maxZoom: 20,\n    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']\n}).addTo(map);",
            ],
        ];

        foreach ($rows as $row) {
            $exists = $this->db->table('mst_map_type')
                ->where('id', (int) $row['id'])
                ->countAllResults() > 0;

            if ($exists) {
                $this->db->table('mst_map_type')
                    ->where('id', (int) $row['id'])
                    ->update([
                        'map_name' => $row['map_name'],
                        'map_script' => $row['map_script'],
                    ]);
                continue;
            }

            $this->db->table('mst_map_type')->insert($row);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('mst_map_type')) {
            $this->forge->dropTable('mst_map_type', true);
        }
    }
}
