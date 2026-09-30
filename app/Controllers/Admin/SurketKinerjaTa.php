<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SurketKinerjaTaModel;
use App\Models\MstPaketModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\TemplateProcessor;

class SurketKinerjaTa extends BaseController
{
    protected SurketKinerjaTaModel $surketModel;
    protected MstPaketModel $paketModel;
    protected MstPegawaiModel $pegawaiModel;

    public function __construct()
    {
        $this->surketModel  = new SurketKinerjaTaModel();
        $this->paketModel   = new MstPaketModel();
        $this->pegawaiModel = new MstPegawaiModel();
    }

    /**
     * Halaman Utama / Daftar SURKET KINERJA TA
     */
    public function index()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        $surketList  = $this->surketModel->getList();
        $paketList   = $this->paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();
        $pegawaiList = $this->pegawaiModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();

        // Cari default PPK (Nurhidayat Nugroho)
        $defaultPpk = null;
        foreach ($pegawaiList as $p) {
            if (stripos($p['nama'] ?? '', 'Nurhidayat') !== false || stripos($p['nama'] ?? '', 'Nugroho') !== false) {
                $defaultPpk = $p;
                break;
            }
        }

        return view('admin/kontrak/surket_kinerja_ta/index', [
            'title'        => 'Surat Keterangan Kinerja Tenaga Ahli',
            'surketList'   => $surketList,
            'paketList'    => $paketList,
            'pegawaiList'  => $pegawaiList,
            'defaultPpk'   => $defaultPpk,
            'permissions'  => $permissions,
        ]);
    }

    /**
     * Data JSON untuk DataTable jika diperlukan
     */
    public function data()
    {
        $search = trim((string) ($this->request->getGet('search')['value'] ?? ''));
        $data   = $this->surketModel->getList($search);

        return $this->response->setJSON([
            'data' => $data,
        ]);
    }

    /**
     * Simpan Surat Keterangan Kinerja TA Baru
     */
    public function simpan()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        if (! ($permissions['add'] ?? false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menambah data Surat Keterangan Kinerja TA.',
            ]);
        }

        $rules = [
            'nama_tenaga_ahli'      => ['label' => 'Nama Tenaga Ahli', 'rules' => 'required|min_length[2]'],
            'jabatan_pekerjaan'     => ['label' => 'Jabatan dalam Pekerjaan', 'rules' => 'required'],
            'nama_badan_usaha'      => ['label' => 'Nama Badan Usaha', 'rules' => 'required'],
            'alamat_badan_usaha'    => ['label' => 'Alamat Badan Usaha', 'rules' => 'required'],
            'nama_paket'            => ['label' => 'Nama Paket Pekerjaan', 'rules' => 'required'],
            'lingkup_jasa'          => ['label' => 'Lingkup Jasa', 'rules' => 'required|in_list[Manajemen Konstruksi,Fisik]'],
            'lokasi_pekerjaan'      => ['label' => 'Lokasi Pekerjaan', 'rules' => 'required'],
            'nomor_kontrak'         => ['label' => 'Nomor Kontrak', 'rules' => 'required'],
            'tanggal_kontrak'       => ['label' => 'Tanggal Kontrak', 'rules' => 'required|valid_date'],
            'nilai_kontrak'         => ['label' => 'Nilai Kontrak', 'rules' => 'required'],
            'sumber_dana'           => ['label' => 'Sumber Dana', 'rules' => 'required'],
            'masa_penugasan_hari'   => ['label' => 'Masa Penugasan (Hari)', 'rules' => 'required|numeric|greater_than[0]'],
            'status_persen'         => ['label' => 'Status Pekerjaan (%)', 'rules' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'penilaian_keseluruhan' => ['label' => 'Penilaian Keseluruhan', 'rules' => 'required'],
            'ppk_nama'              => ['label' => 'Nama PPK', 'rules' => 'required'],
            'ppk_nip'               => ['label' => 'NIP PPK', 'rules' => 'required'],
            'ppk_jabatan'           => ['label' => 'Jabatan PPK', 'rules' => 'required'],
            'ppk_satker'            => ['label' => 'Satuan Kerja PPK', 'rules' => 'required'],
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
        }

        helper('custom');
        $post = $this->request->getPost();

        $paketId = ! empty($post['paket_id']) ? (int) $post['paket_id'] : null;
        $ppkPegawaiId = ! empty($post['ppk_pegawai_id']) ? (int) $post['ppk_pegawai_id'] : null;

        // Format Nilai Kontrak Rupiah
        $rawNilai = preg_replace('/[^0-9]/', '', (string) ($post['nilai_kontrak'] ?? ''));
        $formattedNilai = $rawNilai !== '' ? 'Rp ' . number_format((float) $rawNilai, 0, ',', '.') . ',-' : trim((string) $post['nilai_kontrak']);

        // Masa Penugasan Hari
        $hari = (int) ($post['masa_penugasan_hari'] ?? 0);
        $masaPenugasan = $hari > 0 ? ($hari . ' Hari') : trim((string) ($post['masa_penugasan'] ?? ''));

        // Status Pekerjaan (Jika 100% -> 'Selesai', selain itu angka %)
        $persen = (int) ($post['status_persen'] ?? 100);
        $statusPekerjaan = ($persen >= 100) ? 'Selesai' : ($persen . '%');

        // Nomor & Tanggal Kontrak Terpisah
        $nomorKontrak = trim((string) ($post['nomor_kontrak'] ?? ''));
        $tanggalKontrak = ! empty($post['tanggal_kontrak']) ? $post['tanggal_kontrak'] : null;
        $nomorTanggalKontrak = $tanggalKontrak 
            ? ($nomorKontrak . ' tanggal ' . tanggal_indonesia($tanggalKontrak)) 
            : $nomorKontrak;

        $insertData = [
            'nomor_surat'           => trim((string) ($post['nomor_surat'] ?? '')),
            'tanggal_surat'         => ! empty($post['tanggal_surat']) ? $post['tanggal_surat'] : date('Y-m-d'),
            'kota_surat'            => trim((string) ($post['kota_surat'] ?? 'Pekanbaru')) ?: 'Pekanbaru',
            'ppk_pegawai_id'        => $ppkPegawaiId,
            'ppk_nama'              => trim((string) ($post['ppk_nama'] ?? '')),
            'ppk_nip'               => trim((string) ($post['ppk_nip'] ?? '')),
            'ppk_jabatan'           => trim((string) ($post['ppk_jabatan'] ?? '')),
            'ppk_satker'            => trim((string) ($post['ppk_satker'] ?? '')),
            'ppk_alamat'            => trim((string) ($post['ppk_alamat'] ?? '')),
            'nama_tenaga_ahli'      => trim((string) ($post['nama_tenaga_ahli'] ?? '')),
            'jabatan_pekerjaan'     => trim((string) ($post['jabatan_pekerjaan'] ?? '')),
            'nama_badan_usaha'      => trim((string) ($post['nama_badan_usaha'] ?? '')),
            'alamat_badan_usaha'    => trim((string) ($post['alamat_badan_usaha'] ?? '')),
            'paket_id'              => $paketId,
            'nama_paket'            => trim((string) ($post['nama_paket'] ?? '')),
            'lingkup_jasa'          => trim((string) ($post['lingkup_jasa'] ?? 'Manajemen Konstruksi')),
            'lokasi_pekerjaan'      => trim((string) ($post['lokasi_pekerjaan'] ?? '')),
            'nomor_kontrak'         => $nomorKontrak,
            'tanggal_kontrak'       => $tanggalKontrak,
            'nomor_tanggal_kontrak' => $nomorTanggalKontrak,
            'nilai_kontrak'         => $formattedNilai,
            'sumber_dana'           => trim((string) ($post['sumber_dana'] ?? '')),
            'masa_penugasan_hari'   => $hari,
            'masa_penugasan'        => $masaPenugasan,
            'status_persen'         => $persen,
            'status_pekerjaan'      => $statusPekerjaan,
            'penilaian_keseluruhan' => trim((string) ($post['penilaian_keseluruhan'] ?? 'Sangat Baik')),
            'created_by'            => (string) (session()->get('username') ?? 'admin'),
        ];

        $insertId = $this->surketModel->insert($insertData);
        if (! $insertId) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan Surat Keterangan Kinerja TA ke database.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Surat Keterangan Kinerja Tenaga Ahli berhasil disimpan.',
            'id'      => $insertId,
        ]);
    }

    /**
     * Ambil Detail Data untuk Form Edit / Modal Preview
     */
    public function detail(int $id)
    {
        $row = $this->surketModel->find($id);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data Surat Keterangan Kinerja TA tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * Perbarui Data Surat Keterangan Kinerja TA
     */
    public function ubah(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        if (! ($permissions['edit'] ?? false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengubah data ini.',
            ]);
        }

        $existing = $this->surketModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ]);
        }

        $rules = [
            'nama_tenaga_ahli'      => ['label' => 'Nama Tenaga Ahli', 'rules' => 'required|min_length[2]'],
            'jabatan_pekerjaan'     => ['label' => 'Jabatan dalam Pekerjaan', 'rules' => 'required'],
            'nama_badan_usaha'      => ['label' => 'Nama Badan Usaha', 'rules' => 'required'],
            'alamat_badan_usaha'    => ['label' => 'Alamat Badan Usaha', 'rules' => 'required'],
            'nama_paket'            => ['label' => 'Nama Paket Pekerjaan', 'rules' => 'required'],
            'lingkup_jasa'          => ['label' => 'Lingkup Jasa', 'rules' => 'required|in_list[Manajemen Konstruksi,Fisik]'],
            'lokasi_pekerjaan'      => ['label' => 'Lokasi Pekerjaan', 'rules' => 'required'],
            'nomor_kontrak'         => ['label' => 'Nomor Kontrak', 'rules' => 'required'],
            'tanggal_kontrak'       => ['label' => 'Tanggal Kontrak', 'rules' => 'required|valid_date'],
            'nilai_kontrak'         => ['label' => 'Nilai Kontrak', 'rules' => 'required'],
            'sumber_dana'           => ['label' => 'Sumber Dana', 'rules' => 'required'],
            'masa_penugasan_hari'   => ['label' => 'Masa Penugasan (Hari)', 'rules' => 'required|numeric|greater_than[0]'],
            'status_persen'         => ['label' => 'Status Pekerjaan (%)', 'rules' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'penilaian_keseluruhan' => ['label' => 'Penilaian Keseluruhan', 'rules' => 'required'],
            'ppk_nama'              => ['label' => 'Nama PPK', 'rules' => 'required'],
            'ppk_nip'               => ['label' => 'NIP PPK', 'rules' => 'required'],
            'ppk_jabatan'           => ['label' => 'Jabatan PPK', 'rules' => 'required'],
            'ppk_satker'            => ['label' => 'Satuan Kerja PPK', 'rules' => 'required'],
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
        }

        helper('custom');
        $post = $this->request->getPost();

        $paketId = ! empty($post['paket_id']) ? (int) $post['paket_id'] : null;
        $ppkPegawaiId = ! empty($post['ppk_pegawai_id']) ? (int) $post['ppk_pegawai_id'] : null;

        // Format Nilai Kontrak Rupiah
        $rawNilai = preg_replace('/[^0-9]/', '', (string) ($post['nilai_kontrak'] ?? ''));
        $formattedNilai = $rawNilai !== '' ? 'Rp ' . number_format((float) $rawNilai, 0, ',', '.') . ',-' : trim((string) $post['nilai_kontrak']);

        // Masa Penugasan Hari
        $hari = (int) ($post['masa_penugasan_hari'] ?? 0);
        $masaPenugasan = $hari > 0 ? ($hari . ' Hari') : trim((string) ($post['masa_penugasan'] ?? ''));

        // Status Pekerjaan (Jika 100% -> 'Selesai', selain itu angka %)
        $persen = (int) ($post['status_persen'] ?? 100);
        $statusPekerjaan = ($persen >= 100) ? 'Selesai' : ($persen . '%');

        // Nomor & Tanggal Kontrak Terpisah
        $nomorKontrak = trim((string) ($post['nomor_kontrak'] ?? ''));
        $tanggalKontrak = ! empty($post['tanggal_kontrak']) ? $post['tanggal_kontrak'] : null;
        $nomorTanggalKontrak = $tanggalKontrak 
            ? ($nomorKontrak . ' tanggal ' . tanggal_indonesia($tanggalKontrak)) 
            : $nomorKontrak;

        $updateData = [
            'nomor_surat'           => trim((string) ($post['nomor_surat'] ?? '')),
            'tanggal_surat'         => ! empty($post['tanggal_surat']) ? $post['tanggal_surat'] : date('Y-m-d'),
            'kota_surat'            => trim((string) ($post['kota_surat'] ?? 'Pekanbaru')) ?: 'Pekanbaru',
            'ppk_pegawai_id'        => $ppkPegawaiId,
            'ppk_nama'              => trim((string) ($post['ppk_nama'] ?? '')),
            'ppk_nip'               => trim((string) ($post['ppk_nip'] ?? '')),
            'ppk_jabatan'           => trim((string) ($post['ppk_jabatan'] ?? '')),
            'ppk_satker'            => trim((string) ($post['ppk_satker'] ?? '')),
            'ppk_alamat'            => trim((string) ($post['ppk_alamat'] ?? '')),
            'nama_tenaga_ahli'      => trim((string) ($post['nama_tenaga_ahli'] ?? '')),
            'jabatan_pekerjaan'     => trim((string) ($post['jabatan_pekerjaan'] ?? '')),
            'nama_badan_usaha'      => trim((string) ($post['nama_badan_usaha'] ?? '')),
            'alamat_badan_usaha'    => trim((string) ($post['alamat_badan_usaha'] ?? '')),
            'paket_id'              => $paketId,
            'nama_paket'            => trim((string) ($post['nama_paket'] ?? '')),
            'lingkup_jasa'          => trim((string) ($post['lingkup_jasa'] ?? 'Manajemen Konstruksi')),
            'lokasi_pekerjaan'      => trim((string) ($post['lokasi_pekerjaan'] ?? '')),
            'nomor_kontrak'         => $nomorKontrak,
            'tanggal_kontrak'       => $tanggalKontrak,
            'nomor_tanggal_kontrak' => $nomorTanggalKontrak,
            'nilai_kontrak'         => $formattedNilai,
            'sumber_dana'           => trim((string) ($post['sumber_dana'] ?? '')),
            'masa_penugasan_hari'   => $hari,
            'masa_penugasan'        => $masaPenugasan,
            'status_persen'         => $persen,
            'status_pekerjaan'      => $statusPekerjaan,
            'penilaian_keseluruhan' => trim((string) ($post['penilaian_keseluruhan'] ?? 'Sangat Baik')),
            'updated_by'            => (string) (session()->get('username') ?? 'admin'),
        ];

        $this->surketModel->update($id, $updateData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data Surat Keterangan Kinerja Tenaga Ahli berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus Data Surat Keterangan Kinerja TA
     */
    public function hapus(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        if (! ($permissions['delete'] ?? false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus data ini.',
            ]);
        }

        $existing = $this->surketModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ]);
        }

        $this->surketModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data Surat Keterangan Kinerja Tenaga Ahli berhasil dihapus.',
        ]);
    }

    /**
     * Unduh Dokumen Word (.docx) Sesuai Template
     */
    public function unduhDocx(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Anda tidak memiliki hak akses untuk mengunduh dokumen.');
        }

        $row = $this->surketModel->find($id);
        if (! $row) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Data tidak ditemukan.');
        }

        $templateFile = APPPATH . 'Views/admin/kontrak/surket_kinerja_ta_template.docx';
        if (! file_exists($templateFile)) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Berkas template dokumen Word tidak ditemukan.');
        }

        helper('custom');

        $tglSuratStr = ! empty($row['tanggal_surat']) ? tanggal_indonesia($row['tanggal_surat']) : tanggal_indonesia(date('Y-m-d'));
        $kotaSurat   = ! empty($row['kota_surat']) ? $row['kota_surat'] : 'Pekanbaru';
        $kotaTanggal = $kotaSurat . ', ' . $tglSuratStr;

        $replacements = [
            'nomor_surat'               => $row['nomor_surat'] ?: '...............',
            'ppk_nama'                  => $row['ppk_nama'] ?: 'Nurhidayat Nugroho, S.Ars',
            'ppk_jabatan'               => $row['ppk_jabatan'] ?: 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis',
            'ppk_satker'                => $row['ppk_satker'] ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'ppk_alamat'                => $row['ppk_alamat'] ?: 'Jl. Datuk Setia Maharaja No. 1 Pekanbaru',
            'nama_tenaga_ahli'          => $row['nama_tenaga_ahli'] ?: '-',
            'jabatan_pekerjaan'         => $row['jabatan_pekerjaan'] ?: '-',
            'nama_badan_usaha'          => $row['nama_badan_usaha'] ?: '-',
            'alamat_badan_usaha'        => $row['alamat_badan_usaha'] ?: '-',
            'nama_paket'                => $row['nama_paket'] ?: '-',
            'lingkup_jasa'              => $row['lingkup_jasa'] ?: 'Manajemen Konstruksi',
            'lokasi_pekerjaan'          => $row['lokasi_pekerjaan'] ?: '-',
            'nomor_tanggal_kontrak'     => $row['nomor_tanggal_kontrak'] ?: '-',
            'nilai_kontrak'             => $row['nilai_kontrak'] ?: 'Rp .',
            'sumber_dana'               => $row['sumber_dana'] ?: 'APBN DIPA Satker Pelaksanaan Prasarana Strategis Riau',
            'masa_penugasan'            => $row['masa_penugasan'] ?: '-',
            'status_pekerjaan'          => $row['status_pekerjaan'] ?: 'selesai 100%',
            'penilaian_keseluruhan'     => $row['penilaian_keseluruhan'] ?: 'Sangat Baik',
            'kota_tanggal_surat'        => $kotaTanggal,
            'ppk_tanda_tangan_jabatan1' => ($row['ppk_jabatan'] ?: 'PPK Pelaksanaan Prasarana Strategis') . ',',
            'ppk_tanda_tangan_jabatan2' => $row['ppk_satker'] ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'ppk_tanda_tangan_nama'     => $row['ppk_nama'] ?: 'Nurhidayat Nugroho, S. Ars',
            'ppk_tanda_tangan_nip'      => $row['ppk_nip'] ?: '199012212018021001',
        ];

        $docxBinary = $this->renderDocxFromTemplate($templateFile, $replacements);
        if (! $docxBinary) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Gagal memproses dokumen Word.');
        }

        $sanitizedName = preg_replace('/[^A-Za-z0-9_-]/', '_', $row['nama_tenaga_ahli'] ?? 'Tenaga_Ahli');
        $filename      = 'Surket_Kinerja_TA_' . $sanitizedName . '_' . date('Ymd') . '.docx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($docxBinary);
    }

    /**
     * Cetak Dokumen PDF Resmi
     */
    public function cetakPdf(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Anda tidak memiliki hak akses untuk mencetak PDF.');
        }

        $row = $this->surketModel->find($id);
        if (! $row) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Data tidak ditemukan.');
        }

        helper('custom');

        // Siapkan logo kop surat dalam base64 data URI
        $kopPath = FCPATH . 'uploads/kop_surat/kop_surket_ta.png';
        $kopBase64 = '';
        if (file_exists($kopPath)) {
            $kopBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath));
        }

        $html = view('admin/kontrak/surket_kinerja_ta/cetak_pdf', [
            'row'       => $row,
            'kopBase64' => $kopBase64,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $sanitizedName = preg_replace('/[^A-Za-z0-9_-]/', '_', $row['nama_tenaga_ahli'] ?? 'Tenaga_Ahli');
        $filename      = 'Surket_Kinerja_TA_' . $sanitizedName . '_' . date('Ymd') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Proses template DOCX dengan keamanan karakter XML
     */
    private function renderDocxFromTemplate(string $templateFile, array $replacements): ?string
    {
        if (! file_exists($templateFile)) {
            return null;
        }

        // Method 1: PhpWord TemplateProcessor
        if (class_exists(TemplateProcessor::class)) {
            try {
                $processor = new TemplateProcessor($templateFile);
                foreach ($replacements as $key => $val) {
                    $escaped = htmlspecialchars((string) $val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $processor->setValue($key, $escaped);
                }

                $tempPath = WRITEPATH . 'uploads/' . uniqid('surket_ta_', true) . '.docx';
                $processor->saveAs($tempPath);
                $content = file_get_contents($tempPath);
                @unlink($tempPath);
                return $content ?: null;
            } catch (\Throwable $e) {
                log_message('warning', 'PhpWord TemplateProcessor failed: ' . $e->getMessage());
            }
        }

        // Method 2: Native ZipArchive Fallback
        if (class_exists(\ZipArchive::class)) {
            $tempPath = WRITEPATH . 'uploads/' . uniqid('surket_ta_native_', true) . '.docx';
            if (! @copy($templateFile, $tempPath)) {
                return null;
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath) !== true) {
                @unlink($tempPath);
                return null;
            }

            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) {
                $zip->close();
                @unlink($tempPath);
                return null;
            }

            foreach ($replacements as $key => $val) {
                $search = '${' . $key . '}';
                $escaped = htmlspecialchars((string) $val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml = str_replace($search, $escaped, $xml);
            }

            $zip->addFromString('word/document.xml', $xml);
            $zip->close();

            $content = file_get_contents($tempPath);
            @unlink($tempPath);
            return $content ?: null;
        }

        return null;
    }

    /**
     * Helper resolusi hak akses menu
     */
    private function resolveMenuAksesPermissions(string $link): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $role = strtolower(trim((string) (session()->get('role') ?? '')));
        $db   = db_connect();

        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = null;
        if ($db->tableExists('access_roles')) {
            $variants = [$role];
            if (in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
                $variants = ['super administrator', 'super_administrator', 'super-admin', 'superadmin'];
            }
            $roleRow = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', $variants)
                ->where('is_active', 1)
                ->get()
                ->getRowArray();
            if ($roleRow && isset($roleRow['id'])) {
                $roleId = (int) $roleRow['id'];
            }
        }

        if ($roleId === null) {
            $roleId = match ($role) {
                'admin'  => 2,
                'editor' => 3,
                default  => 1,
            };
        }

        $menuId = null;
        foreach (['menu_lv2', 'menu_lv1', 'menu_lv3'] as $table) {
            if (! $db->tableExists($table)) continue;
            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower($link))
                ->get()
                ->getRowArray();
            if ($row && isset($row['id'])) {
                $menuId = (string) $row['id'];
                break;
            }
        }

        if ($menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (int) ($row['FiturAdd'] ?? 0) === 1,
            'edit'     => (int) ($row['FiturEdit'] ?? 0) === 1,
            'delete'   => (int) ($row['FiturDelete'] ?? 0) === 1,
            'export'   => (int) ($row['FiturExport'] ?? 0) === 1,
            'import'   => (int) ($row['FiturImport'] ?? 0) === 1,
            'approval' => (int) ($row['FiturApproval'] ?? 0) === 1,
        ];
    }
}
