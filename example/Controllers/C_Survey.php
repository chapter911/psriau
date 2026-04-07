<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 */

namespace App\Controllers;

class C_Survey extends BaseController {
    public function __construct() {
        $this->l_auth = new \App\Libraries\L_Auth();
        $this->l_auth->cek_auth();
    }

    public function madrasah()
    {
        $data['data'] = $this->M_AllFunction->CustomQuery("WITH last_periode AS (
            SELECT
                npsn, MAX(periode) AS periode
            FROM trn_survey_sekolah
            GROUP BY npsn
            ), last_data AS (
            SELECT
                trn_survey_sekolah.*
                FROM trn_survey_sekolah
            JOIN last_periode
            ON trn_survey_sekolah.npsn = last_periode.npsn AND trn_survey_sekolah.periode = last_periode.periode
            ORDER BY id DESC
            )

            SELECT
            mst_sekolah.npsn,
            mst_sekolah.nama,
            mst_sekolah.kabupaten,
            mst_sekolah.kecamatan,
            mst_sekolah.latitude,
            mst_sekolah.longitude,
            last_data.periode,
            last_data.emis_jumlah_siswa,
            last_data.survey_tingat_kerusakan,
            last_data.survey_klasifikasi_kerusakan,
            last_data.status_lahan,
            last_data.status_penanganan,
            last_data.ekspos_tingkat_kerusakan,
            last_data.ekspos_klasifikasi_kerusakan,
            last_data.ekspos_status
            FROM mst_sekolah
            JOIN last_data
            ON mst_sekolah.npsn = last_data.npsn");
        return $this->template->display('survey/madrasah', $data);
    }

    public function DetailMadrasah(){
        $uri = $this->request->getUri();
        $npsn = $uri->getSegment(3);
        $data['klasifikasi'] = $this->M_AllFunction->CustomQuery(
            "SELECT survey_klasifikasi_kerusakan
            FROM trn_survey_sekolah
            WHERE survey_klasifikasi_kerusakan != '' OR survey_klasifikasi_kerusakan IS NOT NULL
            GROUP BY survey_klasifikasi_kerusakan");
        $data['sekolah'] = $this->M_AllFunction->CustomQuery("SELECT * FROM mst_sekolah WHERE npsn = '$npsn'");
        $data['detail'] = $this->M_AllFunction->CustomQuery("SELECT * FROM trn_survey_sekolah WHERE npsn = '$npsn' ORDER BY periode DESC");
        return $this->template->display('survey/madrasah_detail', $data);
    }

    public function getDetail(){
        $data['survey'] = $this->M_AllFunction->Where('trn_survey_sekolah', "id = '" . $this->request->getPost('id') . "'");
        return json_encode($data);
    }

    public function saveDetail(){
        $data = array(
            "periode" => $this->request->getPost("periode"),
            "npsn" => $this->request->getPost("npsn"),
            "emis_jumlah_siswa" => $this->request->getPost("emis_jumlah_siswa"),
            "survey_jumlah_siswa" => $this->request->getPost("survey_jumlah_siswa"),
            "survey_tingat_kerusakan" => $this->request->getPost("survey_tingat_kerusakan"),
            "survey_klasifikasi_kerusakan" => $this->request->getPost("survey_klasifikasi_kerusakan"),
            "status_lahan" => $this->request->getPost("status_lahan"),
            "status_penanganan" => $this->request->getPost("status_penanganan"),
            "ekspos_tingkat_kerusakan" => $this->request->getPost("ekspos_tingkat_kerusakan"),
            "ekspos_klasifikasi_kerusakan" => $this->request->getPost("ekspos_klasifikasi_kerusakan"),
            "ekspos_status" => $this->request->getPost("ekspos_status")
        );
        if($this->request->getPost("id") == "0"){
            $this->M_AllFunction->Inserts("trn_survey_sekolah", $data);
        } else {
            $this->M_AllFunction->Updates("trn_survey_sekolah", $data, "id = '" . $this->request->getPost("id") . "'");
        }
        return redirect()->to(base_url() . 'C_Survey/DetailMadrasah/' . $this->request->getPost("npsn"));
    }

    public function potensiKonflik(){
        $data['data'] = $this->M_AllFunction->CustomQuery(
            "SELECT
                vw_wilayah_administratif.kode_provinsi,
                vw_wilayah_administratif.nama_provinsi,
                vw_wilayah_administratif.kode_kabupaten,
                vw_wilayah_administratif.nama_kabupaten,
                trn_wilayah_konflik.tingkat_konflik
            FROM trn_wilayah_konflik
            LEFT JOIN vw_wilayah_administratif
            ON trn_wilayah_konflik.kode_provinsi = vw_wilayah_administratif.kode_provinsi
                AND trn_wilayah_konflik.kode_kabupaten = vw_wilayah_administratif.kode_kabupaten
            WHERE vw_wilayah_administratif.kode_provinsi = 14
            GROUP BY 
                vw_wilayah_administratif.kode_provinsi,
                vw_wilayah_administratif.kode_kabupaten"
        );
        return $this->template->display('survey/potensi_konflik', $data);
    }

    function updatePotensiKonflik(){
        $data = array(
            "kode_provinsi" => $this->request->getPost("kode_provinsi"),
            "kode_kabupaten" => $this->request->getPost("kode_kabupaten"),
            "tingkat_konflik" => $this->request->getPost("tingkat_konflik")
        );
        $where = "kode_provinsi = '" . $this->request->getPost("kode_provinsi") . "' AND kode_kabupaten = '" . $this->request->getPost("kode_kabupaten") . "'";
        $cek = $this->M_AllFunction->Where('trn_wilayah_konflik', $where);
        if(count($cek) > 0){
            $this->M_AllFunction->Updates("trn_wilayah_konflik", $data, $where);
        } else {
            $this->M_AllFunction->Inserts("trn_wilayah_konflik", $data);
        }
        return redirect()->to(base_url() . 'C_Survey/potensiKonflik');
    }
}