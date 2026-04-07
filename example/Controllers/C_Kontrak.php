<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 */

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;

class C_Kontrak extends BaseController {
    public function __construct() {
        $this->l_auth = new \App\Libraries\L_Auth();
        $this->l_auth->cek_auth();
    }

    private function findPaketIdByName($namaPaket)
    {
        $namaPaket = trim((string) $namaPaket);
        if ($namaPaket === '') {
            return null;
        }

        if (ctype_digit($namaPaket)) {
            $exists = $this->M_AllFunction->Where('trn_kontrak_paket', ['id' => $namaPaket]);
            if (!empty($exists)) {
                return $namaPaket;
            }
        }

        $paket = $this->M_AllFunction->Where('trn_kontrak_paket', ['nama_paket' => $namaPaket])[0] ?? null;
        return $paket->id ?? null;
    }

    public function Paket(){
        $data['data'] = $this->M_AllFunction->CustomQuery('SELECT id, nama_paket FROM trn_kontrak_paket');
        return $this->template->display("kontrak/paket", $data);
    }

    public function KI($id){
        $data['data'] = $this->M_AllFunction->Where('vw_kontrak_ki', "paket = '$id'");
        return $this->template->display("kontrak/ki", $data);
    }

    public function import_excel_ki()
    {
        $file = $this->request->getFile('excel_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();
            $spreadsheet = IOFactory::load($filePath);

            $sheetKI = $spreadsheet->getSheetByName('Kontrak KI');
            if ($sheetKI) {
                $dataRowsKI = $sheetKI->toArray();
                array_shift($dataRowsKI);

                foreach ($dataRowsKI as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = [
                        'nomor_kontrak'                        => $row[0],
                        'tanggal_kontrak'                      => !empty($row[1]) ? date('Y-m-d', strtotime($row[1])): null,
                        'paket'                                => $this->findPaketIdByName($row[2]) ?? $row[2],
                        'kode_personil'                        => $row[3],
                        'nama'                                 => $row[4],
                        'alamat'                               => $row[5],
                        'nik'                                  => $row[6],
                        'npwp'                                 => $row[7],
                        'jabatan'                              => $row[8],
                        'durasi_pelaksanaan'                   => $row[9],
                        'nomor_dipa'                           => $row[10],
                        'tanggal_dipa'                         => !empty($row[11]) ? date('Y-m-d', strtotime($row[11])): null,
                        'mata_anggaran'                        => $row[12],
                        'nomor_surat_undangan_pengadaan'       => $row[13],
                        'tanggal_surat_undangan_pengadaan'     => !empty($row[14]) ? date('Y-m-d', strtotime($row[14])): null,
                        'nomor_surat_berita_acara_pengadaan'   => $row[15],
                        'tanggal_surat_berita_acara_pengadaan' => !empty($row[16]) ? date('Y-m-d', strtotime($row[16])): null,
                        'nomor_surat_penawaran'                => $row[17],
                        'tanggal_surat_penawaran'              => !empty($row[18]) ? date('Y-m-d', strtotime($row[18])): null,
                        'nomor_undangan'                       => $row[19],
                        'total_penawaran'                      => str_replace(',', '', $row[20]),
                        'tanggal_awal'                         => !empty($row[21]) ? date('Y-m-d', strtotime($row[21])): null,
                        'tanggal_akhir'                        => !empty($row[22]) ? date('Y-m-d', strtotime($row[22])): null,
                        'tahun_anggaran'                       => $row[23],
                        'no_sppbj'                             => $row[24],
                        'tanggal_sppbj'                        => !empty($row[25]) ? date('Y-m-d', strtotime($row[25])): null,
                        'pejabat_ppk'                          => $row[26],
                        'nip_pejabat_ppk'                      => $row[27],
                        'kedudukan_pejabat_ppk'                => $row[28],
                        'nomor_surat_keputusan_menteri'        => $row[29],
                        'tanggal_surat_keputusan_menteri'      => !empty($row[30]) ? date('Y-m-d', strtotime($row[30])): null,
                        'nomor_perubahan_keputusan_menteri'    => $row[31],
                        'bank_nomor_rekening'                  => $row[32],
                        'bank_nama'                            => $row[33],
                        'bank_atas_nama'                       => $row[34],
                        'bank_pembayaran'                      => $row[35],
                        'kategori'                             => $row[36],
                        'nomor_telefon_ki'                     => $row[37],
                        'email_ki'                             => $row[38],
                        'nominal_kontrak'                      => str_replace(',', '', $row[39]),
                        'nominal_hps'                          => $row[40],
                        'nomor_spmk'                           => $row[41],
                        'nomor_baphp'                          => $row[42],
                        'nomor_surat_permohonan'               => $row[43],
                        'tanggal_surat_permohonan'             => !empty($row[44]) ? date('Y-m-d', strtotime($row[44])): null,
                        'nama_pekerjaan'                       => $row[45],
                        'jenis_pembayaran'                     => $row[46],
                        'nomor_bast'                           => $row[47],
                        'created_by'                           => session()->get('username'),
                    ];
                    $this->M_AllFunction->Replaces('trn_kontrak_ki', $data);
                }
            }

            $sheetPekerjaan = $spreadsheet->getSheetByName('Pekerjaan (BAPHP)');
            if ($sheetPekerjaan) {
                $dataRowsPekerjaan = $sheetPekerjaan->toArray();
                array_shift($dataRowsPekerjaan);

                $last_deleted_id = 0;

                foreach ($dataRowsPekerjaan as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $id_paket = $this->M_AllFunction->Where('trn_kontrak_paket', ['nama_paket' => $row[0]]);

                    if ($last_deleted_id == 0 || ($id_paket != $last_deleted_id)) {
                        $this->M_AllFunction->Deletes('trn_kontrak_ki_pekerjaan_baphp', ['id_kontrak_paket' => $id_paket[0]->id]);
                        $last_deleted_id = $id_paket[0]->id;
                    }

                    if (!empty($id_paket)) {
                        $data = array(
                            'id_kontrak_paket' => $id_paket[0]->id,
                            'pekerjaan' => $row[1],
                            'is_all_personel' => strtolower(trim($row[2])) === 'ya' ? true : false,
                        );
                        $this->M_AllFunction->Replaces('trn_kontrak_ki_pekerjaan_baphp', $data);
                    }
                }
            }

            $sheetPengalaman = $spreadsheet->getSheetByName('Pengalaman');
            if ($sheetPengalaman) {
                $dataRowsPengalaman = $sheetPengalaman->toArray();
                array_shift($dataRowsPengalaman);

                $nik = 0;

                foreach ($dataRowsPengalaman as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    if($nik != $row[0]){
                        $nik = $row[0];
                        $this->M_AllFunction->Deletes('trn_pengalaman_ki', ['nik' => $nik]);
                    }
                    $data = array(
                        'nik'           => $row[0],
                        'pengalaman'    => $row[1],
                        'tanggal_awal'  => !empty($row[1]) ? date('Y-m-d', strtotime($row[2])): null,
                        'tanggal_akhir' => !empty($row[1]) ? date('Y-m-d', strtotime($row[3])): null,
                    );
                    $this->M_AllFunction->Replaces('trn_pengalaman_ki', $data);
                }
            }

            return redirect()->to(base_url('C_Kontrak/Paket'))->with('success', 'Data berhasil diimpor.');
        } else {
            return redirect()->to(base_url('C_Kontrak/Paket'))->with('failed', 'Gagal mengunggah file.');
        }
    }

