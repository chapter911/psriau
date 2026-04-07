<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 * @property M_Master $M_Master
 */

namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;

class C_Dinas extends BaseController
{
    public function __construct()
    {
        $this->l_auth = new \App\Libraries\L_Auth();
        $this->l_auth->cek_auth();
    }

    public function Laporan()
    {
        return $this->template->display('dinas/laporan');
    }

    public function AddLaporan()
    {
        $data['user']     = $this->M_AllFunction->Get('vw_mst_user');
        $data['provinsi'] = $this->M_AllFunction->Get('mst_provinsi');
        return $this->template->display('dinas/laporan_add', $data);
    }

    public function SPT()
    {
        $data['data'] = $this->M_AllFunction->CustomQuery("SELECT spt_id, spt_no, rangka, COUNT(pelaksana) AS jumlah_pelaksana, tanggal_awal, tanggal_akhir, tujuan, transportasi FROM trn_spt GROUP BY spt_id");
        return $this->template->display('dinas/spt', $data);
    }

    public function import_spt()
    {
        $file = $this->request->getFile('excel_file');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $filePath    = $file->getTempName();
            $spreadsheet = IOFactory::load($filePath);

            $sheetSPT = $spreadsheet->getSheetByName('SPT');
            if ($sheetSPT) {
                $dataRows = $sheetSPT->toArray();
                array_shift($dataRows);

                // truncate table sebelum impor
                $db = \Config\Database::connect();
                $db->table('trn_spt')->truncate();

                foreach ($dataRows as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = [
                        'spt_id'           => $row[0],
                        'spt_no'           => $row[1],
                        'rangka'           => $row[2],
                        'pelaksana'        => $row[3],
                        'nip_pelaksana'    => $row[4],
                        'golongan'         => $row[5],
                        'jabatan'          => $row[6],
                        'unit_kerja'       => $row[7],
                        'tanggal_awal'     => ! empty($row[8]) ? date('Y-m-d', strtotime($row[8])) : null,
                        'tanggal_akhir'    => ! empty($row[9]) ? date('Y-m-d', strtotime($row[9])) : null,
                        'asal'             => $row[10],
                        'tujuan'           => $row[11],
                        'transportasi'     => $row[12],
                        'spt_di_keluarkan' => $row[13],
                        'spt_tanggal'      => ! empty($row[14]) ? date('Y-m-d', strtotime($row[14])) : null,
                        'jabatan_approval' => $row[15],
                        'approval_by'      => $row[16],
                        'nip_approval'     => $row[17],
                        'created_by'       => session()->get('username'),
                    ];
                    $this->M_AllFunction->Replaces('trn_spt', $data);
                }
            }

            $sheetSPDHeader = $spreadsheet->getSheetByName('SPD Header');
            if ($sheetSPDHeader) {
                $dataRows = $sheetSPDHeader->toArray();
                array_shift($dataRows);

                // truncate table sebelum impor
                $db = \Config\Database::connect();
                $db->table('trn_spd_header')->truncate();

                foreach ($dataRows as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = [
                        'spt_id'                => $row[0],
                        'spd_id'                => $row[1],
                        'no_spd'                => $row[2],
                        'tanggal_spd'           => !empty($row[3]) ? date('Y-m-d', strtotime($row[3])): null,
                        'tanggal_ttd_bendahara' => !empty($row[4]) ? date('Y-m-d', strtotime($row[4])): null,
                        'nama_bendahara'        => $row[5],
                        'nip_bendahara'         => $row[6],
                        'penerima'              => $row[7],
                        'jabatan_penerima'      => $row[8],
                        'nip_penerima'          => $row[9],
                        'pejabat_ppk'           => $row[10],
                        'nip_ppk'               => $row[11],
                        'tahun_anggaran'        => $row[12],
                        'nomor_bukti'           => $row[13],
                        'mata_anggaran'         => $row[14],
                        'golongan_penerima'     => $row[15],
                        'tingkat_biaya'         => $row[16],
                        'created_by'            => session()->get('username'),
                    ];
                    $this->M_AllFunction->Replaces('trn_spd_header', $data);
                }
            }

            $sheetSPDDetail = $spreadsheet->getSheetByName('SPD Detail');
            if ($sheetSPDDetail) {
                $dataRows = $sheetSPDDetail->toArray();
                array_shift($dataRows);

                // truncate table sebelum impor
                $db = \Config\Database::connect();
                $db->table('trn_spd_detail')->truncate();

                foreach ($dataRows as $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = [
                        'spd_id'        => $row[0],
                        'jenis_biaya'   => $row[1],
                        'nama_biaya'    => $row[2],
                        'durasi'        => $row[3],
                        'jumlah_satuan' => $row[4],
                        'keterangan'    => $row[5],
                        'created_by'    => session()->get('username'),
                    ];
                    $this->M_AllFunction->Replaces('trn_spd_detail', $data);
                }
            }

            return redirect()->to(base_url('C_Dinas/SPT'))->with('success', 'Data berhasil diimpor.');
        } else {
            return redirect()->to(base_url('C_Dinas/SPT'))->with('failed', 'Gagal mengunggah file.');
        }
    }

    public function Export()
    {
        $uri = current_url(true);
        $id  = $uri->getSegment(4);

        $data['spt'] = $this->M_AllFunction->Where('trn_spt', "spt_id = '$id'");
        $data['spd_header'] = $this->M_AllFunction->Where('trn_spd_header', "spt_id = '$id'");
        $data['spd_detail'] = $this->M_AllFunction->Where('trn_spd_detail', "spd_id = '$id'");

        if($uri->getSegment(3) == "SPT"){
            $html      = view('dinas/export_pdf_spt', $data);
            $nama_file = "export_spt.pdf";
        } else {
            $html      = view('dinas/export_pdf_nomina', $data);
            $nama_file = "export_spt_nomina.pdf";
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        ob_end_clean();

        $dompdf->stream($nama_file, ["Attachment" => 0]);

        exit();
    }
}
