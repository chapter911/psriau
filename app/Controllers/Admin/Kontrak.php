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

    public function simak()
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return view('admin/kontrak/simak', [
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

        return view('admin/kontrak/simak', [
            'title' => 'SIMAK Kontrak',
            'data' => $rows,
            'addOnsBySimakId' => $this->getSimakAddOnsBySimakId(),
            'pegawaiOptions' => $this->getSimakPegawaiOptions(),
            'can_edit' => $this->canViewKontrak(),
            'can_import' => $this->canManageKontrak(),
            'can_share' => $this->canManageKontrak(),
        ]);
    }

    public function createSimakShare(int $id)
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Anda tidak memiliki akses untuk membuat link share SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak') || ! $db->tableExists('trn_kontrak_simak_share')) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Tabel share SIMAK belum tersedia. Jalankan migration terbaru.');
        }
        $shareHasExpiresCol = $this->tableHasColumn('trn_kontrak_simak_share', 'expires_at');

        $simakBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($simakBuilder, 'trn_kontrak_simak');
        $simak = $simakBuilder->get()->getRowArray();
        if (! is_array($simak)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $duration = strtolower(trim((string) $this->request->getPost('share_duration')));
        $durationMap = [
            '1week' => '+1 week',
            '30days' => '+30 days',
        ];

        if (! array_key_exists($duration, $durationMap)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Durasi link share tidak valid. Pilih 1 minggu atau 30 hari.');
        }

        $expiresAtDate = date('Y-m-d', strtotime($durationMap[$duration]));
        $expiresAt = $expiresAtDate . ' 23:59:59';

        $token = $this->generateSimakShareToken();
        if ($token === null) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Gagal membuat token share SIMAK. Silakan coba lagi.');
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
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Gagal menyimpan link share SIMAK.');
        }

        $shareUrl = site_url('simak/share/' . $token);
        $successMessage = 'Link share SIMAK berhasil dibuat. Berlaku sampai ' . $expiresAtDate . '.';
        $redirect = redirect()->to(site_url('admin/kontrak/simak'))
            ->with('success', $successMessage)
            ->with('simak_share_link', $shareUrl);

        if (is_array($existing) && trim((string) ($existing['share_token'] ?? '')) !== '') {
            $redirect = $redirect->with('simak_share_notice', 'Sebaiknya bagikan link yang sudah ada agar pihak kontraktor tidak bingung. Buat link baru hanya jika memang diperlukan karena link lama akan tidak berlaku.');
        }

        return $redirect;
    }

    public function importSimak()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Anda tidak memiliki akses untuk import data SIMAK.');
        }

        if (! $this->isKontrakTableReady()) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'File import tidak valid.');
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, ['xls', 'xlsx'], true)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Format file harus .xls atau .xlsx.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'File Excel gagal dibaca. Pastikan format file valid (.xls/.xlsx).');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, true);
        if ($rows === []) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'File Excel kosong.');
        }

        $headerRow = array_shift($rows);
        if (! is_array($headerRow) || $headerRow === []) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Header Excel tidak ditemukan.');
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
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Header Excel tidak dikenali.');
        }

        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Master pegawai tidak tersedia. Import SIMAK membutuhkan referensi nama PPK dari NIP.');
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
                'penyedia' => trim((string) ($rowData['penyedia'] ?? '')),
                'nomor_kontrak' => $nomorKontrak,
                'nilai_kontrak' => $nilaiKontrak,
                'tahapan_pekerjaan' => trim((string) ($rowData['tahapan_pekerjaan'] ?? '')),
                'tanggal_pemeriksaan' => $this->normalizeDateValue((string) ($rowData['tanggal_pemeriksaan'] ?? '')),
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
            $redirect = redirect()->to(site_url('admin/kontrak/simak'))
                ->with('error', 'Tidak ada data yang diproses. Pastikan kolom minimal: ppk_nip, nama_paket, tahun_anggaran, nomor_kontrak, nilai_kontrak dan NIP tersedia di master pegawai.');

            if ($importReport !== []) {
                $redirect = $redirect->with('import_simak_report', $importReport);
            }

            return $redirect;
        }

        $message = 'Import SIMAK selesai. Insert: ' . $inserted . ', Update: ' . $updated . ', Dilewati: ' . $skipped . '.';
        $redirect = redirect()->to(site_url('admin/kontrak/simak'))->with('success', $message);
        if ($importReport !== []) {
            $redirect = $redirect->with('import_simak_report', $importReport);
        }

        return $redirect;
    }

    public function exportSimakTemplate()
    {
        if (! $this->canManageKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Anda tidak memiliki akses untuk mengunduh template SIMAK.');
        }

        $headers = [
            'satker',
            'ppk_nip',
            'nama_paket',
            'tahun_anggaran',
            'penyedia',
            'nomor_kontrak',
            'nilai_kontrak',
            'tahapan_pekerjaan',
            'tanggal_pemeriksaan',
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
            'Penyedia Contoh',
            'SIMAK/001/2026',
            1000000000,
            'Tahapan Contoh',
            '2026-04-15',
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
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Anda tidak memiliki akses untuk menambah data SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $satker = trim((string) $this->request->getPost('satker'));
        $ppkNip = trim((string) $this->request->getPost('ppk_nip'));
        $ppkNama = trim((string) $this->request->getPost('ppk_nama'));
        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $tahunAnggaran = trim((string) $this->request->getPost('tahun_anggaran'));
        $penyedia = trim((string) $this->request->getPost('penyedia'));
        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nilaiKontrak = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak'));
        $tahapanPekerjaan = trim((string) $this->request->getPost('tahapan_pekerjaan'));
        $tanggalPemeriksaan = $this->normalizeDateValue((string) $this->request->getPost('tanggal_pemeriksaan'));

        if ($satker === '') {
            $satker = 'Perencanaan Prasarana Strategis';
        }

        if ($ppkNip === '' || $ppkNama === '' || $namaPaket === '' || $tahunAnggaran === '' || $nomorKontrak === '') {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Field wajib belum lengkap.');
        }

        if (! preg_match('/^\d{4}\s*-\s*\d{4}$/', $tahunAnggaran)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Format tahun anggaran harus seperti 2024 - 2025.');
        }

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')->select('nip, nama')->where('nip', $ppkNip)->get()->getRowArray();
            if (! is_array($pegawai)) {
                return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'NIP PPK tidak ditemukan pada master pegawai.');
            }

            $ppkNama = trim((string) ($pegawai['nama'] ?? $ppkNama));
        }

        $duplicateBuilder = $db->table('trn_kontrak_simak')->select('id')->where('nomor_kontrak', $nomorKontrak);
        $this->applyNotDeletedWhere($duplicateBuilder, 'trn_kontrak_simak');
        $duplicate = $duplicateBuilder->get()->getRowArray();
        if (is_array($duplicate)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Nomor kontrak sudah digunakan.');
        }

        $payload = [
            'satker' => $satker,
            'ppk_nama' => $ppkNama,
            'ppk_nip' => $ppkNip,
            'nama_paket' => $namaPaket,
            'tahun_anggaran' => $tahunAnggaran,
            'penyedia' => $penyedia,
            'nomor_kontrak' => $nomorKontrak,
            'nilai_kontrak' => $nilaiKontrak,
            'tahapan_pekerjaan' => $tahapanPekerjaan,
            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
            'created_by' => (string) (session()->get('username') ?: session()->get('name') ?: 'system'),
            'created_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $db->table('trn_kontrak_simak')->insert($payload);
        if (! $ok) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Gagal menyimpan data SIMAK.');
        }

        return redirect()->to(site_url('admin/kontrak/simak'))->with('success', 'Data SIMAK berhasil disimpan.');
    }

    public function updateSimak(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Anda tidak memiliki akses untuk mengubah data SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $satker = trim((string) $this->request->getPost('satker'));
        $ppkNip = trim((string) $this->request->getPost('ppk_nip'));
        $ppkNama = trim((string) $this->request->getPost('ppk_nama'));
        $namaPaket = trim((string) $this->request->getPost('nama_paket'));
        $tahunAnggaran = trim((string) $this->request->getPost('tahun_anggaran'));
        $penyedia = trim((string) $this->request->getPost('penyedia'));
        $nomorKontrak = trim((string) $this->request->getPost('nomor_kontrak'));
        $nilaiKontrak = $this->parseMoneyToFloat($this->request->getPost('nilai_kontrak'));
        $tahapanPekerjaan = trim((string) $this->request->getPost('tahapan_pekerjaan'));
        $tanggalPemeriksaan = $this->normalizeDateValue((string) $this->request->getPost('tanggal_pemeriksaan'));

        if ($satker === '') {
            $satker = 'Perencanaan Prasarana Strategis';
        }

        if ($ppkNip === '' || $ppkNama === '' || $namaPaket === '' || $tahunAnggaran === '' || $nomorKontrak === '') {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Field wajib belum lengkap.');
        }

        if (! preg_match('/^\d{4}\s*-\s*\d{4}$/', $tahunAnggaran)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Format tahun anggaran harus seperti 2024 - 2025.');
        }

        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')->select('nip, nama')->where('nip', $ppkNip)->get()->getRowArray();
            if (! is_array($pegawai)) {
                return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'NIP PPK tidak ditemukan pada master pegawai.');
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
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Nomor kontrak sudah digunakan pada data lain.');
        }

        $payload = [
            'satker' => $satker,
            'ppk_nama' => $ppkNama,
            'ppk_nip' => $ppkNip,
            'nama_paket' => $namaPaket,
            'tahun_anggaran' => $tahunAnggaran,
            'penyedia' => $penyedia,
            'nomor_kontrak' => $nomorKontrak,
            'nilai_kontrak' => $nilaiKontrak,
            'tahapan_pekerjaan' => $tahapanPekerjaan,
            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
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
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Gagal mengubah data SIMAK.');
        }

        return redirect()->to(site_url('admin/kontrak/simak'))->with('success', 'Data SIMAK berhasil diubah.');
    }

    public function detailSimak(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Tabel SIMAK belum tersedia. Jalankan migration.');
        }

        $builder = $db->table('trn_kontrak_simak')->select('*')->where('id', $id);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak');
        $item = $builder->get()->getRowArray();

        if (! is_array($item)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
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
                ->select('id, row_no, file_original_name, file_mime, file_size, created_at, created_by, kelengkapan_dokumen, verifikasi_ki, keterangan, pic')
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

        $addOns = [];
        $nilaiAddOn = 0.0;
        if ($db->tableExists('trn_kontrak_simak_add_on')) {
            $addOnBuilder = $db->table('trn_kontrak_simak_add_on')
                ->select('id, urutan, nilai_add_on, tanggal_add_on')
                ->where('simak_id', $id)
                ->orderBy('urutan', 'ASC')
                ->orderBy('id', 'ASC');
            $this->applyNotDeletedWhere($addOnBuilder, 'trn_kontrak_simak_add_on');
            $addOns = $addOnBuilder->get()->getResultArray();

            foreach ($addOns as $row) {
                $nilaiAddOn += (float) ($row['nilai_add_on'] ?? 0);
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

        return view('admin/kontrak/simak_detail', [
            'title' => 'Detail SIMAK',
            'item' => $item,
            'addOns' => $addOns,
            'nilaiAddOn' => $nilaiAddOn,
            'totalKontrak' => ((float) ($item['nilai_kontrak'] ?? 0)) + $nilaiAddOn,
            'templateItems' => $templateItems,
            'verifikasiByRow' => $verifikasiByRow,
            'dokumenByRow' => $dokumenByRow,
            'kelengkapanPercentage' => $kelengkapanPercentage,
        ]);
    }

    public function saveSimakVerifikasi(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Anda tidak memiliki akses untuk menyimpan verifikasi SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak') || ! $db->tableExists('trn_kontrak_simak_verifikasi')) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Tabel verifikasi SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $templateItems = $this->getSimakPelaksanaanFisikTemplateItems();
        if ($templateItems === []) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Template verifikasi SIMAK tidak ditemukan.');
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
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Gagal menyimpan verifikasi SIMAK.');
        }

        return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('success', 'Verifikasi SIMAK berhasil disimpan.');
    }

    public function uploadSimakVerifikasiDokumen(int $id)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Anda tidak memiliki akses untuk upload dokumen verifikasi SIMAK.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak')
            || ! $db->tableExists('trn_kontrak_simak_verifikasi')
            || ! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Tabel dokumen verifikasi SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $existingBuilder = $db->table('trn_kontrak_simak')->select('id')->where('id', $id);
        $this->applyNotDeletedWhere($existingBuilder, 'trn_kontrak_simak');
        $existing = $existingBuilder->get()->getRowArray();
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Data SIMAK tidak ditemukan.');
        }

        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($rowNo <= 0) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Baris verifikasi tidak valid.');
        }

        $templateItems = $this->getSimakPelaksanaanFisikTemplateItems();
        $templateByRow = [];
        foreach ($templateItems as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Upload hanya diizinkan pada baris hirarki terbawah.');
        }

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

        if (! $hasUpload && $kel === null && $ver === null && $ket === '' && $pic === '') {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Tidak ada perubahan yang disimpan. Isi data atau upload file terlebih dahulu.');
        }

        $relativePath = '';
        $storedName = '';
        if ($hasUpload) {
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
            $ext = strtolower((string) $file->getClientExtension());
            if (! in_array($ext, $allowedExt, true)) {
                return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX.');
            }

            $subDir = 'uploads/simak_verifikasi/' . $id . '/' . $rowNo;
            $absDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir);
            if (! is_dir($absDir) && ! @mkdir($absDir, 0775, true) && ! is_dir($absDir)) {
                return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Gagal membuat direktori upload dokumen.');
            }

            $storedName = $file->getRandomName();
            $file->move($absDir, $storedName, true);
            $relativePath = $subDir . '/' . $storedName;
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
            'verifikasi_ki' => $ver,
            'keterangan' => $ket,
            'pic' => $pic,
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
                'file_original_name' => (string) $file->getClientName(),
                'file_stored_name' => $storedName,
                'file_relative_path' => $relativePath,
                'file_mime' => (string) ($file->getClientMimeType() ?: ''),
                'file_size' => (int) ($file->getSizeByUnit('b') ?? 0),
                'created_by' => $actor,
                'created_date' => $today,
                'created_at' => $now,
            ];
            $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert($dokumenRow);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('error', 'Gagal menyimpan upload dokumen verifikasi SIMAK.');
        }

        $message = $hasUpload
            ? 'Update verifikasi tersimpan dan dokumen berhasil diupload. Riwayat dokumen tercatat.'
            : 'Update verifikasi berhasil disimpan.';

        return redirect()->to(site_url('admin/kontrak/simak/' . $id))->with('success', $message);
    }

    public function viewSimakVerifikasiDokumen(int $dokumenId)
    {
        if (! $this->canViewKontrak()) {
            return redirect()->to(site_url('/admin'));
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Tabel dokumen verifikasi SIMAK belum tersedia.');
        }

        $builder = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id, simak_id, file_original_name, file_relative_path, file_mime')
            ->where('id', $dokumenId);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_verifikasi_dokumen');
        $row = $builder->get()->getRowArray();

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/kontrak/simak'))->with('error', 'Dokumen verifikasi tidak ditemukan.');
        }

        $relativePath = ltrim((string) ($row['file_relative_path'] ?? ''), '/');
        $absPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if ($relativePath === '' || ! is_file($absPath)) {
            return redirect()->to(site_url('admin/kontrak/simak/' . (int) ($row['simak_id'] ?? 0)))->with('error', 'File dokumen tidak ditemukan di server.');
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

    public function sharedSimak(string $token): string
    {
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tautan share SIMAK tidak valid.');
        }

        // Calculate document completion percentages
        $simakId = (int) ($shared['item']['id'] ?? 0);
        $kelengkapanPercentages = $this->getSimakAdministrasiKelengkapanBySimakId([$simakId]);
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
        ]);
    }

    public function sharedUploadSimakDokumen(string $token)
    {
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tautan share SIMAK tidak valid.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_verifikasi') || ! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Tabel dokumen verifikasi SIMAK belum tersedia. Jalankan migration terbaru.');
        }

        $simakId = (int) ($shared['item']['id'] ?? 0);
        $rowNo = (int) ($this->request->getPost('row_no') ?? 0);
        if ($simakId <= 0 || $rowNo <= 0) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Baris verifikasi tidak valid.');
        }

        $templateByRow = [];
        foreach (($shared['templateItems'] ?? []) as $templateItem) {
            $templateByRow[(int) ($templateItem['row_no'] ?? 0)] = $templateItem;
        }

        $targetTemplate = $templateByRow[$rowNo] ?? null;
        if (! is_array($targetTemplate) || (($targetTemplate['is_leaf'] ?? false) !== true)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Upload hanya diizinkan pada baris hirarki terbawah.');
        }

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $googleCredential = trim((string) $this->request->getPost('google_access_token'));
        $googleIdentity = $this->verifyGoogleAccessToken($googleCredential);
        if (! is_array($googleIdentity)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Login Google diperlukan sebelum upload dokumen.');
        }

        $googleName = trim((string) ($googleIdentity['name'] ?? 'Google User'));
        $googleEmail = trim((string) ($googleIdentity['email'] ?? ''));
        $actorLabel = $googleName !== '' ? $googleName : 'Google User';
        if ($googleEmail !== '') {
            $actorLabel .= ' <' . $googleEmail . '>';
        }
        $actor = 'google: ' . $actorLabel;

        $existingVerifikasi = $db->table('trn_kontrak_simak_verifikasi')
            ->select('verifikasi_ki, keterangan, pic')
            ->where('simak_id', $simakId)
            ->where('row_no', $rowNo)
            ->get()
            ->getRowArray();

        $existingVerifikasiStatus = is_array($existingVerifikasi) ? strtolower(trim((string) ($existingVerifikasi['verifikasi_ki'] ?? ''))) : '';
        if (! in_array($existingVerifikasiStatus, ['sesuai', 'tidak_sesuai'], true)) {
            $existingVerifikasiStatus = null;
        }

        $existingDokumen = $db->table('trn_kontrak_simak_verifikasi_dokumen')
            ->select('id')
            ->where('simak_id', $simakId)
            ->where('row_no', $rowNo)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($existingVerifikasiStatus === 'sesuai' && is_array($existingDokumen)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Dokumen sudah ada dan status verifikasi sudah sesuai. Upload tidak diperlukan.');
        }

        // Every contractor upload resets verification status to "menunggu verifikasi".
        $verifikasi = null;

        $file = $this->request->getFile('dokumen_file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'File upload tidak valid.');
        }

        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $ext = strtolower((string) $file->getClientExtension());
        if (! in_array($ext, $allowedExt, true)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Tipe file tidak didukung. Gunakan PDF/JPG/PNG/DOC/DOCX/XLS/XLSX.');
        }

        $subDir = 'uploads/simak_verifikasi/' . $simakId . '/' . $rowNo;
        $absDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir);
        if (! is_dir($absDir) && ! @mkdir($absDir, 0775, true) && ! is_dir($absDir)) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Gagal membuat direktori upload dokumen.');
        }

        $storedName = $file->getRandomName();
        $file->move($absDir, $storedName, true);
        $relativePath = $subDir . '/' . $storedName;

        $keterangan = 'Menunggu Verifikasi';
        $pic = is_array($existingVerifikasi) ? trim((string) ($existingVerifikasi['pic'] ?? '')) : '';

        $verifikasiRow = [
            'simak_id' => $simakId,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => 'ada',
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

        $dokumenRow = [
            'simak_id' => $simakId,
            'row_no' => $rowNo,
            'kode' => (string) ($targetTemplate['display_no'] ?? ''),
            'uraian' => (string) ($targetTemplate['uraian'] ?? ''),
            'kelengkapan_dokumen' => 'ada',
            'verifikasi_ki' => $verifikasi,
            'keterangan' => $keterangan,
            'pic' => $pic,
            'file_original_name' => (string) $file->getClientName(),
            'file_stored_name' => $storedName,
            'file_relative_path' => $relativePath,
            'file_mime' => (string) ($file->getClientMimeType() ?: ''),
            'file_size' => (int) ($file->getSizeByUnit('b') ?? 0),
            'created_by' => $actor,
            'created_date' => $today,
            'created_at' => $now,
        ];

        $db->transStart();
        $db->table('trn_kontrak_simak_verifikasi')->where('simak_id', $simakId)->where('row_no', $rowNo)->delete();
        $db->table('trn_kontrak_simak_verifikasi')->insert($verifikasiRow);
        $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert($dokumenRow);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('simak/share/' . $token))->with('error', 'Gagal menyimpan upload dokumen.');
        }

        return redirect()->to(site_url('simak/share/' . $token))->with('success', 'Dokumen berhasil diupload. Status kelengkapan dokumen diperbarui menjadi Ada dan Verifikasi Dit. KI menjadi Menunggu Verifikasi.');
    }

    public function sharedDownloadDokumen(string $token, int $dokumenId)
    {
        $shared = $this->resolveSharedSimak($token);
        if ($shared === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tautan share SIMAK tidak valid atau sudah kedaluwarsa.');
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            return $this->renderSharedDokumenNotFound(
                $token,
                'Data dokumen verifikasi belum tersedia. Silakan hubungi admin.',
                is_array($shared['item'] ?? null) ? $shared['item'] : null
            );
        }

        $dokumen = $db->table('trn_kontrak_simak_verifikasi_dokumen')
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

        $relativePath = (string) ($dokumen['file_relative_path'] ?? '');
        $originalName = (string) ($dokumen['file_original_name'] ?? 'dokumen');

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

        return $this->response->download($filePath, null)->setFileName($originalName);
    }

    private function verifyGoogleAccessToken(string $accessToken): ?array
    {
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
        if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
            return '';
        }

        $raw = trim((string) getenv('GOOGLE_CLIENT_ID'));
        return trim($raw, " \t\n\r\0\x0B'\"");
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

    private function getSimakSharePublicUrlBySimakId(array $simakIds): array
    {
        $simakIds = array_values(array_unique(array_filter(array_map('intval', $simakIds), static function (int $id): bool {
            return $id > 0;
        })));

        if ($simakIds === []) {
            return [];
        }

        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_share')) {
            return [];
        }
        $shareHasExpiresCol = $this->tableHasColumn('trn_kontrak_simak_share', 'expires_at');

        $select = 'simak_id, share_token';
        if ($shareHasExpiresCol) {
            $select .= ', expires_at';
        }

        $rows = $db->table('trn_kontrak_simak_share')
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

            if ($shareHasExpiresCol && ! $this->isSimakShareActiveByDate($expiresAt)) {
                continue;
            }

            $result[$simakId] = site_url('simak/share/' . $token);
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
        if (! $db->tableExists('trn_kontrak_simak_share') || ! $db->tableExists('trn_kontrak_simak')) {
            return null;
        }
        $shareHasExpiresCol = $this->tableHasColumn('trn_kontrak_simak_share', 'expires_at');

        $shareSelect = 'id, simak_id, share_token, is_active';
        if ($shareHasExpiresCol) {
            $shareSelect .= ', expires_at';
        }

        $share = $db->table('trn_kontrak_simak_share')
            ->select($shareSelect)
            ->where('share_token', $token)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if (! is_array($share)) {
            return null;
        }

        $expiresAt = trim((string) ($share['expires_at'] ?? ''));
        if ($shareHasExpiresCol && ! $this->isSimakShareActiveByDate($expiresAt)) {
            return null;
        }

        $shareSimakId = (int) ($share['simak_id'] ?? 0);
        if ($shareSimakId <= 0) {
            return null;
        }

        $simakBuilder = $db->table('trn_kontrak_simak')->select('*')->where('id', $shareSimakId);
        $this->applyNotDeletedWhere($simakBuilder, 'trn_kontrak_simak');
        $item = $simakBuilder->get()->getRowArray();
        if (! is_array($item)) {
            return null;
        }

        $templateItems = $this->getSimakPelaksanaanFisikTemplateItems();
        if ($templateItems === []) {
            return null;
        }

        $verifikasiByRow = [];
        if ($db->tableExists('trn_kontrak_simak_verifikasi')) {
            $verifikasiRows = $db->table('trn_kontrak_simak_verifikasi')
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
        if ($db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
            $dokumenBuilder = $db->table('trn_kontrak_simak_verifikasi_dokumen')
                ->select('id, row_no, file_original_name, file_mime, file_size, created_at, created_by')
                ->where('simak_id', $shareSimakId)
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

        return [
            'share' => $share,
            'item' => $item,
            'templateItems' => $templateItems,
            'verifikasiByRow' => $verifikasiByRow,
            'dokumenByRow' => $dokumenByRow,
        ];
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

        $db->table('trn_kontrak_simak_add_on')->where('simak_id', $simakId)->delete();

        $rows = [];
        $urutan = 1;
        foreach ($values as $value) {
            $nominal = $this->parseMoneyToFloat($value);
            if ($nominal <= 0) {
                continue;
            }

            $tanggalAddOn = $this->normalizeDateValue((string) ($dates[$urutan - 1] ?? ''));

            $rows[] = [
                'simak_id' => $simakId,
                'urutan' => $urutan++,
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

    private function getSimakAddOnsBySimakId(): array
    {
        $db = db_connect();
        if (! $db->tableExists('trn_kontrak_simak_add_on')) {
            return [];
        }

        $builder = $db->table('trn_kontrak_simak_add_on')
            ->select('simak_id, urutan, nilai_add_on, tanggal_add_on')
            ->orderBy('simak_id', 'ASC')
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC');
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_add_on');

        $rows = $builder->get()->getResultArray();
        $grouped = [];
        foreach ($rows as $row) {
            $simakId = (int) ($row['simak_id'] ?? 0);
            if (! isset($grouped[$simakId])) {
                $grouped[$simakId] = [];
            }

            $grouped[$simakId][] = [
                'urutan' => (int) ($row['urutan'] ?? 0),
                'nilai_add_on' => (float) ($row['nilai_add_on'] ?? 0),
                'tanggal_add_on' => (string) ($row['tanggal_add_on'] ?? ''),
            ];
        }

        return $grouped;
    }

    private function getSimakAdministrasiKelengkapanBySimakId(array $simakIds): array
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

        $templateItems = $this->getSimakPelaksanaanFisikTemplateItems();
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
        if (! $db->tableExists('trn_kontrak_simak_verifikasi')) {
            return $result;
        }

        $builder = $db->table('trn_kontrak_simak_verifikasi')
            ->select('simak_id, row_no, kelengkapan_dokumen, verifikasi_ki')
            ->whereIn('simak_id', $simakIds)
            ->whereIn('row_no', $leafRows);
        $this->applyNotDeletedWhere($builder, 'trn_kontrak_simak_verifikasi');

        $rows = $builder->get()->getResultArray();

        $statusBySimak = [];
        foreach ($rows as $row) {
            $simakId = (int) ($row['simak_id'] ?? 0);
            $rowNo = (int) ($row['row_no'] ?? 0);
            $kelengkapan = strtolower(trim((string) ($row['kelengkapan_dokumen'] ?? '')));
            $verifikasi = strtolower(trim((string) ($row['verifikasi_ki'] ?? '')));

            if ($simakId <= 0 || $rowNo <= 0) {
                continue;
            }

            if (! isset($statusBySimak[$simakId])) {
                $statusBySimak[$simakId] = [];
            }

            if ($kelengkapan === 'ada' && $verifikasi === 'sesuai') {
                $statusBySimak[$simakId][$rowNo] = 'lengkap';
            } elseif ($kelengkapan === 'ada' && $verifikasi === 'tidak_sesuai') {
                $statusBySimak[$simakId][$rowNo] = 'belum_sesuai';
            } elseif ($kelengkapan === 'ada') {
                $statusBySimak[$simakId][$rowNo] = 'belum_verifikasi';
            } else {
                $statusBySimak[$simakId][$rowNo] = 'belum_ada';
            }
        }

        foreach ($simakIds as $simakId) {
            $lengkapCount = 0;
            $belumSesuaiCount = 0;
            $belumVerifikasiCount = 0;
            $belumAdaCount = 0;

            foreach ($leafRows as $rowNo) {
                $status = $statusBySimak[$simakId][$rowNo] ?? 'belum_ada';
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

    private function getSimakPelaksanaanFisikTemplateItems(): array
    {
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
            }

            return $items;
        } catch (\Throwable $e) {
            return [];
        }
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
