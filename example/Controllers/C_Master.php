<?php

/**
 * @property Template $template
 * @property M_AllFunction $M_AllFunction
 * @property M_Master $M_Master
 */

namespace App\Controllers;

class C_Master extends BaseController {
    public function __construct() {
        $this->l_auth = new \App\Libraries\L_Auth();
        $this->l_auth->cek_auth();
    }

    public function Sekolah()
    {
        $data['data'] = $this->M_AllFunction->CustomQuery('SELECT * FROM mst_sekolah ORDER BY kabupaten, kecamatan, nama');
		return $this->template->display('master/sekolah', $data);
    }

    public function getDataSekolah(){
        $data = $this->M_AllFunction->Where('mst_sekolah', "npsn = '" . $this->request->getPost('npsn') . "'");
        return json_encode($data);
    }

    public function saveSekolah(){
        $data = array(
            "npsn" => $this->request->getPost('npsn'),
            "nama" => $this->request->getPost('nama'),
            "jenis" => $this->request->getPost('jenis'),
            "nsm" => $this->request->getPost('nsm'),
            "kabupaten" => $this->request->getPost('kabupaten'),
            "kecamatan" => $this->request->getPost('kecamatan'),
            "latitude" => $this->request->getPost('latitude'),
            "longitude" => $this->request->getPost('longitude')
        );
        if($this->request->getPost('is_update') == "0"){
            $this->M_AllFunction->Inserts("mst_sekolah", $data);
        } else {
            $this->M_AllFunction->Updates("mst_sekolah", $data, "npsn = '" . $this->request->getPost('npsn') . "'");
        }
        return redirect()->to(base_url() . 'C_Master/Sekolah');
    }

