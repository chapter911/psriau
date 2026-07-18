<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppSettingModel;
use App\Traits\SimakNumberingTrait;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Kontrak extends BaseController
{
    use SimakNumberingTrait;

    private const SHARED_SIMAK_OTP_CODE_TTL_SECONDS = 300;
    private const SHARED_SIMAK_OTP_SESSION_TTL_SECONDS = 1200;

    // Set TRUE untuk bypass OTP saat testing/smoke test
    private const SHARED_SIMAK_OTP_BYPASS = false;

    /**
     * Get app setting value by key, with optional default
     */
    private function getAppSetting(string $key, mixed $default = null): mixed
    {
        try {
            $setting = (new AppSettingModel())->first();
            if (is_array($setting) && array_key_exists($key, $setting)) {
                return $setting[$key];
            }
        } catch (\Throwable $e) {
            // Keep default on error
        }
        return $default;
    }

    /**
     * Get SIMAK max upload size in MB (0 = unlimited, default 50MB)
     */
    private function getSimakMaxUploadMb(): int
    {
        $mb = (int) $this->getAppSetting('simak_max_upload_mb', 0);
        return $mb > 0 ? $mb : 50; // Default to 50MB if not set or unlimited
    }

    /**
     * Get SIMAK max upload size in bytes
     */
    private function getSimakMaxUploadBytes(): int
    {
        return $this->getSimakMaxUploadMb() * 1024 * 1024;
    }

    public function paket()
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! $this->isKontrakTableReady()) {
            return view('admin/kontrak/paket', [
                'title' => 'Daftar Paket',
                'data' => [],
                'error' => 'Tabel kontrak belum tersedia. Jalankan migration.',
            ]);
        }

        $builder = db_connect()->table('trn_kontrak_paket p')
            ->select([
                'p.id',
                'p.nama_paket',
                'p.laporan',
                'p.kop_surat_id',
                'ks.title AS kop_surat_title',
                'ks.is_active AS kop_surat_is_active',
            ])
            ->join('kop_surat ks', 'ks.id = p.kop_surat_id', 'left');
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_paket');
        $pakets = $builder->orderBy('id', 'ASC')->get()->getResultArray();

        return view('admin/kontrak/paket', [
            'title' => 'Daftar Paket',
            'data' => $pakets,
            'can_edit' => $this->canManageKontrak(),
            'kopSuratList' => db_connect()->table('kop_surat')->orderBy('is_active', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function updatePaketKopSurat(int $paketId)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Anda tidak memiliki akses untuk mengubah kop surat paket.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        if (! $this->tableHasColumn('trn_kontrak_paket', 'kop_surat_id')) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Kolom kop_surat_id belum tersedia. Jalankan migration.');
        }

        $db = db_connect();
        $paketBuilder = $db->table('trn_kontrak_paket')->select('id');
        $this->applyNotDeletedWhere($paketBuilder, 'trn_kontrak_paket');
        $paket = $paketBuilder->where('id', $paketId)->get()->getRowArray();

        if (! is_array($paket)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak ditemukan.');
        }

        $kopSuratId = (int) $this->request->getPost('kop_surat_id');
        $kopSuratId = $kopSuratId > 0 ? $kopSuratId : null;

        if ($kopSuratId !== null) {
            $kopSurat = $db->table('kop_surat')->select('id')->where('id', $kopSuratId)->get()->getRowArray();
            if (! is_array($kopSurat)) {
                return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Kop surat tidak ditemukan.');
            }
        }

        $payload = ['kop_surat_id' => $kopSuratId];
        if ($this->tableHasColumn('trn_kontrak_paket', 'updated_by')) {
            $payload['updated_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'updated_date')) {
            $payload['updated_date'] = date('Y-m-d');
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'updated_at')) {
            $payload['updated_at'] = date('Y-m-d H:i:s');
        }

        $ok = $db->table('trn_kontrak_paket')->where('id', $paketId)->update($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Gagal memperbarui kop surat paket.');
        }

        return redirect()->to(site_url('admin/kontrak/paket'))->with('success', 'Kop surat paket berhasil diperbarui.');
    }

    public function createPaket()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Anda tidak memiliki akses untuk menambah paket.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        if ($namaPaket === '') {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Nama paket wajib diisi.');
        }

        $payload = [
            'nama_paket' => $namaPaket,
        ];

        if ($this->tableHasColumn('trn_kontrak_paket', 'laporan')) {
            $payload['laporan'] = '';
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'hasil')) {
            $payload['hasil'] = '';
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'tugas_tanggung_jawab')) {
            $payload['tugas_tanggung_jawab'] = '';
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'created_by')) {
            $payload['created_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'created_date')) {
            $payload['created_date'] = date('Y-m-d');
        }
        if ($this->tableHasColumn('trn_kontrak_paket', 'created_at')) {
            $payload['created_at'] = date('Y-m-d H:i:s');
        }

        $ok = db_connect()->table('trn_kontrak_paket')->insert($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Gagal menambahkan paket.');
        }

        return redirect()->to(site_url('admin/kontrak/paket'))->with('success', 'Paket berhasil ditambahkan.');
    }

    public function ki(int $paketId)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $db = db_connect();

        $paketSelect = ['id', 'nama_paket'];
        if ($this->tableHasColumn('trn_kontrak_paket', 'kop_surat_id')) {
            $paketSelect[] = 'kop_surat_id';
        }

        $paketBuilder = $db->table('trn_kontrak_paket')->select($paketSelect);
        $this->applyNotDeletedWhere($paketBuilder, 'trn_kontrak_paket');
        $paket = $paketBuilder->where('id', $paketId)->get()->getRowArray();

        if (! is_array($paket)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak ditemukan.');
        }

        $kiSelect = [
                'k.id',
                'k.nomor_kontrak',
                'k.tanggal_kontrak',
                'k.paket',
                'k.kode_personil',
                'k.nama',
                'k.alamat',
                'k.nik',
                'k.npwp',
                'k.jabatan',
                'k.durasi_pelaksanaan',
                'k.nomor_dipa',
                'k.tanggal_dipa',
                'k.mata_anggaran',
                'k.nomor_surat_undangan_pengadaan',
                'k.tanggal_surat_undangan_pengadaan',
                'k.nomor_surat_berita_acara_pengadaan',
                'k.tanggal_surat_berita_acara_pengadaan',
                'k.nomor_surat_penawaran',
                'k.tanggal_surat_penawaran',
                'k.nomor_undangan',
                'k.total_penawaran',
                'k.tanggal_awal',
                'k.tanggal_akhir',
                'k.tahun_anggaran',
                'k.no_sppbj',
                'k.tanggal_sppbj',
                'k.pejabat_ppk',
                'k.nip_pejabat_ppk',
                'k.kedudukan_pejabat_ppk',
                'k.nomor_surat_keputusan_menteri',
                'k.tanggal_surat_keputusan_menteri',
                'k.nomor_perubahan_keputusan_menteri',
                'k.bank_nomor_rekening',
                'k.bank_nama',
                'k.bank_atas_nama',
                'k.bank_pembayaran',
                'k.kategori',
                'k.nomor_telefon_ki',
                'k.email_ki',
                'k.nominal_kontrak',
                'k.nominal_hps',
                'k.nomor_spmk',
                'k.nomor_baphp',
                'k.nomor_surat_permohonan',
                'k.tanggal_surat_permohonan',
                'k.nama_pekerjaan',
                'k.jenis_pembayaran',
                'k.nomor_bast',
            ];

        $kiSelect[] = $this->tableHasColumn('trn_kontrak_ki', 'tanggal_spmk') ? 'k.tanggal_spmk' : 'NULL AS tanggal_spmk';
        $kiSelect[] = $this->tableHasColumn('trn_kontrak_ki', 'pendidikan') ? 'k.pendidikan' : 'NULL AS pendidikan';
        $kiSelect[] = $this->tableHasColumn('trn_kontrak_ki', 'sertifikat') ? 'k.sertifikat' : 'NULL AS sertifikat';

        $kiBuilder = $db->table('trn_kontrak_ki k')
            ->select($kiSelect)
            ->where('k.paket', (string) $paketId)
            ->orderBy('k.id', 'ASC');

        $this->applyNotDeletedWhere($kiBuilder, 'trn_kontrak_ki', 'k.deleted_at');
        $rows = $kiBuilder->get()->getResultArray();

        return view('admin/kontrak/ki', [
            'title' => 'Daftar Kontrak KI',
            'paket' => $paket,
            'data' => $rows,
            'can_edit' => $this->canManageKontrak(),
        ]);
    }

    public function createKi(int $paketId)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Anda tidak memiliki akses untuk menambah data KI.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $paket = $this->getPaketById($paketId);
        if (! is_array($paket)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak ditemukan.');
        }

        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nama = trim((string) $this->request->getPost('nama'));

        if ($nomorKontrak === '' || $nama === '') {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Nomor kontrak dan nama wajib diisi.');
        }

        $payload = [
            'paket' => (string) $paketId,
            'nomor_kontrak' => $nomorKontrak,
            'nama' => $nama,
        ];

        $textFields = [
            'kode_personil', 'alamat', 'nik', 'npwp', 'jabatan', 'durasi_pelaksanaan',
            'nomor_dipa', 'mata_anggaran', 'nomor_surat_undangan_pengadaan',
            'nomor_surat_berita_acara_pengadaan', 'nomor_surat_penawaran', 'nomor_undangan',
            'tahun_anggaran', 'no_sppbj', 'pejabat_ppk', 'nip_pejabat_ppk',
            'kedudukan_pejabat_ppk', 'nomor_surat_keputusan_menteri',
            'nomor_perubahan_keputusan_menteri', 'bank_nomor_rekening', 'bank_nama',
            'bank_atas_nama', 'bank_pembayaran', 'kategori', 'nomor_telefon_ki', 'email_ki',
            'nomor_spmk', 'nomor_baphp', 'nomor_surat_permohonan', 'nama_pekerjaan',
            'jenis_pembayaran', 'nomor_bast', 'pendidikan', 'sertifikat',
        ];

        foreach ($textFields as $field) {
            if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                $payload[$field] = trim((string) $this->request->getPost($field));
            }
        }

        $dateFields = [
            'tanggal_kontrak', 'tanggal_dipa', 'tanggal_surat_undangan_pengadaan',
            'tanggal_surat_berita_acara_pengadaan', 'tanggal_surat_penawaran',
            'tanggal_awal', 'tanggal_akhir', 'tanggal_sppbj',
            'tanggal_surat_keputusan_menteri', 'tanggal_surat_permohonan', 'tanggal_spmk',
        ];

        foreach ($dateFields as $field) {
            if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                $payload[$field] = $this->normalizeDateValue((string) $this->request->getPost($field));
            }
        }

        $numericFields = ['total_penawaran', 'nominal_kontrak', 'nominal_hps'];
        foreach ($numericFields as $field) {
            if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                $value = $this->request->getPost($field);
                $payload[$field] = $value === null || $value === '' ? 0 : (float) $value;
            }
        }

        if ($this->tableHasColumn('trn_kontrak_ki', 'created_by')) {
            $payload['created_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        }
        if ($this->tableHasColumn('trn_kontrak_ki', 'created_date')) {
            $payload['created_date'] = date('Y-m-d');
        }
        if ($this->tableHasColumn('trn_kontrak_ki', 'created_at')) {
            $payload['created_at'] = date('Y-m-d H:i:s');
        }

        $ok = db_connect()->table('trn_kontrak_ki')->insert($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Gagal menambahkan data KI.');
        }

        return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('success', 'Data KI berhasil ditambahkan.');
    }

    public function importKi(int $paketId)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Anda tidak memiliki akses untuk import data KI.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $paket = $this->getPaketById($paketId);
        if (! is_array($paket)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak ditemukan.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'File import tidak valid.');
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, ['xls', 'xlsx'], true)) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Format file harus .xls atau .xlsx.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'File Excel gagal dibaca. Pastikan format file valid (.xls/.xlsx).');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, true);
        if ($rows === []) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'File Excel kosong.');
        }

        $headerRow = array_shift($rows);
        if (! is_array($headerRow) || $headerRow === []) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Header Excel tidak ditemukan.');
        }

        $normalizeHeader = static function ($value): string {
            $header = strtolower(trim((string) $value));
            $header = str_replace(['-', '/', ' '], '_', $header);
            $header = preg_replace('/[^a-z0-9_]/', '', $header) ?? $header;
            return $header;
        };

        $headers = [];
        foreach ($headerRow as $column => $name) {
            $normalized = $normalizeHeader($name);
            if ($normalized !== '') {
                $headers[$column] = $normalized;
            }
        }

        if ($headers === []) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Header Excel tidak dikenali.');
        }

        $textFields = [
            'kode_personil', 'alamat', 'nik', 'npwp', 'jabatan', 'durasi_pelaksanaan',
            'nomor_dipa', 'mata_anggaran', 'nomor_surat_undangan_pengadaan',
            'nomor_surat_berita_acara_pengadaan', 'nomor_surat_penawaran', 'nomor_undangan',
            'tahun_anggaran', 'no_sppbj', 'pejabat_ppk', 'nip_pejabat_ppk',
            'kedudukan_pejabat_ppk', 'nomor_surat_keputusan_menteri',
            'nomor_perubahan_keputusan_menteri', 'bank_nomor_rekening', 'bank_nama',
            'bank_atas_nama', 'bank_pembayaran', 'kategori', 'nomor_telefon_ki', 'email_ki',
            'nomor_spmk', 'nomor_baphp', 'nomor_surat_permohonan', 'nama_pekerjaan',
            'jenis_pembayaran', 'nomor_bast', 'pendidikan', 'sertifikat',
        ];
        $dateFields = [
            'tanggal_kontrak', 'tanggal_dipa', 'tanggal_surat_undangan_pengadaan',
            'tanggal_surat_berita_acara_pengadaan', 'tanggal_surat_penawaran',
            'tanggal_awal', 'tanggal_akhir', 'tanggal_sppbj',
            'tanggal_surat_keputusan_menteri', 'tanggal_surat_permohonan', 'tanggal_spmk',
        ];
        $numericFields = ['total_penawaran', 'nominal_kontrak', 'nominal_hps'];

        $db = db_connect();
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $skipped++;
                continue;
            }

            $rowData = [];
            foreach ($headers as $column => $headerName) {
                $rowData[$headerName] = trim((string) ($row[$column] ?? ''));
            }

            $nomorKontrak = trim((string) ($rowData['nomor_kontrak'] ?? ''));
            $nama = trim((string) ($rowData['nama'] ?? ''));
            if ($nomorKontrak === '' || $nama === '') {
                $skipped++;
                continue;
            }

            $payload = [
                'paket' => (string) $paketId,
                'nomor_kontrak' => $nomorKontrak,
                'nama' => $nama,
            ];

            foreach ($textFields as $field) {
                if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                    $payload[$field] = trim((string) ($rowData[$field] ?? ''));
                }
            }

            foreach ($dateFields as $field) {
                if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                    $payload[$field] = $this->normalizeDateValue((string) ($rowData[$field] ?? ''));
                }
            }

            foreach ($numericFields as $field) {
                if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                    $value = trim((string) ($rowData[$field] ?? ''));
                    $payload[$field] = $value === '' ? 0 : (float) $value;
                }
            }

            $existingBuilder = $db->table('trn_kontrak_ki')
                ->where('paket', (string) $paketId)
                ->where('nomor_kontrak', $nomorKontrak);
            $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_ki');
            $existing = $existingBuilder->get()->getRowArray();

            if (is_array($existing)) {
                if ($this->tableHasColumn('trn_kontrak_ki', 'updated_by')) {
                    $payload['updated_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
                }
                if ($this->tableHasColumn('trn_kontrak_ki', 'updated_date')) {
                    $payload['updated_date'] = date('Y-m-d');
                }
                if ($this->tableHasColumn('trn_kontrak_ki', 'updated_at')) {
                    $payload['updated_at'] = date('Y-m-d H:i:s');
                }

                if ($db->table('trn_kontrak_ki')->where('id', (int) $existing['id'])->update($payload)) {
                    $updated++;
                } else {
                    $skipped++;
                }
                continue;
            }

            if ($this->tableHasColumn('trn_kontrak_ki', 'created_by')) {
                $payload['created_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
            }
            if ($this->tableHasColumn('trn_kontrak_ki', 'created_date')) {
                $payload['created_date'] = date('Y-m-d');
            }
            if ($this->tableHasColumn('trn_kontrak_ki', 'created_at')) {
                $payload['created_at'] = date('Y-m-d H:i:s');
            }

            if ($db->table('trn_kontrak_ki')->insert($payload)) {
                $inserted++;
            } else {
                $skipped++;
            }
        }

        if ($inserted === 0 && $updated === 0) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Tidak ada data yang diproses. Pastikan kolom minimal: nomor_kontrak, nama.');
        }

        $message = 'Import selesai. Insert: ' . $inserted . ', Update: ' . $updated . ', Dilewati: ' . $skipped . '.';
        return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('success', $message);
    }

    public function exportKi(int $paketId)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $db = db_connect();
        $paketBuilder = $db->table('trn_kontrak_paket')->select('id, nama_paket');
        $this->applyNotDeletedWhere($paketBuilder, 'trn_kontrak_paket');
        $paket = $paketBuilder->where('id', $paketId)->get()->getRowArray();

        if (! is_array($paket)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak ditemukan.');
        }

        $kiBuilder = $db->table('trn_kontrak_ki')
            ->select('*')
            ->where('paket', (string) $paketId)
            ->orderBy('id', 'ASC');
        $this->applyNotDeletedWhere($kiBuilder, 'trn_kontrak_ki');
        $rows = $kiBuilder->get()->getResultArray();

        $filename = 'format_kontrak_ki_paket_' . $paketId . '_' . date('Ymd_His') . '.xlsx';

        $headers = [
            'nomor_kontrak', 'tanggal_kontrak', 'kode_personil', 'nama', 'alamat', 'nik', 'npwp',
            'jabatan', 'durasi_pelaksanaan', 'nomor_dipa', 'tanggal_dipa', 'mata_anggaran',
            'nomor_surat_undangan_pengadaan', 'tanggal_surat_undangan_pengadaan',
            'nomor_surat_berita_acara_pengadaan', 'tanggal_surat_berita_acara_pengadaan',
            'nomor_surat_penawaran', 'tanggal_surat_penawaran', 'nomor_undangan', 'total_penawaran',
            'tanggal_awal', 'tanggal_akhir', 'tahun_anggaran', 'no_sppbj', 'tanggal_sppbj', 'pejabat_ppk',
            'nip_pejabat_ppk', 'kedudukan_pejabat_ppk', 'nomor_surat_keputusan_menteri',
            'tanggal_surat_keputusan_menteri', 'nomor_perubahan_keputusan_menteri', 'bank_nomor_rekening',
            'bank_nama', 'bank_atas_nama', 'bank_pembayaran', 'kategori', 'nomor_telefon_ki', 'email_ki',
            'nominal_kontrak', 'nominal_hps', 'nomor_spmk', 'nomor_baphp', 'nomor_surat_permohonan',
            'tanggal_surat_permohonan', 'nama_pekerjaan', 'jenis_pembayaran', 'nomor_bast',
            'pendidikan', 'sertifikat', 'tanggal_spmk',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = $row[$header] ?? '';
            }
            $sheet->fromArray($line, null, 'A' . $rowNumber);
            $rowNumber++;
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary === false ? '' : $binary);
    }

    public function updateKi(int $paketId, int $kiId)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Anda tidak memiliki akses untuk mengubah data KI.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $db = db_connect();
        $existing = $db->table('trn_kontrak_ki')->where('id', $kiId)->where('paket', (string) $paketId)->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Data KI tidak ditemukan.');
        }

        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nama = trim((string) $this->request->getPost('nama'));

        if ($nomorKontrak === '' || $nama === '') {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Nomor kontrak dan nama wajib diisi.');
        }

        $payload = [
            'nomor_kontrak' => $nomorKontrak,
            'nama' => $nama,
        ];

        $textFields = [
            'kode_personil', 'alamat', 'nik', 'npwp', 'jabatan', 'durasi_pelaksanaan',
            'nomor_dipa', 'mata_anggaran', 'nomor_surat_undangan_pengadaan',
            'nomor_surat_berita_acara_pengadaan', 'nomor_surat_penawaran', 'nomor_undangan',
            'tahun_anggaran', 'no_sppbj', 'pejabat_ppk', 'nip_pejabat_ppk',
            'kedudukan_pejabat_ppk', 'nomor_surat_keputusan_menteri',
            'nomor_perubahan_keputusan_menteri', 'bank_nomor_rekening', 'bank_nama',
            'bank_atas_nama', 'bank_pembayaran', 'kategori', 'nomor_telefon_ki', 'email_ki',
            'nomor_spmk', 'nomor_baphp', 'nomor_surat_permohonan', 'nama_pekerjaan',
            'jenis_pembayaran', 'nomor_bast', 'pendidikan', 'sertifikat',
        ];

        foreach ($textFields as $field) {
            if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                $payload[$field] = trim((string) $this->request->getPost($field));
            }
        }

        $dateFields = [
            'tanggal_kontrak', 'tanggal_dipa', 'tanggal_surat_undangan_pengadaan',
            'tanggal_surat_berita_acara_pengadaan', 'tanggal_surat_penawaran',
            'tanggal_awal', 'tanggal_akhir', 'tanggal_sppbj',
            'tanggal_surat_keputusan_menteri', 'tanggal_surat_permohonan', 'tanggal_spmk',
        ];

        foreach ($dateFields as $field) {
            if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                $payload[$field] = $this->normalizeDateValue((string) $this->request->getPost($field));
            }
        }

        $numericFields = ['total_penawaran', 'nominal_kontrak', 'nominal_hps'];
        foreach ($numericFields as $field) {
            if ($this->tableHasColumn('trn_kontrak_ki', $field)) {
                $value = $this->request->getPost($field);
                $payload[$field] = $value === null || $value === '' ? 0 : (float) $value;
            }
        }

        if ($this->tableHasColumn('trn_kontrak_ki', 'updated_by')) {
            $payload['updated_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        }
        if ($this->tableHasColumn('trn_kontrak_ki', 'updated_date')) {
            $payload['updated_date'] = date('Y-m-d');
        }
        if ($this->tableHasColumn('trn_kontrak_ki', 'updated_at')) {
            $payload['updated_at'] = date('Y-m-d H:i:s');
        }

        $ok = $db->table('trn_kontrak_ki')->where('id', $kiId)->update($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('error', 'Gagal mengubah data KI.');
        }

        return redirect()->to(site_url('admin/kontrak/ki/' . $paketId))->with('success', 'Data KI berhasil diubah.');
    }

    public function updateSyaratUmum()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Anda tidak memiliki akses untuk mengubah syarat umum.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $paketId = (int) $this->request->getPost('paket_id');
        if ($paketId <= 0) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak valid.');
        }

        $db = db_connect();
        $paketBuilder = $db->table('trn_kontrak_paket')->select('id');
        $this->applyNotDeletedWhere($paketBuilder, 'trn_kontrak_paket');
        $exists = $paketBuilder->where('id', $paketId)->get()->getRowArray();

        if (! is_array($exists)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Paket tidak ditemukan.');
        }

        $db->table('trn_kontrak_paket')
            ->where('id', $paketId)
            ->update([
                'laporan' => (string) $this->request->getPost('laporan'),
                'hasil' => (string) $this->request->getPost('hasil'),
                'tugas_tanggung_jawab' => (string) $this->request->getPost('tugas_tanggung_jawab'),
            ]);

        return redirect()->to(site_url('admin/kontrak/paket'))->with('success', 'Syarat umum berhasil diperbarui.');
    }

    public function getJabaranSyaratUmum()
    {
        if (! $this->canViewKontrak()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $paketId = (int) $this->request->getGet('paket_id');
        if ($paketId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid paket_id']);
        }

        if (! $this->isKontrakTableReady()) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Tables not ready']);
        }

        $db = db_connect();

        $jabatanBuilder = $db->table('trn_kontrak_ki k')
            ->distinct()
            ->select('k.jabatan')
            ->where('k.paket', (string) $paketId);
        $this->applyNotDeletedWhere($jabatanBuilder, 'trn_kontrak_ki', 'k.deleted_at');
        $jabatans = $jabatanBuilder->orderBy('k.jabatan', 'ASC')->get()->getResultArray();

        return $this->response->setJSON([
            'jabatan' => $jabatans,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function getSyaratUmumByPaketId()
    {
        if (! $this->canViewKontrak()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $paketId = (int) $this->request->getPost('paket_id');
        $jabatan = (string) $this->request->getPost('jabatan');

        if ($paketId <= 0 || empty($jabatan)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid parameters']);
        }

        if (! $this->isKontrakTableReady()) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Tables not ready']);
        }

        $db = db_connect();

        // Try to get from syarat umum table
        if ($db->tableExists('trn_syarat_umum_kontrak_ki')) {
            $syarat = $db->table('trn_syarat_umum_kontrak_ki')
                ->where('paket_id', $paketId)
                ->where('jabatan_name', $jabatan)
                ->get()
                ->getRowArray();

            if (is_array($syarat)) {
                $viewData = [
                    'laporan' => (string) ($syarat['laporan_modal'] ?? $syarat['laporan'] ?? ''),
                    'hasil' => (string) ($syarat['hasil_modal'] ?? $syarat['hasil'] ?? ''),
                    'tugas_tanggung_jawab' => (string) ($syarat['tugas_tanggung_jawab_modal'] ?? $syarat['tugas_tanggung_jawab'] ?? ''),
                ];

                return $this->response->setJSON([
                    'paket' => $viewData,
                    'csrfHash' => csrf_hash(),
                ]);
            }
        }

        // Return empty if not found
        return $this->response->setJSON([
            'paket' => null,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function saveSyaratUmumByJabatan()
    {
        if (! $this->canManageKontrak()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        if (! $this->isKontrakTableReady()) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Tables not ready']);
        }

        $paketId = (int) $this->request->getPost('paket_id');
        $jabatan = (string) $this->request->getPost('jabatan');

        if ($paketId <= 0 || empty($jabatan)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid parameters']);
        }

        $db = db_connect();

        if (! $db->tableExists('trn_syarat_umum_kontrak_ki')) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Syarat umum table not ready']);
        }

        $laporanModal = trim((string) $this->request->getPost('laporan_modal'));
        $hasilModal = trim((string) $this->request->getPost('hasil_modal'));
        $tugasModal = trim((string) $this->request->getPost('tugas_tanggung_jawab_modal'));

        $data = [
            'paket_id' => $paketId,
            'jabatan_name' => $jabatan,
            'laporan' => $this->sanitizeRichText($this->request->getPost('laporan')),
            'hasil' => $this->sanitizeRichText($this->request->getPost('hasil')),
            'tugas_tanggung_jawab' => $this->sanitizeRichText($this->request->getPost('tugas_tanggung_jawab')),
            'laporan_modal' => $laporanModal !== '' ? $laporanModal : (string) $this->request->getPost('laporan'),
            'hasil_modal' => $hasilModal !== '' ? $hasilModal : (string) $this->request->getPost('hasil'),
            'tugas_tanggung_jawab_modal' => $tugasModal !== '' ? $tugasModal : (string) $this->request->getPost('tugas_tanggung_jawab'),
        ];

        $exists = $db->table('trn_syarat_umum_kontrak_ki')
            ->where('paket_id', $paketId)
            ->where('jabatan_name', $jabatan)
            ->get()
            ->getRowArray();

        if (is_array($exists)) {
            $db->table('trn_syarat_umum_kontrak_ki')
                ->where('paket_id', $paketId)
                ->where('jabatan_name', $jabatan)
                ->update($data);
        } else {
            $db->table('trn_syarat_umum_kontrak_ki')->insert($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Syarat umum berhasil disimpan',
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function simak()
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return view('admin/kontrak/simak_konstruksi', [
                'title' => 'SIMAK Kontrak',
                'data' => [],
                'addOnsBySimakId' => [],
                'pegawaiOptions' => $this->getSimakPegawaiOptions(),
                'can_edit' => $this->canViewKontrak(),
                'can_import' => $this->canManageKontrak(),
                'error' => 'Tabel SIMAK belum tersedia. Jalankan migration.',
            ]);
        }

        $builder = $db->table('trn_kontrak_simak s')
            ->select('s.*, COALESCE(soa.nilai_add_on, 0) AS nilai_add_on, (s.nilai_kontrak + COALESCE(soa.nilai_add_on, 0)) AS total_kontrak')
            ->orderBy('s.id', 'DESC');

        if ($db->tableExists('trn_kontrak_simak_add_on')) {
            $summaryBuilder = $db->table('trn_kontrak_simak_add_on')
                ->select('simak_id, SUM(nilai_add_on) AS nilai_add_on')
                ->groupBy('simak_id');
            $this->applyNotDeletedWhere($summaryBuilder, 'trn_kontrak_simak_add_on');

            $builder->join('(' . $summaryBuilder->getCompiledSelect() . ') soa', 'soa.simak_id = s.id', 'left', false);
        }

        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak', 's.deleted_at');

        $rows = $builder->get()->getResultArray();

        $simakIds = array_values(array_filter(array_map(static function (array $row): int {
            return (int) ($row['id'] ?? 0);
        }, $rows), static function (int $id): bool {
            return $id > 0;
        }));

        $kelengkapanBySimakId = $this->getSimakAdministrasiKelengkapanBySimakId($simakIds);
        $shareUrlBySimakId = $this->getSimakSharePublicUrlBySimakId($simakIds);

        foreach ($rows as &$row) {
            $simakId = (int) ($row['id'] ?? 0);
            $summary = $kelengkapanBySimakId[$simakId] ?? [];
            $row['kelengkapan_dokumen_administrasi_persen'] = (float) ($summary['lengkap_persen'] ?? 0);
            $row['kelengkapan_dokumen_lengkap_persen'] = (float) ($summary['lengkap_persen'] ?? 0);
            $row['kelengkapan_dokumen_belum_sesuai_persen'] = (float) ($summary['belum_sesuai_persen'] ?? 0);
            $row['kelengkapan_dokumen_belum_verifikasi_persen'] = (float) ($summary['belum_verifikasi_persen'] ?? 0);
            $row['kelengkapan_dokumen_belum_ada_persen'] = (float) ($summary['belum_ada_persen'] ?? 0);
            $row['share_public_url'] = (string) ($shareUrlBySimakId[$simakId] ?? '');
        }
        unset($row);

        return view('admin/kontrak/simak_konstruksi', [
            'title' => 'SIMAK Kontrak',
            'data' => $rows,
            'addOnsBySimakId' => $this->getSimakAddOnsBySimakId(),
            'pegawaiOptions' => $this->getSimakPegawaiOptions(),
            'can_edit' => $this->canViewKontrak(),
            'can_import' => $this->canManageKontrak(),
            'can_share' => $this->canManageKontrak(),
        ]);
    }

    public function simakKonstruksi()
    {
        return $this->simakByType('konstruksi');
    }

    public function simakKonsultasi()
    {
        return $this->simakByType('konsultasi');
    }

    private function simakByType(string $type)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        
        // Determine table and view based on type
        $tableMain = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';
        $tableAddOn = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_add_on' : 'trn_kontrak_simak_add_on';
        $tableShare = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_share' : 'trn_kontrak_simak_share';
        $view = ($type === 'konsultasi') ? 'admin/kontrak/simak_konsultasi' : 'admin/kontrak/simak_konstruksi';
        
        if (! $db->tableExists($tableMain)) {
            return view($view, [
                'title' => 'SIMAK Kontrak - ' . ucfirst($type),
                'data' => [],
                'addOnsBySimakId' => [],
                'pegawaiOptions' => $this->getSimakPegawaiOptions(),
                'paketOptions' => $this->getSimakPaketOptions(),
                'can_edit' => $this->canViewKontrak(),
                'can_import' => $this->canManageKontrak(),
                'error' => 'Tabel SIMAK belum tersedia. Jalankan migration.',
            ]);
        }

        $builder = $db->table($tableMain . ' s')
            ->select('s.*, COALESCE(soa.nilai_add_on, 0) AS nilai_add_on, (s.nilai_kontrak + COALESCE(soa.nilai_add_on, 0)) AS total_kontrak, mp.nama_paket AS paket_nama, mp.singkatan_paket AS paket_singkatan')
            ->orderBy('s.paket_id', 'ASC');

        if ($db->tableExists($tableAddOn)) {
            $summaryBuilder = $db->table($tableAddOn)
                ->select('simak_id, SUM(nilai_add_on) AS nilai_add_on')
                ->groupBy('simak_id');
            $this->applyNotDeletedWhere($summaryBuilder, $tableAddOn);

            $builder->join('(' . $summaryBuilder->getCompiledSelect() . ') soa', 'soa.simak_id = s.id', 'left', false);
        }

        // Join with mst_paket to get paket name
        if ($db->tableExists('mst_paket')) {
            $builder->join('mst_paket mp', 'mp.id = s.paket_id', 'left');
        }

        $this->applyNotDeletedWhere($builder, $tableMain, 's.deleted_at');

        $rows = $builder->get()->getResultArray();

        $simakIds = array_values(array_filter(array_map(static function (array $row): int {
            return (int) ($row['id'] ?? 0);
        }, $rows), static function (int $id): bool {
            return $id > 0;
        }));

        $kelengkapanBySimakId = $this->getSimakAdministrasiKelengkapanBySimakId($simakIds, $type);
        $shareUrlBySimakId = $this->getSimakSharePublicUrlBySimakId($simakIds, $type);
        $gdriveStatusBySimakId = $this->getSimakGdriveStatusBySimakId($simakIds, $type);

        foreach ($rows as &$row) {
            $simakId = (int) ($row['id'] ?? 0);
            $summary = $kelengkapanBySimakId[$simakId] ?? [];
            $row['kelengkapan_dokumen_administrasi_persen'] = (float) ($summary['lengkap_persen'] ?? 0);
            $row['kelengkapan_dokumen_lengkap_persen'] = (float) ($summary['lengkap_persen'] ?? 0);
            $row['kelengkapan_dokumen_belum_sesuai_persen'] = (float) ($summary['belum_sesuai_persen'] ?? 0);
            $row['kelengkapan_dokumen_belum_verifikasi_persen'] = (float) ($summary['belum_verifikasi_persen'] ?? 0);
            $row['kelengkapan_dokumen_belum_ada_persen'] = (float) ($summary['belum_ada_persen'] ?? 0);
            $row['share_public_url'] = (string) ($shareUrlBySimakId[$simakId] ?? '');
            $row['has_gdrive_documents'] = ($gdriveStatusBySimakId[$simakId] ?? false) === true ? '1' : '0';
        }
        unset($row);

        $titleMap = [
            'konstruksi' => 'SIMAK Kontrak - Konstruksi',
            'konsultasi' => 'SIMAK Kontrak - Konsultasi',
        ];

        return view($view, [
            'title' => $titleMap[$type] ?? 'SIMAK Kontrak',
            'data' => $rows,
            'addOnsBySimakId' => $this->getSimakAddOnsBySimakId($type),
            'pegawaiOptions' => $this->getSimakPegawaiOptions(),
            'paketOptions' => $this->getSimakPaketOptions(),
            'can_edit' => $this->canViewKontrak(),
            'can_import' => $this->canManageKontrak(),
            'can_share' => $this->canManageKontrak(),
        ]);
    }

    public function exportSimakKonstruksiExcel()
    {
        return $this->exportSimakExcel('konstruksi');
    }

    public function exportSimakKonsultasiExcel()
    {
        return $this->exportSimakExcel('konsultasi');
    }

    public function exportSimakKonstruksiHtml()
    {
        return $this->exportSimakHtml('konstruksi');
    }

    public function exportSimakKonsultasiHtml()
    {
        return $this->exportSimakHtml('konsultasi');
    }

    private function exportSimakExcel(string $type)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        $tableMain = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';
        $tableAddOn = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_add_on' : 'trn_kontrak_simak_add_on';

        if (! $db->tableExists($tableMain)) {
            return redirect()->back()->with('error', 'Tabel SIMAK belum tersedia.');
        }

        $builder = $db->table($tableMain . ' s')
            ->select('s.*, COALESCE(soa.nilai_add_on, 0) AS nilai_add_on, (s.nilai_kontrak + COALESCE(soa.nilai_add_on, 0)) AS total_kontrak')
            ->orderBy('s.id', 'DESC');

        if ($db->tableExists($tableAddOn)) {
            $summaryBuilder = $db->table($tableAddOn)
                ->select('simak_id, SUM(nilai_add_on) AS nilai_add_on')
                ->groupBy('simak_id');
            $this->applyNotDeletedWhere($summaryBuilder, $tableAddOn);
            $builder->join('(' . $summaryBuilder->getCompiledSelect() . ') soa', 'soa.simak_id = s.id', 'left', false);
        }

        $this->applyNotDeletedWhere($builder, $tableMain, 's.deleted_at');
        $rows = $builder->get()->getResultArray();

        // Get kelengkapan data
        $simakIds = array_values(array_filter(array_map(static function (array $row): int {
            return (int) ($row['id'] ?? 0);
        }, $rows), static function (int $id): bool {
            return $id > 0;
        }));

        $kelengkapanBySimakId = $this->getSimakAdministrasiKelengkapanBySimakId($simakIds, $type);

        // Build spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SIMAK ' . ucfirst($type));

        // Headers
        $headers = [
            'No',
            'Nomor Kontrak',
            'Nama Paket',
            'Tahun Anggaran',
            'PPK Nama',
            'PPK NIP',
            'Penyedia',
            'Nilai Kontrak (Rp)',
            'Nilai Add On (Rp)',
            'Total Kontrak (Rp)',
            'Lengkap (%)',
            'Belum Sesuai (%)',
            'Belum Verifikasi (%)',
            'Belum Ada (%)',
            'Email Responden 1',
            'Email Responden 2',
            'Share Link',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);

        // Data rows
        $rowNum = 2;
        foreach ($rows as $item) {
            $simakId = (int) ($item['id'] ?? 0);
            $summary = $kelengkapanBySimakId[$simakId] ?? [];

            $shareUrl = site_url('simak/share/' . ($item['share_token'] ?? ''));

            $sheet->setCellValue('A' . $rowNum, $rowNum - 1);
            $sheet->setCellValue('B' . $rowNum, $item['nomor_kontrak'] ?? '');
            $sheet->setCellValue('C' . $rowNum, $item['nama_paket'] ?? '');
            $sheet->setCellValue('D' . $rowNum, $item['tahun_anggaran'] ?? '');
            $sheet->setCellValue('E' . $rowNum, $item['ppk_nama'] ?? '');
            $sheet->setCellValue('F' . $rowNum, $item['ppk_nip'] ?? '');
            $sheet->setCellValue('G' . $rowNum, $item['penyedia'] ?? '');
            $sheet->setCellValue('H' . $rowNum, (float) ($item['nilai_kontrak'] ?? 0));
            $sheet->setCellValue('I' . $rowNum, (float) ($item['nilai_add_on'] ?? 0));
            $sheet->setCellValue('J' . $rowNum, (float) ($item['total_kontrak'] ?? 0));
            $sheet->setCellValue('K' . $rowNum, (float) ($summary['lengkap_persen'] ?? 0));
            $sheet->setCellValue('L' . $rowNum, (float) ($summary['belum_sesuai_persen'] ?? 0));
            $sheet->setCellValue('M' . $rowNum, (float) ($summary['belum_verifikasi_persen'] ?? 0));
            $sheet->setCellValue('N' . $rowNum, (float) ($summary['belum_ada_persen'] ?? 0));
            $sheet->setCellValue('O' . $rowNum, $item['email_responden_1'] ?? '');
            $sheet->setCellValue('P' . $rowNum, $item['email_responden_2'] ?? '');
            $sheet->setCellValue('Q' . $rowNum, $shareUrl);

            $rowNum++;
        }

        // Auto size columns
        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Number format for currency
        $sheet->getStyle('H2:J' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K2:N' . $rowNum)->getNumberFormat()->setFormatCode('0.00%');

        $filename = 'simak_' . $type . '_export_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary === false ? '' : $binary);
    }

    private function exportSimakHtml(string $type)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        $tableMain = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';
        $tableAddOn = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_add_on' : 'trn_kontrak_simak_add_on';

        if (! $db->tableExists($tableMain)) {
            return redirect()->back()->with('error', 'Tabel SIMAK belum tersedia.');
        }

        $builder = $db->table($tableMain . ' s')
            ->select('s.*, COALESCE(soa.nilai_add_on, 0) AS nilai_add_on, (s.nilai_kontrak + COALESCE(soa.nilai_add_on, 0)) AS total_kontrak')
            ->orderBy('s.id', 'DESC');

        if ($db->tableExists($tableAddOn)) {
            $summaryBuilder = $db->table($tableAddOn)
                ->select('simak_id, SUM(nilai_add_on) AS nilai_add_on')
                ->groupBy('simak_id');
            $this->applyNotDeletedWhere($summaryBuilder, $tableAddOn);
            $builder->join('(' . $summaryBuilder->getCompiledSelect() . ') soa', 'soa.simak_id = s.id', 'left', false);
        }

        $this->applyNotDeletedWhere($builder, $tableMain, 's.deleted_at');
        $rows = $builder->get()->getResultArray();

        // Get kelengkapan data
        $simakIds = array_values(array_filter(array_map(static function (array $row): int {
            return (int) ($row['id'] ?? 0);
        }, $rows), static function (int $id): bool {
            return $id > 0;
        }));

        $kelengkapanBySimakId = $this->getSimakAdministrasiKelengkapanBySimakId($simakIds, $type);

        // Build HTML
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAK ' . ucfirst($type) . ' - Export ' . date('d/m/Y') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header-info { margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f5f5f5; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; color: white; }
        .footer { margin-top: 30px; text-align: center; color: #999; font-size: 11px; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>SIMAK ' . ucfirst($type) . '</h1>
    <div class="header-info">
        <p>Tanggal Export: ' . date('d/m/Y H:i:s') . ' | Total Data: ' . count($rows) . ' item</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Kontrak</th>
                <th>Nama Paket</th>
                <th>Tahun</th>
                <th>PPK</th>
                <th>Penyedia</th>
                <th class="text-right">Nilai Kontrak</th>
                <th class="text-right">Add On</th>
                <th class="text-right">Total</th>
                <th class="text-center">Lengkap</th>
                <th class="text-center">Belum Sesuai</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>';

        $no = 1;
        foreach ($rows as $item) {
            $simakId = (int) ($item['id'] ?? 0);
            $summary = $kelengkapanBySimakId[$simakId] ?? [];

            $lengkap = (float) ($summary['lengkap_persen'] ?? 0);
            $belumSesuai = (float) ($summary['belum_sesuai_persen'] ?? 0);

            // Determine status badge
            if ($lengkap >= 100) {
                $statusBadge = '<span class="badge badge-success">Lengkap</span>';
            } elseif ($belumSesuai > 0) {
                $statusBadge = '<span class="badge badge-warning">Belum Sesuai</span>';
            } else {
                $statusBadge = '<span class="badge badge-danger">Belum</span>';
            }

            $html .= '
            <tr>
                <td class="text-center">' . $no++ . '</td>
                <td>' . htmlspecialchars($item['nomor_kontrak'] ?? '-') . '</td>
                <td>' . htmlspecialchars($item['nama_paket'] ?? '-') . '</td>
                <td>' . htmlspecialchars($item['tahun_anggaran'] ?? '-') . '</td>
                <td>' . htmlspecialchars($item['ppk_nama'] ?? '-') . '</td>
                <td>' . htmlspecialchars($item['penyedia'] ?? '-') . '</td>
                <td class="text-right">' . number_format((float) ($item['nilai_kontrak'] ?? 0), 0, ',', '.') . '</td>
                <td class="text-right">' . number_format((float) ($item['nilai_add_on'] ?? 0), 0, ',', '.') . '</td>
                <td class="text-right"><strong>' . number_format((float) ($item['total_kontrak'] ?? 0), 0, ',', '.') . '</strong></td>
                <td class="text-center">' . number_format($lengkap, 2, ',', '.') . '%</td>
                <td class="text-center">' . number_format($belumSesuai, 2, ',', '.') . '%</td>
                <td class="text-center">' . $statusBadge . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>
    <div class="footer">
        Generated by SIMAK System | Export Date: ' . date('d/m/Y H:i:s') . '
    </div>
</body>
</html>';

        $filename = 'simak_' . $type . '_export_' . date('Y-m-d_His') . '.html';

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($html);
    }

    public function exportSimakKonstruksiDetailExcel(int $id)
    {
        return $this->exportSimakDetailExcel($id, 'konstruksi');
    }

    public function exportSimakKonsultasiDetailExcel(int $id)
    {
        return $this->exportSimakDetailExcel($id, 'konsultasi');
    }

    public function exportSimakKonstruksiDetailHtml(int $id)
    {
        return $this->exportSimakDetailHtml($id, 'konstruksi');
    }

    public function exportSimakKonsultasiDetailHtml(int $id)
    {
        return $this->exportSimakDetailHtml($id, 'konsultasi');
    }

    public function downloadSimakKonstruksiZip(int $id)
    {
        return $this->downloadSimakZip($id, 'konstruksi');
    }

    public function downloadSimakKonsultasiZip(int $id)
    {
        return $this->downloadSimakZip($id, 'konsultasi');
    }

    private function downloadSimakZip(int $id, string $type)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        $tableMain = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';
        $tableVerifDok = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen' : 'trn_kontrak_simak_verifikasi_dokumen';

        if (! $db->tableExists($tableMain) || ! $db->tableExists($tableVerifDok)) {
            return redirect()->back()->with('error', 'Tabel SIMAK atau tabel dokumen verifikasi belum tersedia.');
        }

        $builder = $db->table($tableMain . ' s');
        $this->applyNotDeletedWhere($builder, $tableMain);
        $simak = $builder->where('s.id', $id)->get()->getRowArray();
        if (! is_array($simak)) {
            return redirect()->back()->with('error', 'Data SIMAK tidak ditemukan.');
        }

        // collect documents
        $dokumenRows = $db->table($tableVerifDok)
            ->select('id, row_no, file_original_name, file_relative_path, file_mime, tipe_dokumen')
            ->where('simak_id', $id)
            ->orderBy('row_no', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        if ($dokumenRows === []) {
            return redirect()->back()->with('error', 'Tidak ada dokumen yang dapat diunduh.');
        }

        if (! class_exists('ZipArchive')) {
            return redirect()->back()->with('error', 'Ekstensi ZIP tidak tersedia di server.');
        }

        $downloadDir = WRITEPATH . 'downloads';
        if (! is_dir($downloadDir) && ! @mkdir($downloadDir, 0775, true) && ! is_dir($downloadDir)) {
            return redirect()->back()->with('error', 'Folder sementara unduhan tidak dapat dibuat.');
        }

        try {
            $randomSuffix = bin2hex(random_bytes(4));
        } catch (\Throwable $e) {
            $randomSuffix = (string) mt_rand(1000, 9999);
        }

        $zipTempPath = $downloadDir . DIRECTORY_SEPARATOR . 'simak_' . $type . '_' . $id . '_' . date('YmdHis') . '_' . $randomSuffix . '.zip';

        $zip = new \ZipArchive();
        $openResult = $zip->open($zipTempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        // helper to sanitize names for file system
        $sanitize = function (string $input) {
            $tmp = trim($input);
            // replace slashes and invalid chars with dashes
            $tmp = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]+/', '-', $tmp);
            // remove control chars
            $tmp = preg_replace('/[\x00-\x1F\x7F]/u', '', $tmp);
            // collapse multiple non-alnum to single dash
            $tmp = preg_replace('/[^A-Za-z0-9\-_. ]+/', '-', $tmp);
            $tmp = preg_replace('/\s+/', ' ', $tmp);
            $tmp = trim($tmp);
            $tmp = trim($tmp, '.- _');
            if ($tmp === '') {
                return 'file';
            }
            // limit length
            return mb_substr($tmp, 0, 120);
        };

        // Prepare template mapping: row_no -> section_title (tahapan) and display_no/uraian
        $templateItems = $this->getSimakTemplateItems($type, true);
        $rowToSection = [];
        $rowToDisplay = [];
        $rowToUraian = [];
        foreach ($templateItems as $ti) {
            $rno = (int) ($ti['row_no'] ?? 0);
            $rowToDisplay[$rno] = trim((string) ($ti['display_no_auto'] ?? $ti['display_no'] ?? ''));
            $rowToUraian[$rno] = trim((string) ($ti['uraian'] ?? ''));
            $rowToSection[$rno] = trim((string) ($ti['section_title'] ?? '')); // section_title holds tahapan when available
        }

        $usedNames = [];
        $added = 0;

        foreach ($dokumenRows as $doc) {
            $relative = trim((string) ($doc['file_relative_path'] ?? ''));
            $original = trim((string) ($doc['file_original_name'] ?? 'dokumen'));
            $rowNo = (int) ($doc['row_no'] ?? 0);

            $displayNo = $rowToDisplay[$rowNo] ?? (string) $rowNo;
            $uraian = $rowToUraian[$rowNo] ?? '';
            $sectionTitle = $rowToSection[$rowNo] ?? '';

            $sectionFolder = $sectionTitle !== '' ? $sanitize($sectionTitle) : $sanitize('Tahapan Lain');
            $itemFolder = $sanitize($displayNo !== '' ? $displayNo : (string) $rowNo) . ' - ' . $sanitize($uraian !== '' ? $uraian : 'uraian');
            $folderPath = $sectionFolder . '/' . $itemFolder;

            $fileBase = $sanitize($original);
            $ext = '';

            // handle google drive links by adding a text file containing the link
            if ($this->isAllowedGoogleDriveUrl($relative)) {
                $linkContent = $relative . "\n";
                $entryName = $folderPath . '/' . $fileBase . '.url.txt';
                $counter = 1;
                $uniqueEntry = $entryName;
                while (isset($usedNames[$uniqueEntry])) {
                    $uniqueEntry = $folderPath . '/' . $fileBase . '_' . $counter . '.url.txt';
                    $counter++;
                }
                $usedNames[$uniqueEntry] = true;
                $zip->addFromString($uniqueEntry, $linkContent);
                $added++;
                continue;
            }

            if ($relative === '') {
                continue;
            }

            $absPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relative, '/'));
            if (! is_file($absPath)) {
                continue;
            }

            $ext = pathinfo($absPath, PATHINFO_EXTENSION);
            $ext = $ext !== '' ? '.' . $ext : '';

            $entryName = $folderPath . '/' . $fileBase . $ext;
            $counter = 1;
            $uniqueEntry = $entryName;
            while (isset($usedNames[$uniqueEntry])) {
                $uniqueEntry = $folderPath . '/' . $fileBase . '_' . $counter . $ext;
                $counter++;
            }
            $usedNames[$uniqueEntry] = true;

            if ($zip->addFile($absPath, $uniqueEntry)) {
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipTempPath);
            return redirect()->back()->with('error', 'Tidak ada file valid yang dapat dimasukkan ke ZIP.');
        }

        $safeTitle = $sanitize($simak['nomor_kontrak'] ?? ($simak['nama_paket'] ?? 'simak'));
        $downloadName = 'simak-' . $type . '-' . $safeTitle . '-' . date('Ymd') . '.zip';

        return $this->response->download($zipTempPath, null)->setFileName($downloadName);
    }

    private function exportSimakDetailExcel(int $id, string $type)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        $tableMain = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';
        $tableVerif = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi' : 'trn_kontrak_simak_verifikasi';
        $tableVerifDok = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen' : 'trn_kontrak_simak_verifikasi_dokumen';

        if (! $db->tableExists($tableMain)) {
            return redirect()->back()->with('error', 'Tabel SIMAK belum tersedia.');
        }

        // Get main item
        $builder = $db->table($tableMain . ' s');
        $this->applyNotDeletedWhere($builder, $tableMain);
        $simak = $builder->where('s.id', $id)->get()->getRowArray();

        if (! is_array($simak)) {
            return redirect()->back()->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $kelengkapanPercentages = $this->getSimakAdministrasiKelengkapanBySimakId([$id], $type);
        $kelengkapanSummary = $kelengkapanPercentages[$id] ?? [];

        // Get template items (include hidden share items)
        $templateItems = $this->getSimakTemplateItems($type, true);

        $verifikasiByRow = [];
        if ($db->tableExists($tableVerif)) {
            $verifikasiBuilder = $db->table($tableVerif)
                ->select('row_no, kelengkapan_dokumen, verifikasi_ki, keterangan, pic')
                ->where('simak_id', $id)
                ->orderBy('row_no', 'ASC');
            $this->applyNotDeletedWhere($verifikasiBuilder, $tableVerif);
            $verifikasiRows = $verifikasiBuilder->get()->getResultArray();
            foreach ($verifikasiRows as $verifikasiRow) {
                $verifikasiByRow[(int) ($verifikasiRow['row_no'] ?? 0)] = $verifikasiRow;
            }
        }

        // Get verification documents
        $dokumenBuilder = $db->table($tableVerifDok);
        $this->applyNotDeletedWhere($dokumenBuilder, $tableVerifDok);
        $dokumenRows = $dokumenBuilder->where('simak_id', $id)->get()->getResultArray();
        $dokumenByRow = [];
        foreach ($dokumenRows as $doc) {
            $rowNo = (int) ($doc['row_no'] ?? 0);
            if (! isset($dokumenByRow[$rowNo])) {
                $dokumenByRow[$rowNo] = [];
            }
            $dokumenByRow[$rowNo][] = $doc;
        }

        // Build spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SIMAK ' . ucfirst($type));

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');
        $sheet->mergeCells('A5:I5');
        $sheet->mergeCells('A6:I6');
        $sheet->mergeCells('A7:I7');
        $sheet->setCellValue('A1', 'SIMAK Detail Export');
        $sheet->setCellValue('A2', 'Jenis SIMAK: ' . ucfirst($type));
        $sheet->setCellValue('A3', 'Nomor Kontrak: ' . ($simak['nomor_kontrak'] ?? '-'));
        $sheet->setCellValue('A4', 'Nama Paket: ' . ($simak['nama_paket'] ?? '-'));
        $sheet->setCellValue('A5', 'PPK: ' . ($simak['ppk_nama'] ?? '-'));
        $sheet->setCellValue('A6', 'Penyedia: ' . ($simak['penyedia'] ?? '-'));
        $sheet->setCellValue('A7', 'Nilai Kontrak: ' . angka_ribuan_id($simak['nilai_kontrak'] ?? 0) . ' | Add On: ' . angka_ribuan_id($simak['nilai_add_on'] ?? 0) . ' | Total: ' . angka_ribuan_id($simak['total_kontrak'] ?? 0) . ' | Export Date: ' . date('d/m/Y H:i:s'));
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
        $sheet->getStyle('A2:I7')->getFont()->setBold(true);
        $sheet->getStyle('A1:I7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Table headers
        $headers = ['No', 'Uraian', 'Kelengkapan', 'Verifikasi Draft', 'Verifikasi Final', 'Keterangan', 'PIC', 'File Draft', 'File Final'];
        $sheet->fromArray($headers, null, 'A10');
        $sheet->getStyle('A10:I10')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A10:I10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('0F766E');
        $sheet->freezePane('A11');
        $sheet->setAutoFilter('A10:I10');

        // Data rows
        $rowNum = 11;
        foreach ($templateItems as $item) {
            $rowNo = (int) ($item['row_no'] ?? 0);
            $dokumens = $dokumenByRow[$rowNo] ?? [];
            $existing = $verifikasiByRow[$rowNo] ?? [];
            $displayNo = trim((string) ($item['display_no_auto'] ?? $item['display_no'] ?? ''));
            if ($displayNo === '') {
                $displayNo = (string) ($rowNo > 0 ? $rowNo : '');
            }

            // Find draft and final documents
            $draftDoc = null;
            $finalDoc = null;
            foreach ($dokumens as $doc) {
                $tipe = strtolower((string) ($doc['tipe_dokumen'] ?? ''));
                if ($tipe === 'draft' || $tipe === 'draft_upload') {
                    $draftDoc = $doc;
                } elseif ($tipe === 'final' || $tipe === 'final_upload') {
                    $finalDoc = $doc;
                }
            }

            $kelengkapan = trim((string) ($existing['kelengkapan_dokumen'] ?? ''));
            if ($kelengkapan === '') {
                $kelengkapan = trim((string) ($draftDoc['kelengkapan_dokumen'] ?? ($finalDoc['kelengkapan_dokumen'] ?? ($item['kelengkapan_dokumen'] ?? ''))));
            }

            $verifikasiDraft = trim((string) ($draftDoc['verifikasi_ki'] ?? ($existing['verifikasi_ki'] ?? '')));
            $verifikasiFinal = trim((string) ($finalDoc['verifikasi_ki'] ?? ($existing['verifikasi_ki'] ?? '')));
            $keterangan = trim((string) ($existing['keterangan'] ?? ($draftDoc['keterangan'] ?? ($finalDoc['keterangan'] ?? ''))));
            $pic = trim((string) ($existing['pic'] ?? ($draftDoc['pic'] ?? ($finalDoc['pic'] ?? ''))));

            $sheet->setCellValue('A' . $rowNum, $displayNo);
            $sheet->setCellValue('B' . $rowNum, $item['uraian'] ?? '');
            $sheet->setCellValue('C' . $rowNum, $kelengkapan !== '' ? $kelengkapan : '-');
            $sheet->setCellValue('D' . $rowNum, $verifikasiDraft !== '' ? $verifikasiDraft : '-');
            $sheet->setCellValue('E' . $rowNum, $verifikasiFinal !== '' ? $verifikasiFinal : '-');
            $sheet->setCellValue('F' . $rowNum, $keterangan !== '' ? $keterangan : '-');
            $sheet->setCellValue('G' . $rowNum, $pic !== '' ? $pic : '-');
            $draftUrl = $this->buildSimakDocumentFileUrl($draftDoc);
            $finalUrl = $this->buildSimakDocumentFileUrl($finalDoc);

            $sheet->setCellValue('H' . $rowNum, $draftUrl !== '' ? 'Klik Disini' : '-');
            if ($draftUrl !== '') {
                $sheet->getCell('H' . $rowNum)->getHyperlink()->setUrl($draftUrl);
                $sheet->getStyle('H' . $rowNum)->getFont()->setUnderline(true)->getColor()->setRGB('0563C1');
            }

            $sheet->setCellValue('I' . $rowNum, $finalUrl !== '' ? 'Klik Disini' : '-');
            if ($finalUrl !== '') {
                $sheet->getCell('I' . $rowNum)->getHyperlink()->setUrl($finalUrl);
                $sheet->getStyle('I' . $rowNum)->getFont()->setUnderline(true)->getColor()->setRGB('0563C1');
            }

            $rowNum++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(22);

        $sheet->getStyle('A10:I' . ($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A10:I' . ($rowNum - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $sheet->getStyle('B11:F' . ($rowNum - 1))->getAlignment()->setWrapText(true);
        $sheet->getStyle('A11:A' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C11:E' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H11:I' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        for ($currentRow = 11; $currentRow < $rowNum; $currentRow++) {
            if (($currentRow - 11) % 2 === 0) {
                $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
        }

        $filename = 'simak_' . $type . '_detail_' . $id . '_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary === false ? '' : $binary);
    }

    public function deleteSimak(int $id)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $db = db_connect();
        $simak = $db->table('trn_kontrak_simak')->where('id', $id)->get()->getRowArray();
        if (! is_array($simak)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Data SIMAK konstruksi tidak ditemukan.');
        }
        
        $confirm = trim((string) $this->request->getPost('confirm_nomor_kontrak'));
        if ($confirm === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Konfirmasi nomor kontrak wajib diisi. Ketik nomor kontrak untuk melanjutkan.');
        }

        if ($confirm !== (string) ($simak['nomor_kontrak'] ?? '')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Nomor kontrak yang diketik tidak cocok. Penghapusan dibatalkan.');
        }

        $db->transStart();
        try {
            $db->table('trn_kontrak_simak_verifikasi_dokumen')->where('simak_id', $id)->delete();
            $db->table('trn_kontrak_simak_verifikasi')->where('simak_id', $id)->delete();
            $db->table('trn_kontrak_simak_share')->where('simak_id', $id)->delete();
            if ($db->tableExists('trn_kontrak_simak_add_on')) {
                $db->table('trn_kontrak_simak_add_on')->where('simak_id', $id)->delete();
            }
            $db->table('trn_kontrak_simak')->where('id', $id)->delete();
            $db->transComplete();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('success', 'Data SIMAK konstruksi berhasil dihapus.');
    }

    public function deleteSimakKonsultasi(int $id)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $db = db_connect();
        $simak = $db->table('trn_kontrak_simak_konsultasi')->where('id', $id)->get()->getRowArray();
        if (! is_array($simak)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Data SIMAK konsultasi tidak ditemukan.');
        }
        
        $confirm = trim((string) $this->request->getPost('confirm_nomor_kontrak'));
        if ($confirm === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Konfirmasi nomor kontrak wajib diisi. Ketik nomor kontrak untuk melanjutkan.');
        }

        if ($confirm !== (string) ($simak['nomor_kontrak'] ?? '')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Nomor kontrak yang diketik tidak cocok. Penghapusan dibatalkan.');
        }

        $db->transStart();
        try {
            $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')->where('simak_id', $id)->delete();
            $db->table('trn_kontrak_simak_konsultasi_verifikasi')->where('simak_id', $id)->delete();
            $db->table('trn_kontrak_simak_konsultasi_share')->where('simak_id', $id)->delete();
            if ($db->tableExists('trn_kontrak_simak_konsultasi_add_on')) {
                $db->table('trn_kontrak_simak_konsultasi_add_on')->where('simak_id', $id)->delete();
            }
            $db->table('trn_kontrak_simak_konsultasi')->where('id', $id)->delete();
            $db->transComplete();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('success', 'Data SIMAK konsultasi berhasil dihapus.');
    }

    private function exportSimakDetailHtml(int $id, string $type)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        $tableMain = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';
        $tableVerif = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi' : 'trn_kontrak_simak_verifikasi';
        $tableVerifDok = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen' : 'trn_kontrak_simak_verifikasi_dokumen';

        if (! $db->tableExists($tableMain)) {
            return redirect()->back()->with('error', 'Tabel SIMAK belum tersedia.');
        }

        // Get main item
        $builder = $db->table($tableMain . ' s');
        $this->applyNotDeletedWhere($builder, $tableMain);
        $simak = $builder->where('s.id', $id)->get()->getRowArray();

        if (! is_array($simak)) {
            return redirect()->back()->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $kelengkapanPercentages = $this->getSimakAdministrasiKelengkapanBySimakId([$id], $type);
        $kelengkapanSummary = $kelengkapanPercentages[$id] ?? [];

        // Get template items, including hidden share rows
        $templateItems = $this->getSimakTemplateItems($type, true);

        $verifikasiByRow = [];
        if ($db->tableExists($tableVerif)) {
            $verifikasiBuilder = $db->table($tableVerif)
                ->select('row_no, kelengkapan_dokumen, verifikasi_ki, keterangan, pic')
                ->where('simak_id', $id)
                ->orderBy('row_no', 'ASC');
            $this->applyNotDeletedWhere($verifikasiBuilder, $tableVerif);
            $verifikasiRows = $verifikasiBuilder->get()->getResultArray();
            foreach ($verifikasiRows as $verifikasiRow) {
                $verifikasiByRow[(int) ($verifikasiRow['row_no'] ?? 0)] = $verifikasiRow;
            }
        }

        // Get verification documents
        $dokumenBuilder = $db->table($tableVerifDok);
        $this->applyNotDeletedWhere($dokumenBuilder, $tableVerifDok);
        $dokumenRows = $dokumenBuilder->where('simak_id', $id)->get()->getResultArray();
        $dokumenByRow = [];
        foreach ($dokumenRows as $doc) {
            $rowNo = (int) ($doc['row_no'] ?? 0);
            if (! isset($dokumenByRow[$rowNo])) {
                $dokumenByRow[$rowNo] = [];
            }
            $dokumenByRow[$rowNo][] = $doc;
        }

        // Build HTML
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAK ' . ucfirst($type) . ' Detail - ' . htmlspecialchars($simak['nomor_kontrak'] ?? '-') . '</title>
    <style>
        :root { --bg: #f3f7fb; --card: #ffffff; --line: #dbe4ee; --text: #183153; --muted: #617187; --accent: #1f4e78; --accent-2: #0f766e; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: linear-gradient(180deg, #eef4fa 0%, var(--bg) 100%); color: var(--text); }
        .page { max-width: 1400px; margin: 0 auto; padding: 28px 22px 40px; }
        .hero { background: linear-gradient(135deg, var(--accent) 0%, #224b8f 45%, var(--accent-2) 100%); color: #fff; border-radius: 18px; padding: 22px 24px; box-shadow: 0 18px 40px rgba(24, 49, 83, 0.18); }
        .hero h1 { margin: 0 0 8px; font-size: 26px; }
        .hero p { margin: 0; opacity: 0.92; }
        .meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin: 18px 0 24px; }
        .meta-card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 14px 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04); }
        .meta-label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); margin-bottom: 6px; font-weight: 700; }
        .meta-value { font-size: 14px; font-weight: 700; color: var(--text); word-break: break-word; }
        .table-wrap { background: var(--card); border: 1px solid var(--line); border-radius: 18px; overflow: hidden; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06); }
        table { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: fixed; }
        thead th { background: linear-gradient(135deg, var(--accent) 0%, #24528f 100%); color: #fff; padding: 12px 10px; text-align: left; position: sticky; top: 0; z-index: 1; }
        td { border-top: 1px solid var(--line); padding: 10px; vertical-align: top; word-break: break-word; }
        tbody tr:nth-child(even) { background-color: #f8fbff; }
        tbody tr:hover { background-color: #eef6ff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 10px; border-radius: 999px; font-size: 10px; display: inline-block; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .badge-success { background-color: #e8f5e9; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #dbeafe; color: #1d4ed8; }
        .file-link { color: #0f766e; text-decoration: none; font-weight: 700; }
        .file-link:hover { text-decoration: underline; }
        .footer { margin-top: 20px; text-align: center; color: var(--muted); font-size: 11px; }
        @media print { body { background: #fff; } .page { padding: 0; } .hero, .meta-card, .table-wrap { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>SIMAK ' . ucfirst($type) . ' - Detail Verifikasi</h1>
            <p>Ringkasan data, status verifikasi, dan lampiran dokumen dalam format yang lebih mudah dibaca.</p>
        </div>
        <div class="meta">
            <div class="meta-card"><div class="meta-label">Jenis SIMAK</div><div class="meta-value">' . ucfirst($type) . '</div></div>
            <div class="meta-card"><div class="meta-label">Nomor Kontrak</div><div class="meta-value">' . htmlspecialchars($simak['nomor_kontrak'] ?? '-') . '</div></div>
            <div class="meta-card"><div class="meta-label">Nama Paket</div><div class="meta-value">' . htmlspecialchars($simak['nama_paket'] ?? '-') . '</div></div>
            <div class="meta-card"><div class="meta-label">Penyedia</div><div class="meta-value">' . htmlspecialchars($simak['penyedia'] ?? '-') . '</div></div>
            <div class="meta-card"><div class="meta-label">PPK</div><div class="meta-value">' . htmlspecialchars($simak['ppk_nama'] ?? '-') . ' (NIP: ' . htmlspecialchars($simak['ppk_nip'] ?? '-') . ')</div></div>
            <div class="meta-card"><div class="meta-label">Nilai Kontrak</div><div class="meta-value">Rp ' . angka_ribuan_id($simak['nilai_kontrak'] ?? 0) . '</div></div>
            <div class="meta-card"><div class="meta-label">Nilai Add On</div><div class="meta-value">Rp ' . angka_ribuan_id($simak['nilai_add_on'] ?? 0) . '</div></div>
            <div class="meta-card"><div class="meta-label">Total Kontrak</div><div class="meta-value">Rp ' . angka_ribuan_id($simak['total_kontrak'] ?? 0) . '</div></div>
            <div class="meta-card"><div class="meta-label">Kelengkapan</div><div class="meta-value">Lengkap ' . number_format((float) ($kelengkapanSummary['lengkap_persen'] ?? 0), 2, ',', '.') . '%, Belum Sesuai ' . number_format((float) ($kelengkapanSummary['belum_sesuai_persen'] ?? 0), 2, ',', '.') . '%, Menunggu Verifikasi ' . number_format((float) ($kelengkapanSummary['belum_verifikasi_persen'] ?? 0), 2, ',', '.') . '%, Belum Ada ' . number_format((float) ($kelengkapanSummary['belum_ada_persen'] ?? 0), 2, ',', '.') . '%</div></div>
            <div class="meta-card"><div class="meta-label">Total Items</div><div class="meta-value">' . count($templateItems) . ' poin</div></div>
            <div class="meta-card"><div class="meta-label">Tanggal Export</div><div class="meta-value">' . date('d/m/Y H:i:s') . '</div></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th style="width: 180px;">Uraian</th>
                        <th style="width: 120px;">Kelengkapan</th>
                        <th style="width: 130px;">Verifikasi Draft</th>
                        <th style="width: 130px;">Verifikasi Final</th>
                        <th style="width: 240px;">Keterangan</th>
                        <th style="width: 140px;">PIC</th>
                        <th style="width: 120px;">File Draft</th>
                        <th style="width: 120px;">File Final</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($templateItems as $item) {
            $rowNo = (int) ($item['row_no'] ?? 0);
            $dokumens = $dokumenByRow[$rowNo] ?? [];
            $existing = $verifikasiByRow[$rowNo] ?? [];
            $displayNo = trim((string) ($item['display_no_auto'] ?? $item['display_no'] ?? ''));
            if ($displayNo === '') {
                $displayNo = (string) ($rowNo > 0 ? $rowNo : '');
            }

            $draftDoc = null;
            $finalDoc = null;
            foreach ($dokumens as $docRow) {
                $docType = strtolower(trim((string) ($docRow['tipe_dokumen'] ?? 'final')));
                if ($docType === 'draft' && $draftDoc === null) {
                    $draftDoc = $docRow;
                } elseif ($docType !== 'draft' && $finalDoc === null) {
                    $finalDoc = $docRow;
                }

                if ($draftDoc !== null && $finalDoc !== null) {
                    break;
                }
            }

            $kelengkapan = trim((string) ($existing['kelengkapan_dokumen'] ?? ''));
            if ($kelengkapan === '') {
                $kelengkapan = trim((string) ($draftDoc['kelengkapan_dokumen'] ?? ($finalDoc['kelengkapan_dokumen'] ?? ($item['kelengkapan_dokumen'] ?? ''))));
            }
            $verifikasiDraft = trim((string) ($draftDoc['verifikasi_ki'] ?? ($existing['verifikasi_ki'] ?? '')));
            $verifikasiFinal = trim((string) ($finalDoc['verifikasi_ki'] ?? ($existing['verifikasi_ki'] ?? '')));
            $keterangan = trim((string) ($existing['keterangan'] ?? ($draftDoc['keterangan'] ?? ($finalDoc['keterangan'] ?? ''))));
            $pic = trim((string) ($existing['pic'] ?? ($draftDoc['pic'] ?? ($finalDoc['pic'] ?? ''))));

            $html .= '
            <tr>
                <td class="text-center">' . htmlspecialchars($displayNo !== '' ? $displayNo : '-') . '</td>
                <td>' . htmlspecialchars($item['uraian'] ?? '-') . '</td>
                <td class="text-center">' . htmlspecialchars($kelengkapan !== '' ? $kelengkapan : '-') . '</td>
                <td class="text-center"><span class="badge ' . ($verifikasiDraft === 'sesuai' ? 'badge-success' : ($verifikasiDraft === 'tidak_sesuai' ? 'badge-warning' : 'badge-info')) . '">' . htmlspecialchars($verifikasiDraft !== '' ? $verifikasiDraft : '-') . '</span></td>
                <td class="text-center"><span class="badge ' . ($verifikasiFinal === 'sesuai' ? 'badge-success' : ($verifikasiFinal === 'tidak_sesuai' ? 'badge-warning' : 'badge-info')) . '">' . htmlspecialchars($verifikasiFinal !== '' ? $verifikasiFinal : '-') . '</span></td>
                <td>' . htmlspecialchars($keterangan !== '' ? $keterangan : '-') . '</td>
                <td class="text-center">' . htmlspecialchars($pic !== '' ? $pic : '-') . '</td>
                <td class="text-center">' . $this->renderSimakDocumentLinkHtml($draftDoc) . '</td>
                <td class="text-center">' . $this->renderSimakDocumentLinkHtml($finalDoc) . '</td>
            </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </div>
        <div class="footer">
            Generated by SIMAK System | Export Date: ' . date('d/m/Y H:i:s') . '
        </div>
    </div>
</body>
</html>';

        $filename = 'simak_' . $type . '_detail_' . $id . '_' . date('Y-m-d_His') . '.html';

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($html);
    }

    private function buildSimakDocumentFileUrl(?array $doc): string
    {
        if (! is_array($doc)) {
            return '';
        }

        if (! empty($doc['is_google_drive_link']) && ! empty($doc['google_drive_source_url'])) {
            return trim((string) $doc['google_drive_source_url']);
        }

        $relativePath = trim((string) ($doc['file_relative_path'] ?? ''));
        if ($relativePath === '') {
            return '';
        }

        if (function_exists('media_url')) {
            return media_url($relativePath);
        }

        return base_url(ltrim($relativePath, '/'));
    }

    private function renderSimakDocumentLinkHtml(?array $doc): string
    {
        $url = $this->buildSimakDocumentFileUrl($doc);
        if ($url === '') {
            return '-';
        }

        $label = 'Klik Disini';
        $title = trim((string) ($doc['file_original_name'] ?? $label));

        return '<a class="file-link" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>';
    }

    public function createSimakShare(int $id)
    {
        $isAjax = $this->request->isAJAX()
            || stripos((string) $this->request->getHeaderLine('Accept'), 'application/json') !== false;

        $respondError = function (string $message, int $statusCode = 400) use ($isAjax) {
            if ($isAjax) {
                return $this->response->setStatusCode($statusCode)->setJSON([
                    'success' => false,
                    'message' => $message,
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', $message);
        };

        if (! $this->canManageKontrak()) {
            return $respondError('Anda tidak memiliki akses untuk membuat link share SIMAK.', 403);
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak') || ! $db->tableExists('trn_kontrak_simak_share')) {
            return $respondError('Tabel share SIMAK belum tersedia. Jalankan migration terbaru.', 500);
        }
        $shareHasExpiresCol = $this->tableHasColumn('trn_kontrak_simak_share', 'expires_at');

        $simakBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($simakBuilder, 'trn_kontrak_simak');
        $simak = $simakBuilder->get()->getRowArray();
        if (! is_array($simak)) {
            return $respondError('Data SIMAK tidak ditemukan.', 404);
        }

        // Menghilangkan durasi, aktif sampai dinonaktifkan manual
        $expiresAt = null;

        $token = $this->generateSimakShareToken();
        if ($token === null) {
            return $respondError('Gagal membuat token share SIMAK. Silakan coba lagi.', 500);
        }

        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');

        $existingSelect = 'id, share_token';
        if ($shareHasExpiresCol) {
            $existingSelect .= ', expires_at';
        }

        $existing = $db->table('trn_kontrak_simak_share')
            ->select($existingSelect)
            ->where('simak_id', $id)
            ->get()
            ->getRowArray();

        $payload = [
            'simak_id' => $id,
            'share_token' => $token,
            'is_active' => 1,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
        ];

        if ($shareHasExpiresCol) {
            $payload['expires_at'] = $expiresAt;
        }

        if (is_array($existing)) {
            $ok = $db->table('trn_kontrak_simak_share')->where('id', (int) ($existing['id'] ?? 0))->update($payload);
        } else {
            $payload['created_by'] = $actor;
            $payload['created_date'] = $today;
            $payload['created_at'] = $now;
            $ok = $db->table('trn_kontrak_simak_share')->insert($payload);
        }

        if (! $ok) {
            return $respondError('Gagal menyimpan link share SIMAK.', 500);
        }

        $shareUrl = site_url('simak/share/' . $token);
        $successMessage = 'Link share SIMAK berhasil dibuat.';

        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $successMessage,
                'share_url' => $shareUrl,
                'is_update' => is_array($existing) && trim((string) ($existing['share_token'] ?? '')) !== '',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $redirect = redirect()->to(site_url('admin/kontrak/simak/konstruksi'))
            ->with('success', $successMessage)
            ->with('simak_share_link', $shareUrl);

        if (is_array($existing) && trim((string) ($existing['share_token'] ?? '')) !== '') {
            $redirect = $redirect->with('simak_share_notice', 'Sebaiknya bagikan link yang sudah ada agar pihak kontraktor tidak bingung. Buat link baru hanya jika memang diperlukan karena link lama akan tidak berlaku.');
        }

        return $redirect;
    }

    public function deactivateSimakShare(int $id)
    {
        $isAjax = $this->request->isAJAX()
            || stripos((string) $this->request->getHeaderLine('Accept'), 'application/json') !== false;

        $respondError = function (string $message, int $statusCode = 400) use ($isAjax) {
            if ($isAjax) {
                return $this->response->setStatusCode($statusCode)->setJSON([
                    'success' => false,
                    'message' => $message,
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', $message);
        };

        if (! $this->canManageKontrak()) {
            return $respondError('Anda tidak memiliki akses untuk menonaktifkan link share SIMAK.', 403);
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_share')) {
            return $respondError('Tabel share SIMAK belum tersedia. Jalankan migration terbaru.', 500);
        }

        $share = $db->table('trn_kontrak_simak_share')
            ->select('id, is_active')
            ->where('simak_id', $id)
            ->get()
            ->getRowArray();

        if (! is_array($share)) {
            return $respondError('Link share SIMAK tidak ditemukan.', 404);
        }

        $payload = [
            'is_active' => 0,
            'updated_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'updated_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $db->table('trn_kontrak_simak_share')
            ->where('id', (int) ($share['id'] ?? 0))
            ->update($payload);

        if (! $ok) {
            return $respondError('Gagal menonaktifkan link share SIMAK.', 500);
        }

        $message = 'Link share SIMAK berhasil dinonaktifkan.';
        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('success', $message);
    }

    public function importSimak()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Anda tidak memiliki akses untuk import data SIMAK.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'File import tidak valid.');
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, ['xls', 'xlsx'], true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Format file harus .xls atau .xlsx.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'File Excel gagal dibaca. Pastikan format file valid (.xls/.xlsx).');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, true);
        if ($rows === []) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'File Excel kosong.');
        }

        $headerRow = array_shift($rows);
        if (! is_array($headerRow) || $headerRow === []) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Header Excel tidak ditemukan.');
        }

        $normalizeHeader = static function ($value): string {
            $header = strtolower(trim((string) $value));
            $header = str_replace(['-', '/', ' '], '_', $header);
            $header = preg_replace('/[^a-z0-9_]/', '', $header) ?? $header;
            return $header;
        };

        $headers = [];
        foreach ($headerRow as $column => $name) {
            $normalized = $normalizeHeader($name);
            if ($normalized !== '') {
                $headers[$column] = $normalized;
            }
        }

        if ($headers === []) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Header Excel tidak dikenali.');
        }

        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Master pegawai tidak tersedia. Import SIMAK membutuhkan referensi nama PPK dari NIP.');
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $importReport = [];
        $maxReportRows = 100;
        $appendReport = static function (array &$report, int $rowNumber, string $message) use ($maxReportRows): void {
            if (count($report) >= $maxReportRows) {
                return;
            }

            $report[] = 'Baris ' . $rowNumber . ': ' . $message;
        };

        foreach ($rows as $index => $row) {
            $excelRow = (int) $index + 2;

            if (! is_array($row)) {
                $skipped++;
                $appendReport($importReport, $excelRow, 'Format baris tidak valid.');
                continue;
            }

            $rowData = [];
            foreach ($headers as $column => $headerName) {
                $rowData[$headerName] = trim((string) ($row[$column] ?? ''));
            }

            $ppkNip = trim((string) ($rowData['ppk_nip'] ?? ''));
            $namaPaket = trim((string) ($rowData['nama_paket'] ?? ''));
            $tahunAnggaran = trim((string) ($rowData['tahun_anggaran'] ?? ''));
            $nomorKontrak = trim((string) ($rowData['nomor_kontrak'] ?? ''));
            $nilaiKontrak = $this->parseMoneyToFloat($rowData['nilai_kontrak'] ?? 0);
            $nilaiKontrakJasa = $this->parseMoneyToFloat($rowData['nilai_kontrak_jasa_konsultansi'] ?? 0);
            if ($nilaiKontrakJasa <= 0) {
                $nilaiKontrakJasa = $nilaiKontrak;
            }
            $penyediaKonstruksi = trim((string) ($rowData['penyedia'] ?? ''));
            $penyediaJasa = trim((string) ($rowData['penyedia_jasa_konsultansi'] ?? ''));
            if ($penyediaJasa === '') {
                $penyediaJasa = $penyediaKonstruksi;
            }
            $tahapanPekerjaan = trim((string) ($rowData['tahapan_pekerjaan'] ?? ''));
            $tanggalPemeriksaan = $this->normalizeDateValue((string) ($rowData['tanggal_pemeriksaan'] ?? ''));
            $emailResponden1 = trim((string) ($rowData['email_responden_1'] ?? $rowData['email_responden'] ?? ''));
            $emailResponden2 = trim((string) ($rowData['email_responden_2'] ?? ''));
            $jenisJasa = $this->normalizeSimakJenisPekerjaanJasa((string) ($rowData['jenis_pekerjaan_jasa_konsultansi'] ?? ''));
            if ($jenisJasa === null) {
                $jenisJasa = 'perencanaan';
            }

            $masaPelaksanaan = $this->normalizeSimakMasaPelaksanaan((string) ($rowData['masa_pelaksanaan'] ?? ''));
            if ($masaPelaksanaan === null) {
                $masaPelaksanaan = 'syc';
            }

            $paguAnggaran = $this->parseMoneyToBigInt($rowData['pagu_anggaran'] ?? 0);
            if ($paguAnggaran <= 0) {
                $paguAnggaran = $this->parseMoneyToBigInt($nilaiKontrak);
            }

            $metodePemilihan = $this->normalizeSimakMetodePemilihan((string) ($rowData['metode_pemilihan'] ?? ''));
            if ($metodePemilihan === null) {
                $metodePemilihan = 'seleksi';
            }

            if ($ppkNip === '' || $namaPaket === '' || $tahunAnggaran === '' || $nomorKontrak === '') {
                $skipped++;
                $appendReport($importReport, $excelRow, 'Field wajib belum lengkap (ppk_nip, nama_paket, tahun_anggaran, nomor_kontrak).');
                continue;
            }

            if (! preg_match('/^\d{4}\s*-\s*\d{4}$/', $tahunAnggaran)) {
                $skipped++;
                $appendReport($importReport, $excelRow, 'Format tahun anggaran tidak valid. Gunakan format YYYY - YYYY.');
                continue;
            }

            if ($nilaiKontrak <= 0 || $nilaiKontrakJasa <= 0 || $penyediaKonstruksi === '' || $penyediaJasa === '') {
                $skipped++;
                $appendReport($importReport, $excelRow, 'Field wajib tambahan belum lengkap (penyedia, penyedia_jasa_konsultansi, nilai_kontrak, nilai_kontrak_jasa_konsultansi).');
                continue;
            }

            $pegawai = $db->table('mst_pegawai')->select('nip, nama')->where('nip', $ppkNip)->get()->getRowArray();
            if (! is_array($pegawai) || trim((string) ($pegawai['nama'] ?? '')) === '') {
                $skipped++;
                $appendReport($importReport, $excelRow, 'NIP PPK tidak ditemukan pada master pegawai.');
                continue;
            }

            $ppkNama = trim((string) $pegawai['nama']);

                $payload = [
                'satker' => trim((string) ($rowData['satker'] ?? '')) ?: 'Perencanaan Prasarana Strategis',
                'ppk_nama' => $ppkNama,
                'ppk_nip' => $ppkNip,
                'nama_paket' => $namaPaket,
                'tahun_anggaran' => $tahunAnggaran,
                'penyedia' => $penyediaKonstruksi,
                'penyedia_jasa_konsultansi' => $penyediaJasa,
                'nomor_kontrak' => $nomorKontrak,
                'nilai_kontrak' => $nilaiKontrak,
                'nilai_kontrak_jasa_konsultansi' => $nilaiKontrakJasa,
                'jenis_pekerjaan_jasa_konsultansi' => $jenisJasa,
                'masa_pelaksanaan' => $masaPelaksanaan,
                'pagu_anggaran' => $paguAnggaran,
                'metode_pemilihan' => $metodePemilihan,
                    'email_responden_1' => $emailResponden1,
                    'email_responden_2' => $emailResponden2,
            ];

            $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('nomor_kontrak', $nomorKontrak);
            $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
            $existing = $existingBuilder->get()->getRowArray();

            if (is_array($existing)) {
                if ($this->tableHasColumn('trn_kontrak_simak', 'updated_by')) {
                    $payload['updated_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
                }
                if ($this->tableHasColumn('trn_kontrak_simak', 'updated_date')) {
                    $payload['updated_date'] = date('Y-m-d');
                }
                if ($this->tableHasColumn('trn_kontrak_simak', 'updated_at')) {
                    $payload['updated_at'] = date('Y-m-d H:i:s');
                }

                if ($db->table('trn_kontrak_simak')->where('id', (int) $existing['id'])->update($payload)) {
                    $updated++;
                } else {
                    $skipped++;
                    $appendReport($importReport, $excelRow, 'Gagal update data SIMAK dengan nomor kontrak ' . $nomorKontrak . '.');
                }
                continue;
            }

            if ($this->tableHasColumn('trn_kontrak_simak', 'created_by')) {
                $payload['created_by'] = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
            }
            if ($this->tableHasColumn('trn_kontrak_simak', 'created_date')) {
                $payload['created_date'] = date('Y-m-d');
            }
            if ($this->tableHasColumn('trn_kontrak_simak', 'created_at')) {
                $payload['created_at'] = date('Y-m-d H:i:s');
            }

            if ($db->table('trn_kontrak_simak')->insert($payload)) {
                $inserted++;
            } else {
                $skipped++;
                $appendReport($importReport, $excelRow, 'Gagal insert data SIMAK dengan nomor kontrak ' . $nomorKontrak . '.');
            }
        }

        if ($inserted === 0 && $updated === 0) {
            $redirect = redirect()->to(site_url('admin/kontrak/simak/konstruksi'))
                ->with('error', 'Tidak ada data yang diproses. Pastikan kolom minimal: ppk_nip, nama_paket, tahun_anggaran, nomor_kontrak, nilai_kontrak dan NIP tersedia di master pegawai.');

            if ($importReport !== []) {
                $redirect = $redirect->with('import_simak_report', $importReport);
            }

            return $redirect;
        }

        $message = 'Import SIMAK selesai. Insert: ' . $inserted . ', Update: ' . $updated . ', Dilewati: ' . $skipped . '.';
        $redirect = redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('success', $message);
        if ($importReport !== []) {
            $redirect = $redirect->with('import_simak_report', $importReport);
        }

        return $redirect;
    }

    public function exportSimakTemplate()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Anda tidak memiliki akses untuk mengunduh template SIMAK.');
        }

        $headers = [
            'satker',
            'ppk_nip',
            'nama_paket',
            'tahun_anggaran',
            'penyedia',
            'nomor_kontrak',
            'nilai_kontrak',
            'email_responden_1',
            'email_responden_2',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template SIMAK');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray([
            'Perencanaan Prasarana Strategis',
            '199012212018021001',
            'Nama Paket Contoh',
            '2026 - 2027',
            'Penyedia Konstruksi Contoh',
            'SIMAK/001/2026',
            1000000000,
            'responden1@example.com',
            'responden2@example.com',
        ], null, 'A2');

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'template_import_simak_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary === false ? '' : $binary);
    }

    public function createSimak()
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Anda tidak memiliki akses untuk menambah data SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $satker = trim((string) $this->request->getPost('satker'));
        $ppkNip = trim((string) $this->request->getPost('ppk_nip'));
        $ppkNama = trim((string) $this->request->getPost('ppk_nama'));
        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $tahunAnggaran = trim((string) $this->request->getPost('tahun_anggaran'));
        $penyedia = trim((string) $this->request->getPost('penyedia'));
        $penyediaJasa = trim((string) $this->request->getPost('penyedia_jasa_konsultansi'));
        if ($penyediaJasa === '') {
            $penyediaJasa = $penyedia;
        }
        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nilaiKontrak = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak'));
        $nilaiKontrakJasa = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak_jasa_konsultansi'));
        if ($nilaiKontrakJasa <= 0) {
            $nilaiKontrakJasa = $nilaiKontrak;
        }
        $jenisJasa = $this->normalizeSimakJenisPekerjaanJasa((string) $this->request->getPost('jenis_pekerjaan_jasa_konsultansi'));
        $masaPelaksanaan = $this->normalizeSimakMasaPelaksanaan((string) $this->request->getPost('masa_pelaksanaan'));
        $paguAnggaran = $this->parseMoneyToBigInt($this->request->getPost('pagu_anggaran'));
        $metodePemilihan = $this->normalizeSimakMetodePemilihan((string) $this->request->getPost('metode_pemilihan'));
        $emailResponden1 = trim((string) $this->request->getPost('email_responden_1'));
        $emailResponden2 = trim((string) $this->request->getPost('email_responden_2'));
        $paketId = (int) $this->request->getPost('paket_id');

        // Validate paket_id is required
        if ($paketId <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Pemaketan wajib dipilih.');
        }

        // Validate paket exists
        if ($db->tableExists('mst_paket')) {
            $paket = $db->table('mst_paket')->select('id')->where('id', $paketId)->get()->getRowArray();
            if (! is_array($paket)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Pemaketan tidak ditemukan pada master paket.');
            }
        }

        // Pada menu konstruksi, field konsultansi belum ditampilkan di form.
        // Gunakan default aman agar proses simpan tidak terblokir.
        if ($jenisJasa === null) {
            $jenisJasa = 'perencanaan';
        }

        if ($masaPelaksanaan === null) {
            $masaPelaksanaan = 'syc';
        }

        if ($paguAnggaran <= 0) {
            $paguAnggaran = (int) round($nilaiKontrak > 0 ? $nilaiKontrak : 0);
        }

        if ($metodePemilihan === null) {
            $metodePemilihan = 'seleksi';
        }

        if ($satker === '') {
            $satker = 'Perencanaan Prasarana Strategis';
        }

        if ($ppkNip === '' || $ppkNama === '' || $namaPaket === '' || $tahunAnggaran === '' || $nomorKontrak === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Field wajib belum lengkap.');
        }

        if (! preg_match('/^\d{4}\s*-\s*\d{4}$/', $tahunAnggaran)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Format tahun anggaran harus seperti 2024 - 2025.');
        }

        if ($nilaiKontrak <= 0 || $nilaiKontrakJasa <= 0 || $penyedia === '' || $penyediaJasa === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Seluruh input wajib terisi (termasuk penyedia, penyedia jasa, nilai kontrak konstruksi, dan nilai kontrak jasa konsultansi).');
        }

        // require at least email_responden_1
        if ($emailResponden1 === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Email responden 1 wajib diisi.');
        }

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')->select('nip, nama')->where('nip', $ppkNip)->get()->getRowArray();
            if (! is_array($pegawai)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'NIP PPK tidak ditemukan pada master pegawai.');
            }

            $ppkNama = trim((string) ($pegawai['nama'] ?? $ppkNama));
        }

        $duplicateBuilder = $db->table('trn_kontrak_simak')->select('id')->where('nomor_kontrak', $nomorKontrak);
        $this->applyNotDeletedWhere($duplicateBuilder, 'trn_kontrak_simak');
        $duplicate = $duplicateBuilder->get()->getRowArray();
        if (is_array($duplicate)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Nomor kontrak sudah digunakan.');
        }

        $payload = [
            'satker' => $satker,
            'paket_id' => $paketId,
            'ppk_nama' => $ppkNama,
            'ppk_nip' => $ppkNip,
            'nama_paket' => $namaPaket,
            'tahun_anggaran' => $tahunAnggaran,
            'penyedia' => $penyedia,
            'penyedia_jasa_konsultansi' => $penyediaJasa,
            'nomor_kontrak' => $nomorKontrak,
            'nilai_kontrak' => $nilaiKontrak,
            'nilai_kontrak_jasa_konsultansi' => $nilaiKontrakJasa,
            'jenis_pekerjaan_jasa_konsultansi' => $jenisJasa,
            'masa_pelaksanaan' => $masaPelaksanaan,
            'pagu_anggaran' => $paguAnggaran,
            'metode_pemilihan' => $metodePemilihan,
            'email_responden_1' => $emailResponden1,
            'email_responden_2' => $emailResponden2,
            'created_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'created_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $db->table('trn_kontrak_simak')->insert($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Gagal menyimpan data SIMAK.');
        }

        return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('success', 'Data SIMAK berhasil disimpan.');
    }

    public function updateSimak(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Anda tidak memiliki akses untuk mengubah data SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $satker = trim((string) $this->request->getPost('satker'));
        $ppkNip = trim((string) $this->request->getPost('ppk_nip'));
        $ppkNama = trim((string) $this->request->getPost('ppk_nama'));
        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $tahunAnggaran = trim((string) $this->request->getPost('tahun_anggaran'));
        $penyedia = trim((string) $this->request->getPost('penyedia'));
        $penyediaJasa = trim((string) $this->request->getPost('penyedia_jasa_konsultansi'));
        if ($penyediaJasa === '') {
            $penyediaJasa = $penyedia;
        }
        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nilaiKontrak = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak'));
        $nilaiKontrakJasa = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak_jasa_konsultansi'));
        if ($nilaiKontrakJasa <= 0) {
            $nilaiKontrakJasa = $nilaiKontrak;
        }
        $jenisJasa = $this->normalizeSimakJenisPekerjaanJasa((string) $this->request->getPost('jenis_pekerjaan_jasa_konsultansi'));
        $masaPelaksanaan = $this->normalizeSimakMasaPelaksanaan((string) $this->request->getPost('masa_pelaksanaan'));
        $paguAnggaran = $this->parseMoneyToBigInt($this->request->getPost('pagu_anggaran'));
        $metodePemilihan = $this->normalizeSimakMetodePemilihan((string) $this->request->getPost('metode_pemilihan'));
        $emailResponden1 = trim((string) $this->request->getPost('email_responden_1'));
        $emailResponden2 = trim((string) $this->request->getPost('email_responden_2'));
        $paketId = (int) $this->request->getPost('paket_id');

        // Validate paket_id is required
        if ($paketId <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Pemaketan wajib dipilih.');
        }

        // Validate paket exists
        if ($db->tableExists('mst_paket')) {
            $paket = $db->table('mst_paket')->select('id')->where('id', $paketId)->get()->getRowArray();
            if (! is_array($paket)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Pemaketan tidak ditemukan pada master paket.');
            }
        }

        // Pada menu konstruksi, field konsultansi belum ditampilkan di form.
        // Gunakan default aman agar proses simpan tidak terblokir.
        if ($jenisJasa === null) {
            $jenisJasa = 'perencanaan';
        }

        if ($masaPelaksanaan === null) {
            $masaPelaksanaan = 'syc';
        }

        if ($paguAnggaran <= 0) {
            $paguAnggaran = (int) round($nilaiKontrak > 0 ? $nilaiKontrak : 0);
        }

        if ($metodePemilihan === null) {
            $metodePemilihan = 'seleksi';
        }

        if ($satker === '') {
            $satker = 'Perencanaan Prasarana Strategis';
        }

        if ($ppkNip === '' || $ppkNama === '' || $namaPaket === '' || $tahunAnggaran === '' || $nomorKontrak === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Field wajib belum lengkap.');
        }

        if (! preg_match('/^\d{4}\s*-\s*\d{4}$/', $tahunAnggaran)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Format tahun anggaran harus seperti 2024 - 2025.');
        }

        if ($nilaiKontrak <= 0 || $nilaiKontrakJasa <= 0 || $penyedia === '' || $penyediaJasa === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Seluruh input wajib terisi (termasuk penyedia, penyedia jasa, nilai kontrak konstruksi, dan nilai kontrak jasa konsultansi).');
        }

        if ($emailResponden1 === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Email responden 1 wajib diisi.');
        }

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')->select('nip, nama')->where('nip', $ppkNip)->get()->getRowArray();
            if (! is_array($pegawai)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'NIP PPK tidak ditemukan pada master pegawai.');
            }

            $ppkNama = trim((string) ($pegawai['nama'] ?? $ppkNama));
        }

        $duplicateBuilder = $db->table('trn_kontrak_simak')
            ->select('id')
            ->where('nomor_kontrak', $nomorKontrak)
            ->where('id !=', $id);
        $this->applyNotDeletedWhere($duplicateBuilder, 'trn_kontrak_simak');
        $duplicate = $duplicateBuilder->get()->getRowArray();
        if (is_array($duplicate)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Nomor kontrak sudah digunakan pada data lain.');
        }

        $payload = [
            'satker' => $satker,
            'paket_id' => $paketId,
            'ppk_nama' => $ppkNama,
            'ppk_nip' => $ppkNip,
            'nama_paket' => $namaPaket,
            'tahun_anggaran' => $tahunAnggaran,
            'penyedia' => $penyedia,
            'penyedia_jasa_konsultansi' => $penyediaJasa,
            'nomor_kontrak' => $nomorKontrak,
            'nilai_kontrak' => $nilaiKontrak,
            'nilai_kontrak_jasa_konsultansi' => $nilaiKontrakJasa,
            'jenis_pekerjaan_jasa_konsultansi' => $jenisJasa,
            'masa_pelaksanaan' => $masaPelaksanaan,
            'pagu_anggaran' => $paguAnggaran,
            'metode_pemilihan' => $metodePemilihan,
            'email_responden_1' => $emailResponden1,
            'email_responden_2' => $emailResponden2,
            'updated_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'updated_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $db->transStart();
        $ok = $db->table('trn_kontrak_simak')->where('id', $id)->update($payload);

        if ($ok) {
            $this->syncSimakAddOns($id);
        }

        $db->transComplete();

        if (! $ok || ! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Gagal mengubah data SIMAK.');
        }

        return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('success', 'Data SIMAK berhasil diubah.');
    }

    public function detailSimak(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $builder = $db->table('trn_kontrak_simak')->select('*')->where('id', $id);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak');
        $item = $builder->get()->getRowArray();

        if (! is_array($item)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $templateItems = $this->getSimakPelaksanaanFisikTemplateItems();
        $verifikasiByRow = [];
        if ($db->tableExists('trn_kontrak_simak_verifikasi')) {
            $verifikasiBuilder = $db->table('trn_kontrak_simak_verifikasi')
                ->select('row_no, kelengkapan_dokumen, verifikasi_ki, keterangan, pic')
                ->where('simak_id', $id)
                ->orderBy('row_no', 'ASC');
            $this->applyNotDeletedWhere($verifikasiBuilder, 'trn_kontrak_simak_verifikasi');
            $rows = $verifikasiBuilder->get()->getResultArray();
            foreach ($rows as $row) {
                $verifikasiByRow[(int) ($row['row_no'] ?? 0)] = $row;
            }
        }

        $dokumenByRow = [];
        if ($db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            $dokumenBuilder = $db->table('trn_kontrak_simak_verifikasi_dokumen')
                ->select('id, row_no, file_original_name, file_relative_path, file_mime, file_size, created_at, created_by, tipe_dokumen, kelengkapan_dokumen, verifikasi_ki, keterangan, pic, is_google_drive_link, google_drive_source_url, copied_to_project_drive, copied_to_project_drive_at, copied_to_project_drive_by, original_file_id')
                ->where('simak_id', $id)
                ->orderBy('row_no', 'ASC')
                ->orderBy('id', 'DESC');
            $this->applyNotDeletedWhere($dokumenBuilder, 'trn_kontrak_simak_verifikasi_dokumen');
            $dokumenRows = $dokumenBuilder->get()->getResultArray();
            foreach ($dokumenRows as $doc) {
                $rowNo = (int) ($doc['row_no'] ?? 0);
                if ($rowNo <= 0) {
                    continue;
                }

                if (! isset($dokumenByRow[$rowNo])) {
                    $dokumenByRow[$rowNo] = [];
                }
                $dokumenByRow[$rowNo][] = $doc;
            }
        }

        $addOnsByCategory = [];
        $nilaiAddOn = 0.0;
        if ($db->tableExists('trn_kontrak_simak_add_on')) {
            $addOnBuilder = $db->table('trn_kontrak_simak_add_on')
                ->select('id, urutan, kategori_add_on, nilai_add_on, tanggal_add_on')
                ->where('simak_id', $id)
                ->orderBy('urutan', 'ASC')
                ->orderBy('id', 'ASC');
            $this->applyNotDeletedWhere($addOnBuilder, 'trn_kontrak_simak_add_on');
            $addOns = $addOnBuilder->get()->getResultArray();

            foreach ($addOns as $row) {
                $nilaiAddOn += (float) ($row['nilai_add_on'] ?? 0);
                $kategori = $this->normalizeSimakAddOnCategory((string) ($row['kategori_add_on'] ?? 'konstruksi_fisik'));
                if (! isset($addOnsByCategory[$kategori])) {
                    $addOnsByCategory[$kategori] = [];
                }

                $addOnsByCategory[$kategori][] = [
                    'urutan' => (int) ($row['urutan'] ?? 0),
                    'kategori_add_on' => $kategori,
                    'nilai_add_on' => (float) ($row['nilai_add_on'] ?? 0),
                    'tanggal_add_on' => (string) ($row['tanggal_add_on'] ?? ''),
                ];
            }
        }

        // Calculate document completion percentages
        $kelengkapanPercentages = $this->getSimakAdministrasiKelengkapanBySimakId([$id]);
        $kelengkapanPercentage = $kelengkapanPercentages[$id] ?? [
            'lengkap_persen' => 0.0,
            'belum_sesuai_persen' => 0.0,
            'belum_verifikasi_persen' => 0.0,
            'belum_ada_persen' => 0.0,
        ];

        return view('admin/kontrak/simak_konstruksi_detail', [
            'title' => 'Detail SIMAK',
            'item' => $item,
            'addOnsByCategory' => $addOnsByCategory,
            'nilaiAddOn' => $nilaiAddOn,
            'totalKontrak' => ((float) ($item['nilai_kontrak'] ?? 0)) + $nilaiAddOn,
            'templateItems' => $templateItems,
            'verifikasiByRow' => $verifikasiByRow,
            'dokumenByRow' => $dokumenByRow,
            'kelengkapanPercentage' => $kelengkapanPercentage,
            'appSetting' => [
                'simak_max_upload_mb' => $this->getSimakMaxUploadMb(),
            ],
        ]);
    }

    public function saveSimakVerifikasi(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Anda tidak memiliki akses untuk menyimpan verifikasi SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak') || ! $db->tableExists('trn_kontrak_simak_verifikasi')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tabel verifikasi SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $templateItems = $this->getSimakTemplateItems('konstruksi');
        if ($templateItems === []) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Template verifikasi SIMAK tidak ditemukan.');
        }

        $kelengkapan = $this->request->getPost('kelengkapan_dokumen');
        $verifikasi = $this->request->getPost('verifikasi_ki');
        $keterangan = $this->request->getPost('keterangan');
        $pic = $this->request->getPost('pic');

        $kelengkapan = is_array($kelengkapan) ? $kelengkapan : [];
        $verifikasi = is_array($verifikasi) ? $verifikasi : [];
        $keterangan = is_array($keterangan) ? $keterangan : [];
        $pic = is_array($pic) ? $pic : [];

        $allowedKelengkapan = ['ada', 'tidak'];
        $allowedVerifikasi = ['sesuai', 'tidak_sesuai'];
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');

        $rowsToSave = [];
        foreach ($templateItems as $item) {
            if (($item['is_leaf'] ?? false) !== true) {
                continue;
            }

            $rowNo = (int) ($item['row_no'] ?? 0);
            if ($rowNo <= 0) {
                continue;
            }

            $kel = strtolower(trim((string) ($kelengkapan[$rowNo] ?? '')));
            $ver = strtolower(trim((string) ($verifikasi[$rowNo] ?? '')));
            $ket = trim((string) ($keterangan[$rowNo] ?? ''));
            $picValue = trim((string) ($pic[$rowNo] ?? ''));

            if (! in_array($kel, $allowedKelengkapan, true)) {
                $kel = null;
            }

            if (! in_array($ver, $allowedVerifikasi, true)) {
                $ver = null;
            }

            if ($kel === null && $ver === null && $ket === '' && $picValue === '') {
                continue;
            }

            $rowsToSave[] = [
                'simak_id' => $id,
                'row_no' => $rowNo,
                'kode' => (string) ($item['display_no'] ?? ''),
                'uraian' => (string) ($item['uraian'] ?? ''),
                'kelengkapan_dokumen' => $kel,
                'verifikasi_ki' => $ver,
                'keterangan' => $ket,
                'pic' => $picValue,
                'updated_by' => $actor,
                'updated_date' => $today,
                'updated_at' => $now,
            ];
        }

        $db->transStart();
        $db->table('trn_kontrak_simak_verifikasi')->where('simak_id', $id)->delete();

        if ($rowsToSave !== []) {
            foreach ($rowsToSave as &$row) {
                $row['created_by'] = $actor;
                $row['created_date'] = $today;
                $row['created_at'] = $now;
            }
            unset($row);

            $db->table('trn_kontrak_simak_verifikasi')->insertBatch($rowsToSave);
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Gagal menyimpan verifikasi SIMAK.');
        }

        // Notify uploader(s) via email
        try {
            $sharedItem = is_array($shared['item'] ?? null) ? $shared['item'] : [];
            $emails = [];

            $emailResponden1 = trim((string) ($sharedItem['email_responden_1'] ?? $sharedItem['email_responden'] ?? ''));
            $emailResponden2 = trim((string) ($sharedItem['email_responden_2'] ?? ''));

            foreach ([$emailResponden1, $emailResponden2] as $candidate) {
                if ($candidate === '') {
                    continue;
                }

                if (filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                    $emails[] = $candidate;
                }
            }

            $emails = array_values(array_unique($emails));

            if ($emails !== [] && class_exists('\Config\Services')) {
                $emailService = \Config\Services::email();
                if ($emailService !== null) {
                    $emailConfig = config('Email');
                    if ($emailConfig !== null) {
                        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
                        $fromName = trim((string) ($emailConfig->fromName ?? 'SIMAK')) ?: 'SIMAK';
                        if ($fromEmail === '') {
                            $host = $_SERVER['HTTP_HOST'] ?? gethostname() ?: 'example.com';
                            $fromEmail = 'no-reply@' . preg_replace('/[^a-z0-9.\-]/i', '', $host);
                        }

                        // Prefer public share link if available
                        $shareUrl = site_url('admin/kontrak/simak/konstruksi/' . $id);
                        if ($db->tableExists('trn_kontrak_simak_share')) {
                            $shareRow = $db->table('trn_kontrak_simak_share')
                                ->select('share_token')
                                ->where('simak_id', $id)
                                ->where('is_active', 1)
                                ->orderBy('id', 'DESC')
                                ->get()
                                ->getRowArray();
                            if (is_array($shareRow) && ! empty($shareRow['share_token'])) {
                                $shareUrl = site_url('simak/share/' . $shareRow['share_token']);
                            }
                        }

                        // Build list of poin (kode or uraian) instead of row numbers
                        $points = [];
                        $rowNos = array_map('intval', array_column($docs ?? [], 'row_no'));
                        if ($rowNos !== []) {
                            $verRows = $db->table('trn_kontrak_simak_verifikasi')
                                ->select('row_no,kode,uraian,verifikasi_ki,keterangan')
                                ->where('simak_id', $id)
                                ->whereIn('row_no', $rowNos)
                                ->get()
                                ->getResultArray();
                            $map = [];
                            foreach ($verRows as $vr) {
                                $kode = trim((string) ($vr['kode'] ?? ''));
                                $uraian = trim((string) ($vr['uraian'] ?? ''));
                                $status = (string) ($vr['verifikasi_ki'] ?? '');
                                $ketRow = trim((string) ($vr['keterangan'] ?? ''));
                                $map[(int) ($vr['row_no'] ?? 0)] = [
                                    'kode' => $kode,
                                    'uraian' => $uraian,
                                    'status' => $status,
                                    'keterangan' => $ketRow,
                                ];
                            }
                            foreach ($rowNos as $rn) {
                                $m = $map[(int) $rn] ?? null;
                                if (is_array($m) && (($m['kode'] ?? '') !== '' || ($m['uraian'] ?? '') !== '')) {
                                    $points[] = $m;
                                }
                            }
                        }

                        $subject = 'Notifikasi: Verifikasi SIMAK';
                        $message = "Verifikasi SIMAK telah disimpan.";
                        if ($points !== []) {
                            $lines = [];
                            foreach ($points as $p) {
                                $statusLabel = ($p['status'] === 'sesuai') ? 'Sesuai' : (($p['status'] === 'tidak_sesuai') ? 'Tidak Sesuai' : '-');
                                $line = '';
                                if (($p['kode'] ?? '') !== '') {
                                    $line .= $p['kode'] . '. ';
                                }
                                $line .= ($p['uraian'] ?? '-');
                                $line .= "\nStatus: " . $statusLabel;
                                if (($p['keterangan'] ?? '') !== '') {
                                    $line .= "\nKeterangan: " . $p['keterangan'];
                                }
                                $lines[] = $line;
                            }
                            $message .= "\n\nPoin yang diverifikasi:\n- " . implode("\n- ", $lines);
                        }
                        $message .= "\n\nLihat: " . $shareUrl;

                        foreach ($emails as $to) {
                            try {
                                if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
                                    continue;
                                }

                                $emailService->clear(true);
                                $emailService->setFrom($fromEmail, $fromName);
                                $emailService->setTo($to);
                                $emailService->setSubject($subject);
                                $emailService->setMessage($message);
                                $emailService->send();
                            } catch (\Throwable $e) {
                                log_message('error', 'Failed to send verification notification to ' . $to . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to prepare/send verification notifications: ' . $e->getMessage());
        }

        return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('success', 'Verifikasi SIMAK berhasil disimpan.');
    }

    public function uploadSimakVerifikasiDokumen(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Anda tidak memiliki akses untuk upload dokumen verifikasi SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')
            || ! $db->tableExists('trn_kontrak_simak_verifikasi')
            || ! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tabel dokumen verifikasi SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($rowNo <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Baris verifikasi tidak valid.');
        }

        $templateItems = $this->getSimakTemplateItems('konstruksi');
        $templateByRow = [];
        foreach ($templateItems as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload hanya diizinkan pada baris hirarki terbawah.');
        }

        // Get SIMAK package info for structured folder upload
        $packageInfo = $this->getSimakPackageInfo($id, 'konstruksi');
        $namaPaket = ($packageInfo['nama_paket'] ?? '') ?: 'Tanpa Paket';
        $penyedia = ($packageInfo['penyedia'] ?? '') ?: 'Tanpa Penyedia';
        $headerUraian = (string) ($targetTemplate['display_no'] ?? '');
        $uraian = (string) ($targetTemplate['uraian'] ?? '');

        $tipeDokumen = strtolower(trim((string) $this->request->getPost('tipe_dokumen')));
        if (! in_array($tipeDokumen, ['draft', 'final'], true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tipe dokumen tidak valid.');
        }

        $selectedDoc = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id, verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', $tipeDokumen)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        $selectedDocId = is_array($selectedDoc) ? (int) ($selectedDoc['id'] ?? 0) : 0;

        $latestDraftDoc = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id, verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'draft')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        $latestFinalDoc = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id, verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'final')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $existingVerifikasi = $db->table('trn_kontrak_simak_verifikasi')
            ->select('kelengkapan_dokumen')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->get()
            ->getRowArray();

        $allowedKelengkapan = ['ada', 'tidak'];
        $allowedVerifikasi = ['sesuai', 'tidak_sesuai'];

        $kelRaw = strtolower(trim((string) $this->request->getPost('kelengkapan_dokumen')));
        $ver = strtolower(trim((string) $this->request->getPost('verifikasi_ki')));
        $ket = trim((string) $this->request->getPost('keterangan'));
        $pic = trim((string) $this->request->getPost('pic'));

        $kel = in_array($kelRaw, $allowedKelengkapan, true)
            ? $kelRaw
            : strtolower(trim((string) ($existingVerifikasi['kelengkapan_dokumen'] ?? '')));

        if (! in_array($kel, $allowedKelengkapan, true)) {
            $kel = null;
        }

        if (! in_array($ver, $allowedVerifikasi, true)) {
            $ver = null;
        }

        $file = $this->request->getFile('dokumen_file');
        $hasUpload = $file && $file->isValid() && ! $file->hasMoved();

        if ($kel === 'tidak' && $ket === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Keterangan wajib diisi jika dokumen memang tidak ada.');
        }

        if ($kel === 'ada' && ! $hasUpload && $selectedDocId <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Pilih file dokumen terlebih dahulu.');
        }

        if (! $hasUpload && $kel === null && $ver === null && $ket === '' && $pic === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tidak ada perubahan yang disimpan. Isi data atau upload file terlebih dahulu.');
        }

        $relativePath = '';
        $storedName = '';
        $summaryVerifikasi = 'belum_verifikasi';
        $isDocumentComplete = false;
        if ($tipeDokumen === 'final') {
            if ($ver === 'sesuai') {
                $summaryVerifikasi = 'sesuai';
            } elseif ($ver === 'tidak_sesuai') {
                $summaryVerifikasi = 'belum_sesuai';
            }
        } else {
            $finalCurrentStatus = strtolower(trim((string) ($latestFinalDoc['verifikasi_ki'] ?? '')));
            if ($finalCurrentStatus === 'sesuai') {
                $summaryVerifikasi = 'sesuai';
            } elseif ($finalCurrentStatus === 'tidak_sesuai') {
                $summaryVerifikasi = 'belum_sesuai';
            }
        }
        $isDocumentComplete = $ver === 'sesuai' && ($kel === 'tidak' || $hasUpload || $selectedDocId > 0);
        if ($hasUpload) {
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, $allowedExt, true)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX/ZIP/RAR.');
            }

            $originalName = (string) $file->getClientName();
            $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');
            $fileSize = (int) ($file->getSizeByUnit('b') ?? 0);

            // Upload langsung ke Google Drive tanpa simpan di server lokal (structured)
            $gdriveLink = $this->uploadFileToGoogleDriveStructured(
                file_get_contents($file->getTempName()),
                $originalName,
                $mimeType,
                $namaPaket,
                $penyedia,
                $headerUraian,
                $uraian
            );
            if ($gdriveLink === 'FAILED_UPLOAD' || $gdriveLink === 'NOT_READY') {
                log_message('error', 'uploadSimakVerifikasiDokumen - Google Drive upload failed (' . $gdriveLink . ') for: ' . $originalName);
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload dokumen gagal: Google Drive tidak tersedia. Silakan coba lagi atau hubungi admin.');
            } elseif ($gdriveLink !== null) {
                $relativePath = $gdriveLink;
                $storedName = '';
            } else {
                // Fallback: tidak ada penyimpanan lokal, upload gagal
                log_message('error', 'uploadSimakVerifikasiDokumen - No storage configured and Google Drive failed for: ' . $originalName);
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload dokumen gagal: Tidak ada penyimpanan yang dikonfigurasi.');
            }
        }

        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        $verifikasiRow = [
            'simak_id' => $id,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => $kel,
            'verifikasi_ki' => $summaryVerifikasi,
            'keterangan' => $ket,
            'pic' => $pic,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
        ];

        $db->transStart();

        if ($selectedDocId > 0) {
            $db->table('trn_kontrak_simak_verifikasi_dokumen')
                ->where('id', $selectedDocId)
                ->update([
                    'verifikasi_ki' => $ver,
                    'keterangan' => $ket,
                    'pic' => $pic,
                    'updated_by' => $actor,
                    'updated_date' => $today,
                    'updated_at' => $now,
                ]);
        }

        if (! $hasUpload && $kel === 'tidak' && $selectedDocId <= 0) {
            $placeholderName = $tipeDokumen === 'draft' ? 'Dokumen Draft Tidak Ada' : 'Dokumen Final Tidak Ada';
            $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert([
                'simak_id' => $id,
                'row_no' => $rowNo,
                'kode' => (string) ($targetTemplate['display_no'] ?? ''),
                'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
                'kelengkapan_dokumen' => $kel,
                'verifikasi_ki' => $ver,
                'keterangan' => $ket,
                'pic' => $pic,
                'file_original_name' => $placeholderName,
                'file_stored_name' => '',
                'file_relative_path' => '',
                'file_mime' => '',
                'file_size' => 0,
                'tipe_dokumen' => $tipeDokumen,
                'created_by' => $actor,
                'created_date' => $today,
                'created_at' => $now,
            ]);
        }

        $db->table('trn_kontrak_simak_verifikasi')->where('simak_id', $id)->where('row_no', $rowNo)->delete();
        $verifikasiRow['created_by'] = $actor;
        $verifikasiRow['created_date'] = $today;
        $verifikasiRow['created_at'] = $now;
        $db->table('trn_kontrak_simak_verifikasi')->insert($verifikasiRow);

        if ($hasUpload) {
            $dokumenRow = [
                'simak_id' => $id,
                'row_no' => $rowNo,
                'kode' => (string) ($targetTemplate['display_no'] ?? ''),
                'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
                'kelengkapan_dokumen' => $kel,
                'verifikasi_ki' => in_array($ver, ['sesuai', 'tidak_sesuai'], true) ? $ver : 'belum_verifikasi',
                'keterangan' => $ket,
                'pic' => $pic,
                'file_original_name' => $originalName,
                'file_stored_name' => $storedName,
                'file_relative_path' => $relativePath,
                'file_mime' => $mimeType,
                'file_size' => $fileSize,
                'tipe_dokumen' => $tipeDokumen,
                'created_by' => $actor,
                'created_date' => $today,
                'created_at' => $now,
            ];
            $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert($dokumenRow);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Gagal menyimpan upload dokumen verifikasi SIMAK.');
        }

        $message = $hasUpload
            ? 'Update verifikasi tersimpan dan dokumen berhasil diupload. Riwayat dokumen tercatat.'
            : 'Update verifikasi berhasil disimpan.';

        // Send notification email
        try {
            $emails = [];

            // Priority 1: Check for manual notification email from POST
            $notificationEmail = trim((string) ($this->request->getPost('notification_email') ?? ''));
            if ($notificationEmail !== '') {
                if (filter_var($notificationEmail, FILTER_VALIDATE_EMAIL) !== false) {
                    $emails[] = $notificationEmail;
                }
            }

            // Priority 2: If upload, extract from created_by uploader
            if ($emails === [] && $hasUpload) {
                $createdBy = trim((string) ($actor ?? ''));
                if ($createdBy !== '') {
                    if (preg_match('/<([^>]+)>/', $createdBy, $m)) {
                        $candidate = trim($m[1]);
                        if (filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                            $emails[] = $candidate;
                        }
                    } elseif (preg_match('/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i', $createdBy, $m2)) {
                        $candidate = trim($m2[1]);
                        if (filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                            $emails[] = $candidate;
                        }
                    }
                }
            }

            if ($emails !== [] && class_exists('\Config\Services')) {
                $emailService = \Config\Services::email();
                if ($emailService !== null) {
                    $emailConfig = config('Email');
                    if ($emailConfig !== null) {
                        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
                        $fromName = trim((string) ($emailConfig->fromName ?? 'SIMAK')) ?: 'SIMAK';
                        if ($fromEmail === '') {
                            $host = $_SERVER['HTTP_HOST'] ?? gethostname() ?: 'example.com';
                            $fromEmail = 'no-reply@' . preg_replace('/[^a-z0-9.\-]/i', '', $host);
                        }

                        // Prefer public share link if available
                        $shareUrl = site_url('admin/kontrak/simak/konstruksi/' . $id);
                        if ($db->tableExists('trn_kontrak_simak_share')) {
                            $shareRow = $db->table('trn_kontrak_simak_share')
                                ->select('share_token')
                                ->where('simak_id', $id)
                                ->where('is_active', 1)
                                ->orderBy('id', 'DESC')
                                ->get()
                                ->getRowArray();
                            if (is_array($shareRow) && ! empty($shareRow['share_token'])) {
                                $shareUrl = site_url('simak/share/' . $shareRow['share_token']);
                            }
                        }

                        $pointKode = trim((string) ($targetTemplate['display_no'] ?? ''));
                        $pointUraian = trim((string) ($targetTemplate['uraian'] ?? ''));
                        $pointLabel = $pointKode !== '' ? $pointKode . '. ' : '';
                        $pointLabel .= $pointUraian !== '' ? $pointUraian : ('Poin ' . $rowNo);
                        $subject = 'Notifikasi: Verifikasi dokumen SIMAK' . ($pointLabel !== '' ? ' - ' . $pointLabel : '');
                        $statusLabel = ($ver === 'sesuai') ? 'Sesuai' : (($ver === 'tidak_sesuai') ? 'Tidak Sesuai' : '-');
                        $message_email = 'Verifikasi dokumen untuk poin:' . "\n" . $pointLabel . "\nStatus: " . $statusLabel;
                        if ($ket !== '') {
                            $message_email .= "\nKeterangan: " . $ket;
                        }
                        $message_email .= "\n\nLihat: " . $shareUrl;

                        foreach ($emails as $to) {
                            try {
                                if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
                                    continue;
                                }

                                $emailService->clear(true);
                                $emailService->setFrom($fromEmail, $fromName);
                                $emailService->setTo($to);
                                $emailService->setSubject($subject);
                                $emailService->setMessage($message_email);
                                $emailService->send();
                            } catch (\Throwable $e) {
                                log_message('error', 'Failed to send verification notification to ' . $to . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to prepare/send verification notification for document: ' . $e->getMessage());
        }

        return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('success', $message);
    }

    public function adminUploadSimakDokumen(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Anda tidak memiliki akses untuk upload dokumen SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')
            || ! $db->tableExists('trn_kontrak_simak_verifikasi')
            || ! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tabel dokumen SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($rowNo <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Baris dokumen tidak valid.');
        }

        $templateItems = $this->getSimakTemplateItems('konstruksi');
        $templateByRow = [];
        foreach ($templateItems as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload hanya diizinkan pada baris terbawah.');
        }

        // Get SIMAK package info for structured folder upload
        $packageInfo = $this->getSimakPackageInfo($id, 'konstruksi');
        $namaPaket = ($packageInfo['nama_paket'] ?? '') ?: 'Tanpa Paket';
        $penyedia = ($packageInfo['penyedia'] ?? '') ?: 'Tanpa Penyedia';
        $headerUraian = (string) ($targetTemplate['display_no'] ?? '');
        $uraian = (string) ($targetTemplate['uraian'] ?? '');

        $existingVerifikasi = $db->table('trn_kontrak_simak_verifikasi')
            ->select('verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $file = $this->request->getFile('dokumen_file');
        $googleDriveLink = trim((string) $this->request->getPost('google_drive_url'));

        // Check if user submitted a Google Drive link instead of file
        $hasFile = $file !== null && $file->isValid() && !$file->hasMoved();
        $hasDriveLink = $googleDriveLink !== '';

        // Validate: cannot have both file and Google Drive link
        if ($hasFile && $hasDriveLink) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Gunakan salah satu saja: upload file atau link Google Drive.');
        }

        // Must have either file or Google Drive link
        if (!$hasFile && !$hasDriveLink) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload file atau masukkan link Google Drive.');
        }

        $maxFileSizeBytes = $this->getSimakMaxUploadBytes();
        $maxFileSizeMb = $this->getSimakMaxUploadMb();
        $isGoogleDriveLink = false;
        $originalName = '';
        $mimeType = '';
        $fileSize = 0;
        $relativePath = '';
        $storedName = '';

        // Handle Google Drive Link
        if ($hasDriveLink) {
            if (!$this->isAllowedGoogleDriveUrl($googleDriveLink)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Link Google Drive tidak valid. Gunakan link dari drive.google.com atau docs.google.com.');
            }

            $isGoogleDriveLink = true;
            $relativePath = $googleDriveLink;
            $originalName = 'Google Drive Link';
            $mimeType = 'text/uri-list';
            $fileSize = 0;

            log_message('info', 'adminUploadSimakDokumen - Using Google Drive link: ' . $googleDriveLink);
        } else {
            // Handle File Upload
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, $allowedExt, true)) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX/ZIP/RAR.');
            }

            $fileSize = (int) ($file->getSizeByUnit('b') ?? 0);
            if ($fileSize > $maxFileSizeBytes) {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))
                    ->with('error', "Ukuran file maksimal {$maxFileSizeMb}MB. Silakan upload ke Google Drive Anda dan masukkan linknya.")
                    ->with('show_google_drive_input', true);
            }

            $originalName = (string) $file->getClientName();
            $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');

            // Upload langsung ke Google Drive tanpa simpan di server lokal (structured)
            $gdriveLink = $this->uploadFileToGoogleDriveStructured(
                file_get_contents($file->getTempName()),
                $originalName,
                $mimeType,
                $namaPaket,
                $penyedia,
                $headerUraian,
                $uraian
            );
            if ($gdriveLink === 'FAILED_UPLOAD' || $gdriveLink === 'NOT_READY') {
                log_message('error', 'adminUploadSimakDokumen - Google Drive upload failed (' . $gdriveLink . ') for: ' . $originalName);
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload dokumen gagal: Google Drive tidak tersedia. Silakan coba lagi atau hubungi admin.');
            } elseif ($gdriveLink !== null) {
                $relativePath = $gdriveLink;
                $storedName = '';
            } else {
                // Fallback: tidak ada penyimpanan lokal, upload gagal
                log_message('error', 'adminUploadSimakDokumen - No storage configured and Google Drive failed for: ' . $originalName);
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload dokumen gagal: Tidak ada penyimpanan yang dikonfigurasi.');
            }
        }
        $tipeDokumen = strtolower(trim((string) $this->request->getPost('tipe_dokumen')));
        if (! in_array($tipeDokumen, ['draft', 'final'], true)) {
            $tipeDokumen = 'final';
        }

        // Admin dapat upload Final tanpa perlu baris sudah diverifikasi "Sesuai"
        if ($tipeDokumen === 'final' && ! $this->canManageKontrak()) {
            $rowVerificationStatus = strtolower(trim((string) ($existingVerifikasi['verifikasi_ki'] ?? '')));
            if ($rowVerificationStatus !== 'sesuai') {
                return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Upload Final hanya tersedia setelah baris diverifikasi Sesuai.');
            }
        }

        $existingDraftDoc = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'draft')
            ->limit(1)
            ->get()
            ->getRowArray();
        $existingFinalDoc = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'final')
            ->limit(1)
            ->get()
            ->getRowArray();
        $willHaveDraft = (is_array($existingDraftDoc) && ! empty($existingDraftDoc)) || $tipeDokumen === 'draft';
        $willHaveFinal = (is_array($existingFinalDoc) && ! empty($existingFinalDoc)) || $tipeDokumen === 'final';

        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // Admin upload: langsung set sesuai kebutuhan
        // - Draft → belum_verifikasi (perlu verifikasi manual oleh KI)
        // - Final → sesuai (admin sudah review, langsung lengkap)
        if ($tipeDokumen === 'draft') {
            $verifikasiKi = $willHaveFinal ? 'sesuai' : 'belum_verifikasi';
        } else {
            $verifikasiKi = 'sesuai';
        }

        $verifikasiRow = [
            'simak_id' => $id,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => $verifikasiKi,
            'keterangan' => 'Upload dokumen dari admin',
            'pic' => $actor,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
        ];

        $db->transStart();
        $db->table('trn_kontrak_simak_verifikasi')->where('simak_id', $id)->where('row_no', $rowNo)->delete();
        $verifikasiRow['created_by'] = $actor;
        $verifikasiRow['created_date'] = $today;
        $verifikasiRow['created_at'] = $now;
        $db->table('trn_kontrak_simak_verifikasi')->insert($verifikasiRow);

        $dokumenRow = [
            'simak_id' => $id,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => $verifikasiKi,
            'keterangan' => 'Upload dokumen dari admin',
            'pic' => $actor,
            'file_original_name' => $originalName,
            'file_stored_name' => $storedName,
            'file_relative_path' => $relativePath,
            'file_mime' => $mimeType,
            'file_size' => $fileSize,
            'tipe_dokumen' => $tipeDokumen,
            'is_google_drive_link' => $isGoogleDriveLink ? 1 : 0,
            'google_drive_source_url' => $isGoogleDriveLink ? $googleDriveLink : null,
            'original_file_id' => $isGoogleDriveLink ? $this->extractGoogleDriveFileId($googleDriveLink) : null,
            'created_by' => $actor,
            'created_date' => $today,
            'created_at' => $now,
        ];
        $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert($dokumenRow);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('error', 'Gagal menyimpan upload dokumen.');
        }

        $msg = $tipeDokumen === 'draft'
            ? 'Dokumen Draft berhasil diupload.'
            : 'Dokumen Final berhasil diupload dan otomatis terverifikasi Sesuai.';
        return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . $id))->with('success', $msg);
    }

    public function viewSimakVerifikasiDokumen(int $dokumenId)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Tabel dokumen verifikasi SIMAK belum tersedia.');
        }

        $builder = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id, simak_id, file_original_name, file_relative_path, file_mime')
            ->where('id', $dokumenId);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_verifikasi_dokumen');
        $row = $builder->get()->getRowArray();

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi'))->with('error', 'Dokumen verifikasi tidak ditemukan.');
        }

        $relativePath = trim((string) ($row['file_relative_path'] ?? ''));
        if ($this->isAllowedGoogleDriveUrl($relativePath)) {
            return redirect()->to($relativePath);
        }

        $relativePath = ltrim($relativePath, '/');
        $absPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if ($relativePath === '' || ! is_file($absPath)) {
            return redirect()->to(site_url('admin/kontrak/simak/konstruksi/' . (int) ($row['simak_id'] ?? 0)))->with('error', 'File dokumen tidak ditemukan di server.');
        }

        $mime = trim((string) ($row['file_mime'] ?? ''));
        if ($mime === '') {
            $mime = mime_content_type($absPath) ?: 'application/octet-stream';
        }

        $fileName = (string) ($row['file_original_name'] ?? basename($absPath));
        $content = file_get_contents($absPath);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($fileName) . '"')
            ->setBody($content === false ? '' : $content);
    }

    public function adminDeleteSimakDokumen(int $dokumenId)
    {
        // removed temporary adminDeleteSimakDokumen method
        return redirect()->to(site_url('/admin'))->with('error', 'Endpoint penghapusan sementara telah dihapus.');
    }

    public function sharedSimak(string $token)
    {
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            return $this->renderSharedInvalidLink(
                'Tautan share SIMAK tidak valid.',
                $token,
                null
            );
        }

        // Raw request trace: write a minimal record to a dedicated debug file
        // This bypasses CI logger configuration to ensure we capture hits.
        try {
            $traceFile = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'simak_upload_raw.log';
            $trace = [
                'time' => date('c'),
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'token' => $token,
                'post_keys' => is_array($this->request->getPost()) ? array_keys($this->request->getPost()) : [],
                'files' => is_array($_FILES) ? array_keys($_FILES) : [],
            ];
            @file_put_contents($traceFile, json_encode($trace, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $_e) {
            // swallow - logging must not break upload flow
        }

        // Calculate document completion percentages
        $simakId = (int) ($shared['item']['id'] ?? 0);
        $sharedType = (string) ($shared['type'] ?? 'konstruksi');
        $kelengkapanPercentages = $this->getSimakAdministrasiKelengkapanBySimakId([$simakId], $sharedType, false);
        $kelengkapanPercentage = $kelengkapanPercentages[$simakId] ?? [
            'lengkap_persen' => 0.0,
            'belum_sesuai_persen' => 0.0,
            'belum_verifikasi_persen' => 0.0,
            'belum_ada_persen' => 0.0,
        ];

        return view('public/simak_share_upload', [
            'title' => 'Upload Dokumen SIMAK',
            'token' => $token,
            'item' => $shared['item'],
            'templateItems' => $shared['templateItems'],
            'verifikasiByRow' => $shared['verifikasiByRow'],
            'dokumenByRow' => $shared['dokumenByRow'],
            'kelengkapanPercentage' => $kelengkapanPercentage,
            'googleClientId' => $this->getGoogleClientId(),
            'googleDriveUploadFolderId' => $this->getGoogleDriveUploadFolderId(),
            'googleDriveUploadFolderUrl' => $this->getGoogleDriveUploadFolderUrl(),
            'ciEnvironment' => trim((string) getenv('CI_ENVIRONMENT')),
            'otpState' => $this->getSharedSimakOtpState($token),
            'otpVerified' => $this->isSharedSimakOtpGranted($token),
            'otpRecipientEmails' => $this->getSharedSimakRecipientEmails($shared),
        ]);
    }

    public function sharedRequestOtp(string $token)
    {
        log_message('error', 'sharedRequestOtp - entry point: token=' . $token . ' POST=' . json_encode($this->request->getPost()));
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            return $this->renderSharedInvalidLink(
                'Tautan share SIMAK tidak valid.',
                $token,
                null
            );
        }

        $recipientEmails = $this->getSharedSimakRecipientEmails($shared);
        if ($recipientEmails === []) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Belum ada email responden yang tersimpan. Kode OTP tidak dapat dikirim.');
        }

        $otpCode = (string) random_int(100000, 999999);
        $otpState = [
            'status' => 'pending',
            'code_hash' => password_hash($otpCode, PASSWORD_DEFAULT),
            'sent_at' => time(),
            'expires_at' => time() + self::SHARED_SIMAK_OTP_CODE_TTL_SECONDS,
            'recipient_emails' => $recipientEmails,
        ];

        $sent = $this->sendSharedSimakOtpCode($shared, $otpCode, $recipientEmails);
        if (! ($sent['success'] ?? false)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', (string) ($sent['message'] ?? 'Gagal mengirim kode OTP.'));
        }

        $this->setSharedSimakOtpState($token, $otpState);

        return redirect()->to(site_url('simak/share/' . $token))->with('success', 'Kode verifikasi berhasil dikirim ke email responden yang tersimpan. Kode OTP berlaku selama 5 menit.');
    }

    public function sharedVerifyOtp(string $token)
    {
        log_message('error', 'sharedVerifyOtp - entry point: token=' . $token . ' POST=' . json_encode($this->request->getPost()));
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            return $this->renderSharedInvalidLink(
                'Tautan share SIMAK tidak valid.',
                $token,
                null
            );
        }

        $otpState = $this->getSharedSimakOtpState($token);
        if (! is_array($otpState) || ($otpState['status'] ?? '') !== 'pending') {
            $this->clearSharedSimakOtpState($token);
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Kode OTP belum dikirim atau sudah kedaluwarsa. Silakan kirim kode verifikasi terlebih dahulu.');
        }

        $expiresAt = (int) ($otpState['expires_at'] ?? 0);
        if ($expiresAt <= time()) {
            $this->clearSharedSimakOtpState($token);
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode verifikasi.');
        }

        $otpInput = preg_replace('/\D+/', '', (string) $this->request->getPost('otp_code')) ?? '';
        if (strlen($otpInput) !== 6) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Kode OTP harus terdiri dari 6 digit angka.');
        }

        $codeHash = (string) ($otpState['code_hash'] ?? '');
        if ($codeHash === '' || ! password_verify($otpInput, $codeHash)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Kode OTP tidak valid.');
        }

        $otpState['status'] = 'verified';
        $otpState['verified_at'] = time();
        $otpState['last_activity_at'] = time();
        $otpState['expires_at'] = time() + self::SHARED_SIMAK_OTP_SESSION_TTL_SECONDS;
        unset($otpState['code_hash']);
        $this->setSharedSimakOtpState($token, $otpState);

        return redirect()->to(site_url('simak/share/' . $token))->with('success', 'Kode OTP berhasil diverifikasi. Akses halaman aktif selama 20 menit dan akan diperpanjang selama ada aktivitas.');
    }

    public function sharedTouchOtp(string $token)
    {
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Tautan share SIMAK tidak valid.',
            ]);
        }

        if (! $this->isSharedSimakOtpGranted($token, true)) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'expired',
                'message' => 'Sesi OTP tidak aktif. Silakan verifikasi ulang OTP.',
            ]);
        }

        $state = $this->getSharedSimakOtpState($token);
        $expiresAt = (int) ($state['expires_at'] ?? 0);

        return $this->response->setJSON([
            'status' => 'ok',
            'expires_at' => $expiresAt,
            'expires_in' => max(0, $expiresAt - time()),
        ]);
    }

    public function sharedUploadSimakDokumen(string $token)
    {
        $debugInfo = [
            'token' => $token,
            'post_keys' => array_keys($this->request->getPost()),
            'files_keys' => is_array($_FILES) ? array_keys($_FILES) : [],
            'otp_session_key' => $this->getSharedSimakOtpSessionKey($token),
            'otp_granted' => false,
            'step' => 'start',
        ];
        log_message('error', 'sharedUploadSimakDokumen - entry: ' . json_encode($debugInfo));

        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            log_message('error', 'sharedUploadSimakDokumen - invalid token');
            return $this->renderSharedInvalidLink(
                'Tautan share SIMAK tidak valid.',
                $token,
                null
            );
        }

        if ($this->isPostBodyTooLarge()) {
            log_message('error', 'sharedUploadSimakDokumen - body too large');
            return redirect()->to(site_url('simak/share/' . $token . '?error=' . rawurlencode('Upload gagal karena ukuran request melebihi batas server (post_max_size/upload_max_filesize). Perbesar batas upload di server lalu coba lagi.')));
        }

        $debugInfo['otp_granted'] = $this->isSharedSimakOtpGranted($token, false);
        log_message('error', 'sharedUploadSimakDokumen - OTP check: granted=' . ($debugInfo['otp_granted'] ? 'YES' : 'NO') . ', session_key=' . $debugInfo['otp_session_key']);

        if (! $this->isSharedSimakOtpGranted($token)) {
            log_message('error', 'sharedUploadSimakDokumen - OTP not granted');
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Kode OTP verifikasi belum valid. Silakan kirim dan masukkan kode terlebih dahulu.');
        }

        $debugInfo['step'] = 'otp_ok';

        $db = db_connect();
        $tableVerifikasi = (string) ($shared['table_verifikasi'] ?? 'trn_kontrak_simak_verifikasi');
        $tableDokumen = (string) ($shared['table_dokumen'] ?? 'trn_kontrak_simak_verifikasi_dokumen');

        if (! $db->tableExists($tableVerifikasi) || ! $db->tableExists($tableDokumen)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Tabel dokumen verifikasi SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $simakId = (int) ($shared['item']['id'] ?? 0);
        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($simakId <= 0 || $rowNo <= 0) {
            return redirect()->to(site_url('simak/share/' . $token . '?error=' . rawurlencode('Baris verifikasi tidak valid.')));
        }

        $templateByRow = [];
        foreach (($shared['templateItems'] ?? []) as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload hanya diizinkan pada baris hirarki terbawah.');
        }

        // Get SIMAK type for structured folder upload
        $sharedType = (string) ($shared['type'] ?? 'konstruksi');

        // Get SIMAK package info for structured folder upload
        $packageInfo = $this->getSimakPackageInfo($simakId, $sharedType);
        $namaPaket = ($packageInfo['nama_paket'] ?? '') ?: 'Tanpa Paket';
        $penyedia = ($packageInfo['penyedia'] ?? '') ?: 'Tanpa Penyedia';
        $headerUraian = (string) ($targetTemplate['display_no'] ?? '');
        $uraian = (string) ($targetTemplate['uraian'] ?? '');

        // Check if this row has draft requirement
        $hasDraftRow = (bool) ($targetTemplate['has_draft'] ?? false);

        $existingDokumen = $db->table($tableDokumen)
            ->select('id, tipe_dokumen, verifikasi_ki')
            ->where('simak_id', $simakId)
            ->where('row_no', $rowNo)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $existingVerifikasi = $db->table($tableVerifikasi)
            ->select('kelengkapan_dokumen, verifikasi_ki, pic')
            ->where('simak_id', $simakId)
            ->where('row_no', $rowNo)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $draftDocument = $db->table($tableDokumen)
            ->select('id, verifikasi_ki')
            ->where('simak_id', $simakId)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'draft')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        if (! is_array($draftDocument) && is_array($existingDokumen) && (string) ($targetTemplate['has_draft'] ?? '') !== '' ) {
            $existingCount = (int) $db->table($tableDokumen)
                ->where('simak_id', $simakId)
                ->where('row_no', $rowNo)
                ->countAllResults();
            if ($existingCount === 1) {
                $draftDocument = $existingDokumen;
            }
        }

        $finalDocument = $db->table($tableDokumen)
            ->select('id, verifikasi_ki')
            ->where('simak_id', $simakId)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'final')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $uploadMethod = strtolower(trim((string) $this->request->getPost('upload_method')));
        if (! in_array($uploadMethod, ['file', 'drive', 'none'], true)) {
            $uploadMethod = 'file';
        }

        $tipeDokumen = strtolower(trim((string) $this->request->getPost('tipe_dokumen')));
        if (! in_array($tipeDokumen, ['draft', 'final'], true)) {
            $tipeDokumen = 'final';
        }

        $file = $this->request->getFile('dokumen_file');
        $googleDriveLink = trim((string) $this->request->getPost('google_drive_link'));
        $keteranganTidakAda = trim((string) $this->request->getPost('keterangan'));
        $hasFile = $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
        $hasDriveLink = $googleDriveLink !== '';
        $rowVerifikasiStatus = strtolower(trim((string) ($existingVerifikasi['verifikasi_ki'] ?? '')));
        $draftCurrentStatus = strtolower(trim((string) ($draftDocument['verifikasi_ki'] ?? '')));

        // Trust the actual payload first so a stale UI selection does not push
        // a real file submission into the no-document branch.
        if ($hasFile && $uploadMethod !== 'drive') {
            $uploadMethod = 'file';
        } elseif ($hasDriveLink && $uploadMethod !== 'none') {
            $uploadMethod = 'drive';
        }

        // Debug info for logging on failure paths
        $debugInfo = [
            'token' => $token,
            'simak_id' => $simakId,
            'row_no' => $rowNo,
            'tipe_dokumen' => $tipeDokumen,
            'upload_method_posted' => (string) $this->request->getPost('upload_method'),
            'resolved_upload_method' => $uploadMethod,
            'hasFile' => $hasFile ? 1 : 0,
            'hasDriveLink' => $hasDriveLink ? 1 : 0,
            'googleDriveLink' => $googleDriveLink,
            'rowVerifikasiStatus' => $rowVerifikasiStatus,
            'draft_verifikasi' => is_array($draftDocument) ? ($draftDocument['verifikasi_ki'] ?? null) : null,
            'final_verifikasi' => is_array($finalDocument) ? ($finalDocument['verifikasi_ki'] ?? null) : null,
            'existingDokumen' => is_array($existingDokumen) ? $existingDokumen : null,
        ];

        $verifikasi = null;
        if ($tipeDokumen === 'final' && $rowVerifikasiStatus === 'sesuai') {
            $verifikasi = 'sesuai';
        }

        if ($tipeDokumen === 'draft') {
            if ($draftCurrentStatus === 'sesuai') {
                log_message('error', 'sharedUploadSimakDokumen - blocked draft upload; draft already sesuai: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload Draft tidak lagi tersedia karena draft sudah diverifikasi Sesuai.');
            }
        }

        // Final upload logic depends on has_draft:
        // - has_draft=0 (final only): Allow upload if row not yet verified (sesuai means done)
        // - has_draft=1 (draft+final): Allow upload if row verified OR draft verified (draft must come first)
        $canUploadFinal = $hasDraftRow
            ? ($rowVerifikasiStatus === 'sesuai' || $draftCurrentStatus === 'sesuai')
            : ($rowVerifikasiStatus !== 'sesuai');
        if ($tipeDokumen === 'final' && ! $canUploadFinal) {
            if ($hasDraftRow) {
                log_message('error', 'sharedUploadSimakDokumen - blocked final upload; row/draft not verified: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload Final hanya diperbolehkan setelah draft atau baris berstatus Sesuai.');
            } else {
                log_message('error', 'sharedUploadSimakDokumen - blocked final upload; row already verified: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload Final tidak lagi tersedia karena dokumen sudah diverifikasi.');
            }
        }

        if ($tipeDokumen === 'final') {
            $finalCurrentStatus = strtolower(trim((string) ($finalDocument['verifikasi_ki'] ?? '')));
            $finalIsNoFilePlaceholder = is_array($finalDocument)
                && trim((string) ($finalDocument['file_relative_path'] ?? '')) === ''
                && trim((string) ($finalDocument['file_stored_name'] ?? '')) === '';
            if ($finalCurrentStatus === 'sesuai' && ! $finalIsNoFilePlaceholder) {
                log_message('error', 'sharedUploadSimakDokumen - blocked final upload; final already verified with real file: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload Final tidak lagi tersedia karena final sudah diverifikasi Sesuai.');
            }
        }

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $actor = 'responden';

        if ($uploadMethod === 'none') {
            if ($keteranganTidakAda === '') {
                log_message('error', 'sharedUploadSimakDokumen - missing keterangan for none: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Mohon isi keterangan kenapa dokumen belum tersedia.');
            }
        } elseif ($uploadMethod === 'drive') {
            if (! $hasDriveLink) {
                log_message('error', 'sharedUploadSimakDokumen - drive selected but no link: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Pilih salah satu: upload file atau isi link Google Drive.');
            }

            if ($hasFile && $hasDriveLink) {
                log_message('error', 'sharedUploadSimakDokumen - both file and drive provided: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Gunakan salah satu saja: upload file atau link Google Drive.');
            }
        } elseif (! $hasFile) {
            log_message('error', 'sharedUploadSimakDokumen - no file selected: ' . json_encode($debugInfo));
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Pilih file dokumen terlebih dahulu.');
        }

        $storedName = '';
        $relativePath = '';
        $originalName = '';
        $mimeType = '';
        $fileSize = 0;
        $sourceLabel = 'dokumen';

        if ($uploadMethod === 'none') {
            $sourceLabel = 'keterangan dokumen memang tidak ada';
        } elseif ($hasDriveLink) {
            if (! $this->isAllowedGoogleDriveUrl($googleDriveLink)) {
                log_message('error', 'sharedUploadSimakDokumen - invalid drive link: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Link tidak valid. Gunakan link dari drive.google.com atau docs.google.com.');
            }

            $relativePath = $googleDriveLink;
            $originalName = 'Google Drive Link';
            $mimeType = 'text/uri-list';
            $sourceLabel = 'link Google Drive';
        } else {
            if (! $file || ! $file->isValid() || $file->hasMoved()) {
                log_message('error', 'sharedUploadSimakDokumen - invalid uploaded file object: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'File upload tidak valid.');
            }

            $maxFileSizeBytes = $this->getSimakMaxUploadBytes();
            $maxFileSizeMb = $this->getSimakMaxUploadMb();
            $uploadedSize = (int) ($file->getSizeByUnit('b') ?? 0);
            if ($uploadedSize <= 0 || $uploadedSize > $maxFileSizeBytes) {
                log_message('error', 'sharedUploadSimakDokumen - invalid file size: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', "Ukuran file maksimal {$maxFileSizeMb}MB.");
            }

            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, $allowedExt, true)) {
                log_message('error', 'sharedUploadSimakDokumen - unsupported extension: ' . json_encode($debugInfo));
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX/ZIP/RAR.');
            }

            $originalName = (string) $file->getClientName();
            $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');
            $fileSize = (int) ($file->getSizeByUnit('b') ?? 0);

            // Upload langsung ke Google Drive tanpa simpan di server lokal (structured)
            $gdriveLink = $this->uploadFileToGoogleDriveStructured(
                file_get_contents($file->getTempName()),
                $originalName,
                $mimeType,
                $namaPaket,
                $penyedia,
                $headerUraian,
                $uraian
            );
            if ($gdriveLink === 'FAILED_UPLOAD' || $gdriveLink === 'NOT_READY') {
                log_message('error', 'sharedUploadSimakDokumen - Google Drive upload failed (' . $gdriveLink . ') for: ' . $originalName);
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload dokumen gagal: Google Drive tidak tersedia. Silakan coba lagi atau hubungi admin.');
            } elseif ($gdriveLink !== null) {
                $uniqueHash = substr(md5(uniqid('', true)), 0, 8);
                $storedName = 'simak_' . $simakId . '_' . $rowNo . '_' . $uniqueHash . '.' . $ext;
                $relativePath = 'uploads/simak/' . $sharedType . '/' . $simakId . '/' . $storedName;
                
                $uploadMethod = 'drive';
                $googleDriveLink = $gdriveLink;

                log_message('info', 'sharedUploadSimakDokumen - Google Drive upload SUCCESS: ' . $originalName . ' -> ' . $gdriveLink);
            } else {
                // Fallback: tidak ada penyimpanan lokal, upload gagal
                log_message('error', 'sharedUploadSimakDokumen - No storage configured and Google Drive failed for: ' . $originalName);
                return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload dokumen gagal: Tidak ada penyimpanan yang dikonfigurasi.');
            }
        }

        $kelengkapanDokumen = $uploadMethod === 'none' ? 'tidak' : 'ada';
        $keterangan = $uploadMethod === 'none' ? $keteranganTidakAda : 'Menunggu Verifikasi';

        log_message('info', 'sharedUploadSimakDokumen - proceeding to save to database: ' . json_encode(['simak_id' => $simakId, 'row_no' => $rowNo, 'kelengkapan' => $kelengkapanDokumen]));

        $pic = is_array($existingVerifikasi) ? trim((string) ($existingVerifikasi['pic'] ?? '')) : '';

        $verifikasiRow = [
            'simak_id' => $simakId,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => $kelengkapanDokumen,
            'verifikasi_ki' => $verifikasi,
            'keterangan' => $keterangan,
            'pic' => $pic,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
            'created_by' => $actor,
            'created_date' => $today,
            'created_at' => $now,
        ];

        // Check if this is a Google Drive link upload
        $isGoogleDriveLink = ($uploadMethod === 'drive');

        $dokumenRow = [
            'simak_id' => $simakId,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => $kelengkapanDokumen,
            'verifikasi_ki' => $verifikasi,
            'keterangan' => $keterangan,
            'pic' => $pic,
            'file_original_name' => $originalName,
            'file_stored_name' => $storedName,
            'file_relative_path' => $relativePath,
            'file_mime' => $mimeType,
            'file_size' => $fileSize,
            'tipe_dokumen' => $tipeDokumen,
            'is_google_drive_link' => $isGoogleDriveLink ? 1 : 0,
            'google_drive_source_url' => $isGoogleDriveLink ? $googleDriveLink : null,
            'original_file_id' => $isGoogleDriveLink ? $this->extractGoogleDriveFileId($googleDriveLink) : null,
            'created_by' => $actor,
            'created_date' => $today,
            'created_at' => $now,
        ];

        $db->transStart();
        $deleteOk = $db->table($tableVerifikasi)->where('simak_id', $simakId)->where('row_no', $rowNo)->delete();
        $insertVerifikasiOk = $db->table($tableVerifikasi)->insert($verifikasiRow);

        // Insert placeholder document untuk "Tidak Ada" agar bisa diverifikasi oleh admin
        $insertDokumenOk = true;
        if ($uploadMethod === 'none') {
            $dokumenRow = [
                'simak_id' => $simakId,
                'row_no' => $rowNo,
                'kode' => (string) ($targetTemplate['display_no'] ?? ''),
                'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
                'kelengkapan_dokumen' => $kelengkapanDokumen,
                'verifikasi_ki' => null, // NULL agar bisa diverifikasi
                'keterangan' => $keterangan,
                'pic' => $pic,
                'file_original_name' => $tipeDokumen === 'draft' ? 'Dokumen Draft Tidak Ada' : 'Dokumen Final Tidak Ada',
                'file_stored_name' => '',
                'file_relative_path' => '',
                'file_mime' => '',
                'file_size' => 0,
                'tipe_dokumen' => $tipeDokumen,
                'created_by' => $actor,
                'created_date' => $today,
                'created_at' => $now,
            ];
            $insertDokumenOk = $db->table($tableDokumen)->insert($dokumenRow);
        } else {
            $insertDokumenOk = $db->table($tableDokumen)->insert($dokumenRow);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'sharedUploadSimakDokumen - transaction failed: ' . json_encode([
                'debug' => $debugInfo,
                'deleteOk' => $deleteOk,
                'insertVerifikasiOk' => $insertVerifikasiOk,
                'insertDokumenOk' => $insertDokumenOk,
                'dbError' => $db->error(),
            ], JSON_UNESCAPED_SLASHES));
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Gagal menyimpan upload dokumen.');
        }

        log_message('info', 'sharedUploadSimakDokumen - transaction success: ' . json_encode([
            'debug' => $debugInfo,
            'deleteOk' => $deleteOk,
            'insertVerifikasiOk' => $insertVerifikasiOk,
            'insertDokumenOk' => $insertDokumenOk,
        ], JSON_UNESCAPED_SLASHES));

        if ($uploadMethod === 'none') {
            return redirect()->to(site_url('simak/share/' . $token))->with('success', 'Keterangan dokumen memang tidak ada berhasil disimpan. Status kelengkapan dokumen diperbarui menjadi Tidak Ada.');
        }

        return redirect()->to(site_url('simak/share/' . $token))->with('success', ucfirst($sourceLabel) . ' ' . $tipeDokumen . ' berhasil dikirim. Status kelengkapan dokumen diperbarui menjadi Ada dan Verifikasi Dit. KI menjadi Menunggu Verifikasi.');
    }

    public function sharedDownloadDokumen(string $token, int $dokumenId)
    {
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            return $this->renderSharedInvalidLink(
                'Tautan share SIMAK tidak valid atau sudah kedaluwarsa.',
                $token,
                null
            );
        }

        if (! $this->isSharedSimakOtpGranted($token)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Kode OTP verifikasi belum valid. Silakan verifikasi kode terlebih dahulu.');
        }

        $db = db_connect();
        $tableDokumen = (string) ($shared['table_dokumen'] ?? 'trn_kontrak_simak_verifikasi_dokumen');
        if (! $db->tableExists($tableDokumen)) {
            return $this->renderSharedDokumenNotFound(
                $token,
                'Data dokumen verifikasi belum tersedia. Silakan hubungi admin.',
                is_array($shared['item'] ?? null) ? $shared['item'] : null
            );
        }

        $dokumen = $db->table($tableDokumen)
            ->select('*')
            ->where('id', $dokumenId)
            ->where('simak_id', (int) ($shared['item']['id'] ?? 0))
            ->get()
            ->getRowArray();

        if (! is_array($dokumen)) {
            return $this->renderSharedDokumenNotFound(
                $token,
                'Dokumen yang Anda cari tidak ditemukan atau sudah tidak tersedia.',
                is_array($shared['item'] ?? null) ? $shared['item'] : null
            );
        }

        $relativePath = trim((string) ($dokumen['file_relative_path'] ?? ''));
        $originalName = (string) ($dokumen['file_original_name'] ?? 'dokumen');
        $mimeType = trim((string) ($dokumen['file_mime'] ?? ''));

        if ($this->isAllowedGoogleDriveUrl($relativePath)) {
            return redirect()->to($relativePath);
        }

        if ($relativePath === '') {
            return $this->renderSharedDokumenNotFound(
                $token,
                'Lokasi file dokumen tidak valid. Silakan coba unggah ulang dokumen.',
                is_array($shared['item'] ?? null) ? $shared['item'] : null
            );
        }

        $filePath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_file($filePath)) {
            return $this->renderSharedDokumenNotFound(
                $token,
                'File dokumen tidak ditemukan di server. Kemungkinan file sudah dipindahkan atau dihapus.',
                is_array($shared['item'] ?? null) ? $shared['item'] : null
            );
        }

        if ($mimeType === '') {
            $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        }

        $forceDownload = strtolower(trim((string) $this->request->getGet('download'))) === '1';
        $previewable = ! $forceDownload && $this->isPreviewableSharedDokumen($mimeType, $originalName);

        if (! $previewable) {
            return $this->response->download($filePath, null)->setFileName($originalName);
        }

        $content = file_get_contents($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($originalName) . '"')
            ->setBody($content === false ? '' : $content);
    }

    private function getSharedSimakOtpSessionKey(string $token): string
    {
        return 'shared_simak_otp_' . sha1(trim($token));
    }

    private function getSharedSimakOtpState(string $token): ?array
    {
        $state = session()->get($this->getSharedSimakOtpSessionKey($token));
        if (! is_array($state)) {
            return null;
        }

        $expiresAt = (int) ($state['expires_at'] ?? 0);
        if ($expiresAt > 0 && $expiresAt <= time()) {
            $this->clearSharedSimakOtpState($token);
            return null;
        }

        return $state;
    }

    private function setSharedSimakOtpState(string $token, array $state): void
    {
        session()->set($this->getSharedSimakOtpSessionKey($token), $state);
    }

    private function clearSharedSimakOtpState(string $token): void
    {
        session()->remove($this->getSharedSimakOtpSessionKey($token));
    }

    private function isSharedSimakOtpGranted(string $token, bool $touchActivity = true): bool
    {
        // Bypass OTP untuk admin/editor yang sudah login
        if ($this->canViewKontrak()) {
            return true;
        }

        // Bypass OTP untuk testing
        if (self::SHARED_SIMAK_OTP_BYPASS) {
            return true;
        }

        // Debug: log session info
        $sessionKey = $this->getSharedSimakOtpSessionKey($token);
        $sessionData = session()->get($sessionKey);
        $sessionId = session()->sessionID ?? 'no_session';
        $cookies = $this->request->getCookie();
        log_message('error', 'isSharedSimakOtpGranted - DEBUG: session_key=' . $sessionKey . ', session_id=' . $sessionId . ', has_session_data=' . (is_array($sessionData) ? 'YES' : 'NO') . ', cookies_count=' . count($cookies) . ', cookie_names=' . json_encode(array_keys($cookies)) . ', state=' . json_encode($sessionData));

        $state = $this->getSharedSimakOtpState($token);
        if (! is_array($state)
            || ($state['status'] ?? '') !== 'verified'
            || (int) ($state['expires_at'] ?? 0) <= time()) {
            log_message('error', 'isSharedSimakOtpGranted - OTP not granted: state_is_array=' . (is_array($state) ? 'YES' : 'NO') . ', status=' . ($state['status'] ?? 'NULL') . ', expires_at=' . ($state['expires_at'] ?? 'NULL'));
            return false;
        }

        if ($touchActivity) {
            $now = time();
            $state['last_activity_at'] = $now;
            $state['expires_at'] = $now + self::SHARED_SIMAK_OTP_SESSION_TTL_SECONDS;
            $this->setSharedSimakOtpState($token, $state);
        }

        return true;
    }

    private function getSharedSimakRecipientEmails(array $shared): array
    {
        $item = is_array($shared['item'] ?? null) ? $shared['item'] : [];
        $emails = [
            trim((string) ($item['email_responden_1'] ?? ($item['email_responden'] ?? ''))),
            trim((string) ($item['email_responden_2'] ?? '')),
        ];

        return array_values(array_unique(array_filter($emails, static function (string $email): bool {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        })));
    }

    private function sendSharedSimakOtpCode(array $shared, string $otpCode, array $recipientEmails): array
    {
        $recipientEmails = array_values(array_unique(array_filter($recipientEmails, static function (string $email): bool {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        })));

        if ($recipientEmails === []) {
            return [
                'success' => false,
                'message' => 'Belum ada email responden yang valid untuk dikirimi kode OTP.',
            ];
        }

        if (! class_exists('\Config\Services')) {
            return [
                'success' => false,
                'message' => 'Layanan email belum tersedia di server.',
            ];
        }

        $emailService = \Config\Services::email();
        $emailConfig = config('Email');
        if ($emailService === null || $emailConfig === null) {
            return [
                'success' => false,
                'message' => 'Konfigurasi email belum tersedia.',
            ];
        }

        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
        $fromName = trim((string) ($emailConfig->fromName ?? 'SIMAK')) ?: 'SIMAK';
        if ($fromEmail === '') {
            $host = $_SERVER['HTTP_HOST'] ?? (gethostname() ?: 'example.com');
            $fromEmail = 'no-reply@' . preg_replace('/[^a-z0-9.\-]/i', '', $host);
        }

        $item = is_array($shared['item'] ?? null) ? $shared['item'] : [];
        $packageName = trim((string) ($item['nama_paket'] ?? 'SIMAK'));
        $contractNumber = trim((string) ($item['nomor_kontrak'] ?? ''));
        $recipientListText = implode(', ', $recipientEmails);
        $expiryText = date('d-m-Y H:i', time() + (5 * 60));

        $subject = 'Kode OTP Verifikasi SIMAK';
        $message = "Halo,\n\n";
        $message .= "Kode OTP untuk membuka halaman SIMAK berikut telah dibuat.\n\n";
        if ($packageName !== '') {
            $message .= "Paket: " . $packageName . "\n";
        }
        if ($contractNumber !== '') {
            $message .= "Nomor Kontrak: " . $contractNumber . "\n";
        }
        $message .= "Kode OTP: " . $otpCode . "\n";
        $message .= "Berlaku sampai: " . $expiryText . "\n";
        $message .= "\nKode ini hanya berlaku selama 5 menit sejak email dikirim.\n";
        $message .= "Jika Anda tidak meminta kode ini, abaikan pesan ini.\n";
        $message .= "\nTujuan pengiriman: " . $recipientListText;

        $sentCount = 0;
        $failedEmails = [];

        foreach ($recipientEmails as $recipientEmail) {
            try {
                $emailService->clear(true);
                $emailService->setFrom($fromEmail, $fromName);
                $emailService->setTo($recipientEmail);
                $emailService->setSubject($subject);
                $emailService->setMessage($message);
                $emailService->setMailType('text');
                $success = $emailService->send(false);

                if ($success) {
                    $sentCount++;
                } else {
                    $failedEmails[] = $recipientEmail;
                }
            } catch (\Throwable $e) {
                $failedEmails[] = $recipientEmail;
                log_message('error', 'Failed to send SIMAK OTP to ' . $recipientEmail . ': ' . $e->getMessage());
            }
        }

        if ($sentCount <= 0) {
            return [
                'success' => false,
                'message' => 'Kode OTP gagal dikirim ke seluruh email responden.',
            ];
        }

        if ($failedEmails !== []) {
            return [
                'success' => true,
                'message' => 'Kode OTP berhasil dikirim, tetapi ada beberapa email yang gagal menerima pesan: ' . implode(', ', $failedEmails),
            ];
        }

        return [
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim.',
        ];
    }

    private function isAllowedGoogleDriveUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        return in_array($host, ['drive.google.com', 'docs.google.com'], true);
    }

    /**
     * Extract Google Drive File ID from a Google Drive URL
     */
    private function extractGoogleDriveFileId(string $url): ?string
    {
        $url = trim($url);

        // Pattern for /file/d/{fileId}/view
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for open?id={fileId}
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for drive/folders/{fileId}
        if (preg_match('/\/drive\/folders\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get template item by row number
     *
     * @param int $simakId SIMAK ID to determine type (konstruksi/konsultasi)
     * @param int $rowNo Row number to find
     * @param string $type 'konstruksi' or 'konsultasi'
     * @return array|null Template item or null if not found
     */
    private function getTemplateItemByRowNo(int $simakId, int $rowNo, string $type = 'konstruksi'): ?array
    {
        $templateItems = $this->getSimakTemplateItems($type, true);
        foreach ($templateItems as $item) {
            if ((int) ($item['row_no'] ?? 0) === $rowNo) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Salin dokumen dari Google Drive user ke Google Drive proyek
     * Flow: Buka folder GD proyek → User upload manual → User paste link baru
     */
    public function salinDokumenGoogleDrive(int $dokumenId): \CodeIgniter\HTTP\Response
    {
        // Cek permission
        if (!$this->canViewKontrak()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses.']);
        }

        $db = db_connect();

        // Tentukan tipe berdasarkan URL (konstruksi atau konsultasi)
        $currentUri = service('uri');
        $type = $currentUri->getSegment(4) ?: 'konstruksi'; // Default ke konstruksi
        $type = ($type === 'konsultasi') ? 'konsultasi' : 'konstruksi';

        $tableName = ($type === 'konsultasi')
            ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen'
            : 'trn_kontrak_simak_verifikasi_dokumen';

        if (!$db->tableExists($tableName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tabel dokumen tidak tersedia.']);
        }

        $builder = $db->table($tableName)
            ->select('*')
            ->where('id', $dokumenId);
        $this->applyNotDeletedWhere($builder, $tableName);
        $dokumen = $builder->get()->getRowArray();

        if (!$dokumen) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen tidak ditemukan.']);
        }

        if (!($dokumen['is_google_drive_link'] ?? false)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen ini bukan dari Google Drive link.']);
        }

        if ($dokumen['copied_to_project_drive'] ?? false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen sudah disalin sebelumnya.']);
        }

        $googleDriveSourceUrl = trim((string) ($dokumen['google_drive_source_url'] ?? ''));
        if (empty($googleDriveSourceUrl)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Link Google Drive source tidak ditemukan.']);
        }

        // Extract file ID dan info dokumen
        $fileId = $this->extractGoogleDriveFileId($googleDriveSourceUrl);
        $originalFileName = trim((string) ($dokumen['file_original_name'] ?? ''));
        $simakId = (int) ($dokumen['simak_id'] ?? 0);
        $rowNo = (int) ($dokumen['row_no'] ?? 0);

        // Get package info untuk folder path
        $packageInfo = $this->getSimakPackageInfo($simakId, $type);
        $namaPaket = ($packageInfo['nama_paket'] ?? '') ?: 'Tanpa Paket';
        $penyedia = ($packageInfo['penyedia'] ?? '') ?: 'Tanpa Penyedia';

        // Get template item untuk header dan uraian
        $templateItem = $this->getTemplateItemByRowNo($simakId, $rowNo, $type);
        $sectionKey = $templateItem ? ($templateItem['section_key'] ?? '') : '';
        $sectionTitle = $templateItem ? ($templateItem['section_title'] ?? '') : '';
        $displayNo = $templateItem ? ($templateItem['display_no'] ?? '') : ($dokumen['kode'] ?? '');
        $itemUraian = $templateItem ? ($templateItem['uraian'] ?? '') : '';

        // Build header uraian (section key/title) dan uraian lengkap untuk folder
        $headerUraian = $sectionKey !== '' ? $sectionKey : 'Lainnya';
        $uraianLengkap = ($displayNo !== '' ? $displayNo . ' - ' : '') . ($itemUraian !== '' ? $itemUraian : $originalFileName);

        // Build display path
        $folderPathDisplay = "SIMAK > {$namaPaket} > {$penyedia} > {$headerUraian} > {$uraianLengkap}";

        // Get atau buat folder di Google Drive + upload placeholder file
        $folderUrl = null;
        $fileUrl = null;

        log_message('info', "salinDokumenGoogleDrive - Attempting to create folder and upload placeholder. Path: {$folderPathDisplay}");

        // Use the helper method that supports both OAuth and Service Account
        $result = $this->uploadPlaceholderToSimakFolder(
            $namaPaket,
            $penyedia,
            $headerUraian,
            $uraianLengkap
        );

        if ($result !== null) {
            $folderUrl = $result['folder_url'];
            $fileUrl = $result['file_url'];
            $folderPathDisplay = str_replace('SIMAK/', 'SIMAK > ', str_replace('/', ' > ', $result['folder_path']));
            log_message('info', "salinDokumenGoogleDrive - SUCCESS: Folder created and placeholder uploaded. URL: {$folderUrl}");
        } else {
            log_message('error', "salinDokumenGoogleDrive - Failed to create folder/upload placeholder. Using fallback.");
        }

        // Fallback: buka folder utama jika gagal
        if (empty($folderUrl)) {
            $folderUrl = env('GOOGLE_DRIVE_UPLOAD_FOLDER_URL') ?: 'https://drive.google.com';
        }

        log_message('info', "salinDokumenGoogleDrive - Final folderUrl: {$folderUrl}, path: {$folderPathDisplay}");

        return $this->response->setJSON([
            'success' => true,
            'ready_for_upload' => true,
            'folder_url' => $folderUrl,
            'file_url' => $fileUrl,
            'folder_path' => $folderPathDisplay,
            'dokumen_id' => $dokumenId,
            'source_url' => $googleDriveSourceUrl,
            'file_name' => $originalFileName,
            'message' => 'Folder Google Drive berhasil dibuka. Upload file secara manual, lalu masukkan link baru.'
        ]);
    }

    /**
     * Upload placeholder file to SIMAK folder structure.
     * Follows the same pattern as uploadFileToGoogleDriveStructured.
     *
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header/Section key
     * @param string $uraian Uraian text
     * @return array|null ['folder_url' => string, 'file_url' => string, 'folder_path' => string]|null
     */
    private function uploadPlaceholderToSimakFolder(
        string $namaPaket,
        string $penyedia,
        string $headerUraian,
        string $uraian
    ): ?array {
        $driveFolderId = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'));
        $oauthClientId = trim((string) getenv('GOOGLE_CLIENT_ID'));
        $oauthClientSecret = trim((string) getenv('GOOGLE_CLIENT_SECRET'));

        log_message('info', 'uploadPlaceholderToSimakFolder - Entry. OAuth: ' . ($oauthClientId !== '' ? 'YES' : 'NO') . ', FolderId: ' . ($driveFolderId !== '' ? 'YES' : 'NO'));

        // Build folder path string for reference
        $folderPath = 'SIMAK/' . $namaPaket . '/' . $penyedia . '/' . $headerUraian . '/' . $uraian;

        // Prepare placeholder content
        $placeholderFileName = 'UPLOAD DISINI - ' . $uraian . '.txt';
        $placeholderContent = "UPLOAD FILE ANDA DI SINI\n\nFolder: " . $folderPath . "\n\nPetunjuk:\n1. Upload file dokumen Anda ke folder ini\n2. Pastikan akses file: \"Anyone with the link\" -> \"Viewer\"\n3. Copy link file dan paste di sistem SIMAK\n\nDokumen yang diharapkan:\n- " . $uraian . "\n";

        // Try OAuth first (for personal Gmail accounts)
        if ($oauthClientId !== '' && $oauthClientSecret !== '' && $driveFolderId !== '') {
            log_message('info', 'uploadPlaceholderToSimakFolder - Attempting OAuth flow');
            $result = $this->uploadPlaceholderToSimakFolderOAuth(
                $placeholderContent,
                $placeholderFileName,
                $driveFolderId,
                $namaPaket,
                $penyedia,
                $headerUraian,
                $uraian
            );
            if ($result !== null) {
                return $result;
            }
            log_message('error', 'uploadPlaceholderToSimakFolder - OAuth flow failed, will try Service Account');
        }

        // Fallback to Service Account
        $serviceAccountPath = trim((string) getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH'));
        log_message('info', 'uploadPlaceholderToSimakFolder - Trying Service Account. Path: ' . ($serviceAccountPath !== '' ? 'EXISTS' : 'EMPTY'));

        if ($serviceAccountPath === '' || $driveFolderId === '') {
            log_message('error', 'uploadPlaceholderToSimakFolder - Google Drive config missing.');
            return null;
        }

        if (!class_exists('\App\Libraries\GoogleDriveService')) {
            log_message('error', 'uploadPlaceholderToSimakFolder - GoogleDriveService class not found.');
            return null;
        }

        $gdrive = new \App\Libraries\GoogleDriveService();
        if (!$gdrive->isReady()) {
            $reason = $gdrive->getLastError() ?: 'Service not ready.';
            log_message('error', 'uploadPlaceholderToSimakFolder - GoogleDriveService is not ready. Reason: ' . $reason);
            return null;
        }

        // Build structured folder path using Service Account
        $targetFolderId = $gdrive->buildSimakFolderPath(
            $driveFolderId,
            $namaPaket,
            $penyedia,
            $headerUraian,
            $uraian
        );

        if ($targetFolderId === null) {
            log_message('error', 'uploadPlaceholderToSimakFolder - Failed to build folder path for: ' . $folderPath);
            return null;
        }

        // Upload placeholder file
        $webViewLink = $gdrive->uploadFileContentToFolder(
            $placeholderContent,
            $placeholderFileName,
            'text/plain',
            $targetFolderId
        );

        if ($webViewLink !== null) {
            log_message('info', 'uploadPlaceholderToSimakFolder - SUCCESS: Uploaded placeholder to: ' . $folderPath);
            return [
                'folder_url' => "https://drive.google.com/drive/folders/{$targetFolderId}",
                'file_url' => $webViewLink,
                'folder_path' => $folderPath,
            ];
        }

        $reason = $gdrive->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadPlaceholderToSimakFolder - Upload failed: ' . $reason);
        return null;
    }

    /**
     * Upload placeholder file using OAuth flow.
     *
     * @param string $content File content
     * @param string $fileName File name
     * @param string $driveFolderId Root folder ID
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header key
     * @param string $uraian Uraian text
     * @return array|null
     */
    private function uploadPlaceholderToSimakFolderOAuth(
        string $content,
        string $fileName,
        string $driveFolderId,
        string $namaPaket,
        string $penyedia,
        string $headerUraian,
        string $uraian
    ): ?array {
        if (!class_exists('\App\Libraries\GoogleOAuthService')) {
            log_message('error', 'uploadPlaceholderToSimakFolderOAuth - GoogleOAuthService class not found.');
            return null;
        }

        $oauth = new \App\Libraries\GoogleOAuthService();
        $isAuth = $oauth->isAuthenticated();

        if (!$isAuth) {
            log_message('error', 'uploadPlaceholderToSimakFolderOAuth - Not authenticated with Google.');
            return null;
        }

        // Build structured folder path
        $targetFolderId = $oauth->buildSimakFolderPath(
            $driveFolderId,
            $namaPaket,
            $penyedia,
            $headerUraian,
            $uraian
        );

        if ($targetFolderId === null) {
            log_message('error', 'uploadPlaceholderToSimakFolderOAuth - Failed to build folder path');
            return null;
        }

        // Upload placeholder file
        $webViewLink = $oauth->uploadFileContentToFolder($content, $fileName, 'text/plain', $targetFolderId);

        if ($webViewLink !== null) {
            log_message('info', 'uploadPlaceholderToSimakFolderOAuth - SUCCESS: Uploaded placeholder via OAuth');
            return [
                'folder_url' => "https://drive.google.com/drive/folders/{$targetFolderId}",
                'file_url' => $webViewLink,
                'folder_path' => 'SIMAK/' . $namaPaket . '/' . $penyedia . '/' . $headerUraian . '/' . $uraian,
            ];
        }

        log_message('error', 'uploadPlaceholderToSimakFolderOAuth - Upload failed: ' . ($oauth->getLastError() ?: 'Unknown'));
        return null;
    }

    /**
     * Simpan link baru setelah user upload manual
     */
    public function simpanLinkBaruGoogleDrive(int $dokumenId): \CodeIgniter\HTTP\Response
    {
        // Cek permission
        if (!$this->canViewKontrak()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses.']);
        }

        $newLink = trim((string) $this->request->getPost('new_google_drive_url'));

        if (empty($newLink)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Link Google Drive baru wajib diisi.']);
        }

        // Validasi link Google Drive
        if (!$this->isAllowedGoogleDriveUrl($newLink)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Link Google Drive tidak valid. Gunakan link dari drive.google.com atau docs.google.com.']);
        }

        $db = db_connect();

        // Tentukan tipe berdasarkan URL (konstruksi atau konsultasi)
        $currentUri = service('uri');
        $type = $currentUri->getSegment(4) ?: 'konstruksi';
        $type = ($type === 'konsultasi') ? 'konsultasi' : 'konstruksi';

        $tableName = ($type === 'konsultasi')
            ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen'
            : 'trn_kontrak_simak_verifikasi_dokumen';

        if (!$db->tableExists($tableName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tabel dokumen tidak tersedia.']);
        }

        $builder = $db->table($tableName)
            ->select('*')
            ->where('id', $dokumenId);
        $this->applyNotDeletedWhere($builder, $tableName);
        $dokumen = $builder->get()->getRowArray();

        if (!$dokumen) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen tidak ditemukan.']);
        }

        // Update dengan link baru
        $now = date('Y-m-d H:i:s');
        $actorId = (int) (session()->get('user_id') ?? 0);

        $db->table($tableName)
            ->where('id', $dokumenId)
            ->update([
                'copied_to_project_drive' => 1,
                'copied_to_project_drive_at' => $now,
                'copied_to_project_drive_by' => $actorId,
                'file_relative_path' => $newLink,
            ]);

        log_message('info', 'simpanLinkBaruGoogleDrive - SUCCESS: Dokumen ID ' . $dokumenId . ' (type: ' . $type . ') link diupdate ke: ' . $newLink);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Link berhasil disimpan.',
            'new_url' => $newLink
        ]);
    }

    /**
     * Get or create SIMAK folder and return URL
     *
     * @param string $rootFolderId Root folder ID
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header/Section key (e.g., "A", "B", "1")
     * @param string $uraian Full uraian text for final folder (e.g., "1 - Surat Penugasan")
     * @return string Folder URL
     */
    private function getOrCreateSimakFolderUrl(string $rootFolderId, string $namaPaket, string $penyedia, string $headerUraian, string $uraian): string
    {
        if (!class_exists('\App\Libraries\GoogleDriveService')) {
            return env('GOOGLE_DRIVE_UPLOAD_FOLDER_URL') ?: 'https://drive.google.com';
        }

        try {
            $driveService = new \App\Libraries\GoogleDriveService();
            if (!$driveService->isReady()) {
                return env('GOOGLE_DRIVE_UPLOAD_FOLDER_URL') ?: 'https://drive.google.com';
            }

            // Build folder path with correct hierarchy
            // [Root] / SIMAK / [Nama Paket] / [Penyedia] / [Header Uraian] / [Uraian]
            $folderId = $driveService->buildSimakFolderPath($rootFolderId, $namaPaket, $penyedia, $headerUraian, $uraian);

            if ($folderId) {
                return "https://drive.google.com/drive/folders/{$folderId}";
            }

            return env('GOOGLE_DRIVE_UPLOAD_FOLDER_URL') ?: 'https://drive.google.com';
        } catch (\Exception $e) {
            log_message('error', 'getOrCreateSimakFolderUrl - Error: ' . $e->getMessage());
            return env('GOOGLE_DRIVE_UPLOAD_FOLDER_URL') ?: 'https://drive.google.com';
        }
    }

    private function isPreviewableSharedDokumen(string $mimeType, string $fileName): bool
    {
        $mimeType = strtolower(trim($mimeType));
        $fileName = trim($fileName);
        $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));

        if ($mimeType !== '') {
            if (str_starts_with($mimeType, 'image/')) {
                return true;
            }

            if ($mimeType === 'application/pdf') {
                return true;
            }
        }

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'], true);
    }

    private function verifyGoogleAccessToken(string $accessToken): ?array
    {
        if (strtolower(trim((string) getenv('CI_ENVIRONMENT'))) === 'development') {
            return [
                'sub' => 'dev-mode-dummy-sub',
                'name' => 'Development User',
                'email' => 'dev@localhost',
                'picture' => '',
            ];
        }

        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            return null;
        }

        $clientId = $this->getGoogleClientId();
        if ($clientId === '') {
            return null;
        }

        $tokenInfo = $this->fetchGoogleAccessTokenInfo($accessToken);
        if (! is_array($tokenInfo)) {
            return null;
        }

        $audience = trim((string) ($tokenInfo['audience'] ?? $tokenInfo['aud'] ?? $tokenInfo['issued_to'] ?? ''));
        if ($audience !== '' && $audience !== $clientId) {
            return null;
        }

        $profile = $this->fetchGoogleUserInfo($accessToken);
        if (! is_array($profile)) {
            return null;
        }

        $email = trim((string) ($profile['email'] ?? $tokenInfo['email'] ?? ''));
        $emailVerified = strtolower(trim((string) ($profile['verified_email'] ?? $tokenInfo['verified_email'] ?? $tokenInfo['email_verified'] ?? 'false')));
        if ($email === '' || ! in_array($emailVerified, ['true', '1'], true)) {
            return null;
        }

        return [
            'sub' => trim((string) ($profile['sub'] ?? $tokenInfo['sub'] ?? '')),
            'name' => trim((string) ($profile['name'] ?? $tokenInfo['name'] ?? '')),
            'email' => $email,
            'picture' => trim((string) ($profile['picture'] ?? $tokenInfo['picture'] ?? '')),
        ];
    }

    private function fetchGoogleAccessTokenInfo(string $accessToken): ?array
    {
        $url = 'https://oauth2.googleapis.com/tokeninfo?access_token=' . rawurlencode($accessToken);

        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 8,
                'connect_timeout' => 8,
                'http_errors' => false,
            ]);

            $response = $client->get($url);
            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $decoded = json_decode((string) $response->getBody(), true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 8,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $body = @file_get_contents($url, false, $context);
            if ($body === false) {
                return null;
            }

            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : null;
        }
    }

    private function fetchGoogleUserInfo(string $accessToken): ?array
    {
        $url = 'https://www.googleapis.com/oauth2/v3/userinfo';

        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 8,
                'connect_timeout' => 8,
                'http_errors' => false,
            ]);

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $decoded = json_decode((string) $response->getBody(), true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getGoogleClientId(): string
    {
        $raw = trim((string) getenv('GOOGLE_CLIENT_ID'));
        return trim($raw, " \t\n\r\0\x0B'\"");
    }

    private function getGoogleDriveUploadFolderUrl(): string
    {
        $raw = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_URL'));
        return trim($raw, " \t\n\r\0\x0B'\"");
    }

    private function getGoogleDriveUploadFolderId(): string
    {
        $rawId = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'));
        $rawId = trim($rawId, " \t\n\r\0\x0B'\"");
        if ($rawId !== '') {
            return $rawId;
        }

        return $this->extractGoogleDriveFolderId($this->getGoogleDriveUploadFolderUrl());
    }

    private function extractGoogleDriveFolderId(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $url, $matches) === 1) {
            return (string) ($matches[1] ?? '');
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return '';
        }

        $query = (string) ($parts['query'] ?? '');
        if ($query === '') {
            return '';
        }

        parse_str($query, $params);
        $id = trim((string) ($params['id'] ?? ''));
        return preg_match('/^[a-zA-Z0-9_-]+$/', $id) === 1 ? $id : '';
    }

    private function renderSharedDokumenNotFound(string $token, string $message, ?array $item = null)
    {
        return $this->response
            ->setStatusCode(404)
            ->setBody(view('public/simak_share_dokumen_not_found', [
                'title' => 'Dokumen Tidak Ditemukan',
                'token' => $token,
                'message' => $message,
                'item' => $item ?? [],
            ]));
    }

    private function renderSharedInvalidLink(string $message, string $token = '', ?array $item = null)
    {
        return $this->response
            ->setStatusCode(404)
            ->setBody(view('public/simak_share_invalid_link', [
                'title' => 'Tautan Share Tidak Valid',
                'message' => $message,
                'token' => $token,
                'item' => $item ?? [],
            ]));
    }

    private function sanitizeRichText($value): string
    {
        if (! function_exists('normalize_syarat_umum_html')) {
            helper('custom');
        }

        return normalize_syarat_umum_html($value);
    }

    private function getPaketById(int $paketId): ?array
    {
        $builder = db_connect()->table('trn_kontrak_paket')->select('id, nama_paket');
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_paket');
        $row = $builder->where('id', $paketId)->get()->getRowArray();
        return is_array($row) ? $row : null;
    }

    private function getSimakSharePublicUrlBySimakId(array $simakIds, string $type = 'konstruksi'): array
    {
        $simakIds = array_values(array_unique(array_filter(array_map('intval', $simakIds), static function (int $id): bool {
            return $id > 0;
        })));

        if ($simakIds === []) {
            return [];
        }

        $db = db_connect();
        $tableShare = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_share' : 'trn_kontrak_simak_share';
        if (! $db->tableExists($tableShare)) {
            return [];
        }
        $shareHasExpiresCol = $this->tableHasColumn($tableShare, 'expires_at');

        $select = 'simak_id, share_token';
        if ($shareHasExpiresCol) {
            $select .= ', expires_at';
        }

        $rows = $db->table($tableShare)
            ->select($select)
            ->whereIn('simak_id', $simakIds)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $simakId = (int) ($row['simak_id'] ?? 0);
            $token = trim((string) ($row['share_token'] ?? ''));
            $expiresAt = trim((string) ($row['expires_at'] ?? ''));
            if ($simakId <= 0 || $token === '') {
                continue;
            }

            // if ($shareHasExpiresCol && ! $this->isSimakShareActiveByDate($expiresAt)) {
            //     continue;
            // }

            $result[$simakId] = site_url('simak/share/' . $token);
        }

        return $result;
    }

    private function getSimakGdriveStatusBySimakId(array $simakIds, string $type = 'konstruksi'): array
    {
        $simakIds = array_values(array_unique(array_filter(array_map('intval', $simakIds), static function (int $id): bool {
            return $id > 0;
        })));

        if ($simakIds === []) {
            return [];
        }

        $db = db_connect();
        $tableDokumen = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen' : 'trn_kontrak_simak_verifikasi_dokumen';
        if (! $db->tableExists($tableDokumen)) {
            return array_fill_keys($simakIds, false);
        }

        // Check if file_relative_path contains Google Drive domains
        $rows = $db->table($tableDokumen)
            ->select('DISTINCT(simak_id) as skim_id')
            ->whereIn('simak_id', $simakIds)
            ->groupStart()
                ->like('file_relative_path', 'drive.google.com')
                ->orLike('file_relative_path', 'docs.google.com')
            ->groupEnd()
            ->get()
            ->getResultArray();

        $gdriveSimakIds = [];
        foreach ($rows as $row) {
            $simakId = (int) ($row['skim_id'] ?? 0);
            if ($simakId > 0) {
                $gdriveSimakIds[$simakId] = true;
            }
        }

        $result = [];
        foreach ($simakIds as $simakId) {
            $result[$simakId] = isset($gdriveSimakIds[$simakId]);
        }

        return $result;
    }

    private function generateSimakShareToken(): ?string
    {
        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_share')) {
            return null;
        }

        try {
            for ($i = 0; $i < 5; $i++) {
                $token = bin2hex(random_bytes(24));
                $exists = $db->table('trn_kontrak_simak_share')
                    ->select('id')
                    ->where('share_token', $token)
                    ->get()
                    ->getRowArray();

                if (! is_array($exists)) {
                    return $token;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function resolveSharedSimak(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $db = db_connect();
        $configs = [
            [
                'type' => 'konstruksi',
                'table_share' => 'trn_kontrak_simak_share',
                'table_simak' => 'trn_kontrak_simak',
                'table_verifikasi' => 'trn_kontrak_simak_verifikasi',
                'table_dokumen' => 'trn_kontrak_simak_verifikasi_dokumen',
            ],
            [
                'type' => 'konsultasi',
                'table_share' => 'trn_kontrak_simak_konsultasi_share',
                'table_simak' => 'trn_kontrak_simak_konsultasi',
                'table_verifikasi' => 'trn_kontrak_simak_konsultasi_verifikasi',
                'table_dokumen' => 'trn_kontrak_simak_konsultasi_verifikasi_dokumen',
            ],
        ];

        foreach ($configs as $config) {
            $tableShare = (string) ($config['table_share'] ?? '');
            $tableSimak = (string) ($config['table_simak'] ?? '');
            $tableVerifikasi = (string) ($config['table_verifikasi'] ?? '');
            $tableDokumen = (string) ($config['table_dokumen'] ?? '');

            if (! $db->tableExists($tableShare) || ! $db->tableExists($tableSimak)) {
                continue;
            }

            $shareHasExpiresCol = $this->tableHasColumn($tableShare, 'expires_at');

            $shareSelect = 'id, simak_id, share_token, is_active';
            if ($shareHasExpiresCol) {
                $shareSelect .= ', expires_at';
            }

            $shareBuilder = $db->table($tableShare)
                ->select($shareSelect)
                ->where('share_token', $token)
                ->where('is_active', 1);
            $this->applyNotDeletedWhere($shareBuilder, $tableShare);

            $share = $shareBuilder->get()->getRowArray();
            if (! is_array($share)) {
                continue;
            }

            // $expiresAt = trim((string) ($share['expires_at'] ?? ''));
            // if ($shareHasExpiresCol && ! $this->isSimakShareActiveByDate($expiresAt)) {
            //     continue;
            // }

            $shareSimakId = (int) ($share['simak_id'] ?? 0);
            if ($shareSimakId <= 0) {
                continue;
            }

            $simakBuilder = $db->table($tableSimak)->select('*')->where('id', $shareSimakId);
            $this->applyNotDeletedWhere($simakBuilder, $tableSimak);
            $item = $simakBuilder->get()->getRowArray();
            if (! is_array($item)) {
                continue;
            }

            $templateType = (string) ($config['type'] ?? 'konstruksi');
            $allTemplateItems = $this->getSimakTemplateItems($templateType);
            if ($allTemplateItems === []) {
                return null;
            }

            $templateItems = $this->getSimakTemplateItems($templateType, false);

            $verifikasiByRow = [];
            if ($db->tableExists($tableVerifikasi)) {
                $verifikasiRows = $db->table($tableVerifikasi)
                    ->select('row_no, kelengkapan_dokumen, verifikasi_ki, keterangan, pic')
                    ->where('simak_id', $shareSimakId)
                    ->orderBy('row_no', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($verifikasiRows as $row) {
                    $verifikasiByRow[(int) ($row['row_no'] ?? 0)] = $row;
                }
            }

            $dokumenByRow = [];
            if ($db->tableExists($tableDokumen)) {
                $dokumenBuilder = $db->table($tableDokumen)
                    ->select('id, row_no, file_original_name, file_relative_path, file_mime, file_size, created_at, created_by, tipe_dokumen, verifikasi_ki, is_google_drive_link, google_drive_source_url, copied_to_project_drive, copied_to_project_drive_at, copied_to_project_drive_by, original_file_id')
                    ->where('simak_id', $shareSimakId)
                    ->orderBy('row_no', 'ASC')
                    ->orderBy('id', 'DESC');
                $this->applyNotDeletedWhere($dokumenBuilder, $tableDokumen);
                $dokumenRows = $dokumenBuilder->get()->getResultArray();

                foreach ($dokumenRows as $doc) {
                    $rowNo = (int) ($doc['row_no'] ?? 0);
                    if ($rowNo <= 0) {
                        continue;
                    }

                    if (! isset($dokumenByRow[$rowNo])) {
                        $dokumenByRow[$rowNo] = [];
                    }
                    $dokumenByRow[$rowNo][] = $doc;
                }
            }

            return [
                'type' => (string) ($config['type'] ?? 'konstruksi'),
                'table_verifikasi' => $tableVerifikasi,
                'table_dokumen' => $tableDokumen,
                'share' => $share,
                'item' => $item,
                'templateItems' => $templateItems,
                'verifikasiByRow' => $verifikasiByRow,
                'dokumenByRow' => $dokumenByRow,
            ];
        }

        return null;
    }

    private function isSimakShareActiveByDate(string $expiresAt): bool
    {
        $expiresAt = trim($expiresAt);
        if ($expiresAt === '') {
            return false;
        }

        $expiresTimestamp = strtotime($expiresAt);
        if ($expiresTimestamp === false) {
            return false;
        }

        return $expiresTimestamp >= time();
    }

    private function normalizeDateValue(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function normalizeSimakJenisPekerjaanJasa(string $value): ?string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return null;
        }

        $aliases = [
            'manajemen konstruksi' => 'manajemen_konstruksi',
        ];

        if (isset($aliases[$value])) {
            $value = $aliases[$value];
        }

        $allowed = ['perencanaan', 'perancangan', 'pengawasan', 'manajemen_konstruksi', 'lainnya'];
        return in_array($value, $allowed, true) ? $value : null;
    }

    private function normalizeSimakMasaPelaksanaan(string $value): ?string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return null;
        }

        $allowed = ['syc', 'myc'];
        return in_array($value, $allowed, true) ? $value : null;
    }

    private function normalizeSimakMetodePemilihan(string $value): ?string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return null;
        }

        $aliases = [
            'pengadaan langsung' => 'pengadaan_langsung',
            'penunjukan langsung' => 'penunjukan_langsung',
        ];

        if (isset($aliases[$value])) {
            $value = $aliases[$value];
        }

        $allowed = ['pengadaan_langsung', 'penunjukan_langsung', 'seleksi'];
        return in_array($value, $allowed, true) ? $value : null;
    }

    private function normalizeSimakAddOnCategory(string $value): string
    {
        $value = strtolower(trim($value));
        if ($value === 'jasa_konsultansi') {
            return 'jasa_konsultansi';
        }

        return 'konstruksi_fisik';
    }

    private function parseMoneyToBigInt($value): int
    {
        $normalized = preg_replace('/[^0-9]/', '', (string) $value) ?? '';
        $normalized = ltrim($normalized, '0');
        if ($normalized === '') {
            return 0;
        }

        return (int) $normalized;
    }

    private function parseMoneyToFloat($value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9,\.]/', '', (string) $value) ?? '';
        $normalized = trim($normalized);
        if ($normalized === '') {
            return 0.0;
        }

        if (strpos($normalized, ',') !== false && strpos($normalized, '.') !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (strpos($normalized, ',') !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $parts = explode('.', $normalized);
            if (count($parts) > 1) {
                $decimal = end($parts);
                if (strlen((string) $decimal) === 3) {
                    $normalized = str_replace('.', '', $normalized);
                }
            }
        }

        return (float) $normalized;
    }

    private function syncSimakAddOns(int $simakId): void
    {
        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_add_on')) {
            return;
        }

        $rawValues = $this->request->getPost('add_on_values');
        $values = is_array($rawValues) ? $rawValues : [];
        $rawDates = $this->request->getPost('add_on_dates');
        $dates = is_array($rawDates) ? $rawDates : [];
        $rawCategories = $this->request->getPost('add_on_categories');
        $categories = is_array($rawCategories) ? $rawCategories : [];

        $db->table('trn_kontrak_simak_add_on')->where('simak_id', $simakId)->delete();

        $rows = [];
        $urutan = 1;
        foreach ($values as $index => $value) {
            $nominal = $this->parseMoneyToFloat($value);
            if ($nominal <= 0) {
                continue;
            }

            $tanggalAddOn = $this->normalizeDateValue((string) ($dates[$index] ?? ''));
            $kategoriAddOn = $this->normalizeSimakAddOnCategory((string) ($categories[$index] ?? 'konstruksi_fisik'));

            $rows[] = [
                'simak_id' => $simakId,
                'urutan' => $urutan++,
                'kategori_add_on' => $kategoriAddOn,
                'nilai_add_on' => $nominal,
                'tanggal_add_on' => $tanggalAddOn,
                'created_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
                'created_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        if ($rows !== []) {
            $db->table('trn_kontrak_simak_add_on')->insertBatch($rows);
        }
    }

    private function getSimakAddOnsBySimakId(string $type = 'konstruksi'): array
    {
        $db = db_connect();
        $tableAddOn = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_add_on' : 'trn_kontrak_simak_add_on';
        if (! $db->tableExists($tableAddOn)) {
            return [];
        }

        $hasKategori = $this->tableHasColumn($tableAddOn, 'kategori');
        $hasKategoriAddOn = $this->tableHasColumn($tableAddOn, 'kategori_add_on');
        $hasItemAddOn = $this->tableHasColumn($tableAddOn, 'item_add_on');
        $hasUrutan = $this->tableHasColumn($tableAddOn, 'urutan');

        $selectParts = ['simak_id', 'nilai_add_on', 'tanggal_add_on', 'id'];
        if ($hasKategori) {
            $selectParts[] = 'kategori AS kategori_group';
        } elseif ($hasKategoriAddOn) {
            $selectParts[] = 'kategori_add_on AS kategori_group';
        }

        if ($hasItemAddOn) {
            $selectParts[] = 'item_add_on AS item_label';
        }

        if ($hasUrutan) {
            $selectParts[] = 'urutan AS urutan_no';
        }

        $categoryOrderColumn = $hasKategori ? 'kategori' : ($hasKategoriAddOn ? 'kategori_add_on' : 'id');

        $builder = $db->table($tableAddOn)
            ->select(implode(', ', $selectParts))
            ->orderBy('simak_id', 'ASC')
            ->orderBy($categoryOrderColumn, 'ASC')
            ->orderBy('id', 'ASC');
        $this->applyNotDeletedWhere($builder, $tableAddOn);

        $rows = $builder->get()->getResultArray();
        $grouped = [];
        foreach ($rows as $row) {
            $simakId = (int) ($row['simak_id'] ?? 0);
            $defaultCategory = ($type === 'konsultasi') ? 'jasa_konsultansi' : 'konstruksi_fisik';
            $kategori = $this->normalizeSimakAddOnCategory((string) ($row['kategori_group'] ?? $defaultCategory));
            if (! isset($grouped[$simakId])) {
                $grouped[$simakId] = [];
            }

            if (! isset($grouped[$simakId][$kategori])) {
                $grouped[$simakId][$kategori] = [];
            }

            $itemLabel = trim((string) ($row['item_label'] ?? ''));
            if ($itemLabel === '') {
                $urutan = (int) ($row['urutan_no'] ?? 0);
                if ($urutan <= 0) {
                    $urutan = count($grouped[$simakId][$kategori]) + 1;
                }
                $itemLabel = 'Add On ' . $urutan;
            }

            $grouped[$simakId][$kategori][] = [
                'kategori' => $kategori,
                'item_add_on' => $itemLabel,
                'nilai_add_on' => (float) ($row['nilai_add_on'] ?? 0),
                'tanggal_add_on' => (string) ($row['tanggal_add_on'] ?? ''),
            ];
        }

        return $grouped;
    }

    private function getSimakAdministrasiKelengkapanBySimakId(array $simakIds, string $type = 'konstruksi', bool $includeHiddenShare = true): array
    {
        $simakIds = array_values(array_unique(array_filter(array_map('intval', $simakIds), static function (int $id): bool {
            return $id > 0;
        })));

        if ($simakIds === []) {
            return [];
        }

        $result = [];
        foreach ($simakIds as $simakId) {
            $result[$simakId] = [
                'lengkap_persen' => 0.0,
                'belum_lengkap_persen' => 0.0,
                'belum_ada_persen' => 0.0,
            ];
        }

        $templateItems = $this->getSimakTemplateItems($type, $includeHiddenShare);
        $leafRows = [];
        foreach ($templateItems as $item) {
            if (($item['is_leaf'] ?? false) !== true) {
                continue;
            }

            $rowNo = (int) ($item['row_no'] ?? 0);
            if ($rowNo > 0) {
                $leafRows[] = $rowNo;
            }
        }

        $leafRows = array_values(array_unique($leafRows));
        $totalLeafRows = count($leafRows);
        if ($totalLeafRows === 0) {
            return $result;
        }

        $db = db_connect();
        $tableVerifikasi = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi' : 'trn_kontrak_simak_verifikasi';
        $tableDokumen = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi_verifikasi_dokumen' : 'trn_kontrak_simak_verifikasi_dokumen';
        if (! $db->tableExists($tableVerifikasi)) {
            return $result;
        }

        $verifikasiBySimak = [];
        $builder = $db->table($tableVerifikasi)
            ->select('simak_id, row_no, kelengkapan_dokumen, verifikasi_ki')
            ->whereIn('simak_id', $simakIds)
            ->whereIn('row_no', $leafRows);
        $this->applyNotDeletedWhere($builder, $tableVerifikasi);

        foreach ($builder->get()->getResultArray() as $row) {
            $simakId = (int) ($row['simak_id'] ?? 0);
            $rowNo = (int) ($row['row_no'] ?? 0);
            if ($simakId <= 0 || $rowNo <= 0) {
                continue;
            }

            if (! isset($verifikasiBySimak[$simakId])) {
                $verifikasiBySimak[$simakId] = [];
            }

            $verifikasiBySimak[$simakId][$rowNo] = $row;
        }

        $dokumenBySimak = [];
        if ($db->tableExists($tableDokumen)) {
            $builder = $db->table($tableDokumen)
                ->select('simak_id, row_no, tipe_dokumen, file_relative_path, file_stored_name, verifikasi_ki')
                ->whereIn('simak_id', $simakIds)
                ->whereIn('row_no', $leafRows)
                ->orderBy('row_no', 'ASC')
                ->orderBy('id', 'DESC');
            $this->applyNotDeletedWhere($builder, $tableDokumen);

            foreach ($builder->get()->getResultArray() as $doc) {
                $simakId = (int) ($doc['simak_id'] ?? 0);
                $rowNo = (int) ($doc['row_no'] ?? 0);
                if ($simakId <= 0 || $rowNo <= 0) {
                    continue;
                }

                if (! isset($dokumenBySimak[$simakId])) {
                    $dokumenBySimak[$simakId] = [];
                }

                if (! isset($dokumenBySimak[$simakId][$rowNo])) {
                    $dokumenBySimak[$simakId][$rowNo] = [];
                }

                $dokumenBySimak[$simakId][$rowNo][] = $doc;
            }
        }

        foreach ($simakIds as $simakId) {
            $lengkapCount = 0;
            $belumSesuaiCount = 0;
            $belumVerifikasiCount = 0;
            $belumAdaCount = 0;

            foreach ($leafRows as $rowNo) {
                $templateItem = null;
                foreach ($templateItems as $item) {
                    if ((int) ($item['row_no'] ?? 0) === $rowNo) {
                        $templateItem = $item;
                        break;
                    }
                }

                $status = $this->resolveSimakRowStatus(
                    is_array($templateItem) ? $templateItem : [],
                    $verifikasiBySimak[$simakId][$rowNo] ?? null,
                    $dokumenBySimak[$simakId][$rowNo] ?? []
                );
                if ($status === 'lengkap') {
                    $lengkapCount++;
                } elseif ($status === 'belum_sesuai') {
                    $belumSesuaiCount++;
                } elseif ($status === 'belum_verifikasi') {
                    $belumVerifikasiCount++;
                } else {
                    $belumAdaCount++;
                }
            }

            $result[$simakId] = [
                'lengkap_persen' => round(($lengkapCount / $totalLeafRows) * 100, 2),
                'belum_sesuai_persen' => round(($belumSesuaiCount / $totalLeafRows) * 100, 2),
                'belum_verifikasi_persen' => round(($belumVerifikasiCount / $totalLeafRows) * 100, 2),
                'belum_ada_persen' => round(($belumAdaCount / $totalLeafRows) * 100, 2),
            ];
        }

        return $result;
    }

    private function resolveSimakRowStatus(array $templateItem, ?array $verifikasiRow, array $dokumenRows): string
    {
        $hasDraft = (bool) ($templateItem['has_draft'] ?? false);
        $rowKelengkapan = strtolower(trim((string) ($verifikasiRow['kelengkapan_dokumen'] ?? '')));
        $rowVerifikasi = strtolower(trim((string) ($verifikasiRow['verifikasi_ki'] ?? '')));

        $draftDokumen = null;
        $finalDokumen = null;
        foreach ($dokumenRows as $docRow) {
            $docType = strtolower(trim((string) ($docRow['tipe_dokumen'] ?? 'final')));
            if ($docType === 'draft' && $draftDokumen === null) {
                $draftDokumen = $docRow;
            } elseif ($docType !== 'draft' && $finalDokumen === null) {
                $finalDokumen = $docRow;
            }

            if ($draftDokumen !== null && $finalDokumen !== null) {
                break;
            }
        }

        $draftVerifikasi = is_array($draftDokumen) ? strtolower(trim((string) ($draftDokumen['verifikasi_ki'] ?? ''))) : '';
        $finalVerifikasi = is_array($finalDokumen) ? strtolower(trim((string) ($finalDokumen['verifikasi_ki'] ?? ''))) : '';
        $draftHasFile = is_array($draftDokumen) && trim((string) ($draftDokumen['file_relative_path'] ?? '')) !== '';
        $finalHasFile = is_array($finalDokumen) && trim((string) ($finalDokumen['file_relative_path'] ?? '')) !== '';
        $draftNoFilePlaceholder = $hasDraft
            && is_array($draftDokumen)
            && trim((string) ($draftDokumen['file_relative_path'] ?? '')) === ''
            && trim((string) ($draftDokumen['file_stored_name'] ?? '')) === '';
        $finalNoFilePlaceholder = is_array($finalDokumen)
            && trim((string) ($finalDokumen['file_relative_path'] ?? '')) === ''
            && trim((string) ($finalDokumen['file_stored_name'] ?? '')) === '';

        if ($hasDraft) {
            if ($draftVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }

            if ($draftVerifikasi === 'sesuai') {
                if ($finalVerifikasi === 'sesuai') {
                    return 'lengkap';
                }

                if ($finalVerifikasi === 'tidak_sesuai') {
                    return 'belum_sesuai';
                }

                if ($finalHasFile || is_array($finalDokumen) || $finalNoFilePlaceholder) {
                    return 'belum_verifikasi';
                }

                return 'belum_ada';
            }

            if ($draftVerifikasi === 'belum_verifikasi' || ($draftDokumen !== null && $draftVerifikasi === '')) {
                return 'belum_verifikasi';
            }

            if ($rowVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }

            if ($rowVerifikasi === 'sesuai') {
                return 'belum_ada';
            }

            if ($rowVerifikasi === 'belum_verifikasi') {
                return 'belum_verifikasi';
            }

            if ($finalVerifikasi === 'sesuai') {
                return 'lengkap';
            }

            if ($finalVerifikasi === 'tidak_sesuai') {
                return 'belum_sesuai';
            }

            if ($draftHasFile || $draftNoFilePlaceholder || $draftDokumen !== null) {
                return 'belum_verifikasi';
            }

            return 'belum_ada';
        }

        if ($finalVerifikasi === 'sesuai') {
            return 'lengkap';
        }

        if ($finalVerifikasi === 'tidak_sesuai') {
            return 'belum_sesuai';
        }

        if ($finalNoFilePlaceholder || $finalVerifikasi === 'belum_verifikasi' || ($finalDokumen !== null && $finalVerifikasi === '')) {
            return 'belum_verifikasi';
        }

        if ($rowKelengkapan === 'tidak' && $rowVerifikasi === 'sesuai') {
            return 'lengkap';
        }

        if ($rowVerifikasi === 'tidak_sesuai') {
            return 'belum_sesuai';
        }

        if ($rowVerifikasi === 'belum_verifikasi') {
            return 'belum_verifikasi';
        }

        if ($rowKelengkapan !== '' || $rowVerifikasi !== '') {
            return 'belum_ada';
        }

        return 'belum_ada';
    }

    private function getSimakPelaksanaanFisikTemplateItems(bool $includeHiddenShare = true): array
    {
        $masterItems = $this->getSimakKonstruksiTemplateFromMaster($includeHiddenShare);
        if ($masterItems !== []) {
            return $masterItems;
        }

        if (! $includeHiddenShare) {
            $allMasterItems = $this->getSimakKonstruksiTemplateFromMaster(true);
            if ($allMasterItems !== []) {
                return [];
            }
        }

        $filePath = WRITEPATH . 'templates/contoh_simak.xlsx';
        if (! is_file($filePath)) {
            return [];
        }

        if (! class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            return [];
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName('Daftar SIMAK Pelaksanaan Fisik');
            if ($sheet === null) {
                return [];
            }

            $items = [];
            $currentSectionKey = '';
            $currentSectionTitle = '';
            for ($row = 25; $row <= 221; $row++) {
                $colB = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
                $colC = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
                $colD = trim((string) $sheet->getCell('D' . $row)->getFormattedValue());

                if ($colC === '' && $colD === '') {
                    continue;
                }

                $displayNo = '';
                $indentLevel = 0;
                $rowType = 'detail';
                $rowPriority = 4;
                $uraian = $colD;
                $isSectionHeader = false;

                if ($colD === '' && preg_match('/^[A-Z]$/', $colB) && $colC !== '') {
                    $isSectionHeader = true;
                    $displayNo = $colB;
                    $uraian = $colC;
                    $indentLevel = 0;
                    $rowPriority = 0;
                    $currentSectionKey = $displayNo;
                    $currentSectionTitle = $uraian;
                    $rowType = 'section_header';
                } elseif ($colD === '' && $colB !== '' && $colC !== '') {
                    $displayNo = $colB;
                    $uraian = $colC;
                    $indentLevel = 1;
                    $rowPriority = 1;
                    $rowType = 'subsection_header';
                } elseif ($colD === '' && $colB === '' && $colC !== '') {
                    // Some rows in the SIMAK template store leaf descriptions in column C.
                    $displayNo = '';
                    $uraian = $colC;
                    $indentLevel = 2;
                    $rowPriority = 4;
                    $rowType = 'detail_text';
                } elseif ($colC !== '' && preg_match('/^[a-zA-Z]$/', $colC) && $colD !== '') {
                    $displayNo = $colC;
                    $uraian = $colD;
                    $indentLevel = 1;
                    $rowPriority = 2;
                    $rowType = 'subsection_item';
                } elseif ($colD !== '' && preg_match('/^([0-9]+|[a-zA-Z])([\.|\)]|\s)+\s*(.+)$/u', $colD, $matches)) {
                    $displayNo = $matches[1];
                    $uraian = $matches[3];
                    $indentLevel = 2;
                    $rowPriority = 3;
                    $rowType = 'detail_numbered';
                } elseif ($colD !== '') {
                    $displayNo = '';
                    $uraian = $colD;
                    $indentLevel = 2;
                    $rowPriority = 4;
                    $rowType = 'detail_text';
                }

                // Divider rows in section D should not become upload targets and
                // must not consume the leaf state of the item above (e.g. H/J).
                if ($rowType === 'detail_text' && preg_match('/^untuk\s+jasa\s+konsultansi/i', $uraian)) {
                    $rowType = 'separator';
                    $rowPriority = 1;
                }

                $items[] = [
                    'row_no' => $row,
                    'display_no' => $displayNo,
                    'uraian' => $uraian,
                    'is_header' => $isSectionHeader,
                    'indent_level' => $indentLevel,
                    'row_type' => $rowType,
                    'row_priority' => $rowPriority,
                    'section_key' => $currentSectionKey,
                    'section_title' => $currentSectionTitle,
                ];
            }

            $totalItems = count($items);
            for ($index = 0; $index < $totalItems; $index++) {
                $current = $items[$index];
                $currentSectionKey = (string) ($current['section_key'] ?? '');
                $currentIsHeader = (bool) ($current['is_header'] ?? false);
                $currentPriority = (int) ($current['row_priority'] ?? 4);

                $hasChildren = false;
                for ($nextIndex = $index + 1; $nextIndex < $totalItems; $nextIndex++) {
                    $next = $items[$nextIndex];
                    if ((string) ($next['section_key'] ?? '') !== $currentSectionKey) {
                        break;
                    }

                    $nextPriority = (int) ($next['row_priority'] ?? 4);
                    if ($nextPriority <= $currentPriority) {
                        break;
                    }

                    $hasChildren = true;
                    break;
                }

                $items[$index]['has_children'] = $hasChildren;
                $items[$index]['is_leaf'] = ! $hasChildren && ! $currentIsHeader;

                if (($items[$index]['row_type'] ?? '') === 'separator') {
                    $items[$index]['has_children'] = false;
                    $items[$index]['is_leaf'] = false;
                }
            }

            // Build hierarchical tree structure based on row_priority
            $tree = $this->buildTreeFromPriorities($items);
            
            // Annotate with automatic display numbers following the format: A., 1., a., -
            $annotatedTree = $this->annotateTreeDisplayNumbers($tree);
            
            // Flatten tree while preserving hierarchy information for display
            $flattened = $this->flattenTreeToItems($annotatedTree);

            return $flattened;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function buildTreeFromPriorities(array $items): array
    {
        // Build tree structure based on row_priority, treating items as having parent-child relationships
        // Items with higher priority (greater number) are children of items with lower priority
        
        // First, assign a unique id if not present
        $map = [];
        foreach ($items as $index => $item) {
            $item['id'] = $index;
            $item['parent_id'] = -1;
            $item['children'] = [];
            $map[$index] = $item;
        }

         // Build parent-child relationships based on priority and section
         $totalItems = count($items);
        foreach ($map as $index => &$item) {
            $currentPriority = (int) ($item['row_priority'] ?? 4);
            $currentSection = (string) ($item['section_key'] ?? '');

            // Find parent: the last item before this one with lower priority in same section
            for ($searchIdx = $index - 1; $searchIdx >= 0; $searchIdx--) {
                $candidate = $map[$searchIdx] ?? null;
                if ($candidate === null) {
                    continue;
                }

                $candidateSection = (string) ($candidate['section_key'] ?? '');
                if ($candidateSection !== $currentSection) {
                    // Different section, stop searching
                    break;
                }

                $candidatePriority = (int) ($candidate['row_priority'] ?? 4);
                if ($candidatePriority < $currentPriority) {
                    // Found parent
                    $item['parent_id'] = $searchIdx;
                    break;
                }
            }
        }
        unset($item);

        // Now build root array and create tree structure
        $roots = [];
        foreach ($map as $id => &$item) {
            if ((int) ($item['parent_id'] ?? -1) < 0 || ! isset($map[$item['parent_id']])) {
                $roots[] = &$map[$id];
            } else {
                $parentId = (int) $item['parent_id'];
                $map[$parentId]['children'][] = &$map[$id];
            }
        }
        unset($item);

        return $roots;
    }

    private function flattenTreeToItems(array $tree): array
    {
        $flattened = [];
        
        $walker = function (array $nodes, int $depth = 0) use (&$walker, &$flattened): void {
            foreach ($nodes as $node) {
                // Get the auto-generated display number
                $displayNo = trim((string) ($node['display_no_auto'] ?? $node['display_no'] ?? ''));
                
                // Create item for output
                $outputItem = [
                    'row_no' => (int) ($node['row_no'] ?? 0),
                    'display_no' => $displayNo,
                    'display_no_auto' => trim((string) ($node['display_no_auto'] ?? '')),
                    'uraian' => trim((string) ($node['uraian'] ?? '')),
                    'is_header' => (bool) ($node['is_header'] ?? false),
                    'indent_level' => $depth,
                    'row_type' => (string) ($node['row_type'] ?? 'detail'),
                    'row_priority' => (int) ($node['row_priority'] ?? 4),
                    'section_key' => (string) ($node['section_key'] ?? ''),
                    'section_title' => (string) ($node['section_title'] ?? ''),
                    'has_children' => ! empty($node['children']),
                    'is_leaf' => (bool) ($node['is_leaf'] ?? false),
                    'has_draft' => (bool) ($node['has_draft'] ?? false),
                ];
                
                $flattened[] = $outputItem;
                
                // Recursively process children
                if (! empty($node['children'])) {
                    $walker($node['children'], $depth + 1);
                }
            }
        };
        
        $walker($tree);
        return $flattened;
    }

    private function getSimakKonstruksiTemplateFromMaster(bool $includeHiddenShare = true): array
    {
        $db = db_connect();
        if (! $db->tableExists('mst_simak_konstruksi_item')) {
            return [];
        }

        $selectFields = ['id', 'parent_id', 'row_no', 'uraian', 'row_kind', 'has_question', 'has_draft', 'ordering'];
        // Only select display_no if column exists
        if ($this->tableHasColumn('mst_simak_konstruksi_item', 'display_no')) {
            $selectFields[] = 'display_no';
        }
        if ($this->tableHasColumn('mst_simak_konstruksi_item', 'is_hidden_share')) {
            $selectFields[] = 'is_hidden_share';
        }

        $rows = $db->table('mst_simak_konstruksi_item')
            ->select(implode(', ', $selectFields))
            ->where('is_active', 1)
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return [];
        }

        // Use trait methods to build and annotate tree with automatic numbering
        $tree = $this->annotateTreeDisplayNumbers($this->buildTree($rows));
        $flatTree = $this->flattenTree($tree);

        $flattened = [];
        $walk = static function (array $items, int $depth, string $sectionKey, string $sectionTitle) use (&$walk, &$flattened, $includeHiddenShare): void {
            foreach ($items as $item) {
                if (! $includeHiddenShare && (int) ($item['is_hidden_share'] ?? 0) === 1) {
                    continue;
                }

                $rowKind = (string) ($item['row_kind'] ?? 'question');
                $currentSectionKey = $sectionKey;
                $currentSectionTitle = $sectionTitle;

                if ($rowKind === 'section') {
                    $currentSectionKey = (string) ($item['id'] ?? '');
                    $currentSectionTitle = trim((string) ($item['uraian'] ?? ''));
                }

                $children = is_array($item['children'] ?? null) ? $item['children'] : [];
                $hasChildren = $children !== [];
                $hasQuestion = (int) ($item['has_question'] ?? 0) === 1;

                $rowType = match ($rowKind) {
                    'section' => 'section_header',
                    'group' => 'subsection_header',
                    'separator' => 'separator',
                    default => 'detail_text',
                };

                $rowPriority = match ($rowKind) {
                    'section' => 0,
                    'group' => 1,
                    'separator' => 1,
                    default => 4,
                };

                // Use auto-generated display_no_auto instead of database display_no
                $flattened[] = [
                    'row_no' => (int) ($item['row_no'] ?? 0),
                    'display_no' => trim((string) ($item['display_no_auto'] ?? $item['display_no'] ?? '')),
                    'uraian' => trim((string) ($item['uraian'] ?? '')),
                    'has_draft' => (bool) ($item['has_draft'] ?? false),
                    'is_header' => $rowKind === 'section',
                    'indent_level' => $depth,
                    'row_type' => $rowType,
                    'row_priority' => $rowPriority,
                    'section_key' => $currentSectionKey,
                    'section_title' => $currentSectionTitle,
                    'has_children' => $hasChildren,
                    'is_leaf' => $hasQuestion && ! $hasChildren,
                    'is_hidden_share' => (int) ($item['is_hidden_share'] ?? 0),
                ];

                if ($children !== []) {
                    $walk($children, $depth + 1, $currentSectionKey, $currentSectionTitle);
                }
            }
        };

        $walk($tree, 0, '', '');

        return $flattened;
    }

    private function getSimakTemplateItems(string $type = 'konstruksi', bool $includeHiddenShare = true): array
    {
        return $type === 'konsultasi'
            ? $this->getSimakKonsultasiTemplateItems($includeHiddenShare)
            : $this->getSimakPelaksanaanFisikTemplateItems($includeHiddenShare);
    }

    private function getSimakKonsultasiTemplateItems(bool $includeHiddenShare = true): array
    {
        $masterItems = $this->getSimakKonsultasiTemplateFromMaster($includeHiddenShare);
        if ($masterItems !== []) {
            return $masterItems;
        }

        if (! $includeHiddenShare) {
            $allMasterItems = $this->getSimakKonsultasiTemplateFromMaster(true);
            if ($allMasterItems !== []) {
                return [];
            }
        }

        $filePath = WRITEPATH . 'templates/contoh_simak.xlsx';
        if (! is_file($filePath)) {
            return [];
        }

        if (! class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            return [];
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName('Daftar SIMAK JK (>100juta)');
            if ($sheet === null) {
                return [];
            }

            $items = [];
            $currentSectionKey = '';
            $currentSectionTitle = '';
            $currentSubsectionKey = '';
            $lastSubsectionLetter = [];
            $sectionCounter = 0;
            $seenSectionNo = [];
            $highestRow = (int) $sheet->getHighestRow();
            $lastRelevantRow = 24;

            for ($scanRow = 24; $scanRow <= $highestRow; $scanRow++) {
                $scanColC = trim((string) $sheet->getCell('C' . $scanRow)->getFormattedValue());
                $scanColD = trim((string) $sheet->getCell('D' . $scanRow)->getFormattedValue());
                if ($scanColC !== '' || $scanColD !== '') {
                    $lastRelevantRow = $scanRow;
                }
            }

            for ($row = 24; $row <= $lastRelevantRow; $row++) {
                $colB = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
                $colC = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
                $colD = trim((string) $sheet->getCell('D' . $row)->getFormattedValue());
                $colE = trim((string) $sheet->getCell('E' . $row)->getFormattedValue());
                $colF = trim((string) $sheet->getCell('F' . $row)->getFormattedValue());
                $colG = trim((string) $sheet->getCell('G' . $row)->getFormattedValue());
                $colH = trim((string) $sheet->getCell('H' . $row)->getFormattedValue());
                $colI = trim((string) $sheet->getCell('I' . $row)->getFormattedValue());

                $upperColC = strtoupper($colC);
                $upperColD = strtoupper($colD);

                // Stop parsing once the worksheet reaches recap/sign-off area.
                if ($upperColC === 'REKAPITULASI DOKUMEN'
                    || $upperColD === 'NILAI'
                    || str_starts_with($upperColD, 'DIVERIFIKASI PADA TANGGAL')
                    || str_starts_with($upperColD, 'PPK / TIM PPK')) {
                    break;
                }

                // Skip the grid header row.
                if ($row === 24 && strtolower($colB) === 'no.' && strtolower($colC) === 'tahapan') {
                    continue;
                }

                if ($colC === '' && $colD === '') {
                    continue;
                }

                $displayNo = '';
                $indentLevel = 0;
                $rowType = 'detail';
                $rowPriority = 4;
                $uraian = $colD;
                $isSectionHeader = false;

                if ($colD === '' && preg_match('/^([A-Z])(?:[\.|\)])?$/', $colB, $sectionMatches) && $colC !== '') {
                    $isSectionHeader = true;
                    $displayNo = (string) ($sectionMatches[1] ?? $colB);
                    $sectionCounter++;
                    if ($displayNo === '' || isset($seenSectionNo[$displayNo])) {
                        $normalizedNo = chr(ord('A') + max(0, $sectionCounter - 1));
                        if (preg_match('/^[A-Z]$/', $normalizedNo)) {
                            $displayNo = $normalizedNo;
                        }
                    }
                    $seenSectionNo[$displayNo] = true;
                    $uraian = $colC;
                    $indentLevel = 0;
                    $rowPriority = 0;
                    $currentSectionKey = $displayNo . '_' . $row;
                    $currentSectionTitle = $uraian;
                    $currentSubsectionKey = '';
                    $rowType = 'section_header';
                } elseif ($colD === '' && $colB !== '' && $colC !== '') {
                    $displayNo = $colB;
                    $uraian = $colC;
                    $indentLevel = 1;
                    $rowPriority = 1;
                    $rowType = 'subsection_header';
                    $currentSubsectionKey = $currentSectionKey . '|sub_' . $row;
                } elseif ($colD === '' && $colB === '' && $colC !== '') {
                    $displayNo = '';
                    $uraian = $colC;
                    $indentLevel = 2;
                    $rowPriority = 4;
                    $rowType = 'detail_text';
                } elseif ($colC !== '' && preg_match('/^[a-zA-Z]$/', $colC) && $colD !== '') {
                    $displayNo = $colC;
                    $uraian = $colD;
                    $indentLevel = 1;
                    $rowPriority = 2;
                    $rowType = 'subsection_item';

                    // Some refreshed templates may contain duplicated letter labels
                    // within one subsection (e.g. a, b, b). Normalize to sequence.
                    if ($currentSubsectionKey !== '') {
                        $normalizedLetter = strtolower($displayNo);
                        $lastLetter = $lastSubsectionLetter[$currentSubsectionKey] ?? '';
                        if ($lastLetter !== '' && $normalizedLetter <= $lastLetter) {
                            $nextCode = ord($lastLetter) + 1;
                            if ($nextCode >= ord('a') && $nextCode <= ord('z')) {
                                $normalizedLetter = chr($nextCode);
                            }
                        }
                        $displayNo = $normalizedLetter;
                        $lastSubsectionLetter[$currentSubsectionKey] = $normalizedLetter;
                    }
                } elseif ($colD !== '' && preg_match('/^([0-9]+|[a-zA-Z])([\.|\)]|\s)+\s*(.+)$/u', $colD, $matches)) {
                    $displayNo = $matches[1];
                    $uraian = $matches[3];
                    $indentLevel = 2;
                    $rowPriority = 3;
                    $rowType = 'detail_numbered';
                } elseif ($colD !== '') {
                    $displayNo = '';
                    $uraian = $colD;
                    $indentLevel = 2;
                    $rowPriority = 4;
                    $rowType = 'detail_text';
                }

                // Divider rows in section D should not become upload targets and
                // must not consume the leaf state of the item above (e.g. H/J).
                if ($rowType === 'detail_text' && preg_match('/^untuk\s+jasa\s+konsultansi/i', $uraian)) {
                    $rowType = 'separator';
                    $rowPriority = 1;
                }

                if ($rowType === 'detail_text' && preg_match('/^kondisi\s+khusus$/i', $uraian)) {
                    $rowType = 'separator';
                    $rowPriority = 1;
                }

                if ($rowType === 'separator' && $currentSubsectionKey !== '') {
                    unset($lastSubsectionLetter[$currentSubsectionKey]);
                }

                $items[] = [
                    'row_no' => $row,
                    'display_no' => $displayNo,
                    'uraian' => $uraian,
                    'bentuk_dokumen' => $colE,
                    'referensi' => $colF,
                    'kriteria_administrasi' => $colG,
                    'kriteria_substansi' => $colH,
                    'sumber_dokumen_hasil_integrasi' => $colI,
                    'is_header' => $isSectionHeader,
                    'indent_level' => $indentLevel,
                    'row_type' => $rowType,
                    'row_priority' => $rowPriority,
                    'section_key' => $currentSectionKey,
                    'section_title' => $currentSectionTitle,
                ];
            }

            $totalItems = count($items);
            for ($index = 0; $index < $totalItems; $index++) {
                $current = $items[$index];
                $activeSectionKey = (string) ($current['section_key'] ?? '');
                $currentIsHeader = (bool) ($current['is_header'] ?? false);
                $currentPriority = (int) ($current['row_priority'] ?? 4);

                $hasChildren = false;
                for ($nextIndex = $index + 1; $nextIndex < $totalItems; $nextIndex++) {
                    $next = $items[$nextIndex];
                    if ((string) ($next['section_key'] ?? '') !== $activeSectionKey) {
                        break;
                    }

                    $nextPriority = (int) ($next['row_priority'] ?? 4);
                    if ($nextPriority <= $currentPriority) {
                        break;
                    }

                    $hasChildren = true;
                    break;
                }

                $items[$index]['has_children'] = $hasChildren;
                $items[$index]['is_leaf'] = ! $hasChildren && ! $currentIsHeader;

                if (($items[$index]['row_type'] ?? '') === 'separator') {
                    $items[$index]['has_children'] = false;
                    $items[$index]['is_leaf'] = false;
                }
            }

            // Build hierarchical tree structure based on row_priority
            $tree = $this->buildTreeFromPriorities($items);
            
            // Annotate with automatic display numbers following the format: A., 1., a., -
            $annotatedTree = $this->annotateTreeDisplayNumbers($tree);
            
            // Flatten tree while preserving hierarchy information for display
            $flattened = $this->flattenTreeToItemsKonsultasi($annotatedTree);

            return $flattened;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function flattenTreeToItemsKonsultasi(array $tree): array
    {
        $flattened = [];
        
        $walker = function (array $nodes, int $depth = 0) use (&$walker, &$flattened): void {
            foreach ($nodes as $node) {
                // Get the auto-generated display number
                $displayNo = trim((string) ($node['display_no_auto'] ?? $node['display_no'] ?? ''));
                
                // Create item for output
                $outputItem = [
                    'row_no' => (int) ($node['row_no'] ?? 0),
                    'display_no' => $displayNo,
                    'display_no_auto' => trim((string) ($node['display_no_auto'] ?? '')),
                    'uraian' => trim((string) ($node['uraian'] ?? '')),
                    'bentuk_dokumen' => trim((string) ($node['bentuk_dokumen'] ?? '')),
                    'referensi' => trim((string) ($node['referensi'] ?? '')),
                    'kriteria_administrasi' => trim((string) ($node['kriteria_administrasi'] ?? '')),
                    'kriteria_substansi' => trim((string) ($node['kriteria_substansi'] ?? '')),
                    'sumber_dokumen_hasil_integrasi' => trim((string) ($node['sumber_dokumen_hasil_integrasi'] ?? '')),
                    'is_header' => (bool) ($node['is_header'] ?? false),
                    'indent_level' => $depth,
                    'row_type' => (string) ($node['row_type'] ?? 'detail'),
                    'row_priority' => (int) ($node['row_priority'] ?? 4),
                    'section_key' => (string) ($node['section_key'] ?? ''),
                    'section_title' => (string) ($node['section_title'] ?? ''),
                    'has_children' => ! empty($node['children']),
                    'is_leaf' => (bool) ($node['is_leaf'] ?? false),
                    'has_draft' => (bool) ($node['has_draft'] ?? false),
                ];
                
                $flattened[] = $outputItem;
                
                // Recursively process children
                if (! empty($node['children'])) {
                    $walker($node['children'], $depth + 1);
                }
            }
        };
        
        $walker($tree);
        return $flattened;
    }

    private function getSimakKonsultasiTemplateFromMaster(bool $includeHiddenShare = true): array
    {
        $db = db_connect();
        if (! $db->tableExists('mst_simak_konsultasi_item')) {
            return [];
        }

        $selectFields = ['id', 'parent_id', 'row_no', 'uraian', 'bentuk_dokumen', 'referensi', 'kriteria_administrasi', 'kriteria_substansi', 'sumber_dokumen_hasil_integrasi', 'row_kind', 'has_question', 'has_draft', 'ordering'];
        // Only select display_no if column exists
        if ($this->tableHasColumn('mst_simak_konsultasi_item', 'display_no')) {
            array_splice($selectFields, 3, 0, ['display_no']);
        }
        if ($this->tableHasColumn('mst_simak_konsultasi_item', 'is_hidden_share')) {
            $selectFields[] = 'is_hidden_share';
        }

        $rows = $db->table('mst_simak_konsultasi_item')
            ->select(implode(', ', $selectFields))
            ->where('is_active', 1)
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return [];
        }

        $nodes = [];
        foreach ($rows as $row) {
            $node = $row;
            $node['children'] = [];
            $nodes[(int) $row['id']] = $node;
        }

        $roots = [];
        foreach ($nodes as $id => $node) {
            $parentId = (int) ($node['parent_id'] ?? 0);
            if ($parentId > 0 && isset($nodes[$parentId])) {
                $nodes[$parentId]['children'][] = &$nodes[$id];
            } else {
                $roots[] = &$nodes[$id];
            }
        }

        $sortTree = static function (array &$items) use (&$sortTree): void {
            usort($items, static function (array $a, array $b): int {
                $orderingCmp = ((int) ($a['ordering'] ?? 0)) <=> ((int) ($b['ordering'] ?? 0));
                if ($orderingCmp !== 0) {
                    return $orderingCmp;
                }

                return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
            });

            foreach ($items as &$item) {
                if (! empty($item['children'])) {
                    $sortTree($item['children']);
                }
            }
            unset($item);
        };

        $sortTree($roots);

        $annotatedTree = $this->annotateTreeDisplayNumbers($roots);

        $flattened = [];
        $walk = static function (array $items, int $depth, string $sectionKey, string $sectionTitle) use (&$walk, &$flattened, $includeHiddenShare): void {
            foreach ($items as $item) {
                if (! $includeHiddenShare && (int) ($item['is_hidden_share'] ?? 0) === 1) {
                    continue;
                }

                $rowKind = (string) ($item['row_kind'] ?? 'question');
                $currentSectionKey = $sectionKey;
                $currentSectionTitle = $sectionTitle;

                if ($rowKind === 'section') {
                    $currentSectionKey = (string) ($item['id'] ?? '');
                    $currentSectionTitle = trim((string) ($item['uraian'] ?? ''));
                }

                $children = is_array($item['children'] ?? null) ? $item['children'] : [];
                $hasChildren = $children !== [];
                $hasQuestion = (int) ($item['has_question'] ?? 0) === 1;

                $rowType = match ($rowKind) {
                    'section' => 'section_header',
                    'group' => 'subsection_header',
                    'separator' => 'separator',
                    default => 'detail_text',
                };

                $rowPriority = match ($rowKind) {
                    'section' => 0,
                    'group' => 1,
                    'separator' => 1,
                    default => 4,
                };

                $flattened[] = [
                    'row_no' => (int) ($item['row_no'] ?? 0),
                    'display_no' => trim((string) ($item['display_no_auto'] ?? $item['display_no'] ?? '')),
                    'display_no_auto' => trim((string) ($item['display_no_auto'] ?? '')),
                    'uraian' => trim((string) ($item['uraian'] ?? '')),
                    'bentuk_dokumen' => trim((string) ($item['bentuk_dokumen'] ?? '')),
                    'referensi' => trim((string) ($item['referensi'] ?? '')),
                    'kriteria_administrasi' => trim((string) ($item['kriteria_administrasi'] ?? '')),
                    'kriteria_substansi' => trim((string) ($item['kriteria_substansi'] ?? '')),
                    'sumber_dokumen_hasil_integrasi' => trim((string) ($item['sumber_dokumen_hasil_integrasi'] ?? '')),
                    'has_draft' => (bool) ($item['has_draft'] ?? false),
                    'is_header' => $rowKind === 'section',
                    'indent_level' => $depth,
                    'row_type' => $rowType,
                    'row_priority' => $rowPriority,
                    'section_key' => $currentSectionKey,
                    'section_title' => $currentSectionTitle,
                    'has_children' => $hasChildren,
                    'is_leaf' => $hasQuestion && ! $hasChildren,
                    'is_hidden_share' => (int) ($item['is_hidden_share'] ?? 0),
                ];

                if ($children !== []) {
                    $walk($children, $depth + 1, $currentSectionKey, $currentSectionTitle);
                }
            }
        };

        $walk($annotatedTree, 0, '', '');

        return $flattened;
    }

    private function getSimakPegawaiOptions(): array
    {
        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return [];
        }

        $builder = $db->table('mst_pegawai p')
            ->select('p.nip, p.nama, ju.jabatan AS jabatan_label')
            ->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left')
            ->where('p.nip !=', '')
            ->orderBy('p.nama', 'ASC');

        if ($this->tableHasColumn('mst_pegawai', 'is_active')) {
            $builder->where('p.is_active', 1);
        }

        return $builder->get()->getResultArray();
    }

    private function getSimakPaketOptions(): array
    {
        $db = db_connect();
        if (! $db->tableExists('mst_paket')) {
            return [];
        }

        $builder = $db->table('mst_paket p')
            ->select('p.id, p.nama_paket, p.singkatan_paket')
            ->orderBy('p.nama_paket', 'ASC');

        if ($this->tableHasColumn('mst_paket', 'is_active')) {
            $builder->where('p.is_active', 1);
        }

        return $builder->get()->getResultArray();
    }

    private function canViewKontrak(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function canManageKontrak(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function isKontrakTableReady(): bool
    {
        $db = db_connect();
        return $db->tableExists('trn_kontrak_paket')
            && $db->tableExists('trn_kontrak_ki')
            && $db->tableExists('trn_kontrak_ki_pekerjaan_baphp');
    }

    private function applyNotDeletedWhere($builder, string $table, string $field = 'deleted_at'): void
    {
        if ($this->tableHasColumn($table, 'deleted_at')) {
            $builder->where($field, null);
        }
    }


    public function exportDocument(string $type, int $kiId)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tabel kontrak belum tersedia.');
        }

        $db = db_connect();
        $kiData = $db->table('trn_kontrak_ki')
            ->select('*')
            ->where('id', $kiId)
            ->get()
            ->getRowArray();

        if (! is_array($kiData)) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Data KI tidak ditemukan.');
        }

        helper('custom');
        date_default_timezone_set('Asia/Jakarta');

        $paketId = $kiData['paket'] ?? 0;
        $paketSelect = ['nama_paket'];
        if ($this->tableHasColumn('trn_kontrak_paket', 'kop_surat_id')) {
            $paketSelect[] = 'kop_surat_id';
        }

        $paket = $db->table('trn_kontrak_paket')
            ->select($paketSelect)
            ->where('id', $paketId)
            ->get()
            ->getRowArray();

        $data = (object) array_merge($kiData, [
            'nama_paket' => $paket['nama_paket'] ?? '',
            'kop_surat_id' => isset($paket['kop_surat_id']) ? (int) $paket['kop_surat_id'] : null,
        ]);

        $validTypes = [
            'penawaran' => ['template' => 'admin/kontrak/export_pdf_penawaran', 'ext' => 'pdf'],
            'pakta-integritas' => ['template' => 'admin/kontrak/export_pdf_pakta_integritas_ki', 'ext' => 'pdf'],
            'kualifikasi' => ['template' => 'admin/kontrak/export_pdf_kualifikasi_ki', 'ext' => 'pdf'],
            'formulir-kualifikasi' => ['template' => 'admin/kontrak/export_pdf_formulir_kualifikasi_ki', 'ext' => 'pdf'],
            'boq' => ['template' => 'admin/kontrak/export_boq', 'ext' => 'xlsx'],
            'kesediaan' => ['template' => 'admin/kontrak/export_pdf_kesediaan_ki', 'ext' => 'pdf'],
            'spbbj' => ['template' => 'admin/kontrak/export_pdf_spbbj_ki', 'ext' => 'pdf'],
            'evaluasi' => ['template' => 'admin/kontrak/export_evaluasi', 'ext' => 'xlsx'],
            'baphp' => ['template' => 'admin/kontrak/export_pdf_baphp_ki', 'ext' => 'pdf'],
            'bast' => ['template' => 'admin/kontrak/export_pdf_bast_ki', 'ext' => 'pdf'],
            'spmk' => ['template' => 'admin/kontrak/export_pdf_spmk_ki', 'ext' => 'pdf'],
            'spk' => ['template' => 'admin/kontrak/export_pdf_spk_ki', 'ext' => 'pdf'],
        ];

        $type = strtolower((string) $type);
        if (! isset($validTypes[$type])) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Tipe export tidak valid.');
        }

        try {
            $config = $validTypes[$type];
            $viewData = ['data' => $data];

            if ($type === 'spk') {
                $emptySyaratUmum = (object) [
                    'laporan' => '',
                    'hasil' => '',
                    'tugas_tanggung_jawab' => '',
                ];

                $viewData['syarat_umum'] = $emptySyaratUmum;

                if ($db->tableExists('trn_syarat_umum_kontrak_ki')) {
                    $jabatanName = trim((string) ($kiData['jabatan'] ?? ''));
                    if ($jabatanName !== '') {
                        $syaratRow = $db->table('trn_syarat_umum_kontrak_ki')
                            ->where('paket_id', (int) $paketId)
                            ->where('jabatan_name', $jabatanName)
                            ->get()
                            ->getRowArray();

                        if (is_array($syaratRow)) {
                            $viewData['syarat_umum'] = (object) $syaratRow;
                        }
                    }
                }
            }

            if ($type === 'baphp' && $db->tableExists('trn_kontrak_ki_pekerjaan_baphp')) {
                $pekerjaanBuilder = $db->table('trn_kontrak_ki_pekerjaan_baphp')
                    ->select('pekerjaan')
                    ->where('id_kontrak_paket', (string) $paketId)
                    ->orderBy('id', 'ASC');
                $this->applyNotDeletedWhere($pekerjaanBuilder, 'trn_kontrak_ki_pekerjaan_baphp');
                $viewData['pekerjaan_baphp'] = $pekerjaanBuilder->get()->getResult();
            }

            if (! isset($viewData['pekerjaan_baphp'])) {
                $viewData['pekerjaan_baphp'] = [];
            }

            if ($config['ext'] === 'xlsx') {
                return view($config['template'], $viewData);
            }

            // PDF Export with Dompdf
            $html = view($config['template'], $viewData);
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = str_replace('-', '_', strtolower($type)) . '_' . $kiId . '.pdf';
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $dompdf->stream($filename, ['Attachment' => 0]);
            exit;
        } catch (\Exception $e) {
            return redirect()->to(site_url('admin/kontrak/paket'))->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        $fields = db_connect()->getFieldData($table);
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === strtolower($column)) {
                return true;
            }
        }

        return false;
    }

    private function isPostBodyTooLarge(): bool
    {
        $postMax = $this->iniSizeToBytes((string) ini_get('post_max_size'));
        $contentLength = (int) $this->request->getServer('CONTENT_LENGTH');

        return $postMax > 0 && $contentLength > 0 && $contentLength > $postMax;
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        if ($number <= 0) {
            return 0;
        }

        switch ($unit) {
            case 'g':
                return (int) round($number * 1024 * 1024 * 1024);
            case 'm':
                return (int) round($number * 1024 * 1024);
            case 'k':
                return (int) round($number * 1024);
            default:
                return (int) round($number);
        }
    }

    private function hasShellCommand(string $command): bool
    {
        $output = [];
        $exitCode = 1;
        @exec('command -v ' . escapeshellarg($command) . ' >/dev/null 2>&1', $output, $exitCode);

        return $exitCode === 0;
    }

    private function runShellCommand(string $command): array
    {
        $output = [];
        $exitCode = 1;

        @exec($command, $output, $exitCode);

        return [$exitCode === 0, trim(implode(PHP_EOL, $output))];
    }

    private function tryCompressPdfWithGhostscript(string $absPath): bool
    {
        $absPath = trim($absPath);
        if ($absPath === '' || ! is_file($absPath)) {
            return false;
        }

        $binary = $this->hasShellCommand('gs') ? 'gs' : ($this->hasShellCommand('ghostscript') ? 'ghostscript' : '');
        if ($binary === '') {
            return false;
        }

        $sourceSize = (int) (@filesize($absPath) ?: 0);
        if ($sourceSize <= 0) {
            return false;
        }

        $tempPath = $absPath . '.gs.tmp.pdf';
        if (is_file($tempPath)) {
            @unlink($tempPath);
        }

        $command = implode(' ', [
            escapeshellcmd($binary),
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/screen',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-dDetectDuplicateImages=true',
            '-dCompressFonts=true',
            '-dDownsampleColorImages=true',
            '-dDownsampleGrayImages=true',
            '-dDownsampleMonoImages=true',
            '-sOutputFile=' . escapeshellarg($tempPath),
            escapeshellarg($absPath),
        ]);

        [$ok] = $this->runShellCommand($command . ' 2>&1');
        if (! $ok || ! is_file($tempPath)) {
            @unlink($tempPath);
            return false;
        }

        $optimizedSize = (int) (@filesize($tempPath) ?: 0);
        if ($optimizedSize <= 0 || $optimizedSize >= $sourceSize) {
            @unlink($tempPath);
            return false;
        }

        @chmod($tempPath, 0644);
        if (! @rename($tempPath, $absPath)) {
            @unlink($tempPath);
            return false;
        }

        return true;
    }

    // ============================================================================
    // SIMAK Jasa Konsultansi Methods
    // ============================================================================

    public function createSimakKonsultasi()
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Anda tidak memiliki akses untuk menambah data SIMAK Konsultasi.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Tabel SIMAK Konsultasi belum tersedia. Jalankan migration.');
        }

        $satker = trim((string) $this->request->getPost('satker'));
        $ppkNip = trim((string) $this->request->getPost('ppk_nip'));
        $ppkNama = trim((string) $this->request->getPost('ppk_nama'));
        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $tahunAnggaran = trim((string) $this->request->getPost('tahun_anggaran'));
        $jenisPekerjaan = trim((string) $this->request->getPost('jenis_pekerjaan_jasa_konsultansi'));
        $masaPelaksanaan = trim((string) $this->request->getPost('masa_pelaksanaan'));
        $paguAnggaran = $this->parseMoneyToBigInt($this->request->getPost('pagu_anggaran'));
        $penyedia = trim((string) $this->request->getPost('penyedia'));
        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nilaiKontrak = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak'));
        $metodePemilihan = trim((string) $this->request->getPost('metode_pemilihan'));
        $emailResponden1 = trim((string) $this->request->getPost('email_responden_1'));
        $emailResponden2 = trim((string) $this->request->getPost('email_responden_2'));
        $paketId = (int) $this->request->getPost('paket_id');

        // Validate paket_id is required
        if ($paketId <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Pemaketan wajib dipilih.');
        }

        // Validate paket exists
        if ($db->tableExists('mst_paket')) {
            $paket = $db->table('mst_paket')->select('id')->where('id', $paketId)->get()->getRowArray();
            if (! is_array($paket)) {
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Pemaketan tidak ditemukan pada master paket.');
            }
        }

        // Validate required fields
        if ($satker === '' || $ppkNip === '' || $ppkNama === '' || $namaPaket === '' || $tahunAnggaran === '' ||
            $jenisPekerjaan === '' || $masaPelaksanaan === '' || $paguAnggaran <= 0 || $penyedia === '' ||
            $nomorKontrak === '' || $nilaiKontrak <= 0 || $metodePemilihan === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Seluruh input wajib terisi.');
        }

        if (! preg_match('/^\d{4}\s*-\s*\d{4}$/', $tahunAnggaran)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Format tahun anggaran harus seperti 2024 - 2025.');
        }

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')->select('nip, nama')->where('nip', $ppkNip)->get()->getRowArray();
            if (! is_array($pegawai)) {
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'NIP PPK tidak ditemukan pada master pegawai.');
            }
            $ppkNama = trim((string) ($pegawai['nama'] ?? $ppkNama));
        }

        // Check for duplicate nomor_kontrak
        $duplicateBuilder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('nomor_kontrak', $nomorKontrak);
        $this->applyNotDeletedWhere($duplicateBuilder, 'trn_kontrak_simak_konsultasi');
        $duplicate = $duplicateBuilder->get()->getRowArray();
        if (is_array($duplicate)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Nomor kontrak sudah digunakan.');
        }

        if ($emailResponden1 === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Email responden 1 wajib diisi.');
        }

        $payload = [
            'satker' => $satker,
            'paket_id' => $paketId,
            'ppk_nama' => $ppkNama,
            'ppk_nip' => $ppkNip,
            'nama_paket' => $namaPaket,
            'tahun_anggaran' => $tahunAnggaran,
            'jenis_pekerjaan_jasa_konsultansi' => $jenisPekerjaan,
            'masa_pelaksanaan' => $masaPelaksanaan,
            'pagu_anggaran' => $paguAnggaran,
            'penyedia' => $penyedia,
            'nomor_kontrak' => $nomorKontrak,
            'nilai_kontrak' => $nilaiKontrak,
            'metode_pemilihan' => $metodePemilihan,
            'email_responden_1' => $emailResponden1,
            'email_responden_2' => $emailResponden2,
            'created_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'created_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $db->table('trn_kontrak_simak_konsultasi')->insert($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Gagal menyimpan SIMAK Konsultasi.');
        }

        $simakId = (int) $db->insertID();
        return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('message', 'SIMAK Konsultasi berhasil ditambahkan.');
    }

    public function updateSimakKonsultasi(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Anda tidak memiliki akses untuk mengubah data SIMAK Konsultasi.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Tabel SIMAK Konsultasi belum tersedia.');
        }

        $builder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_konsultasi');
        $existing = $builder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'SIMAK Konsultasi tidak ditemukan.');
        }

        $ppkNip = trim((string) $this->request->getPost('ppk_nip'));
        $ppkNama = trim((string) $this->request->getPost('ppk_nama'));
        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $jenisPekerjaan = trim((string) $this->request->getPost('jenis_pekerjaan_jasa_konsultansi'));
        $masaPelaksanaan = trim((string) $this->request->getPost('masa_pelaksanaan'));
        $paguAnggaran = $this->parseMoneyToBigInt($this->request->getPost('pagu_anggaran'));
        $penyedia = trim((string) $this->request->getPost('penyedia'));
        $nilaiKontrak = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak'));
        $metodePemilihan = trim((string) $this->request->getPost('metode_pemilihan'));
        $emailResponden1 = trim((string) $this->request->getPost('email_responden_1'));
        $emailResponden2 = trim((string) $this->request->getPost('email_responden_2'));
        $paketId = (int) $this->request->getPost('paket_id');

        // Validate paket_id is required
        if ($paketId <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Pemaketan wajib dipilih.');
        }

        // Validate paket exists
        if ($db->tableExists('mst_paket')) {
            $paket = $db->table('mst_paket')->select('id')->where('id', $paketId)->get()->getRowArray();
            if (! is_array($paket)) {
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Pemaketan tidak ditemukan pada master paket.');
            }
        }

        if ($ppkNip === '' || $ppkNama === '' || $namaPaket === '' || $jenisPekerjaan === '' ||
            $masaPelaksanaan === '' || $paguAnggaran <= 0 || $penyedia === '' || $nilaiKontrak <= 0 ||
            $metodePemilihan === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Seluruh input wajib terisi.');
        }

        if ($emailResponden1 === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Email responden 1 wajib diisi.');
        }

        $payload = [
            'paket_id' => $paketId,
            'ppk_nama' => $ppkNama,
            'ppk_nip' => $ppkNip,
            'nama_paket' => $namaPaket,
            'jenis_pekerjaan_jasa_konsultansi' => $jenisPekerjaan,
            'masa_pelaksanaan' => $masaPelaksanaan,
            'pagu_anggaran' => $paguAnggaran,
            'penyedia' => $penyedia,
            'nilai_kontrak' => $nilaiKontrak,
            'metode_pemilihan' => $metodePemilihan,
            'email_responden_1' => $emailResponden1,
            'email_responden_2' => $emailResponden2,
            'updated_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'updated_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $db->transStart();
        $ok = $db->table('trn_kontrak_simak_konsultasi')->where('id', $id)->update($payload);
        if ($ok && $db->tableExists('trn_kontrak_simak_konsultasi_add_on')) {
            $this->syncSimakKonsultasiAddOns($id);
        }
        $db->transComplete();

        if (! $ok || ! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Gagal mengubah SIMAK Konsultasi.');
        }

        return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('message', 'SIMAK Konsultasi berhasil diubah.');
    }

    private function syncSimakKonsultasiAddOns(int $id): void
    {
        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi_add_on')) {
            return;
        }

        $rawValues = $this->request->getPost('add_on_values');
        $values = is_array($rawValues) ? $rawValues : [];
        $rawDates = $this->request->getPost('add_on_dates');
        $dates = is_array($rawDates) ? $rawDates : [];

        $db->table('trn_kontrak_simak_konsultasi_add_on')->where('simak_id', $id)->delete();

        if ($values === []) {
            return;
        }

        $username = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        $rows = [];
        $urutan = 1;

        foreach ($values as $index => $value) {
            $nilaiAddOn = $this->parseMoneyToFloat($value);
            if ($nilaiAddOn <= 0) {
                continue;
            }

            $rows[] = [
                'simak_id' => $id,
                'kategori' => 'jasa_konsultansi',
                'item_add_on' => 'Add On ' . $urutan,
                'nilai_add_on' => $nilaiAddOn,
                'tanggal_add_on' => $this->normalizeDateValue((string) ($dates[$index] ?? '')),
                'created_by' => $username,
                'created_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $urutan++;
        }

        if ($rows !== []) {
            $db->table('trn_kontrak_simak_konsultasi_add_on')->insertBatch($rows);
        }
    }

    public function detailSimakKonsultasi(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Tabel SIMAK Konsultasi belum tersedia. Jalankan migration.');
        }

        $builder = $db->table('trn_kontrak_simak_konsultasi')->select('*')->where('id', $id);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_konsultasi');
        $item = $builder->get()->getRowArray();
        if (! is_array($item)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Data SIMAK Konsultasi tidak ditemukan.');
        }

        $templateItems = $this->getSimakTemplateItems('konsultasi');
        $verifikasiByRow = [];
        if ($db->tableExists('trn_kontrak_simak_konsultasi_verifikasi')) {
            $verifikasiBuilder = $db->table('trn_kontrak_simak_konsultasi_verifikasi')
                ->select('row_no, kelengkapan_dokumen, verifikasi_ki, keterangan, pic')
                ->where('simak_id', $id)
                ->orderBy('row_no', 'ASC');
            $this->applyNotDeletedWhere($verifikasiBuilder, 'trn_kontrak_simak_konsultasi_verifikasi');
            $rows = $verifikasiBuilder->get()->getResultArray();
            foreach ($rows as $row) {
                $verifikasiByRow[(int) ($row['row_no'] ?? 0)] = $row;
            }
        }

        $dokumenByRow = [];
        if ($db->tableExists('trn_kontrak_simak_konsultasi_verifikasi_dokumen')) {
            $dokumenBuilder = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
                ->select('id, row_no, file_original_name, file_relative_path, file_mime, file_size, created_at, created_by, tipe_dokumen, kelengkapan_dokumen, verifikasi_ki, keterangan, pic, is_google_drive_link, google_drive_source_url, copied_to_project_drive, copied_to_project_drive_at, copied_to_project_drive_by, original_file_id')
                ->where('simak_id', $id)
                ->orderBy('row_no', 'ASC')
                ->orderBy('id', 'DESC');
            $this->applyNotDeletedWhere($dokumenBuilder, 'trn_kontrak_simak_konsultasi_verifikasi_dokumen');
            $dokumenRows = $dokumenBuilder->get()->getResultArray();
            foreach ($dokumenRows as $doc) {
                $rowNo = (int) ($doc['row_no'] ?? 0);
                if ($rowNo <= 0) {
                    continue;
                }

                if (! isset($dokumenByRow[$rowNo])) {
                    $dokumenByRow[$rowNo] = [];
                }
                $dokumenByRow[$rowNo][] = $doc;
            }
        }

        $addOnsByCategory = [];
        $nilaiAddOn = 0.0;
        if ($db->tableExists('trn_kontrak_simak_konsultasi_add_on')) {
            $addOnBuilder = $db->table('trn_kontrak_simak_konsultasi_add_on')
                ->select('id, item_add_on, kategori, nilai_add_on, tanggal_add_on')
                ->where('simak_id', $id)
                ->orderBy('id', 'ASC');
            $this->applyNotDeletedWhere($addOnBuilder, 'trn_kontrak_simak_konsultasi_add_on');
            $addOns = $addOnBuilder->get()->getResultArray();

            foreach ($addOns as $row) {
                $nilaiAddOn += (float) ($row['nilai_add_on'] ?? 0);
                $kategori = $this->normalizeSimakAddOnCategory((string) ($row['kategori'] ?? 'jasa_konsultansi'));
                if (! isset($addOnsByCategory[$kategori])) {
                    $addOnsByCategory[$kategori] = [];
                }

                $addOnsByCategory[$kategori][] = [
                    'item_add_on' => (string) ($row['item_add_on'] ?? ''),
                    'kategori_add_on' => $kategori,
                    'nilai_add_on' => (float) ($row['nilai_add_on'] ?? 0),
                    'tanggal_add_on' => (string) ($row['tanggal_add_on'] ?? ''),
                ];
            }
        }

        $kelengkapanPercentages = $this->getSimakAdministrasiKelengkapanBySimakId([$id], 'konsultasi');
        $kelengkapanPercentage = $kelengkapanPercentages[$id] ?? [
            'lengkap_persen' => 0.0,
            'belum_sesuai_persen' => 0.0,
            'belum_verifikasi_persen' => 0.0,
            'belum_ada_persen' => 0.0,
        ];

        return view('admin/kontrak/simak_konsultasi_detail', [
            'title' => 'Detail SIMAK Konsultasi',
            'item' => $item,
            'addOnsByCategory' => $addOnsByCategory,
            'nilaiAddOn' => $nilaiAddOn,
            'totalKontrak' => ((float) ($item['nilai_kontrak'] ?? 0)) + $nilaiAddOn,
            'templateItems' => $templateItems,
            'verifikasiByRow' => $verifikasiByRow,
            'dokumenByRow' => $dokumenByRow,
            'kelengkapanPercentage' => $kelengkapanPercentage,
            'appSetting' => [
                'simak_max_upload_mb' => $this->getSimakMaxUploadMb(),
            ],
        ]);
    }

    public function createSimakKonsultasiShare(int $id)
    {
        $isAjax = $this->request->isAJAX()
            || stripos((string) $this->request->getHeaderLine('Accept'), 'application/json') !== false;

        $respondError = function (string $message, int $statusCode = 400) use ($isAjax) {
            if ($isAjax) {
                return $this->response->setStatusCode($statusCode)->setJSON([
                    'success' => false,
                    'message' => $message,
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', $message);
        };

        if (! $this->canManageKontrak()) {
            return $respondError('Anda tidak memiliki akses untuk membuat link share SIMAK Konsultasi.', 403);
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi') || ! $db->tableExists('trn_kontrak_simak_konsultasi_share')) {
            return $respondError('Tabel share SIMAK Konsultasi belum tersedia. Jalankan migration terbaru.', 500);
        }
        $shareHasExpiresCol = $this->tableHasColumn('trn_kontrak_simak_konsultasi_share', 'expires_at');

        $simakBuilder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($simakBuilder, 'trn_kontrak_simak_konsultasi');
        $simak = $simakBuilder->get()->getRowArray();
        if (! is_array($simak)) {
            return $respondError('Data SIMAK Konsultasi tidak ditemukan.', 404);
        }

        // Menghilangkan durasi, aktif sampai dinonaktifkan manual
        $expiresAt = null;

        $token = $this->generateSimakShareToken();
        if ($token === null) {
            return $respondError('Gagal membuat token share SIMAK Konsultasi. Silakan coba lagi.', 500);
        }

        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');

        $existingSelect = 'id, share_token';
        if ($shareHasExpiresCol) {
            $existingSelect .= ', expires_at';
        }

        $existing = $db->table('trn_kontrak_simak_konsultasi_share')
            ->select($existingSelect)
            ->where('simak_id', $id)
            ->get()
            ->getRowArray();

        $payload = [
            'simak_id' => $id,
            'share_token' => $token,
            'is_active' => 1,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
        ];

        if ($shareHasExpiresCol) {
            $payload['expires_at'] = $expiresAt;
        }

        if (is_array($existing)) {
            $ok = $db->table('trn_kontrak_simak_konsultasi_share')->where('id', (int) ($existing['id'] ?? 0))->update($payload);
        } else {
            $payload['created_by'] = $actor;
            $payload['created_date'] = $today;
            $payload['created_at'] = $now;
            $ok = $db->table('trn_kontrak_simak_konsultasi_share')->insert($payload);
        }

        if (! $ok) {
            return $respondError('Gagal menyimpan link share SIMAK Konsultasi.', 500);
        }

        $shareUrl = site_url('simak/share/' . $token);
        $successMessage = 'Link share SIMAK Konsultasi berhasil dibuat.';

        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $successMessage,
                'share_url' => $shareUrl,
                'is_update' => is_array($existing) && trim((string) ($existing['share_token'] ?? '')) !== '',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $redirect = redirect()->to(site_url('admin/kontrak/simak/konsultasi'))
            ->with('success', $successMessage)
            ->with('simak_share_link', $shareUrl);

        if (is_array($existing) && trim((string) ($existing['share_token'] ?? '')) !== '') {
            $redirect = $redirect->with('simak_share_notice', 'Sebaiknya bagikan link yang sudah ada agar pihak kontraktor tidak bingung. Buat link baru hanya jika memang diperlukan karena link lama akan tidak berlaku.');
        }

        return $redirect;
    }

    public function deactivateSimakKonsultasiShare(int $id)
    {
        $isAjax = $this->request->isAJAX()
            || stripos((string) $this->request->getHeaderLine('Accept'), 'application/json') !== false;

        $respondError = function (string $message, int $statusCode = 400) use ($isAjax) {
            if ($isAjax) {
                return $this->response->setStatusCode($statusCode)->setJSON([
                    'success' => false,
                    'message' => $message,
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', $message);
        };

        if (! $this->canManageKontrak()) {
            return $respondError('Anda tidak memiliki akses untuk menonaktifkan link share SIMAK Konsultasi.', 403);
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi_share')) {
            return $respondError('Tabel share SIMAK Konsultasi belum tersedia. Jalankan migration terbaru.', 500);
        }

        $share = $db->table('trn_kontrak_simak_konsultasi_share')
            ->select('id, is_active')
            ->where('simak_id', $id)
            ->get()
            ->getRowArray();

        if (! is_array($share)) {
            return $respondError('Link share SIMAK Konsultasi tidak ditemukan.', 404);
        }

        $payload = [
            'is_active' => 0,
            'updated_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'updated_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $db->table('trn_kontrak_simak_konsultasi_share')
            ->where('id', (int) ($share['id'] ?? 0))
            ->update($payload);

        if (! $ok) {
            return $respondError('Gagal menonaktifkan link share SIMAK Konsultasi.', 500);
        }

        $message = 'Link share SIMAK Konsultasi berhasil dinonaktifkan.';
        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('success', $message);
    }

    public function importSimakKonsultasi()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Anda tidak memiliki akses untuk import.');
        }

        $file = $this->request->getFile('file_import') ?: $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'File tidak valid.');
        }

        // Store file temporarily
        $tempPath = $file->getTempName();
        $db = db_connect();

        try {
            if (! class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'PhpSpreadsheet tidak tersedia.');
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('Daftar SIMAK JK (>100juta)');
            if ($sheet === null) {
                $sheet = $spreadsheet->getActiveSheet();
            }

            $highestRow = (int) $sheet->getHighestRow();
            $headerRow = $sheet->rangeToArray('A1:O1', null, true, false)[0] ?? [];

            $normalizeHeader = static function ($value): string {
                $header = strtolower(trim((string) $value));
                $header = str_replace(['-', '/', ' '], '_', $header);
                $header = preg_replace('/[^a-z0-9_]/', '', $header) ?? $header;
                return $header;
            };

            $headers = [];
            foreach ($headerRow as $column => $name) {
                $normalized = $normalizeHeader($name);
                if ($normalized !== '') {
                    $headers[$column] = $normalized;
                }
            }

            $useFlatTemplate = isset($headers[0], $headers[1], $headers[2], $headers[3], $headers[4], $headers[5], $headers[6], $headers[7], $headers[8], $headers[9], $headers[10], $headers[11], $headers[12], $headers[13]);
            $importRows = $useFlatTemplate
                ? $sheet->rangeToArray('A2:O' . $highestRow, null, true, false)
                : $sheet->rangeToArray('B24:P139', null, true, false);

            $imported = 0;
            $errors = [];

            foreach ($importRows as $idx => $row) {
                $excelRow = $useFlatTemplate ? ($idx + 2) : (24 + $idx);
                if (! is_array($row)) {
                    $errors[] = 'Baris ' . $excelRow . ': Format baris tidak valid.';
                    continue;
                }

                $rowData = [];
                if ($useFlatTemplate) {
                    foreach ($headers as $column => $headerName) {
                        $rowData[$headerName] = trim((string) ($row[$column] ?? ''));
                    }
                } else {
                    $rowData = [
                        'satker' => trim((string) ($row[0] ?? '')),
                        'ppk_nip' => trim((string) ($row[1] ?? '')),
                        'ppk_nama' => trim((string) ($row[2] ?? '')),
                        'jenis_pekerjaan_jasa_konsultansi' => trim((string) ($row[3] ?? '')),
                        'nama_paket' => trim((string) ($row[4] ?? '')),
                        'masa_pelaksanaan' => trim((string) ($row[5] ?? '')),
                        'tahun_anggaran' => trim((string) ($row[6] ?? '')),
                        'pagu_anggaran' => trim((string) ($row[7] ?? '')),
                        'penyedia' => trim((string) ($row[8] ?? '')),
                        'nomor_kontrak' => trim((string) ($row[9] ?? '')),
                        'nilai_kontrak' => trim((string) ($row[10] ?? '')),
                        'metode_pemilihan' => trim((string) ($row[11] ?? '')),
                        'email_responden_1' => trim((string) ($row[12] ?? '')),
                        'email_responden_2' => trim((string) ($row[13] ?? '')),
                    ];
                }

                $satker = trim((string) $rowData['satker']);
                if ($satker === '') {
                    continue;
                }

                $ppkNip = trim((string) $rowData['ppk_nip']);
                $ppkNama = trim((string) $rowData['ppk_nama']);
                $jenisPekerjaan = $this->normalizeSimakJenisPekerjaanJasa((string) $rowData['jenis_pekerjaan_jasa_konsultansi']);
                $namaPaket = trim((string) $rowData['nama_paket']);
                $masaPelaksanaan = $this->normalizeSimakMasaPelaksanaan((string) $rowData['masa_pelaksanaan']);
                $tahunAnggaran = trim((string) $rowData['tahun_anggaran']);
                $paguAnggaran = $this->parseMoneyToBigInt($rowData['pagu_anggaran']);
                $penyedia = trim((string) $rowData['penyedia']);
                $nomorKontrak = trim((string) $rowData['nomor_kontrak']);
                $nilaiKontrak = $this->parseMoneyToFloat($rowData['nilai_kontrak']);
                $metodePemilihan = $this->normalizeSimakMetodePemilihan((string) $rowData['metode_pemilihan']);
                $emailResponden1 = trim((string) ($rowData['email_responden_1'] ?? $rowData['email_responden'] ?? ''));
                $emailResponden2 = trim((string) ($rowData['email_responden_2'] ?? ''));

                if ($nomorKontrak === '' || $nilaiKontrak <= 0) {
                    $errors[] = 'Baris ' . $excelRow . ': Data tidak lengkap';
                    continue;
                }

                if ($jenisPekerjaan === null || $masaPelaksanaan === null || $metodePemilihan === null) {
                    $errors[] = 'Baris ' . $excelRow . ': Nilai dropdown tidak valid';
                    continue;
                }

                if ($paguAnggaran <= 0) {
                    $errors[] = 'Baris ' . $excelRow . ': Pagu anggaran tidak valid';
                    continue;
                }

                // Check for duplicate
                $duplicateBuilder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('nomor_kontrak', $nomorKontrak);
                $this->applyNotDeletedWhere($duplicateBuilder, 'trn_kontrak_simak_konsultasi');
                if ($duplicateBuilder->get()->getRowArray() !== null) {
                    continue; // Skip duplicates
                }

                $payload = [
                    'satker' => $satker,
                    'ppk_nama' => $ppkNama,
                    'ppk_nip' => $ppkNip,
                    'nama_paket' => $namaPaket,
                    'tahun_anggaran' => $tahunAnggaran,
                    'jenis_pekerjaan_jasa_konsultansi' => $jenisPekerjaan,
                    'masa_pelaksanaan' => $masaPelaksanaan,
                    'pagu_anggaran' => $paguAnggaran,
                    'penyedia' => $penyedia,
                    'nomor_kontrak' => $nomorKontrak,
                    'nilai_kontrak' => $nilaiKontrak,
                    'metode_pemilihan' => $metodePemilihan,
                    'email_responden_1' => $emailResponden1,
                    'email_responden_2' => $emailResponden2,
                    'created_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                if ($db->table('trn_kontrak_simak_konsultasi')->insert($payload)) {
                    $imported++;
                }
            }

            $message = "Import selesai. $imported data berhasil diimpor.";
            if (count($errors) > 0) {
                $message .= " Ada " . count($errors) . " error.";
            }

            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('message', $message);
        } catch (\Exception $e) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function exportSimakKonsultasiTemplate()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Anda tidak memiliki akses untuk mengunduh template SIMAK.');
        }

        $headers = [
            'satker',
            'ppk_nip',
            'ppk_nama',
            'jenis_pekerjaan_jasa_konsultansi',
            'nama_paket',
            'masa_pelaksanaan',
            'tahun_anggaran',
            'pagu_anggaran',
            'penyedia',
            'nomor_kontrak',
            'nilai_kontrak',
            'metode_pemilihan',
            'email_responden_1',
            'email_responden_2',
            'keterangan',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar SIMAK JK (>100juta)');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray([
            'Perencanaan Prasarana Strategis',
            '199012212018021001',
            'Nurhidayat Nugroho, S.Ars',
            'Perencanaan',
            'Paket Konsultansi Contoh',
            'SYC',
            '2026 - 2027',
            250000000,
            'PT Penyedia Contoh',
            'SIMAK/JK/001/2026',
            200000000,
            'Seleksi',
            'responden1@example.com',
            'responden2@example.com',
            '',
        ], null, 'A2');

        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'template_import_simak_konsultasi_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($binary === false ? '' : $binary);
    }

    public function saveSimakKonsultasiVerifikasi(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Anda tidak memiliki akses untuk menyimpan verifikasi SIMAK Konsultasi.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi') || ! $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tabel verifikasi SIMAK Konsultasi belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak_konsultasi');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Data SIMAK Konsultasi tidak ditemukan.');
        }

        $templateItems = $this->getSimakTemplateItems('konsultasi');
        if ($templateItems === []) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Template verifikasi SIMAK tidak ditemukan.');
        }

        $kelengkapan = $this->request->getPost('kelengkapan_dokumen');
        $verifikasi = $this->request->getPost('verifikasi_ki');
        $keterangan = $this->request->getPost('keterangan');
        $pic = $this->request->getPost('pic');

        $kelengkapan = is_array($kelengkapan) ? $kelengkapan : [];
        $verifikasi = is_array($verifikasi) ? $verifikasi : [];
        $keterangan = is_array($keterangan) ? $keterangan : [];
        $pic = is_array($pic) ? $pic : [];

        $allowedKelengkapan = ['ada', 'tidak'];
        $allowedVerifikasi = ['sesuai', 'tidak_sesuai'];
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');

        $rowsToSave = [];
        foreach ($templateItems as $item) {
            if (($item['is_leaf'] ?? false) !== true) {
                continue;
            }

            $rowNo = (int) ($item['row_no'] ?? 0);
            if ($rowNo <= 0) {
                continue;
            }

            $kel = strtolower(trim((string) ($kelengkapan[$rowNo] ?? '')));
            $ver = strtolower(trim((string) ($verifikasi[$rowNo] ?? '')));
            $ket = trim((string) ($keterangan[$rowNo] ?? ''));
            $picValue = trim((string) ($pic[$rowNo] ?? ''));

            if (! in_array($kel, $allowedKelengkapan, true)) {
                $kel = null;
            }

            if (! in_array($ver, $allowedVerifikasi, true)) {
                $ver = null;
            }

            if ($kel === null && $ver === null && $ket === '' && $picValue === '') {
                continue;
            }

            $rowsToSave[] = [
                'simak_id' => $id,
                'row_no' => $rowNo,
                'kode' => (string) ($item['display_no'] ?? ''),
                'uraian' => (string) ($item['uraian'] ?? ''),
                'kelengkapan_dokumen' => $kel,
                'verifikasi_ki' => $ver,
                'keterangan' => $ket,
                'pic' => $picValue,
                'updated_by' => $actor,
                'updated_date' => $today,
                'updated_at' => $now,
            ];
        }

        $db->transStart();
        $db->table('trn_kontrak_simak_konsultasi_verifikasi')->where('simak_id', $id)->delete();

        if ($rowsToSave !== []) {
            foreach ($rowsToSave as &$row) {
                $row['created_by'] = $actor;
                $row['created_date'] = $today;
                $row['created_at'] = $now;
            }
            unset($row);

            $db->table('trn_kontrak_simak_konsultasi_verifikasi')->insertBatch($rowsToSave);
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Gagal menyimpan verifikasi SIMAK Konsultasi.');
        }

        // Notify uploader(s) via email (same behavior as konstruksi)
        try {
            $emails = [];

            // Priority 1: manual notification email from POST
            $notificationEmail = trim((string) ($this->request->getPost('notification_email') ?? ''));
            if ($notificationEmail !== '') {
                if (filter_var($notificationEmail, FILTER_VALIDATE_EMAIL) !== false) {
                    $emails[] = $notificationEmail;
                }
            }

            // Priority 2: extract from uploaded documents
            if ($emails === [] && $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi_dokumen')) {
                $docs = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
                    ->select('created_by, row_no, file_original_name')
                    ->where('simak_id', $id)
                    ->get()
                    ->getResultArray();

                foreach ($docs as $d) {
                    $cb = trim((string) ($d['created_by'] ?? ''));
                    if ($cb === '') {
                        continue;
                    }

                    if (preg_match('/<([^>]+)>/', $cb, $m)) {
                        $emails[] = trim($m[1]);
                        continue;
                    }

                    if (preg_match('/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i', $cb, $m2)) {
                        $emails[] = trim($m2[1]);
                    }
                }

                $emails = array_values(array_unique(array_filter($emails)));
            }

            if ($emails !== [] && class_exists('\Config\Services')) {
                $emailService = \Config\Services::email();
                if ($emailService !== null) {
                    $emailConfig = config('Email');
                    if ($emailConfig !== null) {
                        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
                        $fromName = trim((string) ($emailConfig->fromName ?? 'SIMAK')) ?: 'SIMAK';
                        if ($fromEmail === '') {
                            $host = $_SERVER['HTTP_HOST'] ?? gethostname() ?: 'example.com';
                            $fromEmail = 'no-reply@' . preg_replace('/[^a-z0-9.\-]/i', '', $host);
                        }

                        // Prefer public share link if available
                        $shareUrl = site_url('admin/kontrak/simak/konsultasi/' . $id);
                        if ($db->tableExists('trn_kontrak_simak_konsultasi_share')) {
                            $shareRow = $db->table('trn_kontrak_simak_konsultasi_share')
                                ->select('share_token')
                                ->where('simak_id', $id)
                                ->where('is_active', 1)
                                ->orderBy('id', 'DESC')
                                ->get()
                                ->getRowArray();
                            if (is_array($shareRow) && ! empty($shareRow['share_token'])) {
                                $shareUrl = site_url('simak/share/' . $shareRow['share_token']);
                            }
                        }

                        // Build list of poin (kode or uraian) instead of row numbers
                        $points = [];
                        $rowNos = array_map('intval', array_column($docs ?? [], 'row_no'));
                        if ($rowNos !== []) {
                            $verRows = $db->table('trn_kontrak_simak_konsultasi_verifikasi')
                                ->select('row_no,kode,uraian,verifikasi_ki,keterangan')
                                ->where('simak_id', $id)
                                ->whereIn('row_no', $rowNos)
                                ->get()
                                ->getResultArray();
                            $map = [];
                            foreach ($verRows as $vr) {
                                $kode = trim((string) ($vr['kode'] ?? ''));
                                $uraian = trim((string) ($vr['uraian'] ?? ''));
                                $status = (string) ($vr['verifikasi_ki'] ?? '');
                                $ketRow = trim((string) ($vr['keterangan'] ?? ''));
                                $map[(int) ($vr['row_no'] ?? 0)] = [
                                    'kode' => $kode,
                                    'uraian' => $uraian,
                                    'status' => $status,
                                    'keterangan' => $ketRow,
                                ];
                            }
                            foreach ($rowNos as $rn) {
                                $m = $map[(int) $rn] ?? null;
                                if (is_array($m) && (($m['kode'] ?? '') !== '' || ($m['uraian'] ?? '') !== '')) {
                                    $points[] = $m;
                                }
                            }
                        }

                        $subject = 'Notifikasi: Verifikasi SIMAK';
                        $message = "Verifikasi SIMAK telah disimpan.";
                        if ($points !== []) {
                            $lines = [];
                            foreach ($points as $p) {
                                $statusLabel = ($p['status'] === 'sesuai') ? 'Sesuai' : (($p['status'] === 'tidak_sesuai') ? 'Tidak Sesuai' : '-');
                                $line = '';
                                if (($p['kode'] ?? '') !== '') {
                                    $line .= $p['kode'] . '. ';
                                }
                                $line .= ($p['uraian'] ?? '-');
                                $line .= "\nStatus: " . $statusLabel;
                                if (($p['keterangan'] ?? '') !== '') {
                                    $line .= "\nKeterangan: " . $p['keterangan'];
                                }
                                $lines[] = $line;
                            }
                            $message .= "\n\nPoin yang diverifikasi:\n- " . implode("\n- ", $lines);
                        }
                        $message .= "\n\nLihat: " . $shareUrl;

                        foreach ($emails as $to) {
                            try {
                                if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
                                    continue;
                                }

                                $emailService->clear(true);
                                $emailService->setFrom($fromEmail, $fromName);
                                $emailService->setTo($to);
                                $emailService->setSubject($subject);
                                $emailService->setMessage($message);
                                $emailService->send();
                            } catch (\Throwable $e) {
                                log_message('error', 'Failed to send verification notification to ' . $to . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to prepare/send verification notifications (konsultasi): ' . $e->getMessage());
        }

        return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('success', 'Verifikasi SIMAK Konsultasi berhasil disimpan.');
    }

    public function uploadSimakKonsultasiVerifikasiDokumen(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Anda tidak memiliki akses untuk upload dokumen verifikasi SIMAK Konsultasi.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi')
            || ! $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi')
            || ! $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tabel dokumen verifikasi SIMAK Konsultasi belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak_konsultasi');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Data SIMAK Konsultasi tidak ditemukan.');
        }

        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($rowNo <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Baris verifikasi tidak valid.');
        }

        $templateItems = $this->getSimakTemplateItems('konsultasi');
        $templateByRow = [];
        foreach ($templateItems as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Upload hanya diizinkan pada baris hirarki terbawah.');
        }

        // Get SIMAK package info for structured folder upload
        $packageInfo = $this->getSimakPackageInfo($id, 'konsultasi');
        $namaPaket = ($packageInfo['nama_paket'] ?? '') ?: 'Tanpa Paket';
        $penyedia = ($packageInfo['penyedia'] ?? '') ?: 'Tanpa Penyedia';
        $headerUraian = (string) ($targetTemplate['display_no'] ?? '');
        $uraian = (string) ($targetTemplate['uraian'] ?? '');

        $tipeDokumen = strtolower(trim((string) $this->request->getPost('tipe_dokumen')));
        if (! in_array($tipeDokumen, ['draft', 'final'], true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tipe dokumen tidak valid.');
        }

        $selectedDoc = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->select('id, verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', $tipeDokumen)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! is_array($selectedDoc)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Dokumen ' . $tipeDokumen . ' belum tersedia.');
        }

        $latestDraftDoc = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->select('id, verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'draft')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        $latestFinalDoc = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->select('id, verifikasi_ki')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'final')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $existingVerifikasi = $db->table('trn_kontrak_simak_konsultasi_verifikasi')
            ->select('kelengkapan_dokumen')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->get()
            ->getRowArray();

        $allowedKelengkapan = ['ada', 'tidak'];
        $allowedVerifikasi = ['sesuai', 'tidak_sesuai'];

        $kelRaw = strtolower(trim((string) $this->request->getPost('kelengkapan_dokumen')));
        $ver = strtolower(trim((string) $this->request->getPost('verifikasi_ki')));
        $ket = trim((string) $this->request->getPost('keterangan'));
        $pic = trim((string) $this->request->getPost('pic'));

        $kel = in_array($kelRaw, $allowedKelengkapan, true)
            ? $kelRaw
            : strtolower(trim((string) ($existingVerifikasi['kelengkapan_dokumen'] ?? '')));

        if (! in_array($kel, $allowedKelengkapan, true)) {
            $kel = null;
        }

        if (! in_array($ver, $allowedVerifikasi, true)) {
            $ver = null;
        }

        $file = $this->request->getFile('dokumen_file');
        $hasUpload = $file && $file->isValid() && ! $file->hasMoved();

        if (! $hasUpload && $kel === null && $ver === null && $ket === '' && $pic === '') {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tidak ada perubahan yang disimpan. Isi data atau upload file terlebih dahulu.');
        }

        $relativePath = '';
        $storedName = '';
        $summaryVerifikasi = 'belum_verifikasi';
        if ($tipeDokumen === 'final') {
            if ($ver === 'sesuai') {
                $summaryVerifikasi = 'sesuai';
            } elseif ($ver === 'tidak_sesuai') {
                $summaryVerifikasi = 'belum_sesuai';
            }
        } else {
            $finalCurrentStatus = strtolower(trim((string) ($latestFinalDoc['verifikasi_ki'] ?? '')));
            if ($finalCurrentStatus === 'sesuai') {
                $summaryVerifikasi = 'sesuai';
            } elseif ($finalCurrentStatus === 'tidak_sesuai') {
                $summaryVerifikasi = 'belum_sesuai';
            }
        }
        if ($hasUpload) {
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, $allowedExt, true)) {
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX/ZIP/RAR.');
            }

            $maxFileSizeBytes = $this->getSimakMaxUploadBytes();
            $maxFileSizeMb = $this->getSimakMaxUploadMb();
            $fileSize = (int) ($file->getSizeByUnit('b') ?? 0);
            if ($fileSize > $maxFileSizeBytes) {
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', "Ukuran file maksimal {$maxFileSizeMb}MB.");
            }

            $originalName = (string) $file->getClientName();
            $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');

            // Upload langsung ke Google Drive tanpa simpan di server lokal (structured)
            $gdriveLink = $this->uploadFileToGoogleDriveStructured(
                file_get_contents($file->getTempName()),
                $originalName,
                $mimeType,
                $namaPaket,
                $penyedia,
                $headerUraian,
                $uraian
            );
            if ($gdriveLink === 'FAILED_UPLOAD' || $gdriveLink === 'NOT_READY') {
                log_message('error', 'uploadSimakKonsultasiVerifikasiDokumen - Google Drive upload failed (' . $gdriveLink . ') for: ' . $originalName);
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Upload dokumen gagal: Google Drive tidak tersedia. Silakan coba lagi atau hubungi admin.');
            } elseif ($gdriveLink !== null) {
                $relativePath = $gdriveLink;
                $storedName = '';
            } else {
                // Fallback: tidak ada penyimpanan lokal, upload gagal
                log_message('error', 'uploadSimakKonsultasiVerifikasiDokumen - No storage configured and Google Drive failed for: ' . $originalName);
                return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Upload dokumen gagal: Tidak ada penyimpanan yang dikonfigurasi.');
            }
        }

        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        $verifikasiRow = [
            'simak_id' => $id,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => $kel,
            'verifikasi_ki' => $summaryVerifikasi,
            'keterangan' => $ket,
            'pic' => $pic,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
        ];

        $db->transStart();

        $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->where('id', (int) ($selectedDoc['id'] ?? 0))
            ->update([
                'verifikasi_ki' => $ver,
                'keterangan' => $ket,
                'pic' => $pic,
                'updated_by' => $actor,
                'updated_date' => $today,
                'updated_at' => $now,
            ]);

        $db->table('trn_kontrak_simak_konsultasi_verifikasi')->where('simak_id', $id)->where('row_no', $rowNo)->delete();
        $verifikasiRow['created_by'] = $actor;
        $verifikasiRow['created_date'] = $today;
        $verifikasiRow['created_at'] = $now;
        $db->table('trn_kontrak_simak_konsultasi_verifikasi')->insert($verifikasiRow);

        if ($hasUpload) {
            $dokumenRow = [
                'simak_id' => $id,
                'row_no' => $rowNo,
                'kode' => (string) ($targetTemplate['display_no'] ?? ''),
                'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
                'kelengkapan_dokumen' => $kel,
                'verifikasi_ki' => $ver,
                'keterangan' => $ket,
                'pic' => $pic,
                'file_original_name' => $originalName,
                'file_stored_name' => $storedName,
                'file_relative_path' => $relativePath,
                'file_mime' => $mimeType,
                'file_size' => $fileSize,
                'nama_file' => $originalName,
                'file_path' => $relativePath,
                'status' => ($kel === 'ada' && $ver === 'sesuai') ? 'Lengkap' : (($kel === 'ada' && $ver === 'tidak_sesuai') ? 'Belum Sesuai' : (($kel === 'ada') ? 'Belum Verifikasi' : (($ver === 'sesuai') ? 'Selesai' : 'Tidak Ada'))),
                'catatan' => $ket,
                'tipe_dokumen' => $tipeDokumen,
                'created_by' => $actor,
                'created_date' => $today,
                'created_at' => $now,
            ];
            $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')->insert($dokumenRow);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Gagal menyimpan upload dokumen verifikasi SIMAK Konsultasi.');
        }

        $message = $hasUpload
            ? 'Update verifikasi tersimpan dan dokumen berhasil diupload. Riwayat dokumen tercatat.'
            : 'Update verifikasi berhasil disimpan.';

        // Send notification email (same behavior as konstruksi upload)
        try {
            $emails = [];

            // Priority 1: Check for manual notification email from POST
            $notificationEmail = trim((string) ($this->request->getPost('notification_email') ?? ''));
            if ($notificationEmail !== '') {
                if (filter_var($notificationEmail, FILTER_VALIDATE_EMAIL) !== false) {
                    $emails[] = $notificationEmail;
                }
            }

            // Priority 2: If upload, extract from created_by uploader
            if ($emails === [] && $hasUpload) {
                $createdBy = trim((string) ($actor ?? ''));
                if ($createdBy !== '') {
                    if (preg_match('/<([^>]+)>/', $createdBy, $m)) {
                        $candidate = trim($m[1]);
                        if (filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                            $emails[] = $candidate;
                        }
                    } elseif (preg_match('/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i', $createdBy, $m2)) {
                        $candidate = trim($m2[1]);
                        if (filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                            $emails[] = $candidate;
                        }
                    }
                }
            }

            if ($emails !== [] && class_exists('\Config\Services')) {
                $emailService = \Config\Services::email();
                if ($emailService !== null) {
                    $emailConfig = config('Email');
                    if ($emailConfig !== null) {
                        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
                        $fromName = trim((string) ($emailConfig->fromName ?? 'SIMAK')) ?: 'SIMAK';
                        if ($fromEmail === '') {
                            $host = $_SERVER['HTTP_HOST'] ?? gethostname() ?: 'example.com';
                            $fromEmail = 'no-reply@' . preg_replace('/[^a-z0-9.\-]/i', '', $host);
                        }

                        // Prefer public share link if available
                        $shareUrl = site_url('admin/kontrak/simak/konsultasi/' . $id);
                        if ($db->tableExists('trn_kontrak_simak_konsultasi_share')) {
                            $shareRow = $db->table('trn_kontrak_simak_konsultasi_share')
                                ->select('share_token')
                                ->where('simak_id', $id)
                                ->where('is_active', 1)
                                ->orderBy('id', 'DESC')
                                ->get()
                                ->getRowArray();
                            if (is_array($shareRow) && ! empty($shareRow['share_token'])) {
                                $shareUrl = site_url('simak/share/' . $shareRow['share_token']);
                            }
                        }

                        $pointKode = trim((string) ($targetTemplate['display_no'] ?? ''));
                        $pointUraian = trim((string) ($targetTemplate['uraian'] ?? ''));
                        $pointLabel = $pointKode !== '' ? $pointKode . '. ' : '';
                        $pointLabel .= $pointUraian !== '' ? $pointUraian : ('Poin ' . $rowNo);
                        $subject = 'Notifikasi: Verifikasi dokumen SIMAK' . ($pointLabel !== '' ? ' - ' . $pointLabel : '');
                        $statusLabel = ($ver === 'sesuai') ? 'Sesuai' : (($ver === 'tidak_sesuai') ? 'Tidak Sesuai' : '-');
                        $message_email = 'Verifikasi dokumen untuk poin:\n' . $pointLabel . "\nStatus: " . $statusLabel;
                        if ($ket !== '') {
                            $message_email .= "\nKeterangan: " . $ket;
                        }
                        $message_email .= "\n\nLihat: " . $shareUrl;

                        foreach ($emails as $to) {
                            try {
                                if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
                                    continue;
                                }

                                $emailService->clear(true);
                                $emailService->setFrom($fromEmail, $fromName);
                                $emailService->setTo($to);
                                $emailService->setSubject($subject);
                                $emailService->setMessage($message_email);
                                $emailService->send();
                            } catch (\Throwable $e) {
                                log_message('error', 'Failed to send verification notification to ' . $to . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to prepare/send verification notification for document (konsultasi): ' . $e->getMessage());
        }

        return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('success', $message);
    }

    public function adminUploadSimakKonsultasiDokumen(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Anda tidak memiliki akses untuk upload dokumen SIMAK Konsultasi.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi')
            || ! $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi')
            || ! $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tabel dokumen SIMAK Konsultasi belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak_konsultasi')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak_konsultasi');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Data SIMAK Konsultasi tidak ditemukan.');
        }

        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($rowNo <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Baris dokumen tidak valid.');
        }

        $templateItems = $this->getSimakTemplateItems('konsultasi');
        $templateByRow = [];
        foreach ($templateItems as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Upload hanya diizinkan pada baris terbawah.');
        }

        $file = $this->request->getFile('dokumen_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'File upload tidak valid.');
        }

        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
        $ext = strtolower((string) $file->getClientExtension());
        if (! in_array($ext, $allowedExt, true)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX/ZIP/RAR.');
        }

        $maxFileSizeBytes = $this->getSimakMaxUploadBytes();
        $maxFileSizeMb = $this->getSimakMaxUploadMb();
        $fileSizeCheck = (int) ($file->getSizeByUnit('b') ?? 0);
        if ($fileSizeCheck > $maxFileSizeBytes) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', "Ukuran file maksimal {$maxFileSizeMb}MB.");
        }

        $subDir = 'uploads/simak_admin_konsultasi/' . $id . '/' . $rowNo;
        $absDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir);
        if (! is_dir($absDir) && ! @mkdir($absDir, 0775, true) && ! is_dir($absDir)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Gagal membuat direktori upload dokumen.');
        }

        $storedName = $file->getRandomName();
        $file->move($absDir, $storedName, true);
        $storedPath = $absDir . DIRECTORY_SEPARATOR . $storedName;
        if ($ext === 'pdf' && ((int) ($file->getSizeByUnit('b') ?? 0)) > (1024 * 1024)) {
            $this->tryCompressPdfWithGhostscript($storedPath);
        }
        $relativePath = $subDir . '/' . $storedName;

        $originalName = (string) $file->getClientName();
        $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');
        if (is_file($storedPath)) {
            $fileSize = (int) (@filesize($storedPath) ?: $file->getSizeByUnit('b'));
        } else {
            $fileSize = (int) ($file->getSizeByUnit('b') ?? 0);
        }

        $gdriveLink = $this->uploadFileToGoogleDriveIfConfigured($storedPath, $originalName, $mimeType);
        if ($gdriveLink === 'FAILED_UPLOAD' || $gdriveLink === 'NOT_READY') {
            if (is_file($storedPath)) {
                @unlink($storedPath);
            }
            log_message('error', 'adminUploadSimakKonsultasiDokumen - Google Drive upload failed (' . $gdriveLink . ') for: ' . $originalName);
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Upload dokumen gagal: Google Drive tidak tersedia. Silakan coba lagi atau hubungi admin.');
        } elseif ($gdriveLink !== null) {
            $relativePath = $gdriveLink;
            $storedName = '';
        }
        $tipeDokumen = strtolower(trim((string) $this->request->getPost('tipe_dokumen')));
        if (! in_array($tipeDokumen, ['draft', 'final'], true)) {
            $tipeDokumen = 'final';
        }

        $existingDraftDoc = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->select('id')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'draft')
            ->limit(1)
            ->get()
            ->getRowArray();
        $existingFinalDoc = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->select('id')
            ->where('simak_id', $id)
            ->where('row_no', $rowNo)
            ->where('tipe_dokumen', 'final')
            ->limit(1)
            ->get()
            ->getRowArray();
        $willHaveDraft = (is_array($existingDraftDoc) && ! empty($existingDraftDoc)) || $tipeDokumen === 'draft';
        $willHaveFinal = (is_array($existingFinalDoc) && ! empty($existingFinalDoc)) || $tipeDokumen === 'final';

        $actor = (string) (session()->get('username') ?: session()->get('name') ?: 'system');
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // Admin upload: langsung set sesuai kebutuhan
        // - Draft → belum_verifikasi (perlu verifikasi manual oleh KI)
        // - Final → sesuai (admin sudah review, langsung lengkap)
        if ($tipeDokumen === 'draft') {
            $verifikasiKi = $willHaveFinal ? 'sesuai' : 'belum_verifikasi';
        } else {
            $verifikasiKi = 'sesuai';
        }

        $verifikasiRow = [
            'simak_id' => $id,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => $verifikasiKi,
            'keterangan' => 'Upload dokumen dari admin',
            'pic' => $actor,
            'updated_by' => $actor,
            'updated_date' => $today,
            'updated_at' => $now,
        ];

        $db->transStart();
        $db->table('trn_kontrak_simak_konsultasi_verifikasi')->where('simak_id', $id)->where('row_no', $rowNo)->delete();
        $verifikasiRow['created_by'] = $actor;
        $verifikasiRow['created_date'] = $today;
        $verifikasiRow['created_at'] = $now;
        $db->table('trn_kontrak_simak_konsultasi_verifikasi')->insert($verifikasiRow);

        $dokumenRow = [
            'simak_id' => $id,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => $verifikasiKi,
            'keterangan' => 'Upload dokumen dari admin',
            'pic' => $actor,
            'file_original_name' => $originalName,
            'file_stored_name' => $storedName,
            'file_relative_path' => $relativePath,
            'file_mime' => $mimeType,
            'file_size' => $fileSize,
            'tipe_dokumen' => $tipeDokumen,
            'created_by' => $actor,
            'created_date' => $today,
            'created_at' => $now,
        ];
        $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')->insert($dokumenRow);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('error', 'Gagal menyimpan upload dokumen.');
        }

        $msg = $tipeDokumen === 'draft'
            ? 'Dokumen Draft berhasil diupload.'
            : 'Dokumen Final berhasil diupload dan otomatis terverifikasi Sesuai.';
        return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . $id))->with('success', $msg);
    }

    public function viewSimakKonsultasiVerifikasiDokumen(int $dokumenId)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_konsultasi_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Tabel dokumen verifikasi SIMAK Konsultasi belum tersedia.');
        }

        $builder = $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')
            ->select('id, simak_id, file_original_name, file_relative_path, file_mime, nama_file, file_path')
            ->where('id', $dokumenId);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_konsultasi_verifikasi_dokumen');
        $row = $builder->get()->getRowArray();

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi'))->with('error', 'Dokumen verifikasi tidak ditemukan.');
        }

        $relativePath = trim((string) ($row['file_relative_path'] ?? ''));
        if ($relativePath === '') {
            $relativePath = trim((string) ($row['file_path'] ?? ''));
        }
        if ($this->isAllowedGoogleDriveUrl($relativePath)) {
            return redirect()->to($relativePath);
        }

        $relativePath = ltrim($relativePath, '/');
        $absPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if ($relativePath === '' || ! is_file($absPath)) {
            return redirect()->to(site_url('admin/kontrak/simak/konsultasi/' . (int) ($row['simak_id'] ?? 0)))->with('error', 'File dokumen tidak ditemukan di server.');
        }

        $mime = trim((string) ($row['file_mime'] ?? ''));
        if ($mime === '') {
            $mime = mime_content_type($absPath) ?: 'application/octet-stream';
        }

        $fileName = (string) ($row['file_original_name'] ?? $row['nama_file'] ?? basename($absPath));
        $content = file_get_contents($absPath);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($fileName) . '"')
            ->setBody($content === false ? '' : $content);
    }

    private function isGoogleDriveUploadConfigured(): bool
    {
        $serviceAccountPath = trim((string) getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH'));
        $driveFolderId = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'));

        return $serviceAccountPath !== '' && $driveFolderId !== '';
    }

    private function uploadFileToGoogleDriveIfConfigured(string $storedPath, string $originalName, string $mimeType): ?string
    {
        $driveFolderId = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'));
        $oauthClientId = trim((string) getenv('GOOGLE_CLIENT_ID'));
        $oauthClientSecret = trim((string) getenv('GOOGLE_CLIENT_SECRET'));

        // Use OAuth for personal Gmail accounts (recommended)
        if ($oauthClientId !== '' && $oauthClientSecret !== '' && $driveFolderId !== '') {
            return $this->uploadFileToGoogleDriveOAuth($storedPath, $originalName, $mimeType);
        }

        // Fallback to Service Account (only works with Google Workspace or Shared Drives)
        $serviceAccountPath = trim((string) getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH'));

        if ($serviceAccountPath === '' || $driveFolderId === '') {
            log_message('error', 'uploadFileToGoogleDriveIfConfigured - Google Drive config missing.');
            return 'NOT_READY';
        }

        $gdrive = new \App\Libraries\GoogleDriveService();
        if (! $gdrive->isReady()) {
            $reason = $gdrive->getLastError() ?: 'Service not ready.';
            log_message('error', 'uploadFileToGoogleDriveIfConfigured - GoogleDriveService is not ready. Reason: ' . $reason);
            return 'NOT_READY';
        }

        $webViewLink = $gdrive->uploadFile($storedPath, $originalName, $mimeType, $driveFolderId);
        if ($webViewLink !== null) {
            if (is_file($storedPath)) {
                @unlink($storedPath);
            }
            return $webViewLink;
        }

        $reason = $gdrive->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadFileToGoogleDriveIfConfigured - Google Drive upload failed for: ' . $originalName . '. Reason: ' . $reason);
        return 'FAILED_UPLOAD';
    }

    /**
     * Upload file using OAuth 2.0 (for personal Gmail accounts)
     *
     * @param string $storedPath Local file path
     * @param string $originalName Original file name
     * @param string $mimeType MIME type
     * @return string|null Web view link or error string
     */
    private function uploadFileToGoogleDriveOAuth(string $storedPath, string $originalName, string $mimeType): ?string
    {
        $oauth = new \App\Libraries\GoogleOAuthService();

        if (!$oauth->isAuthenticated()) {
            log_message('error', 'uploadFileToGoogleDriveOAuth - Not authenticated with Google. Please connect via /oauth/connect');
            return 'NOT_READY';
        }

        $webViewLink = $oauth->uploadFile($storedPath, $originalName, $mimeType);
        if ($webViewLink !== null) {
            if (is_file($storedPath)) {
                @unlink($storedPath);
            }
            log_message('info', 'uploadFileToGoogleDriveOAuth - Uploaded via OAuth: ' . $originalName . ' -> ' . $webViewLink);
            return $webViewLink;
        }

        $reason = $oauth->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadFileToGoogleDriveOAuth - OAuth upload failed for: ' . $originalName . '. Reason: ' . $reason);
        return 'FAILED_UPLOAD';
    }

    /**
     * Upload file content directly to Google Drive without local storage.
     * File is uploaded from memory to Google Drive only.
     *
     * @param string $fileContent Binary content of the file
     * @param string $originalName Original client file name
     * @param string $mimeType Mime type of the file
     * @return string|null Web view link of the uploaded file, or 'NOT_READY' if config missing, 'FAILED_UPLOAD' if failed
     */
    private function uploadFileToGoogleDriveDirect(string $fileContent, string $originalName, string $mimeType): ?string
    {
        $driveFolderId = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'));
        $oauthClientId = trim((string) getenv('GOOGLE_CLIENT_ID'));
        $oauthClientSecret = trim((string) getenv('GOOGLE_CLIENT_SECRET'));

        // Use OAuth for personal Gmail accounts (recommended)
        if ($oauthClientId !== '' && $oauthClientSecret !== '' && $driveFolderId !== '') {
            return $this->uploadFileToGoogleDriveDirectOAuth($fileContent, $originalName, $mimeType);
        }

        // Fallback to Service Account (only works with Google Workspace or Shared Drives)
        $serviceAccountPath = trim((string) getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH'));

        if ($serviceAccountPath === '' || $driveFolderId === '') {
            log_message('error', 'uploadFileToGoogleDriveDirect - Google Drive config missing.');
            return 'NOT_READY';
        }

        $gdrive = new \App\Libraries\GoogleDriveService();
        if (! $gdrive->isReady()) {
            $reason = $gdrive->getLastError() ?: 'Service not ready.';
            log_message('error', 'uploadFileToGoogleDriveDirect - GoogleDriveService is not ready. Reason: ' . $reason);
            return 'NOT_READY';
        }

        $webViewLink = $gdrive->uploadFileContent($fileContent, $originalName, $mimeType, $driveFolderId);
        if ($webViewLink !== null) {
            log_message('info', 'uploadFileToGoogleDriveDirect - Uploaded directly to Google Drive: ' . $originalName . ' -> ' . $webViewLink);
            return $webViewLink;
        }

        $reason = $gdrive->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadFileToGoogleDriveDirect - Google Drive upload failed for: ' . $originalName . '. Reason: ' . $reason);
        return 'FAILED_UPLOAD';
    }

    /**
     * Upload file content directly using OAuth 2.0 (for personal Gmail accounts)
     *
     * @param string $fileContent Binary content of the file
     * @param string $originalName Original client file name
     * @param string $mimeType Mime type of the file
     * @return string|null Web view link or error string
     */
    private function uploadFileToGoogleDriveDirectOAuth(string $fileContent, string $originalName, string $mimeType): ?string
    {
        $oauth = new \App\Libraries\GoogleOAuthService();

        if (!$oauth->isAuthenticated()) {
            log_message('error', 'uploadFileToGoogleDriveDirectOAuth - Not authenticated with Google. Please connect via /oauth/connect');
            return 'NOT_READY';
        }

        $webViewLink = $oauth->uploadFileContent($fileContent, $originalName, $mimeType);
        if ($webViewLink !== null) {
            log_message('info', 'uploadFileToGoogleDriveDirectOAuth - Uploaded via OAuth: ' . $originalName . ' -> ' . $webViewLink);
            return $webViewLink;
        }

        $reason = $oauth->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadFileToGoogleDriveDirectOAuth - OAuth upload failed for: ' . $originalName . '. Reason: ' . $reason);
        return 'FAILED_UPLOAD';
    }

    /**
     * Upload file content to structured SIMAK folder hierarchy in Google Drive.
     *
     * Folder structure:
     * [Root Folder] / [Nama Paket] / [Penyedia] / [Header Uraian] / [Uraian] / [file]
     *
     * @param string $fileContent Binary content of the file
     * @param string $originalName Original client file name
     * @param string $mimeType Mime type of the file
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header description (display_no)
     * @param string $uraian Description text
     * @return string|null Web view link or error string ('NOT_READY', 'FAILED_UPLOAD')
     */
    private function uploadFileToGoogleDriveStructured(
        string $fileContent,
        string $originalName,
        string $mimeType,
        string $namaPaket,
        string $penyedia,
        string $headerUraian,
        string $uraian
    ): ?string {
        $driveFolderId = trim((string) getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'));
        $oauthClientId = trim((string) getenv('GOOGLE_CLIENT_ID'));
        $oauthClientSecret = trim((string) getenv('GOOGLE_CLIENT_SECRET'));

        log_message('info', 'uploadFileToGoogleDriveStructured - Entry. OAuth configured: ' . ($oauthClientId !== '' ? 'YES' : 'NO') . ', Secret: ' . ($oauthClientSecret !== '' ? 'YES' : 'NO') . ', FolderId: ' . ($driveFolderId !== '' ? $driveFolderId : 'EMPTY'));

        // Use OAuth for personal Gmail accounts (recommended)
        if ($oauthClientId !== '' && $oauthClientSecret !== '' && $driveFolderId !== '') {
            log_message('info', 'uploadFileToGoogleDriveStructured - Attempting OAuth flow');
            return $this->uploadFileToGoogleDriveStructuredOAuth(
                $fileContent,
                $originalName,
                $mimeType,
                $driveFolderId,
                $namaPaket,
                $penyedia,
                $headerUraian,
                $uraian
            );
        }

        // Fallback to Service Account (only works with Google Workspace or Shared Drives)
        $serviceAccountPath = trim((string) getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH'));
        log_message('info', 'uploadFileToGoogleDriveStructured - Falling back to Service Account. Path: ' . ($serviceAccountPath !== '' ? $serviceAccountPath : 'EMPTY'));

        if ($serviceAccountPath === '' || $driveFolderId === '') {
            log_message('error', 'uploadFileToGoogleDriveStructured - Google Drive config missing.');
            return 'NOT_READY';
        }

        $gdrive = new \App\Libraries\GoogleDriveService();
        if (!$gdrive->isReady()) {
            $reason = $gdrive->getLastError() ?: 'Service not ready.';
            log_message('error', 'uploadFileToGoogleDriveStructured - GoogleDriveService is not ready. Reason: ' . $reason);
            return 'NOT_READY';
        }

        // Build structured folder path
        $targetFolderId = $gdrive->buildSimakFolderPath(
            $driveFolderId,
            $namaPaket,
            $penyedia,
            $headerUraian,
            $uraian
        );

        if ($targetFolderId === null) {
            log_message('error', 'uploadFileToGoogleDriveStructured - Failed to build folder path for: ' . $namaPaket . '/' . $penyedia . '/' . $headerUraian . '/' . $uraian);
            return 'FAILED_UPLOAD';
        }

        // Upload file to the structured folder
        $webViewLink = $gdrive->uploadFileContentToFolder($fileContent, $originalName, $mimeType, $targetFolderId);
        if ($webViewLink !== null) {
            log_message('info', 'uploadFileToGoogleDriveStructured - Uploaded to structured folder: ' .
                $namaPaket . '/' . $penyedia . '/' . $headerUraian . '/' . $uraian . ' -> ' . $webViewLink);
            return $webViewLink;
        }

        $reason = $gdrive->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadFileToGoogleDriveStructured - Upload failed: ' . $reason);
        return 'FAILED_UPLOAD';
    }

    /**
     * Upload file content to structured SIMAK folder using OAuth 2.0.
     *
     * @param string $fileContent Binary content of the file
     * @param string $originalName Original client file name
     * @param string $mimeType Mime type of the file
     * @param string $driveFolderId Root Drive folder ID
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header description
     * @param string $uraian Description text
     * @return string|null Web view link or error string
     */
    private function uploadFileToGoogleDriveStructuredOAuth(
        string $fileContent,
        string $originalName,
        string $mimeType,
        string $driveFolderId,
        string $namaPaket,
        string $penyedia,
        string $headerUraian,
        string $uraian
    ): ?string {
        $oauth = new \App\Libraries\GoogleOAuthService();

        $isAuth = $oauth->isAuthenticated();
        log_message('info', 'uploadFileToGoogleDriveStructuredOAuth - isAuthenticated: ' . ($isAuth ? 'YES' : 'NO') . ', LastError: ' . ($oauth->getLastError() ?: 'none'));

        if (!$isAuth) {
            log_message('error', 'uploadFileToGoogleDriveStructuredOAuth - Not authenticated with Google.');
            return 'NOT_READY';
        }

        // Build structured folder path
        $targetFolderId = $oauth->buildSimakFolderPath(
            $driveFolderId,
            $namaPaket,
            $penyedia,
            $headerUraian,
            $uraian
        );

        if ($targetFolderId === null) {
            log_message('error', 'uploadFileToGoogleDriveStructuredOAuth - Failed to build folder path for: ' .
                $namaPaket . '/' . $penyedia . '/' . $headerUraian . '/' . $uraian);
            return 'FAILED_UPLOAD';
        }

        // Upload file to the structured folder
        $webViewLink = $oauth->uploadFileContentToFolder($fileContent, $originalName, $mimeType, $targetFolderId);
        if ($webViewLink !== null) {
            log_message('info', 'uploadFileToGoogleDriveStructuredOAuth - Uploaded to structured folder: ' .
                $namaPaket . '/' . $penyedia . '/' . $headerUraian . '/' . $uraian . ' -> ' . $webViewLink);
            return $webViewLink;
        }

        $reason = $oauth->getLastError() ?: 'Unknown error';
        log_message('error', 'uploadFileToGoogleDriveStructuredOAuth - Upload failed: ' . $reason);
        return 'FAILED_UPLOAD';
    }

    /**
     * Fetch SIMAK item data including package name and provider.
     *
     * @param int $simakId SIMAK item ID
     * @param string $type 'konstruksi' or 'konsultasi'
     * @return array|null Array with 'nama_paket' and 'penyedia' or null if not found
     */
    private function getSimakPackageInfo(int $simakId, string $type = 'konstruksi'): ?array
    {
        $db = db_connect();
        $table = ($type === 'konsultasi') ? 'trn_kontrak_simak_konsultasi' : 'trn_kontrak_simak';

        if (!$db->tableExists($table)) {
            return null;
        }

        $builder = $db->table($table)
            ->select('nama_paket, penyedia')
            ->where('id', $simakId);

        $this->applyNotDeletedWhere($builder, $table);

        $result = $builder->get()->getRowArray();

        if (!is_array($result)) {
            return null;
        }

        return [
            'nama_paket' => trim((string) ($result['nama_paket'] ?? '')),
            'penyedia' => trim((string) ($result['penyedia'] ?? '')),
        ];
    }
}
