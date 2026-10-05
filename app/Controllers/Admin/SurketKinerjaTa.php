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
        $permissions   = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        $filterPaketId = trim((string) ($this->request->getGet('paket_id') ?? ''));
        $filterLingkup = trim((string) ($this->request->getGet('lingkup_jasa') ?? ''));

        $surketList  = $this->surketModel->getList(
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

        return view('admin/kontrak/surket_kinerja_ta/index', [
            'title'         => 'Surat Keterangan Kinerja Tenaga Ahli',
            'surketList'    => $surketList,
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

        $data = $this->surketModel->getList(
            $search !== '' ? $search : null,
            ($paketId !== '' && $paketId !== '*') ? (int) $paketId : null,
            ($lingkupJasa !== '' && $lingkupJasa !== '*') ? $lingkupJasa : null
        );

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
            'lingkup_jasa'          => ['label' => 'Lingkup Jasa', 'rules' => 'required|in_list[Manajemen Konstruksi,Fisik,Supervisi]'],
            'lokasi_pekerjaan'      => ['label' => 'Lokasi Pekerjaan', 'rules' => 'required'],
            'nomor_kontrak'         => ['label' => 'Nomor Kontrak', 'rules' => 'required'],
            'tanggal_kontrak'       => ['label' => 'Tanggal Kontrak', 'rules' => 'required|valid_date'],
            'nilai_kontrak'             => ['label' => 'Nilai Kontrak', 'rules' => 'required'],
            'sumber_dana'               => ['label' => 'Sumber Dana', 'rules' => 'required'],
            'tanggal_mulai_penugasan'   => ['label' => 'Tanggal Mulai Penugasan', 'rules' => 'required|valid_date'],
            'tanggal_selesai_penugasan' => ['label' => 'Tanggal Selesai Penugasan', 'rules' => 'required|valid_date'],
            'masa_penugasan_hari'       => ['label' => 'Total Hari Penugasan', 'rules' => 'required|numeric|greater_than[0]'],
            'status_persen'             => ['label' => 'Status Pekerjaan (%)', 'rules' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'ppk_nama'                  => ['label' => 'Nama PPK', 'rules' => 'required'],
            'ppk_nip'                   => ['label' => 'NIP PPK', 'rules' => 'required'],
            'ppk_jabatan'               => ['label' => 'Jabatan PPK', 'rules' => 'required'],
            'ppk_satker'                => ['label' => 'Satuan Kerja PPK', 'rules' => 'required'],
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
        $kopSuratId = ! empty($post['kop_surat_id']) ? (int) $post['kop_surat_id'] : null;

        // Format Nilai Kontrak Rupiah
        $rawNilai = preg_replace('/[^0-9]/', '', (string) ($post['nilai_kontrak'] ?? ''));
        $formattedNilai = $rawNilai !== '' ? 'Rp ' . number_format((float) $rawNilai, 0, ',', '.') . ',-' : trim((string) $post['nilai_kontrak']);

        // Masa Penugasan (Dari Tanggal s.d. Tanggal & Total Hari)
        $tglMulai = ! empty($post['tanggal_mulai_penugasan']) ? $post['tanggal_mulai_penugasan'] : null;
        $tglSelesai = ! empty($post['tanggal_selesai_penugasan']) ? $post['tanggal_selesai_penugasan'] : null;
        $hari = (int) ($post['masa_penugasan_hari'] ?? 0);

        if ($tglMulai && $tglSelesai) {
            if ($hari <= 0) {
                $diff = strtotime($tglSelesai) - strtotime($tglMulai);
                $hari = max(1, (int) round($diff / 86400) + 1);
            }
            $strMulai = tanggal_indonesia($tglMulai);
            $strSelesai = tanggal_indonesia($tglSelesai);
            $hariStr = ($hari > 0) ? " ({$hari} Hari)" : '';
            $masaPenugasan = "{$strMulai} s.d. {$strSelesai}{$hariStr}";
        } elseif ($hari > 0) {
            $masaPenugasan = "{$hari} Hari";
        } else {
            $masaPenugasan = trim((string) ($post['masa_penugasan'] ?? ''));
        }

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
            'nomor_surat'               => trim((string) ($post['nomor_surat'] ?? '')),
            'tanggal_surat'             => ! empty($post['tanggal_surat']) ? $post['tanggal_surat'] : date('Y-m-d'),
            'kota_surat'                => trim((string) ($post['kota_surat'] ?? 'Pekanbaru')) ?: 'Pekanbaru',
            'kop_surat_id'              => $kopSuratId,
            'ppk_pegawai_id'            => $ppkPegawaiId,
            'ppk_nama'                  => trim((string) ($post['ppk_nama'] ?? '')),
            'ppk_nip'                   => trim((string) ($post['ppk_nip'] ?? '')),
            'ppk_jabatan'               => trim((string) ($post['ppk_jabatan'] ?? '')),
            'ppk_satker'                => trim((string) ($post['ppk_satker'] ?? '')),
            'ppk_alamat'                => trim((string) ($post['ppk_alamat'] ?? '')),
            'nama_tenaga_ahli'          => trim((string) ($post['nama_tenaga_ahli'] ?? '')),
            'jabatan_pekerjaan'         => trim((string) ($post['jabatan_pekerjaan'] ?? '')),
            'nama_badan_usaha'          => trim((string) ($post['nama_badan_usaha'] ?? '')),
            'alamat_badan_usaha'        => trim((string) ($post['alamat_badan_usaha'] ?? '')),
            'paket_id'                  => $paketId,
            'nama_paket'                => trim((string) ($post['nama_paket'] ?? '')),
            'lingkup_jasa'              => trim((string) ($post['lingkup_jasa'] ?? 'Manajemen Konstruksi')),
            'lokasi_pekerjaan'          => trim((string) ($post['lokasi_pekerjaan'] ?? '')),
            'nomor_kontrak'             => $nomorKontrak,
            'tanggal_kontrak'           => $tanggalKontrak,
            'nomor_tanggal_kontrak'     => $nomorTanggalKontrak,
            'nilai_kontrak'             => $formattedNilai,
            'sumber_dana'               => trim((string) ($post['sumber_dana'] ?? '')),
            'tanggal_mulai_penugasan'   => $tglMulai,
            'tanggal_selesai_penugasan' => $tglSelesai,
            'masa_penugasan_hari'       => $hari,
            'masa_penugasan'            => $masaPenugasan,
            'status_persen'             => $persen,
            'status_pekerjaan'          => $statusPekerjaan,
            'created_by'                => (string) (session()->get('username') ?? 'admin'),
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

        $isFisik = (strcasecmp(trim((string) ($row['lingkup_jasa'] ?? '')), 'Fisik') === 0);
        $row['is_fisik'] = $isFisik;
        $row['sub_judul_2'] = $isFisik ? 'KONSTRUKSI' : 'KONSULTANSI KONSTRUKSI';
        $row['teks_pengantar'] = $isFisik 
            ? 'telah melaksanakan pekerjaan konstruksi dengan data sebagai berikut:'
            : 'telah melaksanakan pekerjaan jasa konsultansi dengan data sebagai berikut:';
        $row['teks_penutup'] = $isFisik
            ? 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan pekerjaan konstruksi.'
            : 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi.';

        $nomorKontrak = trim((string) ($row['nomor_kontrak'] ?? ''));
        $tglKontrak   = ! empty($row['tanggal_kontrak']) ? tanggal_indonesia($row['tanggal_kontrak']) : '';
        if (empty($nomorKontrak) || empty($tglKontrak)) {
            $rawNoTgl = trim((string) ($row['nomor_tanggal_kontrak'] ?? ''));
            if (stripos($rawNoTgl, ' tanggal ') !== false) {
                $parts = preg_split('/ tanggal /i', $rawNoTgl, 2);
                if (empty($nomorKontrak)) $nomorKontrak = trim($parts[0] ?? '');
                if (empty($tglKontrak)) $tglKontrak = trim($parts[1] ?? '');
            } elseif (empty($nomorKontrak)) {
                $nomorKontrak = $rawNoTgl;
            }
        }
        $row['nomor_kontrak_clean'] = $nomorKontrak;
        $row['tanggal_kontrak_clean'] = $tglKontrak;

        $defaultJabatan = 'Pejabat Penanda Tangan Kontrak Pelaksanaan Prasarana Strategis, Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $ppkJabatan = trim((string)($row['ppk_jabatan'] ?? ''));
        if (empty($ppkJabatan) || $ppkJabatan === 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis' || $ppkJabatan === 'PPK Pelaksanaan Prasarana Strategis') {
            $row['ppk_jabatan'] = $defaultJabatan;
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
            'lingkup_jasa'          => ['label' => 'Lingkup Jasa', 'rules' => 'required|in_list[Manajemen Konstruksi,Fisik,Supervisi]'],
            'lokasi_pekerjaan'      => ['label' => 'Lokasi Pekerjaan', 'rules' => 'required'],
            'nomor_kontrak'         => ['label' => 'Nomor Kontrak', 'rules' => 'required'],
            'tanggal_kontrak'       => ['label' => 'Tanggal Kontrak', 'rules' => 'required|valid_date'],
            'nilai_kontrak'             => ['label' => 'Nilai Kontrak', 'rules' => 'required'],
            'sumber_dana'               => ['label' => 'Sumber Dana', 'rules' => 'required'],
            'tanggal_mulai_penugasan'   => ['label' => 'Tanggal Mulai Penugasan', 'rules' => 'required|valid_date'],
            'tanggal_selesai_penugasan' => ['label' => 'Tanggal Selesai Penugasan', 'rules' => 'required|valid_date'],
            'masa_penugasan_hari'       => ['label' => 'Total Hari Penugasan', 'rules' => 'required|numeric|greater_than[0]'],
            'status_persen'             => ['label' => 'Status Pekerjaan (%)', 'rules' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'ppk_nama'                  => ['label' => 'Nama PPK', 'rules' => 'required'],
            'ppk_nip'                   => ['label' => 'NIP PPK', 'rules' => 'required'],
            'ppk_jabatan'               => ['label' => 'Jabatan PPK', 'rules' => 'required'],
            'ppk_satker'                => ['label' => 'Satuan Kerja PPK', 'rules' => 'required'],
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
        $kopSuratId = ! empty($post['kop_surat_id']) ? (int) $post['kop_surat_id'] : null;

        // Format Nilai Kontrak Rupiah
        $rawNilai = preg_replace('/[^0-9]/', '', (string) ($post['nilai_kontrak'] ?? ''));
        $formattedNilai = $rawNilai !== '' ? 'Rp ' . number_format((float) $rawNilai, 0, ',', '.') . ',-' : trim((string) $post['nilai_kontrak']);

        // Masa Penugasan (Dari Tanggal s.d. Tanggal & Total Hari)
        $tglMulai = ! empty($post['tanggal_mulai_penugasan']) ? $post['tanggal_mulai_penugasan'] : null;
        $tglSelesai = ! empty($post['tanggal_selesai_penugasan']) ? $post['tanggal_selesai_penugasan'] : null;
        $hari = (int) ($post['masa_penugasan_hari'] ?? 0);

        if ($tglMulai && $tglSelesai) {
            if ($hari <= 0) {
                $diff = strtotime($tglSelesai) - strtotime($tglMulai);
                $hari = max(1, (int) round($diff / 86400) + 1);
            }
            $strMulai = tanggal_indonesia($tglMulai);
            $strSelesai = tanggal_indonesia($tglSelesai);
            $hariStr = ($hari > 0) ? " ({$hari} Hari)" : '';
            $masaPenugasan = "{$strMulai} s.d. {$strSelesai}{$hariStr}";
        } elseif ($hari > 0) {
            $masaPenugasan = "{$hari} Hari";
        } else {
            $masaPenugasan = trim((string) ($post['masa_penugasan'] ?? ''));
        }

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
            'nomor_surat'               => trim((string) ($post['nomor_surat'] ?? '')),
            'tanggal_surat'             => ! empty($post['tanggal_surat']) ? $post['tanggal_surat'] : date('Y-m-d'),
            'kota_surat'                => trim((string) ($post['kota_surat'] ?? 'Pekanbaru')) ?: 'Pekanbaru',
            'kop_surat_id'              => $kopSuratId,
            'ppk_pegawai_id'            => $ppkPegawaiId,
            'ppk_nama'                  => trim((string) ($post['ppk_nama'] ?? '')),
            'ppk_nip'                   => trim((string) ($post['ppk_nip'] ?? '')),
            'ppk_jabatan'               => trim((string) ($post['ppk_jabatan'] ?? '')),
            'ppk_satker'                => trim((string) ($post['ppk_satker'] ?? '')),
            'ppk_alamat'                => trim((string) ($post['ppk_alamat'] ?? '')),
            'nama_tenaga_ahli'          => trim((string) ($post['nama_tenaga_ahli'] ?? '')),
            'jabatan_pekerjaan'         => trim((string) ($post['jabatan_pekerjaan'] ?? '')),
            'nama_badan_usaha'          => trim((string) ($post['nama_badan_usaha'] ?? '')),
            'alamat_badan_usaha'        => trim((string) ($post['alamat_badan_usaha'] ?? '')),
            'paket_id'                  => $paketId,
            'nama_paket'                => trim((string) ($post['nama_paket'] ?? '')),
            'lingkup_jasa'              => trim((string) ($post['lingkup_jasa'] ?? 'Manajemen Konstruksi')),
            'lokasi_pekerjaan'          => trim((string) ($post['lokasi_pekerjaan'] ?? '')),
            'nomor_kontrak'             => $nomorKontrak,
            'tanggal_kontrak'           => $tanggalKontrak,
            'nomor_tanggal_kontrak'     => $nomorTanggalKontrak,
            'nilai_kontrak'             => $formattedNilai,
            'sumber_dana'               => trim((string) ($post['sumber_dana'] ?? '')),
            'tanggal_mulai_penugasan'   => $tglMulai,
            'tanggal_selesai_penugasan' => $tglSelesai,
            'masa_penugasan_hari'       => $hari,
            'masa_penugasan'            => $masaPenugasan,
            'status_persen'             => $persen,
            'status_pekerjaan'          => $statusPekerjaan,
            'updated_by'                => (string) (session()->get('username') ?? 'admin'),
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

        $isFisik = (trim((string) ($row['lingkup_jasa'] ?? '')) === 'Fisik');
        $teksPengantar = $isFisik 
            ? 'telah melaksanakan pekerjaan konstruksi dengan data sebagai berikut:'
            : 'telah melaksanakan pekerjaan jasa konsultansi dengan data sebagai berikut:';

        $teksPenutup = $isFisik
            ? 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan pekerjaan konstruksi.'
            : 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi.';

        $tglSuratStr = ! empty($row['tanggal_surat']) ? tanggal_indonesia($row['tanggal_surat']) : tanggal_indonesia(date('Y-m-d'));
        $kotaSurat   = ! empty($row['kota_surat']) ? $row['kota_surat'] : 'Pekanbaru';
        $kotaTanggal = $kotaSurat . ', ' . $tglSuratStr;

        $nomorKontrak = trim((string) ($row['nomor_kontrak'] ?? ''));
        $tglKontrak   = ! empty($row['tanggal_kontrak']) ? tanggal_indonesia($row['tanggal_kontrak']) : '';
        if (empty($nomorKontrak) || empty($tglKontrak)) {
            $rawNoTgl = trim((string) ($row['nomor_tanggal_kontrak'] ?? ''));
            if (stripos($rawNoTgl, ' tanggal ') !== false) {
                $parts = preg_split('/ tanggal /i', $rawNoTgl, 2);
                if (empty($nomorKontrak)) $nomorKontrak = trim($parts[0] ?? '');
                if (empty($tglKontrak)) $tglKontrak = trim($parts[1] ?? '');
            } elseif (empty($nomorKontrak)) {
                $nomorKontrak = $rawNoTgl;
            }
        }
        $formattedNoTglKontrak = $nomorKontrak ?: '-';
        if (! empty($tglKontrak)) {
            $formattedNoTglKontrak .= "\n" . $tglKontrak;
        }

        $defaultJabatan = 'Pejabat Penanda Tangan Kontrak Pelaksanaan Prasarana Strategis, Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $ppkJabatan = trim((string)($row['ppk_jabatan'] ?? ''));
        if (empty($ppkJabatan) || $ppkJabatan === 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis' || $ppkJabatan === 'PPK Pelaksanaan Prasarana Strategis') {
            $ppkJabatan = $defaultJabatan;
        }

        $replacements = [
            'nomor_surat'               => $row['nomor_surat'] ?: '...............',
            'ppk_nama'                  => $row['ppk_nama'] ?: 'Nurhidayat Nugroho, S.Ars',
            'ppk_jabatan'               => $ppkJabatan,
            'ppk_satker'                => $row['ppk_satker'] ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'ppk_alamat'                => $row['ppk_alamat'] ?: 'Jl. Datuk Setia Maharaja No. 1 Pekanbaru',
            'nama_tenaga_ahli'          => $row['nama_tenaga_ahli'] ?: '-',
            'jabatan_pekerjaan'         => $row['jabatan_pekerjaan'] ?: '-',
            'nama_badan_usaha'          => $row['nama_badan_usaha'] ?: '-',
            'alamat_badan_usaha'        => $row['alamat_badan_usaha'] ?: '-',
            'teks_pengantar_pekerjaan'  => $teksPengantar,
            'nama_paket'                => $row['nama_paket'] ?: '-',
            'lingkup_jasa'              => $row['lingkup_jasa'] ?: 'Manajemen Konstruksi',
            'lokasi_pekerjaan'          => $row['lokasi_pekerjaan'] ?: '-',
            'nomor_tanggal_kontrak'     => $formattedNoTglKontrak,
            'nilai_kontrak'             => $row['nilai_kontrak'] ?: 'Rp .',
            'sumber_dana'               => $row['sumber_dana'] ?: 'APBN DIPA Satker Pelaksanaan Prasarana Strategis Riau',
            'masa_penugasan'            => $row['masa_penugasan'] ?: '-',
            'status_pekerjaan'          => $row['status_pekerjaan'] ?: 'Selesai',
            'teks_penutup_surat'        => $teksPenutup,
            'kota_tanggal_surat'        => $kotaTanggal,
            'ppk_tanda_tangan_jabatan1' => 'PPK Pelaksanaan Prasarana Strategis',
            'ppk_tanda_tangan_jabatan2' => $row['ppk_satker'] ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            'ppk_tanda_tangan_nama'     => $row['ppk_nama'] ?: 'Nurhidayat Nugroho, S. Ars',
            'ppk_tanda_tangan_nip'      => $row['ppk_nip'] ?: '199012212018021001',
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

        $docxBinary = $this->renderDocxFromTemplate($templateFile, $replacements, $kopLocalPath, $isFisik);
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

        $isFisik = (trim((string) ($row['lingkup_jasa'] ?? '')) === 'Fisik');
        $teksPengantar = $isFisik 
            ? 'telah melaksanakan pekerjaan konstruksi dengan data sebagai berikut:'
            : 'telah melaksanakan pekerjaan jasa konsultansi dengan data sebagai berikut:';

        $teksPenutup = $isFisik
            ? 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan pekerjaan konstruksi.'
            : 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi.';

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

        $html = view('admin/kontrak/surket_kinerja_ta/cetak_pdf', [
            'row'           => $row,
            'kopBase64'     => $kopBase64,
            'teksPengantar' => $teksPengantar,
            'teksPenutup'   => $teksPenutup,
            'isFisik'       => $isFisik,
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
     * Export Seluruh Dokumen PDF Sekaligus Berdasarkan Filter
     */
    public function exportPdf()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/kontrak/surket-kinerja-ta');
        if (! ($permissions['export'] ?? false)) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Anda tidak memiliki hak akses untuk mengekspor PDF.');
        }

        $filterPaketId = trim((string) ($this->request->getGet('paket_id') ?? ''));
        $filterLingkup = trim((string) ($this->request->getGet('lingkup_jasa') ?? ''));

        $items = $this->surketModel->getList(
            null,
            ($filterPaketId !== '' && $filterPaketId !== '*') ? (int) $filterPaketId : null,
            ($filterLingkup !== '' && $filterLingkup !== '*') ? $filterLingkup : null
        );

        if (empty($items)) {
            return redirect()->to(site_url('admin/kontrak/surket-kinerja-ta'))->with('error', 'Tidak ada data Surat Keterangan Kinerja TA yang cocok dengan filter untuk diekspor.');
        }

        helper('custom');

        $kopCache = [];
        $records  = [];

        foreach ($items as $row) {
            $isFisik = (trim((string) ($row['lingkup_jasa'] ?? '')) === 'Fisik');
            $teksPengantar = $isFisik 
                ? 'telah melaksanakan pekerjaan konstruksi dengan data sebagai berikut:'
                : 'telah melaksanakan pekerjaan jasa konsultansi dengan data sebagai berikut:';

            $teksPenutup = $isFisik
                ? 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan pekerjaan konstruksi.'
                : 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya, antara lain sebagai bukti pengalaman dalam proses pengadaan jasa konsultansi.';

            $kopSuratId = ! empty($row['kop_surat_id']) ? (int) $row['kop_surat_id'] : 0;
            if (! isset($kopCache[$kopSuratId])) {
                $kopLocalPath = null;
                $kopUrl = $kopSuratId > 0 ? kop_surat_url($kopSuratId) : null;
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

                $base64 = '';
                if ($kopLocalPath && file_exists($kopLocalPath)) {
                    $mime = mime_content_type($kopLocalPath) ?: 'image/png';
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($kopLocalPath));
                }
                $kopCache[$kopSuratId] = $base64;
            }

            $kota = ! empty($row['kota_surat']) ? $row['kota_surat'] : 'Pekanbaru';
            $tglStr = ! empty($row['tanggal_surat']) ? tanggal_indonesia($row['tanggal_surat']) : tanggal_indonesia(date('Y-m-d'));

            $records[] = [
                'row'           => $row,
                'kopBase64'     => $kopCache[$kopSuratId],
                'teksPengantar' => $teksPengantar,
                'teksPenutup'   => $teksPenutup,
                'kota'          => $kota,
                'tglStr'        => $tglStr,
                'isFisik'       => $isFisik,
            ];
        }

        $html = view('admin/kontrak/surket_kinerja_ta/export_all_pdf', [
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
        $filename = 'Surket_Kinerja_TA_Konsolidasi' . $filenameSuffix . '_' . date('Ymd_His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Proses template DOCX dengan keamanan karakter XML & Penggantian Kop Surat Dinamis
     */
    private function renderDocxFromTemplate(string $templateFile, array $replacements, ?string $kopImagePath = null, bool $isFisik = false): ?string
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

                $hasKopToEmbed = ($kopImagePath && file_exists($kopImagePath));
                if ($hasKopToEmbed || $isFisik) {
                    $zip = new \ZipArchive();
                    if ($zip->open($tempPath) === true) {
                        if ($hasKopToEmbed) {
                            $zip->addFile($kopImagePath, 'word/media/image1.png');
                        }
                        if ($isFisik) {
                            $xml = $zip->getFromName('word/document.xml');
                            if ($xml !== false) {
                                $xml = str_replace('KONSULTANSI KONSTRUKSI', 'KONSTRUKSI', $xml);
                                $zip->addFromString('word/document.xml', $xml);
                            }
                        }
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
                $escaped = str_replace(["\r\n", "\r", "\n"], '</w:t><w:br/><w:t>', $escaped);
                $xml = str_replace($search, $escaped, $xml);
            }

            if ($isFisik) {
                $xml = str_replace('KONSULTANSI KONSTRUKSI', 'KONSTRUKSI', $xml);
            }

            $zip->addFromString('word/document.xml', $xml);

            if ($kopImagePath && file_exists($kopImagePath)) {
                $zip->addFile($kopImagePath, 'word/media/image1.png');
            }

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
