<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 */

namespace App\Controllers;

class Dashboard extends BaseController {
    public function __construct() {
        $this->l_auth = new \App\Libraries\L_Auth();
        $this->l_auth->cek_auth();
    }

    public function index()
    {
      $data['klasifikasi'] = $this->M_AllFunction->CustomQuery(
        "SELECT survey_klasifikasi_kerusakan
        FROM trn_survey_sekolah
        WHERE survey_klasifikasi_kerusakan != '' OR survey_klasifikasi_kerusakan IS NOT NULL
        GROUP BY survey_klasifikasi_kerusakan");
      $data['kabupaten'] = $this->M_AllFunction->CustomQuery('SELECT kabupaten FROM mst_sekolah GROUP BY kabupaten');
      $data['kecamatan'] = $this->M_AllFunction->CustomQuery('SELECT kecamatan FROM mst_sekolah GROUP BY kecamatan');
      $data['map_type'] = $this->M_AllFunction->CustomQuery('SELECT * FROM mst_map_type');
      return $this->template->display('dashboard', $data);
    }

    public function getMap(){
      $where = "";
      if($this->request->getPost('npsn') != ''){
        $where = "WHERE mst_sekolah.npsn = '" . $this->request->getPost('npsn') . "'";
      }
      if($this->request->getPost('nama') != ""){
        $where .= $where == "" ? "WHERE " : " AND ";
        $where .= "nama LIKE '%" . $this->request->getPost('nama') . "%'";
      }
      if($this->request->getPost('kabupaten') != "*"){
        $where .= $where == "" ? "WHERE " : " AND ";
        $where .= "kabupaten = '" . $this->request->getPost('kabupaten') . "'";
      }
      if($this->request->getPost('kecamatan') != "*"){
        $where .= $where == "" ? "WHERE " : " AND ";
        $where .= "kecamatan = '" . $this->request->getPost('kecamatan') . "'";
      }
      if($this->request->getPost('klasifikasi') != "*"){
        $where .= $where == "" ? "WHERE " : " AND ";
        if($this->request->getPost('klasifikasi') == "non_klasifikasi"){
          $where .= "survey_klasifikasi_kerusakan = '' OR survey_klasifikasi_kerusakan IS NULL";
        } else {
          $where .= "survey_klasifikasi_kerusakan = '" . $this->request->getPost('klasifikasi') . "'";
        }
      }
      $query = "WITH last_periode AS (
          SELECT
            npsn, MAX(periode) AS periode
          FROM trn_survey_sekolah
          GROUP BY npsn
        ), last_data AS (
          SELECT
            trn_survey_sekolah.id,
            trn_survey_sekolah.npsn,
            trn_survey_sekolah.survey_klasifikasi_kerusakan
          FROM trn_survey_sekolah
          JOIN last_periode
          ON trn_survey_sekolah.npsn = last_periode.npsn AND trn_survey_sekolah.periode = last_periode.periode
          ORDER BY id DESC
        )

        SELECT
          mst_sekolah.*,
          last_data.survey_klasifikasi_kerusakan
        FROM mst_sekolah
        JOIN last_data
        ON mst_sekolah.npsn = last_data.npsn
        $where";

      $data['data'] = $this->M_AllFunction->CustomQuery($query);

      $setMap = $this->M_AllFunction->CustomQuery('SELECT * FROM mst_map_type WHERE id = ' . $this->request->getPost('map_type'));
      $session = [
        'map_id'   => $setMap[0]->id,
        'map_script'   => $setMap[0]->map_script,
      ];
      session()->set($session);

      $data['tingkat_konflik'] = $this->M_AllFunction->WHERE('trn_wilayah_konflik', 'kode_provinsi = 14');

      return view('dashboard_map', $data);
    }

    public function getDetail(){
      $npsn = $this->request->getPost('npsn');
      $data['nspn'] = $this->M_AllFunction->Where('mst_sekolah', "npsn = '$npsn'");
      $data['survey'] = $this->M_AllFunction->Where('trn_survey_sekolah', "npsn = '$npsn' ORDER BY periode DESC, id DESC");
      return json_encode($data);
    }
}
