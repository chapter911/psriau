<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * KwitansiExcelGenerator
 *
 * Fills the kwitansi_template.xls with real trip data and exports
 * the result as either an XLSX download or a PDF (via PhpSpreadsheet
 * built-in Dompdf PDF writer).
 */
class KwitansiExcelGenerator
{
    private string $templatePath;

    private array $months = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
    ];

    public function __construct()
    {
        $this->templatePath = APPPATH . 'ThirdParty/kwitansi_template.xls';
        if (!function_exists('terbilang_angka')) {
            helper('custom_helper');
        }
    }

    // ── Public API ────────────────────────────────────────────────────────────

    public function generatePdf(array $row, array $pelaksana, ?array $kopSurat, array $biayaMaster, string $mataAnggaran, int $idx = 0): string
    {
        $spreadsheet = $this->buildSpreadsheet($row, $pelaksana, $kopSurat, $biayaMaster, $mataAnggaran, $idx);
        $spreadsheet->setActiveSheetIndexByName('KWITANSI');

        \PhpOffice\PhpSpreadsheet\Settings::setPdfRendererName(\PhpOffice\PhpSpreadsheet\Settings::PDF_RENDERER_DOMPDF);

        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->setSheetIndex($spreadsheet->getActiveSheetIndex());

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    public function generateXlsx(array $row, array $pelaksana, ?array $kopSurat, array $biayaMaster, string $mataAnggaran, int $idx = 0): string
    {
        $spreadsheet = $this->buildSpreadsheet($row, $pelaksana, $kopSurat, $biayaMaster, $mataAnggaran, $idx);
        $spreadsheet->setActiveSheetIndexByName('KWITANSI');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    // ── Internal builder ──────────────────────────────────────────────────────

    private function buildSpreadsheet(array $row, array $pelaksana, ?array $kopSurat, array $biayaMaster, string $mataAnggaran, int $idx): Spreadsheet
    {
        $spreadsheet = IOFactory::load($this->templatePath);
        $utama  = $pelaksana[$idx] ?? ($pelaksana[0] ?? ['nama'=>'-','nip'=>'-','jabatan'=>'-']);
        $costs  = $this->calculateCosts($row, $utama, $biayaMaster);

        $rinci = $spreadsheet->getSheetByName('RINCI');
        if ($rinci) $this->fillRinciSheet($rinci, $row, $utama, $costs);

        $kwit = $spreadsheet->getSheetByName('KWITANSI');
        if ($kwit) $this->fillKwitansiSheet($kwit, $row, $utama, $costs, $mataAnggaran, $kopSurat);

        return $spreadsheet;
    }

    // ── RINCI filler ──────────────────────────────────────────────────────────

    private function fillRinciSheet(Worksheet $s, array $row, array $utama, array $costs): void
    {
        $nomorSPD   = str_replace('SPT', 'SPD', $row['nomor_surat_tugas'] ?? '-');
        $tanggalTtd  = $this->formatDate($row['tanggal_tanda_tangan'] ?? date('Y-m-d'));
        $terbilang   = $this->terbilang($costs['total']) . ' Rupiah,-';

        $s->setCellValue('C2', 'LAMPIRAN SPD NOMOR : ' . $nomorSPD);
        $s->setCellValue('C3', 'TANGGAL : ' . strtoupper($tanggalTtd));

        $pesawatRnd = $this->fillRinciTransport($s, $costs['transportGroups']);

        // Set rounded subtotal for Pesawat row K12
        if ($pesawatRnd > 0) {
            $s->setCellValue('K12', $pesawatRnd);
        }

        // Set total transport in M7
        $s->setCellValue('M7', $costs['transport']);

        // Uang harian (rows 20 area)
        if (!empty($costs['harianDetails'])) {
            $hd = $costs['harianDetails'][0];
            $s->setCellValue('D20', $hd['days']);
            $s->setCellValue('H20', $hd['rate']);
            $s->setCellValue('J20', $hd['sub']);
        }
        $s->setCellValue('M18', $costs['harian']);

        // Penginapan (rows 24-25 area)
        foreach (array_slice($costs['penginapanDetails'], 0, 2) as $i => $pd) {
            $r = 24 + $i;
            $s->setCellValue('D'.$r, $pd['nights']);
            $s->setCellValue('H'.$r, $pd['rate']);
            $s->setCellValue('J'.$r, $pd['sub']);
        }
        $s->setCellValue('M22', $costs['penginapan']);

        // Total M27 & Terbilang G28
        $s->setCellValue('M27', $costs['total']);
        $s->setCellValue('G28', '=TERBILANG(M27) &" Rupiah,-"');
        // Also write value to G28 directly so non-VBA renders it
        $s->setCellValue('G28', $terbilang);

        $s->setCellValue('E32', $costs['total']);
        $s->setCellValue('M33', $costs['total']);

        $s->setCellValue('L30', 'Pekanbaru,        ' . $this->formatMonthYear($row['tanggal_tanda_tangan'] ?? date('Y-m-d')));
        $s->setCellValue('K39', strtoupper($utama['nama']));
        $s->setCellValue('K40', 'NIP. ' . $this->formatNip($utama['nip'] ?? ''));

        // Rampung section totals
        $s->setCellValue('M45', $costs['total']);
    }

    private function fillRinciTransport(Worksheet $s, array $groups): int
    {
        $pesawat = [];
        $taxi    = [];
        $other   = [];
        $pesawatRnd = 0;

        foreach ($groups as $label => $grp) {
            $low = strtolower($label);
            if (str_contains($low, 'pesawat')) {
                $pesawat = array_merge($pesawat, $grp['rows']);
                $pesawatRnd = $grp['rounded_subtotal'] ?? 0;
            } elseif (str_contains($low, 'taxi') || str_contains($low, 'taksi')) {
                $taxi = array_merge($taxi, $grp['rows']);
            } else {
                $other = array_merge($other, $grp['rows']);
            }
        }

        foreach (array_slice(array_merge($pesawat, $other), 0, 2) as $i => $item) {
            $r    = 9 + $i;
            $desc = $item['ket'] !== '' ? $item['ket'] : ($item['lumpsum'] ? 'Transport (PP)' : 'Transport');
            $s->setCellValue('D'.$r, $desc);
            $s->setCellValue('I'.$r, 'Rp.');
            $s->setCellValue('J'.$r, $item['sub']);
        }
        foreach (array_slice($taxi, 0, 2) as $i => $item) {
            $r    = 14 + $i;
            $desc = $item['ket'] !== '' ? $item['ket'] : 'Transport';
            $s->setCellValue('D'.$r, $desc);
            $s->setCellValue('I'.$r, 'Rp.');
            $s->setCellValue('J'.$r, $item['sub']);
        }

        return $pesawatRnd;
    }

    // ── KWITANSI filler ───────────────────────────────────────────────────────

    private function fillKwitansiSheet(Worksheet $s, array $row, array $utama, array $costs, string $mataAnggaran, ?array $kopSurat): void
    {
        $nomorSurat   = $row['nomor_surat_tugas'] ?? '-';
        $nomorSPD     = str_replace('SPT', 'SPD', $nomorSurat);
        $kotaTujuan   = $row['kota_tujuan'] ?? '-';
        $tujuanMaksud = $row['tujuan'] ?? '-';
        $tanggalTtd   = $this->formatDate($row['tanggal_tanda_tangan'] ?? date('Y-m-d'));
        $tglBerangkat = $this->formatDate($row['periode_mulai'] ?? '');
        $tglKembali   = $this->formatDate($row['periode_selesai'] ?? '');
        $tahunAnggaran= date('Y', strtotime($row['tanggal_tanda_tangan'] ?? 'now'));
        $terbilang    = $this->terbilang($costs['total']) . ' Rupiah,-';
        $kodeNomor    = $row['kode_nomor'] ?? '';

        // Info table top-right
        $s->setCellValue('P10', $tahunAnggaran);
        if (!empty($kodeNomor)) {
            $s->setCellValue('P11', $kodeNomor);
        }
        $s->setCellValue('P12', $mataAnggaran);

        // Override formula cells with plain calculated values
        $s->setCellValue('H20', $costs['total']);
        $s->setCellValue('G22', $terbilang);

        // Untuk Pembayaran
        $pembayaran = 'Perjalanan Dinas a.n. ' . strtoupper($utama['nama']) . ', ' . ($utama['jabatan'] ?? '')
            . ' dalam rangka ' . $tujuanMaksud
            . '. Berdasarkan Surat ' . $nomorSurat . ' tanggal ' . $tanggalTtd
            . ', sesuai dengan Peraturan Menteri Keuangan RI Nomor 119 Tahun 2023'
            . ' Tanggal 15 November 2023, sebagaimana daftar perincian terlampir.';
        $s->setCellValue('G24', $pembayaran);

        // SPD info
        $s->setCellValue('G28', $nomorSPD);
        $s->setCellValue('G29', strtoupper($tanggalTtd));
        $s->setCellValue('G30', 'Pekanbaru - ' . $kotaTujuan);
        $s->setCellValue('G31', $tglBerangkat . ' s/d ' . $tglKembali);

        // TTD
        $s->setCellValue('L34', 'Pekanbaru,        ' . $this->formatMonthYear($row['tanggal_tanda_tangan'] ?? date('Y-m-d')));
        $s->setCellValue('L41', strtoupper($utama['nama']));
        $s->setCellValue('M42', 'NIP. ' . $this->formatNip($utama['nip'] ?? ''));

        // Kop surat image replacement if custom image is present
        if (!empty($kopSurat['image_url'])) {
            $imgPath = FCPATH . ltrim($kopSurat['image_url'], '/');
            if (file_exists($imgPath)) {
                try {
                    // Clear existing drawings if any
                    $drawings = $s->getDrawingCollection();
                    foreach ($drawings as $i => $d) {
                        $drawings->offsetUnset($i);
                    }

                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Kop Surat');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates('C2');
                    $drawing->setWidth(960);
                    $drawing->setHeight(152);
                    $drawing->setWorksheet($s);
                } catch (\Throwable) {}
            }
        }
    }

    // ── Cost calculations ───────────────────────────────────────────────────

    private function calculateCosts(array $row, array $utama, array $biayaMaster): array
    {
        $days = 0;
        if (!empty($row['periode_mulai']) && !empty($row['periode_selesai'])) {
            try {
                $days = (new \DateTime($row['periode_mulai']))->diff(new \DateTime($row['periode_selesai']))->days + 1;
            } catch (\Throwable) {}
        }

        $jabUpper = strtoupper(trim((string)($utama['jabatan'] ?? '')));
        $tarifPenginapan = $biayaMaster['penginapan_e4'] ?? 0;
        if (str_contains($jabUpper, 'ESELON I') && !str_contains($jabUpper, 'ESELON II') && !str_contains($jabUpper, 'ESELON III')) {
            $tarifPenginapan = $biayaMaster['penginapan_e1'] ?? 0;
        } elseif (str_contains($jabUpper, 'ESELON II') && !str_contains($jabUpper, 'ESELON III')) {
            $tarifPenginapan = $biayaMaster['penginapan_e2'] ?? 0;
        } elseif (str_contains($jabUpper, 'ESELON III')) {
            $tarifPenginapan = $biayaMaster['penginapan_e3'] ?? 0;
        }

        $rincian = json_decode((string)($row['rincian_biaya_json'] ?? '{}'), true) ?: [];

        // Transport
        $tList = (array)($rincian['transport'] ?? []);
        if (empty($tList) && isset($rincian['transport_start_date'])) {
            $tList = [['tgl_mulai'=>$rincian['transport_start_date']??'','tgl_selesai'=>$rincian['transport_end_date']??'','nominal'=>(int)($rincian['transport_nominal']??0),'keterangan'=>'']];
        }
        $tItems = [];
        foreach ($tList as $ti) {
            $tDays = 0;
            if (!empty($ti['tgl_mulai']) && !empty($ti['tgl_selesai'])) {
                try { $tDays = max(0, (new \DateTime($ti['tgl_mulai']))->diff(new \DateTime($ti['tgl_selesai']))->days + 1); } catch (\Throwable) {}
            }
            $rate = (int)($ti['nominal']??0);
            $lump = !empty($ti['is_lumpsum']);
            $sub  = $lump ? $rate : ($tDays > 0 ? $tDays * $rate : $rate);
            if ($rate > 0) $tItems[] = ['jenis'=>trim((string)($ti['jenis']??'')),'ket'=>trim((string)($ti['keterangan']??'')),'days'=>$tDays,'rate'=>$rate,'sub'=>$sub,'lumpsum'=>$lump];
        }
        $tGroups = [];
        foreach ($tItems as $ti) {
            $jLow = strtolower($ti['jenis']);
            $gKey = str_contains($jLow,'pesawat') ? 'Pesawat Udara' : (str_contains($jLow,'taxi')||str_contains($jLow,'taksi') ? 'Taxi' : ($ti['jenis']?:($ti['ket']?:'Transport')));
            if (!isset($tGroups[$gKey])) $tGroups[$gKey] = ['label'=>$gKey,'rows'=>[],'exact_subtotal'=>0];
            $tGroups[$gKey]['rows'][] = $ti;
            $tGroups[$gKey]['exact_subtotal'] += $ti['sub'];
        }
        $calcT = 0;
        foreach ($tGroups as $k => $g) {
            $r = (int)(floor($g['exact_subtotal']/100)*100);
            $tGroups[$k]['rounded_subtotal'] = $r;
            $tGroups[$k]['has_rounded'] = ($r !== $g['exact_subtotal']);
            $calcT += $r;
        }

        // Harian
        $hList = (array)($rincian['uang_harian'] ?? []);
        $calcH = 0; $hDetails = [];
        if (!empty($hList)) {
            foreach ($hList as $hi) {
                $hd = 0;
                if (!empty($hi['tgl_mulai']) && !empty($hi['tgl_selesai'])) {
                    try { $hd = max(0, (new \DateTime($hi['tgl_mulai']))->diff(new \DateTime($hi['tgl_selesai']))->days + 1); } catch (\Throwable) {}
                }
                $rate = (int)($hi['nominal']??0) ?: (int)($biayaMaster['harian']??0);
                if ($hd === 0 && $rate > 0) $hd = 1;
                $sub = $hd * $rate;
                $calcH += $sub;
                if ($hd > 0 || $rate > 0) $hDetails[] = ['days'=>$hd,'rate'=>$rate,'sub'=>$sub,'ket'=>trim((string)($hi['keterangan']??''))];
            }
        } else {
            $hd = max(0, $days); $rate = (int)($biayaMaster['harian']??0);
            $calcH = $hd * $rate;
            $hDetails[] = ['days'=>$hd,'rate'=>$rate,'sub'=>$calcH,'ket'=>''];
        }

        // Penginapan
        $pList = (array)($rincian['penginapan'] ?? []);
        if (empty($pList) && isset($rincian['penginapan_start_date'])) {
            $pList = [['tgl_mulai'=>$rincian['penginapan_start_date']??'','tgl_selesai'=>$rincian['penginapan_end_date']??'','nominal'=>$rincian['penginapan_nominal']??null,'keterangan'=>'']];
        }
        $calcP = 0; $pDetails = [];
        if (!empty($pList)) {
            foreach ($pList as $pi) {
                $pn = 0;
                if (!empty($pi['tgl_mulai']) && !empty($pi['tgl_selesai'])) {
                    try { $pn = max(0, (new \DateTime($pi['tgl_mulai']))->diff(new \DateTime($pi['tgl_selesai']))->days); } catch (\Throwable) {}
                } else { $pn = max(0, $days - 1); }
                $pNom = isset($pi['nominal']) && $pi['nominal'] !== null && $pi['nominal'] !== '' ? (int)$pi['nominal'] : null;
                $rate = $pNom !== null && $pNom >= 0 ? $pNom : (int)($tarifPenginapan * 0.3);
                if ($pn === 0 && $rate > 0) $pn = 1;
                $sub = $pn * $rate;
                $calcP += $sub;
                if ($pn > 0 || $rate > 0) $pDetails[] = ['nights'=>$pn,'rate'=>$rate,'sub'=>$sub,'ket'=>trim((string)($pi['keterangan']??''))];
            }
        } else {
            $pn = max(0, $days - 1); $rate = (int)($tarifPenginapan * 0.3);
            $calcP = $pn * $rate;
            $pDetails[] = ['nights'=>$pn,'rate'=>$rate,'sub'=>$calcP,'ket'=>''];
        }

        return ['transportGroups'=>$tGroups,'harianDetails'=>$hDetails,'penginapanDetails'=>$pDetails,'transport'=>$calcT,'harian'=>$calcH,'penginapan'=>$calcP,'total'=>$calcH+$calcT+$calcP];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formatDate(string $dateStr): string
    {
        if (empty($dateStr)) return '-';
        $ts = strtotime($dateStr);
        if (!$ts) return $dateStr;
        return date('d', $ts) . ' ' . $this->months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }

    private function formatMonthYear(string $dateStr): string
    {
        $ts = strtotime($dateStr ?: 'now');
        return $this->months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }

    private function formatNip(string $nip): string
    {
        $nip = preg_replace('/\s+/', '', $nip);
        if (strlen($nip) === 18) {
            return substr($nip,0,8).' '.substr($nip,8,6).' '.substr($nip,14,1).' '.substr($nip,15,3);
        }
        return $nip;
    }

    private function terbilang(int|float $angka): string
    {
        if (!function_exists('terbilang_angka')) {
            helper('custom_helper');
        }
        if (function_exists('terbilang_angka')) {
            return ucwords(terbilang_angka((int)$angka));
        }
        return number_format((int)$angka, 0, ',', '.');
    }
}
