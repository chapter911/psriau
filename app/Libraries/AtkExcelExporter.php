<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AtkExcelExporter
{
    public static function bulanNama(int $bulan): string
    {
        $map = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $map[$bulan] ?? '-';
    }

    public static function tanggalIndo(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return (string) $date;
        }
        return date('d', $ts) . ' ' . self::bulanNama((int) date('n', $ts)) . ' ' . date('Y', $ts);
    }

    public static function tanggalSlash(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        $ts = strtotime($date);
        return $ts !== false ? date('d/m/Y', $ts) : (string) $date;
    }

    public static function tanggalKalimatBast(?string $date): string
    {
        if (empty($date)) {
            return 'Pada hari ini ........ tanggal ........ bulan ........ tahun ........';
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return 'Pada hari ini ........';
        }
        $hariMap = [
            'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu',
        ];
        $hari    = $hariMap[date('D', $ts)] ?? 'Senin';
        $tgl     = ucwords(terbilang_angka((int) date('j', $ts)));
        $bulan   = self::bulanNama((int) date('n', $ts));
        $tahun   = ucwords(terbilang_angka((int) date('Y', $ts)));

        return "Pada hari ini {$hari} tanggal {$tgl} bulan {$bulan} tahun {$tahun}";
    }

    /**
     * Bangun workbook SPJ 6 sheet untuk 1 atau beberapa transaksi pengadaan.
     *
     * @param array<int, array<string, mixed>> $bundles Each bundle: ['pengadaan' => ..., 'items' => ..., 'penyedia' => ..., 'kop_path' => ...]
     */
    public function buildSpjWorkbook(array $bundles): Spreadsheet
    {
        if (function_exists('helper')) {
            helper('custom');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Satker Pelaksanaan Prasarana Strategis Riau')
            ->setTitle('SPJ ATK & Supplies');

        $sUsulan = $spreadsheet->getActiveSheet();
        $sUsulan->setTitle('USULAN PPK');
        $sSpb    = $spreadsheet->createSheet()->setTitle('SURAT PESANAN BARANG (PISAH)');
        $sBast   = $spreadsheet->createSheet()->setTitle('BAST');
        $sFaktur = $spreadsheet->createSheet()->setTitle('Faktur PISAH');
        $sKwi    = $spreadsheet->createSheet()->setTitle('Kwi LS');
        $sLamp   = $spreadsheet->createSheet()->setTitle('Lamp. BASTP');

        $sheets = [$sUsulan, $sSpb, $sBast, $sFaktur, $sKwi, $sLamp];
        foreach ($sheets as $sheet) {
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getDefaultRowDimension()->setRowHeight(18);
            $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        }

        // Column widths
        $sUsulan->getColumnDimension('A')->setWidth(8);
        $sUsulan->getColumnDimension('B')->setWidth(46);
        $sUsulan->getColumnDimension('C')->setWidth(14);
        $sUsulan->getColumnDimension('D')->setWidth(24);

        $sSpb->getColumnDimension('A')->setWidth(4);
        $sSpb->getColumnDimension('B')->setWidth(8);
        $sSpb->getColumnDimension('C')->setWidth(44);
        $sSpb->getColumnDimension('D')->setWidth(14);
        $sSpb->getColumnDimension('E')->setWidth(16);
        $sSpb->getColumnDimension('F')->setWidth(18);

        $sBast->getColumnDimension('A')->setWidth(14);
        $sBast->getColumnDimension('B')->setWidth(4);
        $sBast->getColumnDimension('C')->setWidth(28);
        $sBast->getColumnDimension('D')->setWidth(12);
        $sBast->getColumnDimension('E')->setWidth(12);
        $sBast->getColumnDimension('F')->setWidth(12);
        $sBast->getColumnDimension('G')->setWidth(32);

        $sFaktur->getColumnDimension('A')->setWidth(10);
        $sFaktur->getColumnDimension('B')->setWidth(15);
        $sFaktur->getColumnDimension('C')->setWidth(42);
        $sFaktur->getColumnDimension('D')->setWidth(16);
        $sFaktur->getColumnDimension('E')->setWidth(18);

        $sKwi->getColumnDimension('A')->setWidth(4);
        $sKwi->getColumnDimension('B')->setWidth(20);
        $sKwi->getColumnDimension('C')->setWidth(4);
        $sKwi->getColumnDimension('D')->setWidth(14);
        $sKwi->getColumnDimension('E')->setWidth(18);
        $sKwi->getColumnDimension('F')->setWidth(10);
        $sKwi->getColumnDimension('G')->setWidth(8);
        $sKwi->getColumnDimension('H')->setWidth(18);
        $sKwi->getColumnDimension('I')->setWidth(16);
        $sKwi->getColumnDimension('J')->setWidth(24);

        $sLamp->getColumnDimension('A')->setWidth(6);
        $sLamp->getColumnDimension('B')->setWidth(14);
        $sLamp->getColumnDimension('C')->setWidth(13);
        $sLamp->getColumnDimension('D')->setWidth(38);
        $sLamp->getColumnDimension('E')->setWidth(12);
        $sLamp->getColumnDimension('F')->setWidth(16);
        $sLamp->getColumnDimension('G')->setWidth(10);
        $sLamp->getColumnDimension('H')->setWidth(14);
        $sLamp->getColumnDimension('I')->setWidth(14);

        $rUsulan = 1;
        $rSpb    = 1;
        $rBast   = 1;
        $rFaktur = 1;
        $rKwi    = 1;
        $rLamp   = 1;

        foreach ($bundles as $bundle) {
            $p        = $bundle['pengadaan'];
            $items    = $bundle['items'];
            $penyedia = $bundle['penyedia'] ?? [];
            $kopPath  = $bundle['kop_path'] ?? '';

            $rUsulan = $this->renderUsulanBlock($sUsulan, $rUsulan, $p, $items, $kopPath);
            $rSpb    = $this->renderSpbBlock($sSpb, $rSpb, $p, $items, $penyedia, $kopPath);
            $rBast   = $this->renderBastBlock($sBast, $rBast, $p, $penyedia, $kopPath);
            $rFaktur = $this->renderFakturBlock($sFaktur, $rFaktur, $p, $items, $penyedia);
            $rKwi    = $this->renderKwiBlock($sKwi, $rKwi, $p, $penyedia);
            $rLamp   = $this->renderLampBastpBlock($sLamp, $rLamp, $p, $items, $penyedia);
        }

        return $spreadsheet;
    }

    private function addKopImage(Worksheet $sheet, int $row, string $col, string $kopPath): int
    {
        if ($kopPath !== '' && is_file($kopPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setPath($kopPath);
                $drawing->setCoordinates($col . $row);
                $drawing->setHeight(62);
                $drawing->setWorksheet($sheet);
                return $row + 4;
            } catch (\Throwable $e) {
                // fallback to text header
            }
        }
        $sheet->setCellValue($col . $row, 'KEMENTERIAN PEKERJAAN UMUM - SATKER PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->getStyle($col . $row)->getFont()->setBold(true)->setSize(10);
        return $row + 2;
    }

    private function borderTableStyle(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ];
    }

    private function renderUsulanBlock(Worksheet $s, int $r, array $p, array $items, string $kopPath): int
    {
        $r = $this->addKopImage($s, $r, 'A', $kopPath);

        $bulanNama = self::bulanNama((int) ($p['bulan'] ?? 1));
        $tahun     = (int) ($p['tahun_anggaran'] ?? 2026);
        $kategori  = (string) ($p['kategori'] ?? 'ATK');
        $jenisLabel = $kategori === 'Supplies Komputer' ? 'Supplies Komputer' : 'Alat Tulis Kantor (ATK)';
        $tglUsulan = self::tanggalIndo($p['tgl_usulan'] ?? null) ?: ($bulanNama . ' ' . $tahun);

        $s->setCellValue("A{$r}", 'Nomor')->setCellValue("B{$r}", ': ' . ($p['no_usulan'] ?? '-'))
            ->setCellValue("D{$r}", 'Pekanbaru, ' . $tglUsulan);
        $r++;
        $s->setCellValue("A{$r}", 'Sifat')->setCellValue("B{$r}", ': Biasa');
        $r++;
        $s->setCellValue("A{$r}", 'Lampiran')->setCellValue("B{$r}", ': -');
        $r++;
        $s->setCellValue("A{$r}", 'Hal')->setCellValue("B{$r}", ": Usulan Kebutuhan {$jenisLabel} Bulan {$bulanNama} {$tahun}");
        $s->getStyle("B{$r}")->getFont()->setBold(true);
        $r += 2;

        $s->setCellValue("A{$r}", 'Yth.');
        $r++;
        $s->setCellValue("A{$r}", 'Bendahara Pengeluaran');
        $r++;
        $s->setCellValue("A{$r}", 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau');
        $r++;
        $s->setCellValue("A{$r}", 'di Tempat');
        $r += 2;

        $s->setCellValue("A{$r}", 'Dengan hormat,');
        $r++;
        $kegiatan = $p['kegiatan_label'] ?: 'Dukungan Kegiatan Prasarana Strategis';
        $s->mergeCells("A{$r}:D{$r}");
        $s->setCellValue("A{$r}", "Sehubungan dengan kebutuhan operasional dan kelancaran pelaksanaan kegiatan {$kegiatan} pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau bulan {$bulanNama} {$tahun}, bersama ini kami selaku Pejabat Pembuat Komitmen (PPK) mengusulkan pengadaan {$jenisLabel} untuk menunjang pelaksanaan tugas dan kegiatan administrasi.");
        $s->getStyle("A{$r}")->getAlignment()->setWrapText(true);
        $s->getRowDimension($r)->setRowHeight(48);
        $r += 2;

        $s->setCellValue("A{$r}", "Adapun rincian kebutuhan {$jenisLabel} yang diusulkan adalah sebagai berikut:");
        $r++;

        $headerRow = $r;
        $s->setCellValue("A{$r}", 'No.')->setCellValue("B{$r}", 'Nama Barang')->setCellValue("C{$r}", 'Volume')->setCellValue("D{$r}", 'Satuan');
        $s->getStyle("A{$r}:D{$r}")->getFont()->setBold(true);
        $s->getStyle("A{$r}:D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->getStyle("A{$r}:D{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAECEF');
        $r++;

        $no = 1;
        foreach ($items as $item) {
            $s->setCellValue("A{$r}", $no++)
                ->setCellValue("B{$r}", $item['nama_barang'])
                ->setCellValue("C{$r}", (float) $item['volume'])
                ->setCellValue("D{$r}", $item['satuan']);
            $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $s->getStyle("A{$headerRow}:D" . ($r - 1))->applyFromArray($this->borderTableStyle());
        $r++;

        $s->mergeCells("A{$r}:D{$r}");
        $s->setCellValue("A{$r}", "Demikian usulan kebutuhan {$jenisLabel} ini disampaikan untuk dapat dipertimbangkan dan ditindaklanjuti sesuai dengan ketersediaan anggaran serta ketentuan yang berlaku. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.");
        $s->getStyle("A{$r}")->getAlignment()->setWrapText(true);
        $s->getRowDimension($r)->setRowHeight(42);
        $r += 2;

        $s->setCellValue("C{$r}", 'Pejabat Pembuat Komitmen');
        $r++;
        $s->setCellValue("C{$r}", 'Pelaksanaan Prasarana Strategis Riau');
        $r += 4;
        $s->setCellValue("C{$r}", $p['ppk_nama'] ?? 'NURHIDAYAT NUGROHO, S.Ars.');
        $s->getStyle("C{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        $s->setCellValue("C{$r}", 'NIP ' . ($p['ppk_nip'] ?? '199012212018021001'));

        return $r + 4;
    }

    private function renderSpbBlock(Worksheet $s, int $r, array $p, array $items, array $penyedia, string $kopPath): int
    {
        $r = $this->addKopImage($s, $r, 'B', $kopPath);

        $s->mergeCells("B{$r}:F{$r}");
        $s->setCellValue("B{$r}", 'SURAT PESANAN BARANG');
        $s->getStyle("B{$r}")->getFont()->setBold(true)->setSize(12)->setUnderline(true);
        $s->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r++;

        $s->mergeCells("B{$r}:F{$r}");
        $s->setCellValue("B{$r}", 'No. ' . ($p['no_pesanan'] ?: '      /PES/SATKER/PPS-RIAU/' . ($p['tahun_anggaran'] ?? 2026)));
        $s->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r += 2;

        $s->setCellValue("B{$r}", 'Kepada Yth :');
        $r++;
        $s->setCellValue("B{$r}", $penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI');
        $s->getStyle("B{$r}")->getFont()->setBold(true);
        $r++;
        $s->setCellValue("B{$r}", $penyedia['alamat'] ?? '');
        $r++;
        $s->setCellValue("B{$r}", 'di-');
        $r++;
        $s->setCellValue("B{$r}", $penyedia['kota'] ?? 'Pekanbaru');
        $r += 2;

        $s->setCellValue("B{$r}", 'Barang Yang dipesan :');
        $r++;

        $headerRow = $r;
        $s->setCellValue("B{$r}", 'No.')
            ->setCellValue("C{$r}", 'Nama Barang')
            ->setCellValue("D{$r}", 'Banyaknya')
            ->setCellValue("E{$r}", 'Satuan')
            ->setCellValue("F{$r}", 'Keterangan');
        $s->getStyle("B{$r}:F{$r}")->getFont()->setBold(true);
        $s->getStyle("B{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->getStyle("B{$r}:F{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAECEF');
        $r++;

        $no = 1;
        foreach ($items as $item) {
            $s->setCellValue("B{$r}", $no++)
                ->setCellValue("C{$r}", $item['nama_barang'])
                ->setCellValue("D{$r}", (float) $item['volume'])
                ->setCellValue("E{$r}", $item['satuan'])
                ->setCellValue("F{$r}", $item['keterangan'] ?? '');
            $s->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $s->getStyle("B{$headerRow}:F" . ($r - 1))->applyFromArray($this->borderTableStyle());
        $r += 2;

        $batas = self::tanggalIndo($p['tgl_batas_terima'] ?? null) ?: '-';
        $denda = number_format((float) ($p['persen_denda_harian'] ?? 2), 0);
        $s->setCellValue("B{$r}", " - Barang tersebut sudah kami terima selambat-lambatnya tanggal {$batas}");
        $r++;
        $s->setCellValue("B{$r}", " - Keterlambatan dikenakan denda {$denda}% dari harga pesanan/ hari");
        $r += 2;

        $tglPes = self::tanggalIndo($p['tgl_pesanan'] ?? null) ?: '-';
        $s->setCellValue("D{$r}", 'Pekanbaru, ' . $tglPes);
        $r += 2;
        $s->setCellValue("D{$r}", 'PEMESAN :');
        $r++;
        $s->setCellValue("D{$r}", 'Pejabat Pembuat Komitmen');
        $r++;
        $s->setCellValue("D{$r}", 'Pelaksanaan Prasarana Strategis');
        $r += 4;
        $s->setCellValue("D{$r}", $p['ppk_nama'] ?? 'NURHIDAYAT NUGROHO, S.Ars.');
        $s->getStyle("D{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        $s->setCellValue("D{$r}", 'NIP ' . ($p['ppk_nip'] ?? '199012212018021001'));

        return $r + 4;
    }

    private function renderBastBlock(Worksheet $s, int $r, array $p, array $penyedia, string $kopPath): int
    {
        $r = $this->addKopImage($s, $r, 'A', $kopPath);

        $s->mergeCells("A{$r}:G{$r}");
        $s->setCellValue("A{$r}", 'BERITA ACARA SERAH TERIMA DAN PEMERIKSAAN BARANG');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setSize(12)->setUnderline(true);
        $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r++;

        $s->mergeCells("A{$r}:G{$r}");
        $s->setCellValue("A{$r}", 'Nomor : ' . ($p['no_bastp'] ?: '        /BASTP/SATKER/PPS-RIAU/' . ($p['tahun_anggaran'] ?? 2026)));
        $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r += 2;

        $kalimat = self::tanggalKalimatBast($p['tgl_bastp'] ?? null);
        $s->mergeCells("A{$r}:G{$r}");
        $s->setCellValue("A{$r}", "{$kalimat}, bertempat di Pekanbaru yang bertanda tangan dibawah ini :");
        $s->getStyle("A{$r}")->getAlignment()->setWrapText(true);
        $s->getRowDimension($r)->setRowHeight(28);
        $r += 2;

        $s->setCellValue("A{$r}", 'Nama')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", $p['ppk_nama'] ?? 'NURHIDAYAT NUGROHO, S.Ars.');
        $r++;
        $s->setCellValue("A{$r}", 'Jabatan')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis');
        $r++;
        $s->setCellValue("A{$r}", 'NIP')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", $p['ppk_nip'] ?? '199012212018021001');
        $r++;
        $s->setCellValue("A{$r}", 'Alamat')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", 'Jalan Datuk Setia Maharaja No. 15, Tangkerang Labuai Bukit Raya Kota Pekanbaru');
        $r++;
        $s->setCellValue("A{$r}", 'Yang selanjutnya disebut sebagai PIHAK PERTAMA');
        $s->getStyle("A{$r}")->getFont()->setBold(true);
        $r += 2;

        $s->setCellValue("A{$r}", 'Nama')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", $penyedia['nama_direktur'] ?? 'ILHAM RAMAPALA');
        $r++;
        $s->setCellValue("A{$r}", 'Jabatan')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", ($penyedia['jabatan_pimpinan'] ?? 'Direktur') . ' ' . ($penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI'));
        $r++;
        $s->setCellValue("A{$r}", 'Alamat')->setCellValue("B{$r}", ':')->setCellValue("C{$r}", ($penyedia['alamat'] ?? '') . ' Kota ' . ($penyedia['kota'] ?? 'Pekanbaru'));
        $r++;
        $s->setCellValue("A{$r}", 'Yang selanjutnya disebut sebagai PIHAK KEDUA');
        $s->getStyle("A{$r}")->getFont()->setBold(true);
        $r += 2;

        $noPes = $p['no_pesanan'] ?: '        /PES/SATKER/PPS-RIAU/' . ($p['tahun_anggaran'] ?? 2026);
        $s->mergeCells("A{$r}:G{$r}");
        $s->setCellValue("A{$r}", "PIHAK KEDUA telah menyerahkan kepada PIHAK PERTAMA dan PIHAK PERTAMA telah menerima dan memeriksa penyerahan dari PIHAK KEDUA barang-barang persediaan dengan Surat Pesanan Barang Nomor : {$noPes} dengan rincian sebagaimana terlampir pada Berita Acara ini.");
        $s->getStyle("A{$r}")->getAlignment()->setWrapText(true);
        $s->getRowDimension($r)->setRowHeight(42);
        $r += 2;

        $s->mergeCells("A{$r}:G{$r}");
        $s->setCellValue("A{$r}", 'Dengan ditanda tanganinya Berita Acara ini, maka segala tanggungjawab terhadap penyimpangan, pengamanan, dan penatausahaan barang-barang persediaan tersebut berada pada PIHAK PERTAMA.');
        $s->getStyle("A{$r}")->getAlignment()->setWrapText(true);
        $s->getRowDimension($r)->setRowHeight(30);
        $r += 2;

        $s->setCellValue("A{$r}", 'Demikian berita acara ini dibuat untuk dapat dipergunakan sebagaimana mestinya.');
        $r += 2;

        $s->setCellValue("A{$r}", 'PIHAK KEDUA')->setCellValue("G{$r}", 'PIHAK PERTAMA');
        $s->getStyle("A{$r}")->getFont()->setBold(true);
        $s->getStyle("G{$r}")->getFont()->setBold(true);
        $r++;
        $s->setCellValue("A{$r}", $penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI')
            ->setCellValue("G{$r}", 'PEJABAT PEMBUAT KOMITMEN');
        $r++;
        $s->setCellValue("G{$r}", 'PELAKSANAAN PRASARANA STRATEGIS');
        $r += 4;
        $s->setCellValue("A{$r}", $penyedia['nama_direktur'] ?? 'ILHAM RAMAPALA')
            ->setCellValue("G{$r}", $p['ppk_nama'] ?? 'NURHIDAYAT NUGROHO, S.Ars.');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setUnderline(true);
        $s->getStyle("G{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        $s->setCellValue("A{$r}", $penyedia['jabatan_pimpinan'] ?? 'Direktur')
            ->setCellValue("G{$r}", 'NIP ' . ($p['ppk_nip'] ?? '199012212018021001'));

        return $r + 4;
    }

    private function renderFakturBlock(Worksheet $s, int $r, array $p, array $items, array $penyedia): int
    {
        $s->mergeCells("A{$r}:E{$r}");
        $s->setCellValue("A{$r}", $penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setSize(12);
        $r++;
        $s->mergeCells("A{$r}:E{$r}");
        $s->setCellValue("A{$r}", ($penyedia['alamat'] ?? '') . ' - ' . ($penyedia['kota'] ?? 'Pekanbaru'));
        $r += 2;

        $tglFaktur = self::tanggalIndo($p['tgl_faktur'] ?? null) ?: '-';
        $s->setCellValue("A{$r}", 'FAKTUR PENAGIHAN')->setCellValue("D{$r}", 'Pekanbaru, ' . $tglFaktur);
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        if (! empty($p['no_faktur'])) {
            $s->setCellValue("A{$r}", 'No. Faktur: ' . $p['no_faktur']);
            $r++;
        }
        $s->setCellValue("A{$r}", 'Kepada Yth: Pejabat Pembuat Komitmen Satker Pelaksanaan Prasarana Strategis Riau');
        $r += 2;

        $headerRow = $r;
        $s->setCellValue("A{$r}", 'Banyaknya')
            ->setCellValue("B{$r}", 'Satuan')
            ->setCellValue("C{$r}", 'Nama Barang')
            ->setCellValue("D{$r}", 'Harga Satuan')
            ->setCellValue("E{$r}", 'Jumlah');
        $s->getStyle("A{$r}:E{$r}")->getFont()->setBold(true);
        $s->getStyle("A{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAECEF');
        $r++;

        $startItemRow = $r;
        foreach ($items as $item) {
            $s->setCellValue("A{$r}", (float) $item['volume'])
                ->setCellValue("B{$r}", $item['satuan'])
                ->setCellValue("C{$r}", $item['nama_barang'])
                ->setCellValue("D{$r}", (float) $item['harga_satuan'])
                ->setCellValue("E{$r}", "=A{$r}*D{$r}");
            $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("D{$r}:E{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $r++;
        }
        $endItemRow = max($startItemRow, $r - 1);

        $totalRow = $r;
        $s->mergeCells("A{$totalRow}:D{$totalRow}");
        $s->setCellValue("A{$totalRow}", 'JUMLAH TOTAL');
        $s->setCellValue("E{$totalRow}", "=SUM(E{$startItemRow}:E{$endItemRow})");
        $s->getStyle("A{$totalRow}:E{$totalRow}")->getFont()->setBold(true);
        $s->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $s->getStyle("E{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $s->getStyle("A{$headerRow}:E{$totalRow}")->applyFromArray($this->borderTableStyle());
        $r++;

        $s->setCellValue("B{$r}", 'Terbilang :');
        $s->mergeCells("C{$r}:E{$r}");
        $s->setCellValue("C{$r}", $p['terbilang'] ?? '');
        $s->getStyle("B{$r}:C{$r}")->getFont()->setItalic(true)->setBold(true);
        $r += 2;

        $s->setCellValue("D{$r}", 'Hormat Kami,');
        $r += 3;
        $s->setCellValue("D{$r}", $penyedia['nama_direktur'] ?? 'ILHAM RAMAPALA');
        $s->getStyle("D{$r}")->getFont()->setBold(true)->setUnderline(true);

        return $r + 4;
    }

    private function renderKwiBlock(Worksheet $s, int $r, array $p, array $penyedia): int
    {
        $topRow = $r;
        $s->mergeCells("B{$r}:F{$r}");
        $s->setCellValue("B{$r}", 'KUITANSI / BUKTI PEMBAYARAN');
        $s->getStyle("B{$r}")->getFont()->setBold(true)->setSize(12)->setUnderline(true);
        $s->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r += 2;

        $s->setCellValue("H{$r}", 'Tahun Anggaran')->setCellValue("J{$r}", (int) ($p['tahun_anggaran'] ?? 2026));
        $r++;
        $s->setCellValue("H{$r}", 'Nomor Bukti')->setCellValue("J{$r}", $p['no_bukti_kuitansi'] ?? '');
        $r++;
        $s->setCellValue("H{$r}", 'Mata Anggaran')->setCellValue("J{$r}", $p['kode_mata_anggaran'] ?? '7987.FAD.002.101.A.521211');
        $r += 2;

        $s->setCellValue("B{$r}", 'SUDAH TERIMA DARI')->setCellValue("C{$r}", ':')
            ->setCellValue("D{$r}", 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis');
        $r += 2;

        $s->setCellValue("B{$r}", 'JUMLAH UANG')->setCellValue("C{$r}", ':')
            ->setCellValue("D{$r}", 'Rp.')->setCellValue("E{$r}", (float) ($p['total_nilai'] ?? 0))->setCellValue("H{$r}", ',-');
        $s->getStyle("E{$r}")->getFont()->setBold(true);
        $s->getStyle("E{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $r += 2;

        $s->setCellValue("B{$r}", 'TERBILANG')->setCellValue("C{$r}", ':');
        $s->mergeCells("D{$r}:J{$r}");
        $s->setCellValue("D{$r}", $p['terbilang'] ?? '');
        $s->getStyle("D{$r}")->getFont()->setItalic(true)->setBold(true);
        $r += 2;

        $bulanNama = self::bulanNama((int) ($p['bulan'] ?? 1));
        $tahun     = (int) ($p['tahun_anggaran'] ?? 2026);
        $kategori  = (string) ($p['kategori'] ?? 'ATK');
        $jenisLabel = $kategori === 'Supplies Komputer' ? 'Supplies Komputer' : 'Alat Tulis Kantor (ATK)';
        $kegiatan  = $p['kegiatan_label'] ?: 'Dukungan Kegiatan Prasarana Strategis';

        $s->setCellValue("B{$r}", 'UNTUK PEMBAYARAN')->setCellValue("C{$r}", ':');
        $s->mergeCells("D{$r}:J{$r}");
        $s->setCellValue("D{$r}", "Biaya Pembelian {$jenisLabel} untuk keperluan Administrasi {$kegiatan} pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau bulan {$bulanNama} {$tahun}, sebagaimana faktur terlampir.");
        $s->getStyle("D{$r}")->getAlignment()->setWrapText(true);
        $s->getRowDimension($r)->setRowHeight(36);
        $r += 2;

        $tglKwi = self::tanggalIndo($p['tgl_kuitansi'] ?? null) ?: ($bulanNama . ' ' . $tahun);
        $s->setCellValue("H{$r}", 'Pekanbaru, ' . $tglKwi);
        $r++;
        $s->setCellValue("H{$r}", 'Yang menerima uang,');
        $r++;
        $s->setCellValue("H{$r}", $penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI');
        $r += 4;
        $s->setCellValue("H{$r}", $penyedia['nama_direktur'] ?? 'ILHAM RAMAPALA');
        $s->getStyle("H{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        $s->setCellValue("H{$r}", $penyedia['jabatan_pimpinan'] ?? 'Direktur');
        $r += 2;

        $tglLunas = self::tanggalSlash($p['tgl_lunas_bayar'] ?? null) ?: '     /     /' . $tahun;
        $s->setCellValue("A{$r}", 'Yang Memerintahkan Pembayaran :');
        $r++;
        $s->setCellValue("A{$r}", 'Pejabat Pembuat Komitmen')->setCellValue("I{$r}", "Setuju dan Lunas dibayar Tgl. {$tglLunas}");
        $r++;
        $s->setCellValue("A{$r}", 'Pelaksanaan Prasarana Strategis')->setCellValue("I{$r}", 'Bendahara Pengeluaran,');
        $r += 4;
        $s->setCellValue("A{$r}", $p['ppk_nama'] ?? 'NURHIDAYAT NUGROHO, S.Ars.')
            ->setCellValue("I{$r}", $p['bendahara_nama'] ?? 'KH. SRI HANDAYANI, S.Si., M.T.');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setUnderline(true);
        $s->getStyle("I{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        $s->setCellValue("A{$r}", 'NIP ' . ($p['ppk_nip'] ?? '199012212018021001'))
            ->setCellValue("I{$r}", 'NIP ' . ($p['bendahara_nip'] ?? '198204022014122002'));

        $s->getStyle("A{$topRow}:J{$r}")->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);

        return $r + 4;
    }

    private function renderLampBastpBlock(Worksheet $s, int $r, array $p, array $items, array $penyedia): int
    {
        $s->mergeCells("E{$r}:I{$r}");
        $s->setCellValue("E{$r}", 'Lampiran Berita Acara Serah Terima Barang Persediaan');
        $s->getStyle("E{$r}")->getFont()->setBold(true);
        $r += 2;

        $s->setCellValue("E{$r}", 'Nomor')->setCellValue("F{$r}", ': ' . ($p['no_bastp'] ?: '        /BASTP/SATKER/PPS-RIAU/' . ($p['tahun_anggaran'] ?? 2026)));
        $r++;
        $s->setCellValue("E{$r}", 'Tanggal')->setCellValue("F{$r}", ': ' . (self::tanggalIndo($p['tgl_bastp'] ?? null) ?: '-'));
        $r += 2;

        $headerRow = $r;
        $s->setCellValue("A{$r}", 'No')
            ->setCellValue("B{$r}", 'Nomor Bukti')
            ->setCellValue("C{$r}", 'Tgl. Bukti')
            ->setCellValue("D{$r}", 'Uraian Barang')
            ->setCellValue("E{$r}", 'Kondisi')
            ->setCellValue("F{$r}", 'Harga Perolehan')
            ->setCellValue("G{$r}", 'Jlh Unit')
            ->setCellValue("H{$r}", 'Satuan')
            ->setCellValue("I{$r}", 'Ket');
        $s->getStyle("A{$r}:I{$r}")->getFont()->setBold(true);
        $s->getStyle("A{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->getStyle("A{$r}:I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAECEF');
        $r++;

        $tglBukti = self::tanggalSlash($p['tgl_bastp'] ?? null);
        $no = 1;
        foreach ($items as $idx => $item) {
            $s->setCellValue("A{$r}", $idx === 0 ? '1.' : ($no . '.'))
                ->setCellValue("B{$r}", $idx === 0 ? ($p['no_bukti_kuitansi'] ?? '') : '')
                ->setCellValue("C{$r}", $idx === 0 ? $tglBukti : '')
                ->setCellValue("D{$r}", $item['nama_barang'])
                ->setCellValue("E{$r}", $item['kondisi_barang'] ?? 'Baik')
                ->setCellValue("F{$r}", (float) $item['harga_satuan'])
                ->setCellValue("G{$r}", (float) $item['volume'])
                ->setCellValue("H{$r}", $item['satuan'])
                ->setCellValue("I{$r}", $item['keterangan'] ?? '');
            $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $no++;
            $r++;
        }
        $s->getStyle("A{$headerRow}:I" . ($r - 1))->applyFromArray($this->borderTableStyle());
        $r += 2;

        $s->setCellValue("A{$r}", 'PIHAK KEDUA')->setCellValue("E{$r}", 'PIHAK PERTAMA');
        $s->getStyle("A{$r}")->getFont()->setBold(true);
        $s->getStyle("E{$r}")->getFont()->setBold(true);
        $r++;
        $s->setCellValue("A{$r}", $penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI')
            ->setCellValue("E{$r}", 'PEJABAT PEMBUAT KOMITMEN');
        $r++;
        $s->setCellValue("E{$r}", 'PELAKSANAAN PRASARANA STRATEGIS');
        $r += 4;
        $s->setCellValue("A{$r}", $penyedia['nama_direktur'] ?? 'ILHAM RAMAPALA')
            ->setCellValue("E{$r}", $p['ppk_nama'] ?? 'NURHIDAYAT NUGROHO, S.Ars.');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setUnderline(true);
        $s->getStyle("E{$r}")->getFont()->setBold(true)->setUnderline(true);
        $r++;
        $s->setCellValue("A{$r}", $penyedia['jabatan_pimpinan'] ?? 'Direktur')
            ->setCellValue("E{$r}", 'NIP ' . ($p['ppk_nip'] ?? '199012212018021001'));

        return $r + 4;
    }

    /**
     * Ekspor Rekapitulasi Pengadaan ATK & Supplies ke Excel.
     */
    public function buildRekapWorkbook(array $rows, int $tahun): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $s = $spreadsheet->getActiveSheet();
        $s->setTitle('Rekapitulasi ' . $tahun);
        $s->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $s->mergeCells('A1:I1');
        $s->setCellValue('A1', "REKAPITULASI PENGADAAN ATK & SUPPLIES KOMPUTER TAHUN {$tahun}");
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $s->mergeCells('A2:I2');
        $s->setCellValue('A2', 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau');
        $s->getStyle('A2')->getFont()->setItalic(true);

        $r = 4;
        $headers = ['No', 'Bulan', 'Kategori', 'No. Pesanan', 'No. BASTP', 'Penyedia', 'Mata Anggaran', 'Status', 'Total Nilai (Rp)'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        foreach ($headers as $idx => $h) {
            $s->setCellValue($cols[$idx] . $r, $h);
        }
        $s->getStyle("A{$r}:I{$r}")->getFont()->setBold(true);
        $s->getStyle("A{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->getStyle("A{$r}:I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAECEF');
        $headerRow = $r;
        $r++;

        $startData = $r;
        foreach ($rows as $i => $row) {
            $s->setCellValue("A{$r}", $i + 1)
                ->setCellValue("B{$r}", self::bulanNama((int) ($row['bulan'] ?? 0)))
                ->setCellValue("C{$r}", $row['kategori'] ?? '')
                ->setCellValue("D{$r}", $row['no_pesanan'] ?: '-')
                ->setCellValue("E{$r}", $row['no_bastp'] ?: '-')
                ->setCellValue("F{$r}", $row['nama_perusahaan'] ?? '-')
                ->setCellValue("G{$r}", $row['kode_mata_anggaran'] ?? '-')
                ->setCellValue("H{$r}", strtoupper((string) ($row['status'] ?? 'draft')))
                ->setCellValue("I{$r}", (float) ($row['total_nilai'] ?? 0));
            $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("I{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $r++;
        }

        if ($rows !== []) {
            $endData = $r - 1;
            $s->mergeCells("A{$r}:H{$r}");
            $s->setCellValue("A{$r}", 'TOTAL KESELURUHAN');
            $s->setCellValue("I{$r}", "=SUM(I{$startData}:I{$endData})");
            $s->getStyle("A{$r}:I{$r}")->getFont()->setBold(true);
            $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $s->getStyle("I{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $s->getStyle("A{$headerRow}:I{$r}")->applyFromArray($this->borderTableStyle());
        } else {
            $s->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray($this->borderTableStyle());
        }

        foreach ($cols as $col) {
            $s->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