    public function simpan()
    {
        $data = [
            'nomor_kontrak'                        => $this->request->getPost('nomor_kontrak'),
            'tanggal_kontrak'                      => $this->request->getPost('tanggal_kontrak'),
            'paket'                                => $this->findPaketIdByName($this->request->getPost('paket')) ?? $this->request->getPost('paket'),
            'kode_personil'                        => $this->request->getPost('kode_personil'),
            'nama'                                 => $this->request->getPost('nama'),
            'nik'                                  => $this->request->getPost('nik'),
            'npwp'                                 => $this->request->getPost('npwp'),
            'jabatan'                              => $this->request->getPost('jabatan'),
            'harga_kontrak'                        => $this->request->getPost('harga_kontrak'),
            'nilai_kontrak'                        => $this->request->getPost('nilai_kontrak'),
            'durasi_pelaksanaan'                   => $this->request->getPost('durasi_pelaksanaan'),
            'nomor_dipa'                           => $this->request->getPost('nomor_dipa'),
            'tanggal_dipa'                         => $this->request->getPost('tanggal_dipa'),
            'mata_anggaran'                        => $this->request->getPost('mata_anggaran'),
            'nomor_surat_undangan_pengadaan'       => $this->request->getPost('nomor_surat_undangan_pengadaan'),
            'tanggal_surat_undangan_pengadaan'     => $this->request->getPost('tanggal_surat_undangan_pengadaan'),
            'nomor_surat_berita_acara_pengadaan'   => $this->request->getPost('nomor_surat_berita_acara_pengadaan'),
            'tanggal_surat_berita_acara_pengadaan' => $this->request->getPost('tanggal_surat_berita_acara_pengadaan'),
            'created_by'                           => session()->get('username')
        ];

        if ($this->M_AllFunction->Insert('trn_kontrak_ki', $data)) {
            return redirect()->to(base_url('C_Kontrak/KI'))->with('success', 'Data berhasil disimpan.');
        } else {
            return redirect()->to(base_url('C_Kontrak/KI'))->with('failed', 'Gagal menyimpan data.');
        }
    }

