<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 * @property M_Master $M_Master
 */

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class C_Paket extends BaseController {
    public function __construct() {
        $this->l_auth = new \App\Libraries\L_Auth();
        $this->l_auth->cek_auth();
    }

    public function List()
    {
        $data['data'] = $this->M_AllFunction->Get('vw_trn_paket_hdr');
        $data['penyedia'] = $this->M_AllFunction->Get('mst_penyedia');
        $data['pemilihan'] = $this->M_AllFunction->Get('mst_pemilihan_penyedia');
        $data['ppk'] = $this->M_AllFunction->Where('mst_user', "jabatan_id = 1");
		return $this->template->display('paket/list', $data);
    }

    public function ListDetail(){
        $data['penyedia'] = $this->M_AllFunction->Get('mst_penyedia');
        $data['pemilihan'] = $this->M_AllFunction->Get('mst_pemilihan_penyedia');
        $data['ppk'] = $this->M_AllFunction->Where('mst_user', "jabatan_id = 1");

        $no_kontrak = $this->request->getPost('nomor_kontrak');
        $data['header'] = $this->M_AllFunction->Where('vw_trn_paket_hdr', "nomor_kontrak = '" . $no_kontrak . "'")[0];
        $data['detail_head'] = $this->M_AllFunction->Where('mst_form_paket', "level = 1");
        $data['section'] = $this->M_AllFunction->CustomQuery("SELECT
                mst_form_paket.*,
                COALESCE(trn_paket_dtl.kelengkapan, 0) AS kelengkapan,
                COALESCE(trn_paket_dtl.referensi_ki, 0) AS referensi_ki,
                trn_paket_dtl.keterangan
            FROM mst_form_paket
            LEFT JOIN trn_paket_dtl
            ON
                mst_form_paket.section = trn_paket_dtl.section
                AND (COALESCE(mst_form_paket.no, '') = COALESCE(trn_paket_dtl.no, ''))
                AND (COALESCE(mst_form_paket.sub_lv1, '') = COALESCE(trn_paket_dtl.sub_lv1, ''))
                AND (COALESCE(mst_form_paket.sub_lv2, '') = COALESCE(trn_paket_dtl.sub_lv2, ''))
                AND (COALESCE(mst_form_paket.sub_lv3, '') = COALESCE(trn_paket_dtl.sub_lv3, ''))
                AND trn_paket_dtl.nomor_kontrak = '$no_kontrak'
            ORDER BY mst_form_paket.id");
        $data['add_on'] = $this->M_AllFunction->Where('trn_paket_add_on', "nomor_kontrak = '" . $no_kontrak . "' ORDER BY add_on_ke ASC");
        return view('paket/detail_list', $data);
    }

    public function savePaketHDR(){
        $data = array(
            'nomor_kontrak'          => $this->request->getPost('nomor_kontrak'),
            'nilai_kontrak'          => preg_replace("/[^0-9]/", "", $this->request->getPost('nilai_kontrak')),
            'nama_paket'             => $this->request->getPost('nama_paket'),
            'nip_ppk'                => $this->request->getPost('nip_ppk'),
            'masa_pelaksanaan_awal'  => $this->request->getPost('masa_pelaksanaan_awal'),
            'masa_pelaksanaan_akhir' => $this->request->getPost('masa_pelaksanaan_akhir'),
            'tahun_anggaran'         => $this->request->getPost('tahun_anggaran'),
            'penyedia_id'            => $this->request->getPost('penyedia_id'),
            'metode_pemilihan_id'    => $this->request->getPost('metode_pemilihan_id'),
            'created_by'             => session()->get('username'),
        );
        if($this->M_AllFunction->Replaces('trn_paket_hdr', $data)){
            session()->set('success_notification', 'Data berhasil disimpan');
        } else {
            session()->set('failed_notification', 'Data gagal disimpan, cek kembali inputan anda');
        }
        return redirect()->to(base_url() . 'C_Paket/List');
    }

    function saveAddOn(){
        for($i = 1; $i <= 5; $i++){
            $data = array(
                'nomor_kontrak'          => $this->request->getPost('nomor_kontrak'),
                'add_on' => preg_replace("/[^0-9]/", "", $this->request->getPost('add_on_' . $i)),
                'add_on_ke' => $i,
                'tanggal' => $this->request->getPost('tanggal_add_on_' . $i),
                'keterangan' => $this->request->getPost('keterangan_add_on_' . $i),
                'created_by'     => session()->get('username'),
            );
            $this->M_AllFunction->Deletes('trn_paket_add_on', "nomor_kontrak = '" . $this->request->getPost('nomor_kontrak') . "' AND add_on_ke = '" . $i . "'");
            $this->M_AllFunction->Inserts('trn_paket_add_on', $data);
        }
        echo "success";
    }

    function savePaketDTL(){
        $nomor_kontrak = $this->request->getPost('nomor_kontrak');
        $section       = $this->request->getPost('section');
        $no            = $this->request->getPost('no');
        $sub_lv1       = $this->request->getPost('sub_lv1');
        $sub_lv2       = $this->request->getPost('sub_lv2');
        $sub_lv3       = $this->request->getPost('sub_lv3');
        $kelengkapan   = $this->request->getPost('kelengkapan');
        $referensi_ki  = $this->request->getPost('referensi_ki');
        $keterangan    = $this->request->getPost('keterangan');
        $is_checkbox   = $this->request->getPost('is_checkbox');

        for($i=0; $i < count($section); $i++){
            if($is_checkbox[$i] == 1){
                $data = array(
                    'nomor_kontrak' => $nomor_kontrak,
                    'section'       => $section[$i],
                    'no'            => empty($no[$i]) ? null : $no[$i],
                    'sub_lv1'       => empty($sub_lv1[$i]) ? null : $sub_lv1[$i],
                    'sub_lv2'       => empty($sub_lv2[$i]) ? null : $sub_lv2[$i],
                    'sub_lv3'       => empty($sub_lv3[$i]) ? null : $sub_lv3[$i],
                    'kelengkapan'   => $this->request->getPost('kelengkapan_' . $i) !== null ? $this->request->getPost('kelengkapan_' . $i) : 0,
                    'referensi_ki'  => $this->request->getPost('referensi_ki_' . $i) !== null ? $this->request->getPost('referensi_ki_' . $i) : 0,
                    'keterangan'    => $this->request->getPost('keterangan_' . $i) !== null ? $this->request->getPost('keterangan_' . $i) : null,
                    'updated_by'    => session()->get('username'),
                );
                $cek = $this->M_AllFunction->Where('trn_paket_dtl', "nomor_kontrak = '" . $nomor_kontrak . "' AND section = '" . $section[$i] . "' AND no " . (empty($no[$i]) ? "IS NULL" : "= '" . $no[$i] . "'") . " AND sub_lv1 " . (empty($sub_lv1[$i]) ? "IS NULL" : "= '" . $sub_lv1[$i] . "'") . " AND sub_lv2 " . (empty($sub_lv2[$i]) ? "IS NULL" : "= '" . $sub_lv2[$i] . "'") . " AND sub_lv3 " . (empty($sub_lv3[$i]) ? "IS NULL" : "= '" . $sub_lv3[$i] . "'"));
                if(count($cek) > 0){
                    $this->M_AllFunction->Updates('trn_paket_dtl', $data, "id = '" . $cek[0]->id . "'");
                } else {
                    $this->M_AllFunction->Inserts('trn_paket_dtl', $data);
                }
            }
        }

        echo "success";
    }

    function hapusPaket(){
        $nomor_kontrak = $this->request->getPost('nomor_kontrak');
        $this->M_AllFunction->Deletes('trn_paket_dtl', "nomor_kontrak = '" . $nomor_kontrak . "'");
        $this->M_AllFunction->Deletes('trn_paket_hdr', "nomor_kontrak = '" . $nomor_kontrak . "'");
        echo "success";
    }

    function exportPaket(){
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SIMAK PKJ');

        // --- 1. Pengaturan Lebar Kolom ---
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(5);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(3);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(5);
        $sheet->getColumnDimension('H')->setWidth(5);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(18);

        // Gabungkan kolom A sampai N untuk judul utama
        $sheet->mergeCells('D1:N1');
        $sheet->setCellValue('D1', 'DAFTAR SIMAK PENGENDALIAN PELAKSANAAN JASA KONSULTANSI');

        // Gabungkan kolom D sampai N untuk deskripsi
        $sheet->mergeCells('D2:N2');
        $sheet->setCellValue('D2', 'Dokumen-dokumen yang menjadi tanggung jawab PPK dalam Pelaksanaan Pengadaan Jasa Konstruksi');

        // Gabungkan kolom D sampai N untuk sumber peraturan
        $sheet->mergeCells('D3:N3');
        $sheet->setCellValue('D3', '(Berdasarkan: a. Peraturan LKPP 12/2021 tentang Pedoman Pelaksanaan Pengadaan Barang/Jasa Pemerintah Melalui Penyedia; ');

        // Gabungkan kolom D sampai N untuk sumber peraturan (lanjutan)
        $sheet->mergeCells('D4:N4');
        $sheet->setCellValue('D4', 'b. Peraturan Menteri PUPR 10/2021 tentang Pedoman Sistem Manajemen Keselamatan Konstruksi)');


        // --- 2. Data Utama (Baris 6 sampai 22) ---

        // Kolom D (Label)
        $labels = [
            'Satker', 'PPK', 'NIP', 'Data Jasa Konsultansi', 'Jenis Pekerjaan Jasa Konsultansi',
            'Nama Paket', 'Masa Pelaksanaan', 'Tahun Anggaran', 'Pagu Anggaran', 'Penyedia',
            'Nomor Kontrak', 'Add Kontrak (apabila ada)', 'Nilai Kontrak', 'Nilai Add Kontrak',
            'Metode Pemilihan', 'Tahapan Pekerjaan', 'Tanggal Pemeriksaan', 'Kelengkapan dokumen administrasi (%)'
        ];

        $data = [
            'Pelaksanaan Prasarana Strategis Riau', 'Nurhidayat Nugroho, S.Ars', '199012212018021000',
            'Perencanaan/Perancangan/Pengawasan/Manajemen Konstruksi/Lainnya',
            'Supervisi Rehabilitasi dan Renovasi Madrasah PHTc Provinsi Riau 1',
            'SYC', '2025', 'Rp', '785.706.000,00', 'CV. CITRAMATRA ARSITEK',
            'HK.02.01/PPS-RIAU/SPV-PHTc.1/2025/01',
            '', 'Rp', '706.879.000,00',
            'Pengadaan Langsung/Penunjukan Langsung/Seleksi',
            '', ':', '0.344827586206897'
        ];

        // Kolom F (Isi Data)
        $dataCells = ['F6', 'F7', 'F8', 'F9', 'F10', 'F11', 'F12', 'F13', 'I13', 'F14', 'F15', 'F16', 'F17', 'I17', 'F18', 'F19', 'F20', 'F21', 'F22'];

        $rowIndex = 6;
        foreach ($labels as $index => $label) {
            $sheet->setCellValue('D' . $rowIndex, $label);
            $sheet->setCellValue('E' . $rowIndex, ':');
            if (isset($data[$index])) {
                // Data di Kolom F (diperlukan penggabungan jika data panjang)
                $startCell = 'F' . $rowIndex;
                
                // Pengecekan khusus untuk data yang harus digabung ke kanan atau ke baris berikutnya
                if ($rowIndex == 9) { // Data Jenis Pekerjaan Jasa Konsultansi, gabung F9:N9
                    $sheet->mergeCells('F9:N9');
                    $sheet->setCellValue('F9', $data[$index]);
                } elseif ($rowIndex == 10) { // Nama Paket, gabung F10:N10
                    $sheet->mergeCells('F10:N10');
                    $sheet->setCellValue('F10', $data[$index]);
                } elseif ($rowIndex == 15) { // Nomor Kontrak, gabung F15:N15
                    $sheet->mergeCells('F15:N15');
                    $sheet->setCellValue('F15', $data[$index]);
                } elseif ($rowIndex == 19) { // Metode Pemilihan, gabung F19:N19
                    $sheet->mergeCells('F19:N19');
                    $sheet->setCellValue('F19', $data[$index]);
                } elseif ($rowIndex == 20) { // Tahapan Pekerjaan, gabung F20:N20
                    $sheet->mergeCells('F20:N20');
                    $sheet->setCellValue('F20', $data[$index]);
                } elseif ($rowIndex == 22) { // Kelengkapan dokumen, gabung F22:N22
                    $sheet->mergeCells('F22:N22');
                    $sheet->setCellValue('F22', $data[$index]);
                } else {
                    $sheet->setCellValue($startCell, $data[$index]);
                }
            }
            
            // Penempatan data khusus di kolom lain
            if ($rowIndex == 13) {
                $sheet->setCellValue('F13', $data[7]); // "Rp"
                $sheet->setCellValue('I13', $data[8]); // "785.706.000,00"
            } elseif ($rowIndex == 17) {
                $sheet->setCellValue('F17', $data[12]); // "Rp"
                $sheet->setCellValue('I17', $data[13]); // "706.879.000,00"
            } elseif ($rowIndex == 21) {
                $sheet->setCellValue('F21', $data[16]); // ":"
            }

            $rowIndex++;
        }

        // Tambahkan penggabungan sel untuk data yang pendek (misal Nilai Kontrak)
        $sheet->mergeCells('F6:H6'); // Satker
        $sheet->mergeCells('F7:H7'); // PPK
        $sheet->mergeCells('F8:H8'); // NIP
        $sheet->mergeCells('F11:H11'); // Masa Pelaksanaan
        $sheet->mergeCells('F12:H12'); // Tahun Anggaran
        $sheet->mergeCells('F14:H14'); // Penyedia
        $sheet->mergeCells('I6:N6'); // Pelaksanaan Prasarana Strategis Riau
        $sheet->mergeCells('I7:N7'); // Nurhidayat Nugroho, S.Ars
        $sheet->mergeCells('I8:N8'); // 199012212018021000
        $sheet->mergeCells('I11:N11'); // SYC
        $sheet->mergeCells('I12:N12'); // 2025
        $sheet->mergeCells('I14:N14'); // CV. CITRAMATRA ARSITEK
        $sheet->mergeCells('I16:N16'); // Add Kontrak (apabila ada)
        $sheet->mergeCells('I21:N21'); // Tanggal Pemeriksaan

        // --- 3. Tabel Rincian (Baris 24 dan seterusnya) ---

        // Header Tabel Rincian
        $sheet->setCellValue('A24', 'No.');
        $sheet->setCellValue('B24', 'Tahapan');
        $sheet->setCellValue('F24', 'Bentuk Dokumen');
        $sheet->setCellValue('I24', 'Referensi');
        $sheet->setCellValue('J24', 'Kelengkapan Dokumen');
        $sheet->setCellValue('M24', 'Verifikasi DIt. KI');
        $sheet->setCellValue('N24', 'Keterangan');

        // Penggabungan Header Tabel Rincian
        $sheet->mergeCells('A24:A25'); // No.
        $sheet->mergeCells('B24:E25'); // Tahapan
        $sheet->mergeCells('F24:H25'); // Bentuk Dokumen
        $sheet->mergeCells('I24:I25'); // Referensi
        $sheet->mergeCells('J24:L24'); // Kelengkapan Dokumen (Ada/Tidak/Sesuai)
        $sheet->setCellValue('J25', 'Ada');
        $sheet->setCellValue('K25', 'Tidak');
        $sheet->setCellValue('L25', 'Sesuai');
        $sheet->mergeCells('M24:M25'); // Verifikasi DIt. KI
        $sheet->setCellValue('M25', 'Tidak Sesuai'); // Di gambar terlihat di M25
        $sheet->mergeCells('N24:N25'); // Keterangan

        // Isi Tabel Rincian
        $sheet->setCellValue('A26', 'A');
        $sheet->setCellValue('B26', 'PERSIAPAN PENGADAAN');
        $sheet->mergeCells('B26:N26'); // Gabungkan sel Tahapan
        $sheet->setCellValue('A27', '1');
        $sheet->setCellValue('B27', 'Penyiapan Dokumen Pengadaan');
        $sheet->mergeCells('B27:E27');
        $sheet->setCellValue('F27', 'Dokumen Kerangka Acuan Kerja yang telah...');
        $sheet->mergeCells('F27:H27');
        $sheet->setCellValue('I27', 'Peraturan LKPP Nomor 12 Tahun 2021');
        $sheet->setCellValue('J27', 'v');
        $sheet->setCellValue('K27', 'x');
        $sheet->setCellValue('L27', '1');
        $sheet->setCellValue('M27', '1');

        // --- 4. Pengaturan Gaya (Style) ---

        // Gaya untuk Judul Utama (D1)
        $styleTitle = [
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('D1')->applyFromArray($styleTitle);

        // Gaya untuk Deskripsi (D2, D3, D4)
        $styleDescription = [
            'font' => [
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('D2:D4')->applyFromArray($styleDescription);

        // Gaya untuk Label Utama (Kolom D)
        $styleLabel = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ];
        $sheet->getStyle('D6:D22')->applyFromArray($styleLabel);

        // Gaya untuk Data Utama (Kolom F sampai N)
        $styleData = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ];
        $sheet->getStyle('F6:N22')->applyFromArray($styleData);

        // Gaya khusus untuk sel mata uang (I13, I17)
        $styleCurrency = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ];
        $sheet->getStyle('I13:I14')->applyFromArray($styleCurrency);
        $sheet->getStyle('I17:I18')->applyFromArray($styleCurrency);

        // Gaya untuk Header Tabel Rincian (A24:N25)
        $styleHeaderTable = [
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'], // Warna biru muda
            ],
        ];
        $sheet->getStyle('A24:N25')->applyFromArray($styleHeaderTable);
        $sheet->getRowDimension(24)->setRowHeight(30); // Atur tinggi baris 24
        $sheet->getRowDimension(25)->setRowHeight(30); // Atur tinggi baris 25

        // Gaya untuk Isi Tabel Rincian (Baris 26 dan 27)
        $styleTableContent = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A26:N27')->applyFromArray($styleTableContent);

        // Pengaturan khusus untuk baris Tahapan (A26:N26)
        $sheet->getStyle('A26:N26')->getFont()->setBold(true);
        $sheet->getStyle('B26:N26')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A26')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Pengaturan khusus untuk cell dengan 'v' atau 'x' atau angka
        $styleCenter = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A27')->applyFromArray($styleCenter); // No. 1
        $sheet->getStyle('J27:M27')->applyFromArray($styleCenter); // v, x, 1, 1

        // --- 5. Output File ---

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $date = date('d-m-Y_H-i-s');
        header('Content-Disposition: attachment;filename="Export Paket ' . $date . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}