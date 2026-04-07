<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Kontrak extends BaseController
{
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
}
