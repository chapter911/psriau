<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSuratCutiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nomor_surat' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tanggal_pengajuan' => [
                'type' => 'DATE',
            ],
            'pegawai_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'masa_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'unit_kerja' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            ],
            'jenis_cuti' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'alasan_cuti' => [
                'type' => 'TEXT',
            ],
            'lama_cuti_jumlah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'lama_cuti_satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Hari',
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
            ],
            'alamat_selama_cuti' => [
                'type' => 'TEXT',
            ],
            'telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'catatan_cuti_n2' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'catatan_cuti_n1' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'catatan_cuti_n' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'catatan_cuti_keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'atasan_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Muhammad Yudi Prasetya, ST',
            ],
            'atasan_nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => '198002142014121002',
            ],
            'atasan_jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            ],
            'pejabat_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Ir. Agung Hari Prabowo, M.T',
            ],
            'pejabat_nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => '196910301998031005',
            ],
            'pejabat_jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis',
            ],
            'pertimbangan_atasan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Pending',
            ],
            'keputusan_pejabat' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Pending',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'pending',
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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

        $this->forge->addKey('id', true);
        $this->forge->createTable('surat_cuti', true);
    }

    public function down()
    {
        $this->forge->dropTable('surat_cuti', true);
    }
}
