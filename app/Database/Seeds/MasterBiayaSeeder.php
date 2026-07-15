<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterBiayaSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // Fetch all provinces to map codes to official names
        $dbProvinces = $db->table('mst_provinsi')->get()->getResultArray();
        $provMap = [];
        foreach ($dbProvinces as $p) {
            $provMap[$p['kode_provinsi']] = $p['nama_provinsi'];
        }

        // Clean up and seed lodging rates
        $this->seedLodging($db, $provMap);

        // Clean up and seed transit transport rates
        $this->seedTransport($db, $provMap);
    }

    private function seedLodging($db, $provMap)
    {
        // 1. Delete all existing records from 'mst_biaya_penginapan'
        $db->table('mst_biaya_penginapan')->truncate();

        // 2. Define the lodging data for 38 provinces from Page 2 of the PDF
        // Structure: Province Key => [Eselon I, Eselon II, Eselon III, Eselon IV]
        $lodgingData = [
            '11' => [5109000, 3526000, 1578000, 770000],  // ACEH
            '12' => [4960000, 2195000, 1188000, 699000],  // SUMATERA UTARA
            '14' => [3820000, 3119000, 1650000, 852000],  // RIAU
            '21' => [6177000, 2481000, 1388000, 792000],  // KEPULAUAN RIAU
            '15' => [5004000, 4102000, 1252000, 580000],  // JAMBI
            '13' => [5603000, 3373000, 1353000, 701000],  // SUMATERA BARAT
            '16' => [6298000, 3134000, 1966000, 861000],  // SUMATERA SELATAN
            '18' => [4806000, 2663000, 1539000, 621000],  // LAMPUNG
            '17' => [2140000, 1628000, 1546000, 692000],  // BENGKULU
            '19' => [4424000, 2838000, 1957000, 724000],  // BANGKA BELITUNG
            '36' => [5725000, 2373000, 1301000, 775000],  // BANTEN
            '32' => [5812000, 2755000, 1366000, 735000],  // JAWA BARAT
            '31' => [9331000, 2084000, 1062000, 730000],  // D.K.I. JAKARTA
            '33' => [6129000, 2138000, 1286000, 810000],  // JAWA TENGAH
            '34' => [5100000, 2695000, 1600000, 845000],  // D.I. YOGYAKARTA
            '35' => [4449000, 2007000, 1234000, 814000],  // JAWA TIMUR
            '51' => [7328000, 2433000, 1754000, 1138000], // BALI
            '52' => [4682000, 2648000, 1418000, 907000],  // NUSA TENGGARA BARAT
            '53' => [4013000, 2283000, 1450000, 737000],  // NUSA TENGGARA TIMUR
            '61' => [2654000, 1923000, 1125000, 576000],  // KALIMANTAN BARAT
            '62' => [4901000, 3391000, 1189000, 706000],  // KALIMANTAN TENGAH
            '63' => [4797000, 3316000, 1500000, 746000],  // KALIMANTAN SELATAN
            '64' => [4000000, 2342000, 1507000, 804000],  // KALIMANTAN TIMUR
            '65' => [4000000, 2854000, 1507000, 904000],  // KALIMANTAN UTARA
            '71' => [5264000, 2290000, 1270000, 978000],  // SULAWESI UTARA
            '75' => [4168000, 3107000, 1606000, 955000],  // GORONTALO
            '76' => [4076000, 3098000, 1344000, 704000],  // SULAWESI BARAT
            '73' => [4820000, 1938000, 1423000, 745000],  // SULAWESI SELATAN
            '72' => [2309000, 2166000, 1679000, 951000],  // SULAWESI TENGAH
            '74' => [3089000, 2755000, 1297000, 786000],  // SULAWESI TENGGARA
            '81' => [3467000, 3240000, 1059000, 667000],  // MALUKU
            '82' => [4612000, 3843000, 1160000, 654000],  // MALUKU UTARA
            '91' => [3859000, 3318000, 2521000, 1038000], // PAPUA
            '92' => [3872000, 3575000, 2056000, 967000],  // PAPUA BARAT
            '96' => [3872000, 3575000, 2056000, 967000],  // PAPUA BARAT DAYA
            '94' => [3859000, 3318000, 2521000, 1038000], // PAPUA TENGAH
            '93' => [5673000, 4877000, 3706000, 1526000], // PAPUA SELATAN
            '95' => [5711000, 4911000, 3731000, 1536000], // PAPUA PEGUNUNGAN
        ];

        // Levels mapping to mixed case (matching the HTML select options)
        $levels = [
            'Pejabat Negara/Wakil Menteri/Pejabat Eselon I',
            'Pejabat Negara Lainnya/Pejabat Eselon II',
            'Pejabat Eselon III/Golongan IV',
            'Pejabat Eselon IV/Golongan III/II/I'
        ];

        $now = date('Y-m-d H:i:s');
        $batch = [];

        foreach ($lodgingData as $kodeProv => $rates) {
            $namaProv = $provMap[$kodeProv] ?? 'Provinsi ' . $kodeProv;
            foreach ($rates as $idx => $tarif) {
                $batch[] = [
                    'kode_provinsi' => $kodeProv,
                    'nama_provinsi' => $namaProv,
                    'level_pejabat' => $levels[$idx],
                    'tarif'         => $tarif,
                    'created_by'    => 'system',
                    'created_date'  => $now,
                    'updated_by'    => 'system',
                    'updated_date'  => $now,
                ];
            }
        }

        if (! empty($batch)) {
            $db->table('mst_biaya_penginapan')->insertBatch($batch);
        }
    }

    private function seedTransport($db, $provMap)
    {
        // 1. Delete existing airport/terminal transit transport costs
        // We leave city-to-city (Pekanbaru -> Indragiri Hilir, etc.) intact
        $db->table('mst_biaya_transportasi')
            ->where('asal', 'Terminal/Stasiun/Bandara/Pelabuhan')
            ->delete();

        // 2. Define the transport data for 34 provinces from Page 3 of the PDF
        // Structure: Province Key => Price (Rp)
        $transportData = [
            '11' => 123000, // ACEH
            '12' => 278000, // SUMATRA UTARA
            '14' => 99000,  // RIAU
            '21' => 159000, // KEPULAUAN RIAU
            '15' => 133000, // JAMBI
            '13' => 171000, // SUMATRA BARAT
            '16' => 162000, // SUMATRA SELATAN
            '18' => 162000, // LAMPUNG
            '17' => 106000, // BENGKULU
            '19' => 94000,  // BANGKA BELITUNG
            '36' => 300000, // BANTEN
            '32' => 180000, // JAWA BARAT
            '31' => 250000, // D.K.I. JAKARTA
            '33' => 105000, // JAWA TENGAH
            '34' => 258000, // D.I. YOGYAKARTA
            '35' => 225000, // JAWA TIMUR
            '51' => 219000, // BALI
            '52' => 224000, // NUSA TENGGARA BARAT
            '53' => 105000, // NUSA TENGGARA TIMUR
            '61' => 165000, // KALIMANTAN BARAT
            '62' => 130000, // KALIMANTAN TENGAH
            '63' => 174000, // KALIMANTAN SELATAN
            '64' => 300000, // KALIMANTAN TIMUR
            '65' => 211000, // KALIMANTAN UTARA
            '71' => 134000, // SULAWESI UTARA
            '75' => 256000, // GORONTALO
            '76' => 283000, // SULAWESI BARAT
            '73' => 181000, // SULAWESI SELATAN
            '72' => 149000, // SULAWESI TENGAH
            '74' => 154000, // SULAWESI TENGGARA
            '81' => 279000, // MALUKU
            '82' => 208000, // MALUKU UTARA
            '91' => 462000, // PAPUA
            '92' => 228000, // PAPUA BARAT
        ];

        $now = date('Y-m-d H:i:s');
        $batch = [];

        foreach ($transportData as $kodeProv => $besaran) {
            $namaProv = $provMap[$kodeProv] ?? 'Provinsi ' . $kodeProv;
            $batch[] = [
                'kode_provinsi'  => $kodeProv,
                'kode_kabupaten' => null,
                'asal'           => 'Terminal/Stasiun/Bandara/Pelabuhan',
                'tujuan'         => 'Dalam Kota - ' . $namaProv,
                'besaran'        => $besaran,
                'created_by'     => 'system',
                'created_date'   => $now,
                'updated_by'     => 'system',
                'updated_date'   => $now,
            ];
        }

        if (! empty($batch)) {
            $db->table('mst_biaya_transportasi')->insertBatch($batch);
        }
    }
}
