<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BastModel;
use App\Models\MstPaketModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\TemplateProcessor;

class Bast extends BaseController
{
    protected BastModel $bastModel;
    protected MstPaketModel $paketModel;
    protected MstPegawaiModel $pegawaiModel;

    public function __construct()
    {
        $this->bastModel    = new BastModel();
        $this->paketModel   = new MstPaketModel();
        $this->pegawaiModel = new MstPegawaiModel();
    }

    /**
     * Halaman Utama / Daftar Berita Acara Serah Terima (BAST)
     */
    public function index()
    {
        $permissions   = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        $filterPaketId = trim((string) ($this->request->getGet('paket_id') ?? ''));
        $filterLingkup = trim((string) ($this->request->getGet('lingkup_jasa') ?? ''));

        $bastList    = $this->bastModel->getList(
            null,
            ($filterPaketId !== '' && $filterPaketId !== '*') ? (int) $filterPaketId : null,
            ($filterLingkup !== '' && $filterLingkup !== '*') ? $filterLingkup : null
        );
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

        // Master Kop Surat
        $db = \Config\Database::connect();
        $kopSuratList = [];
        $defaultKop = null;
        try {
            if ($db->tableExists('kop_surat')) {
                $kopSuratList = $db->table('kop_surat')
                    ->select('id, title, image_url, is_active')
                    ->orderBy('is_active', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getResultArray();

                foreach ($kopSuratList as $k) {
                    if (! empty($k['is_active'])) {
                        $defaultKop = $k;
                        break;
                    }
                }
                if (! $defaultKop && ! empty($kopSuratList)) {
                    $defaultKop = $kopSuratList[0];
                }
            }
        } catch (\Throwable $e) {
            // Fallback empty
        }

        return view('admin/kontrak/bast/index', [
            'title'         => 'Berita Acara Serah Terima (BAST)',
            'bastList'      => $bastList,
            'paketList'     => $paketList,
            'pegawaiList'   => $pegawaiList,
            'defaultPpk'    => $defaultPpk,
            'kopSuratList'  => $kopSuratList,
            'defaultKop'    => $defaultKop,
            'permissions'   => $permissions,
            'filterPaketId' => $filterPaketId,
            'filterLingkup' => $filterLingkup,
        ]);
    }

    /**
     * Data JSON untuk DataTable jika diperlukan
     */
    public function data()
    {
        $search      = trim((string) ($this->request->getGet('search')['value'] ?? ''));
        $paketId     = trim((string) ($this->request->getGet('paket_id') ?? ''));
        $lingkupJasa = trim((string) ($this->request->getGet('lingkup_jasa') ?? ''));

        $data = $this->bastModel->getList(
            $search !== '' ? $search : null,
            ($paketId !== '' && $paketId !== '*') ? (int) $paketId : null,
            ($lingkupJasa !== '' && $lingkupJasa !== '*') ? $lingkupJasa : null
        );

        return $this->response->setJSON([
            'data' => $data,
        ]);
    }

    /**
     * Auto-fill Info Paket dari database SIMAK Konsultasi / Konstruksi
     */
    public function getPaketInfo(int $paketId)
    {
        $db = \Config\Database::connect();
        $paket = $this->paketModel->find($paketId);
        if (! $paket) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Paket tidak ditemukan.',
            ]);
        }

        $namaPaket = $paket['nama_paket'];
        $simakData = null;
        $tipe = 'konsultasi';

        // 1. Cek di trn_kontrak_simak_konsultasi
        if ($db->tableExists('trn_kontrak_simak_konsultasi')) {
            $simakData = $db->table('trn_kontrak_simak_konsultasi')
                ->where('paket_id', $paketId)
                ->orWhere('nama_paket', $namaPaket)
                ->get()
                ->getRowArray();
        }

        // 2. Jika tidak ada, cek di trn_kontrak_simak (fisik konstruksi)
        if (! $simakData && $db->tableExists('trn_kontrak_simak')) {
            $simakData = $db->table('trn_kontrak_simak')
                ->where('paket_id', $paketId)
                ->orWhere('nama_paket', $namaPaket)
                ->get()
                ->getRowArray();
            if ($simakData) {
                $tipe = 'fisik';
            }
        }

        helper('custom');

        $penyedia     = $simakData['penyedia'] ?? '';
        $nomorKontrak = $simakData['nomor_kontrak'] ?? '';
        $nilaiKontrak = ! empty($simakData['nilai_kontrak']) ? 'Rp ' . number_format((float) $simakData['nilai_kontrak'], 0, ',', '.') . ',-' : '';
        $tglKontrak   = ! empty($simakData['tanggal_kontrak']) ? $simakData['tanggal_kontrak'] : '';
        $tglKontrakStr = ! empty($tglKontrak) ? tanggal_indonesia($tglKontrak) : '';

        $jenisPekerjaan = ($tipe === 'fisik') ? 'PEKERJAAN KONSTRUKSI' : 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI';
        $lingkupJasa    = ($tipe === 'fisik') ? 'Fisik' : 'Manajemen Konstruksi';

        // Saran Dasar Pelaksanaan Standar
        $dasarPelaksanaan = [];
        if (! empty($nomorKontrak)) {
            $tglStr = $tglKontrakStr ? ' tanggal ' . $tglKontrakStr : '';
            $dasarPelaksanaan[] = 'Kontrak/Surat Perjanjian Pekerjaan ' . ($tipe === 'fisik' ? 'Konstruksi ' : 'Jasa Konsultansi ') . $namaPaket . ' Nomor ' . $nomorKontrak . $tglStr;
        } else {
            $dasarPelaksanaan[] = 'Kontrak/Surat Perjanjian Pekerjaan ' . $namaPaket . ' Nomor .........................';
        }
        $dasarPelaksanaan[] = 'Surat Perintah Mulai Kerja (SPMK) Nomor .........................';

        return $this->response->setJSON([
            'success'          => true,
            'nama_paket'       => $namaPaket,
            'penyedia'         => $penyedia,
            'nomor_kontrak'    => $nomorKontrak,
            'nilai_kontrak'    => $nilaiKontrak,
            'tanggal_kontrak'  => $tglKontrak,
            'jenis_pekerjaan'  => $jenisPekerjaan,
            'lingkup_jasa'     => $lingkupJasa,
            'dasar_pelaksanaan'=> $dasarPelaksanaan,
        ]);
    }

    /**
     * Simpan Data BAST Baru
     */
    public function simpan()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        if (! ($permissions['add'] ?? false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menambah data BAST.',
            ]);
        }

        $rules = [
            'nomor_bast'        => ['label' => 'Nomor BAST', 'rules' => 'required|min_length[3]'],
            'judul_bast'        => ['label' => 'Judul Berita Acara', 'rules' => 'required'],
            'jenis_pekerjaan'   => ['label' => 'Jenis / Lingkup Pekerjaan', 'rules' => 'required'],
            'lingkup_jasa'      => ['label' => 'Lingkup Jasa', 'rules' => 'required'],
            'nama_paket'        => ['label' => 'Nama Paket Pekerjaan', 'rules' => 'required'],
            'tanggal_bast'      => ['label' => 'Tanggal BAST', 'rules' => 'required|valid_date'],
            'kota_bast'         => ['label' => 'Kota BAST', 'rules' => 'required'],
            'ppk_nama'          => ['label' => 'Nama PPK', 'rules' => 'required'],
            'ppk_nip'           => ['label' => 'NIP PPK', 'rules' => 'required'],
            'ppk_jabatan'       => ['label' => 'Jabatan PPK', 'rules' => 'required'],
            'ppk_satker'        => ['label' => 'Satker PPK', 'rules' => 'required'],
            'penyedia_nama'     => ['label' => 'Nama Penyedia (Badan Usaha / KSO)', 'rules' => 'required'],
            'penyedia_wakil'    => ['label' => 'Nama Wakil / Direktur Penyedia', 'rules' => 'required'],
            'penyedia_jabatan'  => ['label' => 'Jabatan Penyedia', 'rules' => 'required'],
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
        }

        $post = $this->request->getPost();

        // Olah Dasar Pelaksanaan (array)
        $rawDasar = $post['dasar_pelaksanaan'] ?? [];
        $dasarArr = [];
        if (is_array($rawDasar)) {
            foreach ($rawDasar as $d) {
                $item = trim((string) $d);
                if ($item !== '') {
                    $dasarArr[] = $item;
                }
            }
        }

        // Olah Rincian Hasil Pekerjaan (array of objects)
        $rawRincianNo   = $post['rincian_no'] ?? [];
        $rawRincianUr   = $post['rincian_uraian'] ?? [];
        $rawRincianJml  = $post['rincian_jumlah'] ?? [];
        $rawRincianKet  = $post['rincian_keterangan'] ?? [];

        $rincianArr = [];
        if (is_array($rawRincianUr)) {
            $count = count($rawRincianUr);
            for ($i = 0; $i < $count; $i++) {
                $uraian = trim((string) ($rawRincianUr[$i] ?? ''));
                if ($uraian !== '') {
                    $rincianArr[] = [
                        'no'         => ! empty($rawRincianNo[$i]) ? trim((string) $rawRincianNo[$i]) : (string) (count($rincianArr) + 1),
                        'uraian'     => $uraian,
                        'jumlah'     => trim((string) ($rawRincianJml[$i] ?? '')),
                        'keterangan' => trim((string) ($rawRincianKet[$i] ?? '')),
                    ];
                }
            }
        }

        $insertData = [
            'nomor_bast'              => trim((string) ($post['nomor_bast'] ?? '')),
            'judul_bast'              => trim((string) ($post['judul_bast'] ?? '')) ?: 'BERITA ACARA SERAH TERIMA',
            'jenis_pekerjaan'         => trim((string) ($post['jenis_pekerjaan'] ?? '')),
            'lingkup_jasa'            => trim((string) ($post['lingkup_jasa'] ?? '')),
            'paket_id'                => ! empty($post['paket_id']) ? (int) $post['paket_id'] : null,
            'nama_paket'              => trim((string) ($post['nama_paket'] ?? '')),
            'tanggal_bast'            => ! empty($post['tanggal_bast']) ? $post['tanggal_bast'] : date('Y-m-d'),
            'kota_bast'               => trim((string) ($post['kota_bast'] ?? 'Pekanbaru')) ?: 'Pekanbaru',
            'kop_surat_id'            => ! empty($post['kop_surat_id']) ? (int) $post['kop_surat_id'] : null,
            'ppk_pegawai_id'          => ! empty($post['ppk_pegawai_id']) ? (int) $post['ppk_pegawai_id'] : null,
            'ppk_nama'                => trim((string) ($post['ppk_nama'] ?? '')),
            'ppk_nip'                 => trim((string) ($post['ppk_nip'] ?? '')),
            'ppk_jabatan'             => trim((string) ($post['ppk_jabatan'] ?? '')),
            'ppk_satker'              => trim((string) ($post['ppk_satker'] ?? '')),
            'ppk_alamat'              => trim((string) ($post['ppk_alamat'] ?? '')),
            'penyedia_nama'           => trim((string) ($post['penyedia_nama'] ?? '')),
            'penyedia_wakil'          => trim((string) ($post['penyedia_wakil'] ?? '')),
            'penyedia_jabatan'        => trim((string) ($post['penyedia_jabatan'] ?? '')),
            'penyedia_alamat'         => trim((string) ($post['penyedia_alamat'] ?? '')),
            'dasar_pelaksanaan'       => ! empty($dasarArr) ? json_encode($dasarArr, JSON_UNESCAPED_UNICODE) : null,
            'rincian_hasil_pekerjaan' => ! empty($rincianArr) ? json_encode($rincianArr, JSON_UNESCAPED_UNICODE) : null,
            'kesesuaian_pekerjaan'    => trim((string) ($post['kesesuaian_pekerjaan'] ?? 'telah sesuai')),
            'persentase_pembayaran'   => trim((string) ($post['persentase_pembayaran'] ?? '100%')),
            'created_by'              => (string) (session()->get('username') ?? 'admin'),
        ];

        $insertId = $this->bastModel->insert($insertData);
        if (! $insertId) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan data BAST ke database.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Berita Acara Serah Terima (BAST) berhasil disimpan.',
            'id'      => $insertId,
        ]);
    }

    /**
     * Ambil Detail Data untuk Form Edit / Modal Preview
     */
    public function detail(int $id)
    {
        $row = $this->bastModel->find($id);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data BAST tidak ditemukan.',
            ]);
        }

        helper('custom');
        $row['tanggal_bast_formatted'] = ! empty($row['tanggal_bast']) ? tanggal_indonesia($row['tanggal_bast']) : '';
        $row['hari_tanggal_terbilang'] = ! empty($row['tanggal_bast']) ? $this->formatTanggalTerbilangBAST($row['tanggal_bast']) : '';
        $row['dasar_pelaksanaan_array'] = ! empty($row['dasar_pelaksanaan']) ? json_decode($row['dasar_pelaksanaan'], true) : [];
        $row['rincian_hasil_array']     = ! empty($row['rincian_hasil_pekerjaan']) ? json_decode($row['rincian_hasil_pekerjaan'], true) : [];

        $isFisik = (strcasecmp(trim((string) ($row['lingkup_jasa'] ?? '')), 'Fisik') === 0);
        $row['is_fisik'] = $isFisik;
        $row['jenis_pekerjaan_singkat'] = $isFisik ? 'konstruksi' : 'jasa konsultansi';

        return $this->response->setJSON([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * Perbarui Data BAST
     */
    public function ubah(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        if (! ($permissions['edit'] ?? false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengubah data ini.',
            ]);
        }

        $existing = $this->bastModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ]);
        }

        $rules = [
            'nomor_bast'        => ['label' => 'Nomor BAST', 'rules' => 'required|min_length[3]'],
            'judul_bast'        => ['label' => 'Judul Berita Acara', 'rules' => 'required'],
            'jenis_pekerjaan'   => ['label' => 'Jenis / Lingkup Pekerjaan', 'rules' => 'required'],
            'lingkup_jasa'      => ['label' => 'Lingkup Jasa', 'rules' => 'required'],
            'nama_paket'        => ['label' => 'Nama Paket Pekerjaan', 'rules' => 'required'],
            'tanggal_bast'      => ['label' => 'Tanggal BAST', 'rules' => 'required|valid_date'],
            'kota_bast'         => ['label' => 'Kota BAST', 'rules' => 'required'],
            'ppk_nama'          => ['label' => 'Nama PPK', 'rules' => 'required'],
            'ppk_nip'           => ['label' => 'NIP PPK', 'rules' => 'required'],
            'ppk_jabatan'       => ['label' => 'Jabatan PPK', 'rules' => 'required'],
            'ppk_satker'        => ['label' => 'Satker PPK', 'rules' => 'required'],
            'penyedia_nama'     => ['label' => 'Nama Penyedia (Badan Usaha / KSO)', 'rules' => 'required'],
            'penyedia_wakil'    => ['label' => 'Nama Wakil / Direktur Penyedia', 'rules' => 'required'],
            'penyedia_jabatan'  => ['label' => 'Jabatan Penyedia', 'rules' => 'required'],
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(', ', $errors),
                'errors'  => $errors,
            ]);
        }

        $post = $this->request->getPost();

        // Olah Dasar Pelaksanaan (array)
        $rawDasar = $post['dasar_pelaksanaan'] ?? [];
        $dasarArr = [];
        if (is_array($rawDasar)) {
            foreach ($rawDasar as $d) {
                $item = trim((string) $d);
                if ($item !== '') {
                    $dasarArr[] = $item;
                }
            }
        }

        // Olah Rincian Hasil Pekerjaan (array of objects)
        $rawRincianNo   = $post['rincian_no'] ?? [];
        $rawRincianUr   = $post['rincian_uraian'] ?? [];
        $rawRincianJml  = $post['rincian_jumlah'] ?? [];
        $rawRincianKet  = $post['rincian_keterangan'] ?? [];

        $rincianArr = [];
        if (is_array($rawRincianUr)) {
            $count = count($rawRincianUr);
            for ($i = 0; $i < $count; $i++) {
                $uraian = trim((string) ($rawRincianUr[$i] ?? ''));
                if ($uraian !== '') {
                    $rincianArr[] = [
                        'no'         => ! empty($rawRincianNo[$i]) ? trim((string) $rawRincianNo[$i]) : (string) (count($rincianArr) + 1),
                        'uraian'     => $uraian,
                        'jumlah'     => trim((string) ($rawRincianJml[$i] ?? '')),
                        'keterangan' => trim((string) ($rawRincianKet[$i] ?? '')),
                    ];
                }
            }
        }

        $updateData = [
            'nomor_bast'              => trim((string) ($post['nomor_bast'] ?? '')),
            'judul_bast'              => trim((string) ($post['judul_bast'] ?? '')) ?: 'BERITA ACARA SERAH TERIMA',
            'jenis_pekerjaan'         => trim((string) ($post['jenis_pekerjaan'] ?? '')),
            'lingkup_jasa'            => trim((string) ($post['lingkup_jasa'] ?? '')),
            'paket_id'                => ! empty($post['paket_id']) ? (int) $post['paket_id'] : null,
            'nama_paket'              => trim((string) ($post['nama_paket'] ?? '')),
            'tanggal_bast'            => ! empty($post['tanggal_bast']) ? $post['tanggal_bast'] : date('Y-m-d'),
            'kota_bast'               => trim((string) ($post['kota_bast'] ?? 'Pekanbaru')) ?: 'Pekanbaru',
            'kop_surat_id'            => ! empty($post['kop_surat_id']) ? (int) $post['kop_surat_id'] : null,
            'ppk_pegawai_id'          => ! empty($post['ppk_pegawai_id']) ? (int) $post['ppk_pegawai_id'] : null,
            'ppk_nama'                => trim((string) ($post['ppk_nama'] ?? '')),
            'ppk_nip'                 => trim((string) ($post['ppk_nip'] ?? '')),
            'ppk_jabatan'             => trim((string) ($post['ppk_jabatan'] ?? '')),
            'ppk_satker'              => trim((string) ($post['ppk_satker'] ?? '')),
            'ppk_alamat'              => trim((string) ($post['ppk_alamat'] ?? '')),
            'penyedia_nama'           => trim((string) ($post['penyedia_nama'] ?? '')),
            'penyedia_wakil'          => trim((string) ($post['penyedia_wakil'] ?? '')),
            'penyedia_jabatan'        => trim((string) ($post['penyedia_jabatan'] ?? '')),
            'penyedia_alamat'         => trim((string) ($post['penyedia_alamat'] ?? '')),
            'dasar_pelaksanaan'       => ! empty($dasarArr) ? json_encode($dasarArr, JSON_UNESCAPED_UNICODE) : null,
            'rincian_hasil_pekerjaan' => ! empty($rincianArr) ? json_encode($rincianArr, JSON_UNESCAPED_UNICODE) : null,
            'kesesuaian_pekerjaan'    => trim((string) ($post['kesesuaian_pekerjaan'] ?? 'telah sesuai')),
            'persentase_pembayaran'   => trim((string) ($post['persentase_pembayaran'] ?? '100%')),
            'updated_by'              => (string) (session()->get('username') ?? 'admin'),
        ];

        $this->bastModel->update($id, $updateData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data Berita Acara Serah Terima (BAST) berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus Data BAST
     */
    public function hapus(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        if (! ($permissions['delete'] ?? false)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus data ini.',
            ]);
        }

        $existing = $this->bastModel->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ]);
        }

        $this->bastModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data Berita Acara Serah Terima (BAST) berhasil dihapus.',
        ]);
    }

    /**
     * Unduh Dokumen Word (.docx) Sesuai Template Resmi BAST
     */
    public function unduhDocx(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Anda tidak memiliki hak akses untuk mengunduh dokumen.');
        }

        $row = $this->bastModel->find($id);
        if (! $row) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Data tidak ditemukan.');
        }

        $templateFile = APPPATH . 'Views/admin/kontrak/bast_template.docx';
        if (! file_exists($templateFile)) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Berkas template dokumen Word BAST tidak ditemukan.');
        }

        helper('custom');

        $isFisik = (strcasecmp(trim((string) ($row['lingkup_jasa'] ?? '')), 'Fisik') === 0);
        $jenisPekerjaanSingkat = $isFisik ? 'konstruksi' : 'jasa konsultansi';
        $hariTanggalTerbilang = $this->formatTanggalTerbilangBAST($row['tanggal_bast'] ?? date('Y-m-d'));

        // Dasar Pelaksanaan Block
        $dasarArr = ! empty($row['dasar_pelaksanaan']) ? json_decode($row['dasar_pelaksanaan'], true) : [];
        $dasarText = ! empty($dasarArr) ? implode("\n", $dasarArr) : '-';

        // Tanda Tangan PPK
        $ppkJabatan = trim((string) ($row['ppk_jabatan'] ?? ''));
        $ppkTtd1 = 'Pejabat Penandatangan Kontrak';
        $ppkTtd2 = 'Pelaksanaan Prasarana Strategis';
        if (! empty($ppkJabatan) && stripos($ppkJabatan, ',') !== false) {
            $parts = explode(',', $ppkJabatan, 2);
            $ppkTtd1 = trim($parts[0]);
            $ppkTtd2 = trim($parts[1]);
        } elseif (! empty($ppkJabatan)) {
            $ppkTtd1 = $ppkJabatan;
            $ppkTtd2 = trim((string) ($row['ppk_satker'] ?? 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau'));
        }

        $replacements = [
            'judul_bast'                     => $row['judul_bast'] ?: 'BERITA ACARA SERAH TERIMA I',
            'jenis_pekerjaan'                => $row['jenis_pekerjaan'] ?: 'PEKERJAAN JASA KONSULTANSI KONSTRUKSI',
            'nama_paket'                     => $row['nama_paket'] ?: '-',
            'nomor_bast'                     => $row['nomor_bast'] ?: '...............',
            'hari_tanggal_terbilang'         => $hariTanggalTerbilang,
            'kota_bast'                      => $row['kota_bast'] ?: 'Pekanbaru',
            'ppk_nama'                       => $row['ppk_nama'] ?: 'Nurhidayat Nugroho, S. Ars',
            'ppk_nip'                        => $row['ppk_nip'] ?: '199012212018021001',
            'ppk_jabatan'                    => $row['ppk_jabatan'] ?: 'Pejabat Penandatangan Kontrak Pelaksanaan Prasarana Strategis, Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'ppk_alamat'                     => $row['ppk_alamat'] ?: 'Jl. Datuk Setia Maharaja No. 15 Pekanbaru, Riau',
            'penyedia_wakil'                 => $row['penyedia_wakil'] ?: '-',
            'penyedia_jabatan'               => $row['penyedia_jabatan'] ?: 'Direktur Utama',
            'penyedia_alamat'                => $row['penyedia_alamat'] ?: '-',
            'penyedia_nama'                  => $row['penyedia_nama'] ?: '-',
            'dasar_pelaksanaan_placeholder'  => $dasarText,
            'jenis_pekerjaan_singkat'        => $jenisPekerjaanSingkat,
            'kesesuaian_pekerjaan'           => $row['kesesuaian_pekerjaan'] ?: 'telah sesuai',
            'persentase_pembayaran'          => $row['persentase_pembayaran'] ?: '100%',
            'ppk_tanda_tangan_jabatan1'      => $ppkTtd1,
            'ppk_tanda_tangan_jabatan2'      => $ppkTtd2,
        ];

        // Resolusi berkas kop surat
        $kopLocalPath = null;
        $kopSuratId = ! empty($row['kop_surat_id']) ? (int) $row['kop_surat_id'] : null;
        $kopUrl = kop_surat_url($kopSuratId);
        if (! empty($kopUrl)) {
            $cleanUrl = ltrim($kopUrl, '/');
            if (preg_match('#^https?://[^/]+/(.*)$#i', $kopUrl, $m)) {
                $cleanUrl = ltrim($m[1], '/');
            }
            $candidate = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $cleanUrl);
            if (file_exists($candidate)) {
                $kopLocalPath = $candidate;
            }
        }
        if (! $kopLocalPath) {
            $fallback = FCPATH . 'uploads/kop_surat/kop_surket_ta.png';
            if (file_exists($fallback)) {
                $kopLocalPath = $fallback;
            }
        }

        $rincianArr = ! empty($row['rincian_hasil_pekerjaan']) ? json_decode($row['rincian_hasil_pekerjaan'], true) : [];
        if (empty($rincianArr)) {
            $rincianArr = [
                ['no' => 1, 'uraian' => 'Laporan Hasil Pekerjaan', 'jumlah' => '1 Berkas', 'keterangan' => 'Hardcopy & softcopy'],
            ];
        }

        $docxBinary = $this->renderDocxFromTemplate($templateFile, $replacements, $rincianArr, $kopLocalPath);
        if (! $docxBinary) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Gagal memproses dokumen Word.');
        }

        $cleanNomor = preg_replace('/[^A-Za-z0-9_-]/', '_', $row['nomor_bast'] ?? 'BAST');
        $filename   = 'BAST_' . $cleanNomor . '_' . date('Ymd') . '.docx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($docxBinary);
    }

    /**
     * Cetak Dokumen PDF Resmi BAST
     */
    public function cetakPdf(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Anda tidak memiliki hak akses untuk mencetak PDF.');
        }

        $row = $this->bastModel->find($id);
        if (! $row) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Data tidak ditemukan.');
        }

        helper('custom');

        $isFisik = (strcasecmp(trim((string) ($row['lingkup_jasa'] ?? '')), 'Fisik') === 0);
        $hariTanggalTerbilang = $this->formatTanggalTerbilangBAST($row['tanggal_bast'] ?? date('Y-m-d'));
        $dasarArr = ! empty($row['dasar_pelaksanaan']) ? json_decode($row['dasar_pelaksanaan'], true) : [];
        $rincianArr = ! empty($row['rincian_hasil_pekerjaan']) ? json_decode($row['rincian_hasil_pekerjaan'], true) : [];

        // Resolusi berkas kop surat untuk PDF Base64
        $kopLocalPath = null;
        $kopSuratId = ! empty($row['kop_surat_id']) ? (int) $row['kop_surat_id'] : null;
        $kopUrl = kop_surat_url($kopSuratId);
        if (! empty($kopUrl)) {
            $cleanUrl = ltrim($kopUrl, '/');
            if (preg_match('#^https?://[^/]+/(.*)$#i', $kopUrl, $m)) {
                $cleanUrl = ltrim($m[1], '/');
            }
            $candidate = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $cleanUrl);
            if (file_exists($candidate)) {
                $kopLocalPath = $candidate;
            }
        }
        if (! $kopLocalPath) {
            $fallback = FCPATH . 'uploads/kop_surat/kop_surket_ta.png';
            if (file_exists($fallback)) {
                $kopLocalPath = $fallback;
            }
        }

        $kopBase64 = '';
        if ($kopLocalPath && file_exists($kopLocalPath)) {
            $mime = mime_content_type($kopLocalPath) ?: 'image/png';
            $kopBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($kopLocalPath));
        }

        $html = view('admin/kontrak/bast/cetak_pdf', [
            'row'                  => $row,
            'kopBase64'            => $kopBase64,
            'hariTanggalTerbilang' => $hariTanggalTerbilang,
            'dasarArr'             => $dasarArr,
            'rincianArr'           => $rincianArr,
            'isFisik'              => $isFisik,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $cleanNomor = preg_replace('/[^A-Za-z0-9_-]/', '_', $row['nomor_bast'] ?? 'BAST');
        $filename   = 'BAST_' . $cleanNomor . '_' . date('Ymd') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Export Seluruh Dokumen PDF Sekaligus Berdasarkan Filter
     */
    public function exportPdf()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/bast');
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Anda tidak memiliki hak akses untuk mengekspor PDF.');
        }

        $filterPaketId = trim((string) ($this->request->getGet('paket_id') ?? ''));
        $filterLingkup = trim((string) ($this->request->getGet('lingkup_jasa') ?? ''));

        $bastList = $this->bastModel->getList(
            null,
            ($filterPaketId !== '' && $filterPaketId !== '*') ? (int) $filterPaketId : null,
            ($filterLingkup !== '' && $filterLingkup !== '*') ? $filterLingkup : null
        );

        if (empty($bastList)) {
            return redirect()->to(site_url('admin/kontrak/bast'))->with('error', 'Tidak ada data BAST yang cocok dengan filter untuk diekspor.');
        }

        helper('custom');

        $records = [];
        foreach ($bastList as $row) {
            $isFisik = (strcasecmp(trim((string) ($row['lingkup_jasa'] ?? '')), 'Fisik') === 0);
            $hariTanggalTerbilang = $this->formatTanggalTerbilangBAST($row['tanggal_bast'] ?? date('Y-m-d'));
            $dasarArr = ! empty($row['dasar_pelaksanaan']) ? json_decode($row['dasar_pelaksanaan'], true) : [];
            $rincianArr = ! empty($row['rincian_hasil_pekerjaan']) ? json_decode($row['rincian_hasil_pekerjaan'], true) : [];

            $kopLocalPath = null;
            $kopSuratId = ! empty($row['kop_surat_id']) ? (int) $row['kop_surat_id'] : null;
            $kopUrl = kop_surat_url($kopSuratId);
            if (! empty($kopUrl)) {
                $cleanUrl = ltrim($kopUrl, '/');
                if (preg_match('#^https?://[^/]+/(.*)$#i', $kopUrl, $m)) {
                    $cleanUrl = ltrim($m[1], '/');
                }
                $candidate = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $cleanUrl);
                if (file_exists($candidate)) {
                    $kopLocalPath = $candidate;
                }
            }
            if (! $kopLocalPath) {
                $fallback = FCPATH . 'uploads/kop_surat/kop_surket_ta.png';
                if (file_exists($fallback)) {
                    $kopLocalPath = $fallback;
                }
            }

            $kopBase64 = '';
            if ($kopLocalPath && file_exists($kopLocalPath)) {
                $mime = mime_content_type($kopLocalPath) ?: 'image/png';
                $kopBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($kopLocalPath));
            }

            $records[] = [
                'row'                  => $row,
                'kopBase64'            => $kopBase64,
                'hariTanggalTerbilang' => $hariTanggalTerbilang,
                'dasarArr'             => $dasarArr,
                'rincianArr'           => $rincianArr,
                'isFisik'              => $isFisik,
            ];
        }

        $html = view('admin/kontrak/bast/export_all_pdf', [
            'records'       => $records,
            'filterPaketId' => $filterPaketId,
            'filterLingkup' => $filterLingkup,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filenameSuffix = '';
        if (! empty($filterPaketId)) {
            $filenameSuffix .= '_Paket' . $filterPaketId;
        }
        if (! empty($filterLingkup)) {
            $filenameSuffix .= '_' . preg_replace('/[^A-Za-z0-9]/', '', $filterLingkup);
        }
        $filename = 'BAST_Konsolidasi' . $filenameSuffix . '_' . date('Ymd_His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Helper render DOCX dengan TemplateProcessor dan fallback ZipArchive
     */
    private function renderDocxFromTemplate(string $templateFile, array $replacements, array $rincianArr, ?string $kopImagePath = null): ?string
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

                // Clone table rows
                if (! empty($rincianArr)) {
                    $processor->cloneRow('item_no', count($rincianArr));
                    foreach ($rincianArr as $i => $item) {
                        $idx = $i + 1;
                        $processor->setValue('item_no#' . $idx, htmlspecialchars((string) ($item['no'] ?? $idx), ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                        $processor->setValue('item_uraian#' . $idx, htmlspecialchars((string) ($item['uraian'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                        $processor->setValue('item_jumlah#' . $idx, htmlspecialchars((string) ($item['jumlah'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                        $processor->setValue('item_keterangan#' . $idx, htmlspecialchars((string) ($item['keterangan'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                    }
                }

                $tempPath = WRITEPATH . 'uploads/' . uniqid('bast_', true) . '.docx';
                $processor->saveAs($tempPath);

                $hasKopToEmbed = ($kopImagePath && file_exists($kopImagePath));
                if ($hasKopToEmbed) {
                    $zip = new \ZipArchive();
                    if ($zip->open($tempPath) === true) {
                        $zip->addFile($kopImagePath, 'word/media/image1.png');
                        $zip->close();
                    }
                }

                $content = file_get_contents($tempPath);
                @unlink($tempPath);
                return $content ?: null;
            } catch (\Throwable $e) {
                log_message('warning', 'PhpWord TemplateProcessor failed: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Format Tanggal Terbilang Indonesia Resmi untuk BAST
     * Contoh: "Sabtu tanggal Tiga Puluh bulan Mei tahun Dua Ribu Dua Puluh Enam (30-05-2026)"
     */
    private function formatTanggalTerbilangBAST(string $dateStr): string
    {
        helper('custom');
        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $ts = strtotime($dateStr);
        if (! $ts) {
            $ts = time();
        }

        $dayName   = $days[date('l', $ts)] ?? '';
        $dayNum    = (int) date('d', $ts);
        $monthNum  = (int) date('m', $ts);
        $yearNum   = (int) date('Y', $ts);

        $dayWords   = ucwords(trim(terbilang_angka($dayNum)));
        $monthName  = $months[$monthNum] ?? '';
        $yearWords  = ucwords(trim(terbilang_angka($yearNum)));
        $dmy        = date('d-m-Y', $ts);

        return "{$dayName} tanggal {$dayWords} bulan {$monthName} tahun {$yearWords} ({$dmy})";
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