    public function Provinsi()
    {
        $data['data'] = $this->M_AllFunction->CustomQuery(
            'SELECT kode_provinsi, nama_provinsi
            FROM vw_wilayah_administratif
            GROUP BY kode_provinsi, nama_provinsi
            ORDER BY kode_provinsi');
		return $this->template->display('master/provinsi', $data);
    }

    public function getDataProvinsi(){
        $data = $this->M_AllFunction->Where('vw_wilayah_administratif', "kode_provinsi = '" . $this->request->getPost('kode_provinsi') . "' AND kode_kabupaten IS NULL AND kode_kecamatan IS NULL");
        return json_encode($data);
    }

    public function saveProvinsi(){
        $data = array(
            "kode_provinsi" => $this->request->getPost('kode_provinsi'),
            "nama_provinsi" => $this->request->getPost('nama_provinsi'),
        );
        $this->M_AllFunction->Replaces("mst_provinsi", $data);
        return redirect()->to(base_url() . 'C_Master/Provinsi');
    }

    public function Kabupaten()
    {
        $data['provinsi'] = $this->M_AllFunction->CustomQuery(
            'SELECT kode_provinsi, nama_provinsi
            FROM vw_wilayah_administratif
            GROUP BY kode_provinsi
            ORDER BY kode_provinsi, nama_provinsi'
        );
        $data['data'] = $this->M_AllFunction->CustomQuery(
            "SELECT kode_provinsi, nama_provinsi, kode_kabupaten, nama_kabupaten
            FROM vw_wilayah_administratif
            GROUP BY kode_provinsi, kode_kabupaten
            ORDER BY kode_provinsi, kode_kabupaten"
        );
		return $this->template->display('master/kabupaten', $data);
    }

    public function getKabupaten(){
        $data = $this->M_AllFunction->CustomQuery(
            "SELECT kode_provinsi, nama_provinsi, kode_kabupaten, nama_kabupaten
            FROM vw_wilayah_administratif
            WHERE kode_provinsi = '" . $this->request->getPost('kode_provinsi') . "'
            GROUP BY kode_provinsi, kode_kabupaten
            ORDER BY nama_kabupaten"
        );
        return json_encode($data);
    }

    public function getDataKabupaten(){
        $data = $this->M_AllFunction->Where('vw_wilayah_administratif', "kode_provinsi = '" . $this->request->getPost('kode_provinsi') . "' AND kode_kabupaten = '" . $this->request->getPost('kode_kabupaten') . "' AND kode_kecamatan IS NULL");
        return json_encode($data);
    }

    public function saveKabupaten(){
        $data = array(
            "kode_provinsi" => $this->request->getPost('kode_provinsi'),
            "kode_kabupaten" => $this->request->getPost('kode_kabupaten'),
            "nama_kabupaten" => $this->request->getPost('nama_kabupaten'),
        );
        $this->M_AllFunction->Replaces("mst_kabupaten", $data);
        return redirect()->to(base_url() . 'C_Master/Kabupaten');
    }

    public function Kecamatan()
    {
        $data['provinsi'] = $this->M_AllFunction->CustomQuery(
            'SELECT kode_provinsi, nama_provinsi
            FROM vw_wilayah_administratif
            GROUP BY kode_provinsi'
        );
        $data['data'] = $this->M_AllFunction->CustomQuery(
            'SELECT kode_provinsi, nama_provinsi, kode_kabupaten, nama_kabupaten, kode_kecamatan, nama_kecamatan
            FROM vw_wilayah_administratif
            GROUP BY kode_provinsi, kode_kabupaten, kode_kecamatan
            ORDER BY kode_provinsi, kode_kabupaten, kode_kecamatan'
        );
		return $this->template->display('master/kecamatan', $data);
    }

    public function getKecamatan(){
        $data = $this->M_AllFunction->CustomQuery(
            "SELECT kode_provinsi, nama_provinsi, kode_kabupaten, nama_kabupaten, kode_kecamatan, nama_kecamatan
            FROM vw_wilayah_administratif
            WHERE kode_provinsi = '" . $this->request->getPost('kode_provinsi') . "' AND kode_kabupaten = '" . $this->request->getPost('kode_kabupaten') . "'
            GROUP BY kode_provinsi, kode_kabupaten, kode_kecamatan
            ORDER BY nama_kecamatan"
        );
        return json_encode($data);
    }

    public function getDataKecamatan(){
        $data = $this->M_AllFunction->Where('vw_wilayah_administratif',
            "kode_provinsi = '" . $this->request->getPost('kode_provinsi') . "' AND
            kode_kabupaten = '" . $this->request->getPost('kode_kabupaten') . "' AND
            kode_kecamatan = '" . $this->request->getPost('kode_kecamatan') . "'");
        return json_encode($data);
    }

    public function saveKecamatan(){
        $data = array(
            "kode_provinsi" => $this->request->getPost('kode_provinsi'),
            "kode_kabupaten" => $this->request->getPost('kode_kabupaten'),
            "kode_kecamatan" => $this->request->getPost('kode_kecamatan'),
            "nama_kecamatan" => $this->request->getPost('nama_kecamatan'),
        );
        $this->M_AllFunction->Replaces("mst_kecamatan", $data);
        return redirect()->to(base_url() . 'C_Master/Kecamatan');
    }

    public function Kelurahan()
    {
        $data['provinsi'] = $this->M_AllFunction->CustomQuery(
            'SELECT kode_provinsi, nama_provinsi
            FROM vw_wilayah_administratif
            GROUP BY kode_provinsi'
        );
		return $this->template->display('master/kelurahan', $data);
    }

    function ajaxKelurahan(){
        $list = $this->M_Master->getDataKelurahan();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = esc($field->kode_provinsi);
            $row[] = esc($field->nama_provinsi);
            $row[] = esc($field->kode_kabupaten);
            $row[] = esc($field->nama_kabupaten);
            $row[] = esc($field->kode_kecamatan);
            $row[] = esc($field->nama_kecamatan);
            $row[] = esc($field->kode_kelurahan);
            $row[] = esc($field->nama_kelurahan);
            $row[] = '<button class="btn btn-block btn-warning" onclick="showData(\'' . $field->kode_provinsi . '\', \'' . $field->kode_kabupaten . '\', \'' . $field->kode_kecamatan . '\', \'' . $field->kode_kelurahan . '\')">UPDATE</button>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Master->getTotal(),
            "recordsFiltered" => $this->M_Master->getTotalFiltered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function getDataKelurahan(){
        $data = $this->M_AllFunction->Where('vw_wilayah_administratif',
            "kode_provinsi = '" . $this->request->getPost('kode_provinsi') . "' AND
            kode_kabupaten = '" . $this->request->getPost('kode_kabupaten') . "' AND
            kode_kecamatan = '" . $this->request->getPost('kode_kecamatan') . "' AND
            kode_kelurahan = '" . $this->request->getPost('kode_kelurahan') . "'");
        return json_encode($data);
    }

    public function saveKelurahan(){
        $data = array(
            "kode_provinsi" => $this->request->getPost('kode_provinsi'),
            "kode_kabupaten" => $this->request->getPost('kode_kabupaten'),
            "kode_kecamatan" => $this->request->getPost('kode_kecamatan'),
            "kode_kelurahan" => $this->request->getPost('kode_kelurahan'),
            "nama_kelurahan" => $this->request->getPost('nama_kelurahan'),
        );
        $this->M_AllFunction->Replaces("mst_kelurahan", $data);
        return redirect()->to(base_url() . 'C_Master/Kelurahan');
    }

    public function Penyedia(){
        $data['data'] = $this->M_AllFunction->CustomQuery('SELECT * FROM mst_penyedia ORDER BY penyedia');
        return $this->template->display('master/penyedia', $data);
    }

    public function savePenyedia(){
        $data = array(
            "penyedia" => $this->request->getPost('penyedia'),
        );
        if($this->request->getPost('id') == "0"){
            $this->M_AllFunction->Inserts("mst_penyedia", $data);
        } else {
            $this->M_AllFunction->Updates("mst_penyedia", $data, "id = '" . $this->request->getPost('id') . "'");
        }
        return redirect()->to(base_url() . 'C_Master/Penyedia');
    }
}