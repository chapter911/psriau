<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\RabGedungDetailModel;

class ImportRabGedung extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:import-rab-gedung';
    protected $description = 'Import RAB per gedung dari file Excel contoh_adendum_phtc6.xlsx';

    public function run(array $params)
    {
        $filePath = '/Users/agung_kesuma/psriau/do_not_upload/contoh_adendum_phtc6.xlsx';

        if (!file_exists($filePath)) {
            CLI::error("File tidak ditemukan: $filePath");
            return;
        }

        CLI::write("Membuka file Excel...", "yellow");

        try {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                CLI::error("PhpSpreadsheet tidak tersedia.");
                return;
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly(["RAB PER GEDUNG"]);
            
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getSheetByName("RAB PER GEDUNG");
            
            if ($sheet === null) {
                CLI::error("Sheet 'RAB PER GEDUNG' tidak ditemukan.");
                return;
            }

            $highestRow = $sheet->getHighestRow();
            CLI::write("Sheet 'RAB PER GEDUNG' berhasil dimuat. Total baris: $highestRow", "green");

            $npsnMap = [
                'MTsS  Al Falah Jangkang' => 60729635,
                'MTsS Nurul Hidayah Bantan Tua' => 60730126,
                'MAS Miftahul Jannah Selat Baru' => 69725480,
                'MTSS  Al Irsyadiyah Muntai ' => 60730131,
                'MTSS Darul Aiman Muntai ' => 60730132,
                'MAS Darul Aiman Muntai ' => 69725486,
                'MIS DARUL AIMAN MUNTAI' => 69725290,
                'MTSS Miftahul Ulum Bantan Air' => 60730125
            ];

            $currentLocation = null;
            $currentPekerjaanUtama = null;
            $currentGedung = null;
            $currentCategory1 = null;
            $currentCategory2 = null;

            $itemsToInsert = [];
            $totalParsed = 0;

            // Helper functions
            $isRoman = function ($str) {
                return preg_match('/^[IVXLCDM]+$/i', trim($str));
            };

            $isTotalRow = function ($str) {
                $strLower = strtolower(trim($str));
                return strpos($strLower, 'jumlah') === 0 || strpos($strLower, 'total') === 0 || strpos($strLower, '=+') === 0;
            };

            $getNumericValue = function ($col, $row) use ($sheet) {
                $cell = $sheet->getCell($col . $row);
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) {
                    $val = $cell->getOldCalculatedValue();
                }
                if ($val === null || $val === '' || $val === ' ') {
                    return null;
                }
                if (is_numeric($val)) {
                    return (float) $val;
                }
                return null;
            };

            $getStringValue = function ($col, $row) use ($sheet) {
                $cell = $sheet->getCell($col . $row);
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) {
                    $val = $cell->getOldCalculatedValue();
                }
                return $val !== null ? trim((string)$val) : '';
            };

            CLI::write("Memulai parsing baris demi baris...", "yellow");

            for ($row = 1; $row <= $highestRow; $row++) {
                $valA = $getStringValue("A", $row);
                $valB = $getStringValue("B", $row);
                $valD = $getStringValue("D", $row);
                $valE = $getStringValue("E", $row);
                
                // 1. Detect Pekerjaan Utama
                if (strtolower($valA) === 'pekerjaan') {
                    $currentPekerjaanUtama = $valD;
                    continue;
                }
                
                // 2. Detect Location / School
                if (strtolower($valA) === 'lokasi') {
                    $currentLocation = $valD;
                    $currentGedung = null;
                    $currentCategory1 = null;
                    $currentCategory2 = null;
                    continue;
                }
                
                // Skip header rows
                if ($row <= 14 || $currentLocation === null) {
                    continue;
                }
                
                // Skip table headers and empty rows
                if ($valA === 'NO' || $valB === 'JENIS PEKERJAAN' || ($valA === '' && $valB === '')) {
                    continue;
                }
                
                // Skip total / jumlah rows
                if ($isTotalRow($valB) || $isTotalRow($valA)) {
                    continue;
                }
                
                // 3. Detect Building/Gedung (Single Uppercase Letter in A, e.g. A, B, C, D)
                if (preg_match('/^[A-Z]$/', $valA) && !$isRoman($valA)) {
                    $currentGedung = $valB;
                    $currentCategory1 = null;
                    $currentCategory2 = null;
                    continue;
                }
                
                if ($currentGedung === null) {
                    continue;
                }
                
                // 4. Detect Sub-category I (Roman numerals)
                if ($isRoman($valA) || preg_match('/^[IVXLCDM]+\.\d+$/i', $valA)) {
                    $currentCategory1 = $valB;
                    $currentCategory2 = null;
                    continue;
                }
                
                // 5. Detect Sub-category II (Number in A, empty unit, empty vol/price)
                $valF = $sheet->getCell("F" . $row)->getValue();
                $valG = $sheet->getCell("G" . $row)->getValue();
                if ($valA !== '' && $valE === '' && ($valF === null || $valF === '' || $valF == 0) && ($valG === null || $valG === '' || $valG == 0)) {
                    $currentCategory2 = $valB;
                    continue;
                }
                
                // 6. It's a Work Item
                if ($valB !== '') {
                    // Resolve NPSN
                    $sekolahNpsn = $npsnMap[$currentLocation] ?? null;
                    if ($sekolahNpsn === null) {
                        $cleanName = preg_replace('/\s+/', ' ', strtolower(trim($currentLocation)));
                        foreach ($npsnMap as $key => $npsn) {
                            $cleanKey = preg_replace('/\s+/', ' ', strtolower(trim($key)));
                            if ($cleanKey === $cleanName) {
                                $sekolahNpsn = $npsn;
                                break;
                            }
                        }
                    }

                    $itemsToInsert[] = [
                        'sekolah_npsn'         => $sekolahNpsn,
                        'nama_sekolah'         => $currentLocation,
                        'pekerjaan_utama'      => $currentPekerjaanUtama,
                        'gedung'               => $currentGedung,
                        'kategori_1'           => $currentCategory1,
                        'kategori_2'           => $currentCategory2,
                        'no_urut'              => $valA,
                        'uraian'               => $getStringValue('B', $row),
                        'satuan'               => $valE,
                        'kontrak_volume'       => $getNumericValue('F', $row),
                        'kontrak_harga_satuan' => $getNumericValue('G', $row),
                        'kontrak_jumlah_harga' => $getNumericValue('H', $row),
                        'tambah_volume'        => $getNumericValue('I', $row),
                        'tambah_jumlah_harga'  => $getNumericValue('J', $row),
                        'kurang_volume'        => $getNumericValue('K', $row),
                        'kurang_jumlah_harga'  => $getNumericValue('L', $row),
                        'mc_nol_volume'        => $getNumericValue('M', $row),
                        'mc_nol_jumlah_harga'  => $getNumericValue('N', $row),
                        'bobot_persen'         => $getNumericValue('O', $row),
                        'prestasi_persen'      => $getNumericValue('P', $row),
                        'created_at'           => date('Y-m-d H:i:s'),
                        'updated_at'           => date('Y-m-d H:i:s'),
                    ];
                    
                    $totalParsed++;
                }
            }

            CLI::write("Parsing selesai. Total record yang di-parse: $totalParsed", "green");
            CLI::write("Mengosongkan tabel trn_rab_gedung_detail...", "yellow");
            
            $model = new RabGedungDetailModel();
            $model->truncate();

            CLI::write("Menyimpan data ke database dalam batch...", "yellow");

            // Chunk insert to prevent huge queries
            $chunks = array_chunk($itemsToInsert, 200);
            $inserted = 0;
            foreach ($chunks as $idx => $chunk) {
                $model->insertBatch($chunk);
                $inserted += count($chunk);
                CLI::showProgress($inserted, $totalParsed);
            }

            CLI::write("\nImport berhasil! Total $inserted baris dimasukkan ke tabel trn_rab_gedung_detail.", "green");

        } catch (\Exception $e) {
            CLI::error("Error saat import: " . $e->getMessage());
            CLI::error($e->getTraceAsString());
        }
    }
}
