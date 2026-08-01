<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PerjalananDinasSmokeTestSeeder extends Seeder
{
    public function run()
    {
        $today = date('Y-m-d');
        
        $pelaksana_json = json_encode([
            [
                'id' => 1,
                'nama' => 'Muhammad Yudi Prasetya, ST',
                'nip' => '198002142014121002',
                'jabatan' => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                'pangkat_golongan' => 'Penata / (III/c)',
            ],
            [
                'id' => 2,
                'nama' => 'Nurhidayat Nugraha',
                'nip' => '199012212018021001',
                'jabatan' => 'PPK Prasarana Strategis',
                'pangkat_golongan' => 'Penata Muda / (III/a)',
            ]
        ], JSON_UNESCAPED_UNICODE);
        
        $rincian_biaya_json = json_encode([
            [
                'keterangan' => 'Uang Harian',
                'jumlah' => 2,
                'satuan' => 'hari',
                'harga_satuan' => 400000,
                'total' => 800000
            ],
            [
                'keterangan' => 'Penginapan',
                'jumlah' => 1,
                'satuan' => 'malam',
                'harga_satuan' => 600000,
                'total' => 600000
            ]
        ], JSON_UNESCAPED_UNICODE);

        // 1. Insert ke disposisi_perjalanan_dinas
        $disposisiData = [
            [
                'pelaksana_json'        => $pelaksana_json,
                'periode_mulai'         => '2026-08-10',
                'periode_selesai'       => '2026-08-12',
                'kota_tujuan'           => 'Jakarta',
                'tujuan'                => 'Koordinasi dengan Pusat',
                'transportasi'          => 'Pesawat Udara',
                'perihal'               => 'Rapat Koordinasi Pelaksanaan Prasarana Strategis',
                'menyetujui_pegawai_id' => 1,
                'diketahui_pegawai_id'  => 2,
                'status_menyetujui'     => 'disetujui',
                'status_diketahui'      => 'disetujui',
                'token_menyetujui'      => 'token_m_1',
                'token_diketahui'       => 'token_d_1',
                'status'                => 'disetujui',
                'approval_token'        => 'appr_tok_1',
                'catatan_penolakan'     => null,
                'created_by'            => 'admin',
                'updated_by'            => 'admin',
                'created_at'            => $today . ' 08:00:00',
                'updated_at'            => $today . ' 08:00:00',
            ],
            [
                'pelaksana_json'        => $pelaksana_json,
                'periode_mulai'         => '2026-08-15',
                'periode_selesai'       => '2026-08-17',
                'kota_tujuan'           => 'Bandung',
                'tujuan'                => 'Studi Banding',
                'transportasi'          => 'Kereta Api',
                'perihal'               => 'Studi Banding Pembangunan Prasarana',
                'menyetujui_pegawai_id' => 1,
                'diketahui_pegawai_id'  => 2,
                'status_menyetujui'     => 'pending',
                'status_diketahui'      => 'pending',
                'token_menyetujui'      => 'token_m_2',
                'token_diketahui'       => 'token_d_2',
                'status'                => 'pending',
                'approval_token'        => 'appr_tok_2',
                'catatan_penolakan'     => null,
                'created_by'            => 'admin',
                'updated_by'            => 'admin',
                'created_at'            => $today . ' 09:00:00',
                'updated_at'            => $today . ' 09:00:00',
            ],
            [
                'pelaksana_json'        => $pelaksana_json,
                'periode_mulai'         => '2026-08-20',
                'periode_selesai'       => '2026-08-25',
                'kota_tujuan'           => 'Surabaya',
                'tujuan'                => 'Monitoring Lapangan',
                'transportasi'          => 'Pesawat Udara',
                'perihal'               => 'Evaluasi Kinerja',
                'menyetujui_pegawai_id' => 1,
                'diketahui_pegawai_id'  => 2,
                'status_menyetujui'     => 'ditolak',
                'status_diketahui'      => 'pending',
                'token_menyetujui'      => 'token_m_3',
                'token_diketahui'       => 'token_d_3',
                'status'                => 'ditolak',
                'approval_token'        => 'appr_tok_3',
                'catatan_penolakan'     => 'Anggaran tidak mencukupi untuk periode ini.',
                'created_by'            => 'admin',
                'updated_by'            => 'admin',
                'created_at'            => $today . ' 10:00:00',
                'updated_at'            => $today . ' 10:00:00',
            ],
        ];

        foreach ($disposisiData as $index => $disposisi) {
            $this->db->table('disposisi_perjalanan_dinas')->insert($disposisi);
            $disposisiId = $this->db->insertID();

            // Insert Laporan for the first one only, since it is approved
            if ($index == 0) {
                $laporanData = [
                    'disposisi_id'           => $disposisiId,
                    'nomor_surat_tugas'      => '01/ST/PD/2026',
                    'periode_mulai'          => $disposisi['periode_mulai'],
                    'periode_selesai'        => $disposisi['periode_selesai'],
                    'kota_tujuan'            => $disposisi['kota_tujuan'],
                    'tujuan'                 => $disposisi['tujuan'],
                    'sasaran'                => 'Tercapainya kesepakatan rencana kerja',
                    'laporan_hasil'          => 'Rapat berjalan lancar dan rencana kerja telah disepakati.',
                    'pelaksana_json'         => $disposisi['pelaksana_json'],
                    'foto_dokumentasi_json'  => '[]',
                    'dokumen_pendukung_json' => '[]',
                    'creator_name'           => 'Admin',
                    'creator_pegawai_json'   => '{}',
                    'diketahui_oleh_json'    => '{}',
                    'is_final'               => 1,
                    'verified_spt_path'      => null,
                    'created_at'             => $today . ' 10:00:00',
                    'updated_at'             => $today . ' 10:00:00',
                    'dasar_spt_ids_json'     => '[]',
                    'tanggal_tanda_tangan'   => '2026-08-09',
                    'is_verified'            => 1,
                    'kop_surat_id'           => 1,
                    'mata_anggaran_id'       => 1,
                    'rincian_biaya_json'     => $rincian_biaya_json,
                ];
                $this->db->table('laporan_perjalanan_dinas')->insert($laporanData);
            }
        }
    }
}
