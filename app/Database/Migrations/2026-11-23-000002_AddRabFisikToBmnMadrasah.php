<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRabFisikToBmnMadrasah extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Tambah kolom di trn_bmn_madrasah_paket
        if ($db->tableExists('trn_bmn_madrasah_paket')) {
            $fieldsPaket = [];
            if (! $db->fieldExists('penyedia_jasa', 'trn_bmn_madrasah_paket')) {
                $fieldsPaket['penyedia_jasa'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'wilayah',
                ];
            }
            if (! $db->fieldExists('status_adendum', 'trn_bmn_madrasah_paket')) {
                $fieldsPaket['status_adendum'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'Kontrak Fisik Definitif',
                    'null'       => true,
                    'after'      => 'penyedia_jasa',
                ];
            }
            if (! $db->fieldExists('acuan_rab_fisik', 'trn_bmn_madrasah_paket')) {
                $fieldsPaket['acuan_rab_fisik'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'status_adendum',
                ];
            }
            if (! empty($fieldsPaket)) {
                $forge->addColumn('trn_bmn_madrasah_paket', $fieldsPaket);
            }
        }

        // 2. Tambah kolom di trn_bmn_madrasah_sekolah
        if ($db->tableExists('trn_bmn_madrasah_sekolah')) {
            $fieldsSekolah = [];
            if (! $db->fieldExists('nilai_rab_fisik', 'trn_bmn_madrasah_sekolah')) {
                $fieldsSekolah['nilai_rab_fisik'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0.00,
                    'null'       => false,
                    'after'      => 'kabupaten',
                ];
            }
            if (! $db->fieldExists('acuan_rab_fisik', 'trn_bmn_madrasah_sekolah')) {
                $fieldsSekolah['acuan_rab_fisik'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'acuan_ded',
                ];
            }
            if (! empty($fieldsSekolah)) {
                $forge->addColumn('trn_bmn_madrasah_sekolah', $fieldsSekolah);
            }
        }

        // Update data paket
        $paketUpdates = [
            1 => [
                'penyedia_jasa'   => 'PT. Citra Mas Jaya, KSO',
                'status_adendum'  => 'Adendum 2 (Final MC0)',
                'acuan_rab_fisik' => 'ADD 2 PHTC RIAU 1 Rev Print3 .xlsx / RAB ADD 2.pdf',
            ],
            2 => [
                'penyedia_jasa'   => 'PT. Bumi Palapa Perkasa - CV. Mitra Kulim Mandiri, KSO',
                'status_adendum'  => 'Kontrak Fisik Definitif',
                'acuan_rab_fisik' => 'RAB Fisik Final PHTC Riau 2',
            ],
            3 => [
                'penyedia_jasa'   => 'PT. Jolundra Putra - CV. Mitra Kulim Mandiri, KSO',
                'status_adendum'  => 'Kontrak Fisik Definitif',
                'acuan_rab_fisik' => 'RAB Fisik Final PHTC Riau 3',
            ],
            4 => [
                'penyedia_jasa'   => 'PT. Toleransi Aceh - PT. Bripona Jaya Abadi',
                'status_adendum'  => 'Penawaran SPSE Final',
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (SPSE v4.5 LKPP)',
            ],
            5 => [
                'penyedia_jasa'   => 'PT. Polada Mutiara - Kota Raja KSO',
                'status_adendum'  => 'Kontrak Fisik Definitif',
                'acuan_rab_fisik' => 'RAB Fisik Final PHTC Riau 5',
            ],
            6 => [
                'penyedia_jasa'   => 'PT. Karya Inti Bumi Konstruksi',
                'status_adendum'  => 'Kontrak Fisik Definitif',
                'acuan_rab_fisik' => 'RAB Fisik Final PHTC Riau 6',
            ],
            7 => [
                'penyedia_jasa'   => 'PT. Murda Jaya Abadi',
                'status_adendum'  => 'Kontrak Fisik Definitif',
                'acuan_rab_fisik' => 'RAB Fisik Final PHTC Riau 7',
            ],
            8 => [
                'penyedia_jasa'   => 'PT. Alam Surya - PT. Kota Raja, KSO',
                'status_adendum'  => 'Kontrak Fisik Definitif',
                'acuan_rab_fisik' => 'RAB Fisik Final PHTC Riau 8',
            ],
            9 => [
                'penyedia_jasa'   => 'PT. Zhavira Tetap Jaya (PT. Zhafira Tribina Jaya)',
                'status_adendum'  => 'Penawaran SPSE Final',
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (SPSE v4.5 LKPP)',
            ],
        ];

        foreach ($paketUpdates as $id => $data) {
            $db->table('trn_bmn_madrasah_paket')->where('id', $id)->update($data);
        }

        // Update nilai RAB fisik dan dokumen acuan per madrasah
        $sekolahUpdates = [
            // Paket 1 (Detail dari ADD 2 PHTC RIAU 1 Rev Print3 .xlsx)
            'MIN 2 Pekanbaru' => [
                'nilai_rab_fisik' => 2379472096.28,
                'acuan_rab_fisik' => 'ADD 2 PHTC RIAU 1.xlsx (Sheet MIN 2 PKU)',
            ],
            'MTsN 2 Pekanbaru' => [
                'nilai_rab_fisik' => 1703448757.01,
                'acuan_rab_fisik' => 'ADD 2 PHTC RIAU 1.xlsx (Sheet MTSN 2 PKU)',
            ],
            'MAN 1 Pekanbaru' => [
                'nilai_rab_fisik' => 3700294206.53,
                'acuan_rab_fisik' => 'ADD 2 PHTC RIAU 1.xlsx (Sheet MAN 1 PKU)',
            ],
            'MAN 2 Pekanbaru' => [
                'nilai_rab_fisik' => 3387797757.29,
                'acuan_rab_fisik' => 'ADD 2 PHTC RIAU 1.xlsx (Sheet MAN 2 PKU / MC0 Rp 3,20 M)',
            ],
            'MAN 1 Siak' => [
                'nilai_rab_fisik' => 2885515533.09,
                'acuan_rab_fisik' => 'ADD 2 PHTC RIAU 1.xlsx (Sheet MAN 1 SIAK / MC0 Rp 2,87 M)',
            ],

            // Paket 4 (RAB Riau 4.pdf)
            'MIS Tarbiyah Islamiyah' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MIS Raudhatut Thalibin' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MTsS An-Nur' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MTsS Miftahul Mubtadin' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MTsS Raudhatul Hidayah' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MTsS Raudhatut Thalibin' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MAS Raudhatul Hidayah' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MAS Raudhatut Thalibin' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MTsS Al-Falah' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MTsS Hidayatul Mutaalim' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],
            'MAS Hidayatul Muttaallim Mengkirau' => [
                'acuan_rab_fisik' => 'RAB Riau 4.pdf (BoQ SPSE PT. Toleransi Aceh)',
            ],

            // Paket 9 (RAB Riau 9.pdf)
            'MIS Al-Falah' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MIS Al-Husin' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MIS Al-Qisimiyah' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MIS Al-Washiliyah' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MIS Ishlahiyah' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MTsS Al-Adalah' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MTsS Datuk Batu Hampar' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MTsS Ishlahiyah' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
            'MAS Ishlahiyah Panipahan' => [
                'acuan_rab_fisik' => 'RAB Riau 9.pdf (BoQ SPSE PT. Zhavira Tetap Jaya)',
            ],
        ];

        foreach ($sekolahUpdates as $nama => $data) {
            $db->table('trn_bmn_madrasah_sekolah')->where('nama_sekolah', $nama)->update($data);
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();
        if ($this->db->fieldExists('penyedia_jasa', 'trn_bmn_madrasah_paket')) {
            $forge->dropColumn('trn_bmn_madrasah_paket', 'penyedia_jasa');
        }
        if ($this->db->fieldExists('status_adendum', 'trn_bmn_madrasah_paket')) {
            $forge->dropColumn('trn_bmn_madrasah_paket', 'status_adendum');
        }
        if ($this->db->fieldExists('acuan_rab_fisik', 'trn_bmn_madrasah_paket')) {
            $forge->dropColumn('trn_bmn_madrasah_paket', 'acuan_rab_fisik');
        }
        if ($this->db->fieldExists('nilai_rab_fisik', 'trn_bmn_madrasah_sekolah')) {
            $forge->dropColumn('trn_bmn_madrasah_sekolah', 'nilai_rab_fisik');
        }
        if ($this->db->fieldExists('acuan_rab_fisik', 'trn_bmn_madrasah_sekolah')) {
            $forge->dropColumn('trn_bmn_madrasah_sekolah', 'acuan_rab_fisik');
        }
    }
}