    public function Export()
    {
        $uri = current_url(true);
        $id = $uri->getSegment(4);
        $data['data'] = $this->M_AllFunction->Where('vw_kontrak_ki', ['id' => $id])[0] ?? null;
        // echo '<pre>'; print_r($data); echo '</pre>';die();

        $paket_id = $data['data']->paket;
        $jabatan_name = $data['data']->jabatan;
        $nik = $data['data']->nik;

        if (!$data['data']) {
            return redirect()->to(base_url('C_Kontrak/KI'))->with('failed', 'Data kontrak tidak ditemukan.');
        }

        $jenis_export = $uri->getSegment(3);
        $nama_file = "";
        if($jenis_export == "Penawaran"){
            $html = view('kontrak/export_pdf_penawaran', $data);
            $nama_file = "kontrak-penawaran-". $data['data']->id .".pdf";
        } else if($jenis_export == "Kualifikasi"){
            $data['pengalaman'] = $this->M_AllFunction->Where('trn_pengalaman_ki', ['nik' => $nik]) ?? null;
            $html = view('kontrak/export_pdf_kualifikasi_ki', $data);
            $nama_file = "kontrak-kualifikasi-". $data['data']->id .".pdf";
        } else if($jenis_export == "PaktaIntegritas"){
            $html = view('kontrak/export_pdf_pakta_integritas_ki', $data);
            $nama_file = "kontrak-pakta-integritas-". $data['data']->id .".pdf";
        } else if($jenis_export == "FormulirKualifikasi"){
            $data['pengalaman'] = $this->M_AllFunction->Where('trn_pengalaman_ki', ['nik' => $nik]) ?? null;
            $html = view('kontrak/export_pdf_formulir_kualifikasi_ki', $data);
            $nama_file = "kontrak-formulir-kualifikasi-". $data['data']->id .".pdf";
        } else if($jenis_export == "BOQ"){
            return view('kontrak/export_boq', $data);
        } else if($jenis_export == "Kesediaan"){
            $html = view('kontrak/export_pdf_kesediaan_ki', $data);
            $nama_file = "kontrak-kesediaan-". $data['data']->id .".pdf";
        } else if($jenis_export == "SPBBJ"){
            $html = view('kontrak/export_pdf_spbbj_ki', $data);
            $nama_file = "kontrak-spbbj-". $data['data']->id .".pdf";
        } else if($jenis_export == "BAPHP"){
            $data['pekerjaan_baphp'] = $this->M_AllFunction->Where('trn_kontrak_ki_pekerjaan_baphp', ['id_kontrak_paket' => $paket_id]) ?? null;
            $html = view('kontrak/export_pdf_baphp_ki', $data);
            $nama_file = "kontrak-baphp-". $data['data']->id .".pdf";
        } else if($jenis_export == "BAST"){
            $html = view('kontrak/export_pdf_bast_ki', $data);
            $nama_file = "kontrak-bast-". $data['data']->id .".pdf";
        } else if($jenis_export == "Evaluasi"){
            return view('kontrak/export_evaluasi', $data);
        } else if($jenis_export == "SPMK"){
            $html = view('kontrak/export_pdf_spmk_ki', $data);
            $nama_file = "kontrak-spmk-". $data['data']->id .".pdf";
        } else if($jenis_export == "SPK"){
            $data['syarat_umum'] = $this->M_AllFunction->Where('trn_syarat_umum_kontrak_ki', ['paket_id' => $paket_id, 'jabatan_name' => $jabatan_name])[0] ?? null;
            $html = view('kontrak/export_pdf_spk_ki', $data);
            $nama_file = "kontrak-spk-". $data['data']->id .".pdf";
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        ob_end_clean();
        $dompdf->stream($nama_file, array("Attachment" => 0));
        exit();
    }

    public function update_syarat_paket()
    {
        $paket_id = $this->request->getPost('paket_id');
        $jabatan = $this->request->getPost('jabatan_filter');

        $data = [
            'paket_id'             => $paket_id,
            'jabatan_name'         => $jabatan,
            'laporan'              => $this->request->getPost('laporan'),
            'hasil'                => $this->request->getPost('hasil'),
            'tugas_tanggung_jawab' => $this->request->getPost('tugas_tanggung_jawab'),
        ];

        $cek = $this->M_AllFunction->Where('trn_syarat_umum_kontrak_ki', ['paket_id' => $paket_id, 'jabatan_name' => $jabatan]);
        if(!empty($cek)){
            $this->M_AllFunction->Updates('trn_syarat_umum_kontrak_ki', $data, ['paket_id' => $paket_id, 'jabatan_name' => $jabatan]);
            return redirect()->to(base_url('C_Kontrak/Paket'))->with('success', 'Syarat umum berhasil diperbarui.');
        } else {
            $this->M_AllFunction->Inserts('trn_syarat_umum_kontrak_ki', $data);
            return redirect()->to(base_url('C_Kontrak/Paket'))->with('success', 'Syarat umum berhasil diperbarui.');
        }
    }

    public function get_jabatan_syarat_umum()
    {
        $data['jabatan'] = $this->M_AllFunction->CustomQuery("SELECT jabatan FROM trn_kontrak_ki GROUP BY jabatan");
        if ($data) {
            return $this->response->setJSON($data);
        }
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Data not found']);
    }

    public function get_syarat_umum_by_paket_id()
    {
        $id = $this->request->getPost('id');
        $jabatan = $this->request->getPost('jabatan');

        $data['paket'] = $this->M_AllFunction->Where('trn_syarat_umum_kontrak_ki', ['paket_id' => $id, 'jabatan_name' => $jabatan])[0] ?? null;
        if ($data) {
            return $this->response->setJSON($data);
        }
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Data not found']);
    }
}