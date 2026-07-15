<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DisposisiPerjalananDinasModel;
use App\Models\MstPegawaiModel;
use App\Models\MstTransportasiModel;
use App\Models\LaporanPerjalananDinasModel;

class DisposisiPerjalananDinas extends BaseController
{
    public function index()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $isAjax = $this->request->isAJAX();
        $isDt = $this->isDataTableRequest();
        $logMsg = date('Y-m-d H:i:s') . " - Disposisi::index() - isAJAX: " . ($isAjax ? 'YES' : 'NO') . " - isDt: " . ($isDt ? 'YES' : 'NO') . " - GET: " . json_encode($this->request->getGet()) . "\n";
        file_put_contents(WRITEPATH . 'ajax_debug.log', $logMsg, FILE_APPEND);

        if ($isAjax || $isDt) {
            return $this->dataTable();
        }

        $pegawaiRows = $this->loadPegawaiOptions();
        $kabupatenOptions = $this->loadKabupatenOptions();
        $transportasiOptions = (new MstTransportasiModel())->orderBy('nama_transportasi', 'ASC')->findAll();

        return view('admin/surat/disposisi_perjalanan_dinas', [
            'title'                => 'Disposisi Perjalanan Dinas',
            'can_edit'             => $this->canAccess(),
            'pegawai_options'      => $pegawaiRows,
            'kabupaten_options'    => $kabupatenOptions,
            'transportasi_options' => $transportasiOptions,
        ]);
    }

    private function dataTable()
    {
        $draw = $this->getDataTableDraw();
        $start = $this->getDataTableStart();
        $length = $this->getDataTableLength();
        $search = $this->getDataTableSearchTerm();

        $db = db_connect();
        
        if (! $db->tableExists('disposisi_perjalanan_dinas')) {
            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
        }

        $builder = $db->table('disposisi_perjalanan_dinas');
        $recordsTotal = (int) $builder->countAllResults(false);

        $startDate = trim((string) $this->request->getGet('filter_start_date'));
        $endDate = trim((string) $this->request->getGet('filter_end_date'));
        $kota = trim((string) $this->request->getGet('filter_kota'));
        $pelaksanaId = (int) $this->request->getGet('filter_pelaksana');

        $logMsg = date('Y-m-d H:i:s') . " - dataTable() params - start_date: $startDate, end_date: $endDate, kota: $kota, pelaksana: $pelaksanaId\n";
        file_put_contents(WRITEPATH . 'ajax_debug.log', $logMsg, FILE_APPEND);

        if ($startDate !== '') {
            $builder->where('periode_mulai >=', $startDate);
        }
        if ($endDate !== '') {
            $builder->where('periode_selesai <=', $endDate);
        }
        if ($kota !== '') {
            $builder->where('kota_tujuan', $kota);
        }
        if ($pelaksanaId > 0) {
            $builder->groupStart()
                ->like('pelaksana_json', '"id":' . $pelaksanaId . ',')
                ->orLike('pelaksana_json', '"id":' . $pelaksanaId . '}')
                ->groupEnd();
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('tujuan', $search)
                ->orLike('transportasi', $search)
                ->orLike('perihal', $search)
                ->orLike('pelaksana_json', $search)
                ->groupEnd();
        }

        $recordsFiltered = (int) $builder->countAllResults(false);

        // Sorting by id desc
        $rows = $builder->orderBy('id', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($rows as $row) {
            // Format Pelaksana list
            $pelaksanaList = json_decode((string) ($row['pelaksana_json'] ?? '[]'), true);
            $pelaksanaHtml = '<ol class="pl-3 mb-0">';
            if (empty($pelaksanaList)) {
                $pelaksanaHtml .= '<li>-</li>';
            } else {
                foreach ($pelaksanaList as $p) {
                    $pelaksanaHtml .= '<li>' . esc($p['nama']) . ' (' . esc($p['nip'] ?: '-') . ')</li>';
                }
            }
            $pelaksanaHtml .= '</ol>';

            // Format Periode
            $tglMulai = $row['periode_mulai'] ? date('d-m-Y', strtotime($row['periode_mulai'])) : '-';
            $tglSelesai = $row['periode_selesai'] ? date('d-m-Y', strtotime($row['periode_selesai'])) : '-';
            $periodeHtml = $tglMulai === $tglSelesai ? $tglMulai : $tglMulai . ' s/d ' . $tglSelesai;

            // Action Buttons
            $actionHtml = '<div class="doc-btn-group">';
            $actionHtml .= '<a href="' . site_url('admin/surat/perjalanan-dinas/disposisi/' . $row['id'] . '/pdf') . '" class="btn btn-sm btn-danger btn-pdf" title="Cetak Disposisi (PDF)" target="_blank"><i class="fas fa-file-pdf"></i> Cetak</a>';
            $actionHtml .= '<button type="button" class="btn btn-sm btn-info btn-edit ml-1" data-id="' . $row['id'] . '" title="Ubah"><i class="fas fa-edit"></i> Ubah</button>';
            $actionHtml .= '<button type="button" class="btn btn-sm btn-danger btn-delete ml-1" data-id="' . $row['id'] . '" title="Hapus"><i class="fas fa-trash"></i> Hapus</button>';
            $actionHtml .= '</div>';

            $data[] = [
                'id'              => $row['id'],
                'pelaksana_html'  => $pelaksanaHtml,
                'pelaksana_raw'   => $pelaksanaList,
                'periode_html'    => $periodeHtml,
                'periode_mulai'   => $row['periode_mulai'],
                'periode_selesai' => $row['periode_selesai'],
                'kota_tujuan'     => $row['kota_tujuan'] ?? '',
                'tujuan'          => $row['tujuan'],
                'transportasi'    => $row['transportasi'],
                'perihal'         => $row['perihal'],
                'menyetujui_id'   => $row['menyetujui_pegawai_id'],
                'diketahui_id'    => $row['diketahui_pegawai_id'],
                'action_html'     => $actionHtml,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function buat()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'));
        }

        $pelaksanaIds = $this->request->getPost('pelaksana_id');
        if (is_array($pelaksanaIds)) {
            $pelaksanaIds = array_filter(array_map('intval', $pelaksanaIds));
            $pelaksanaIds = array_values(array_unique($pelaksanaIds));
        } else {
            $pelaksanaIds = [];
        }

        $periodeMulai = trim((string) $this->request->getPost('periode_mulai'));
        $periodeSelesai = trim((string) $this->request->getPost('periode_selesai'));
        $kotaTujuan = trim((string) $this->request->getPost('kota_tujuan'));
        $tujuan = trim((string) $this->request->getPost('tujuan'));
        $transportasiList = $this->request->getPost('transportasi');
        if (is_array($transportasiList)) {
            $transportasi = implode(', ', array_filter(array_map('trim', $transportasiList)));
        } else {
            $transportasi = trim((string) $transportasiList);
        }
        $perihal = trim((string) $this->request->getPost('perihal'));
        $menyetujuiId = (int) $this->request->getPost('menyetujui_pegawai_id');
        $diketahuiId = (int) $this->request->getPost('diketahui_pegawai_id');

        $errors = [];
        if (empty($pelaksanaIds)) {
            $errors[] = 'Pelaksana SPPD minimal harus dipilih 1 orang.';
        }
        if ($periodeMulai === '') {
            $errors[] = 'Periode Mulai Perjalanan Dinas wajib diisi.';
        }
        if ($periodeSelesai === '') {
            $errors[] = 'Periode Selesai Perjalanan Dinas wajib diisi.';
        }
        if ($kotaTujuan === '') {
            $errors[] = 'Kota/Kab. Tujuan Perjalanan Dinas wajib diisi.';
        }
        if ($tujuan === '') {
            $errors[] = 'Tujuan Perjalanan Dinas wajib diisi.';
        }
        if ($transportasi === '') {
            $errors[] = 'Transportasi wajib diisi.';
        }
        if ($perihal === '') {
            $errors[] = 'Perihal wajib diisi.';
        }
        if ($menyetujuiId <= 0) {
            $errors[] = 'Pejabat Pembuat Komitmen (Menyetujui) wajib dipilih.';
        }
        if ($diketahuiId <= 0) {
            $errors[] = 'Kepala Satuan Kerja (Diketahui) wajib dipilih.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', implode(' ', $errors));
        }

        $pegawaiOptions = $this->loadPegawaiOptions();
        $pelaksanaRows = $this->buildPegawaiRowsByIds($pegawaiOptions, $pelaksanaIds);
        $username = trim((string) session()->get('username'));

        $model = new DisposisiPerjalananDinasModel();
        $data = [
            'pelaksana_json'        => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'periode_mulai'         => $periodeMulai,
            'periode_selesai'       => $periodeSelesai,
            'kota_tujuan'           => $kotaTujuan,
            'tujuan'                => $tujuan,
            'transportasi'          => $transportasi,
            'perihal'               => $perihal,
            'menyetujui_pegawai_id' => $menyetujuiId,
            'diketahui_pegawai_id'  => $diketahuiId,
            'created_by'            => $username,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        $insertId = $model->insert($data);
        if ($insertId === false) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', 'Gagal menyimpan data.');
        }

        // Auto-create draft record in laporan_perjalanan_dinas table
        $satkerData = $this->getPegawaiSignatureData($diketahuiId);
        $laporanModel = new LaporanPerjalananDinasModel();
        $laporanModel->insert([
            'disposisi_id'           => $insertId,
            'nomor_surat_tugas'      => '',
            'periode_mulai'          => $periodeMulai !== '' ? $periodeMulai : null,
            'periode_selesai'        => $periodeSelesai !== '' ? $periodeSelesai : null,
            'kota_tujuan'            => $kotaTujuan,
            'tujuan'                 => $tujuan,
            'sasaran'                => '',
            'laporan_hasil'          => '',
            'pelaksana_json'         => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'foto_dokumentasi_json'  => '[]',
            'dokumen_pendukung_json' => '[]',
            'creator_name'           => session()->get('fullName') ?: session()->get('username') ?: 'system',
            'creator_pegawai_json'   => '[]',
            'diketahui_oleh_json'    => json_encode($satkerData, JSON_UNESCAPED_UNICODE),
            'is_final'               => 0,
            'created_at'             => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('success', 'Disposisi Perjalanan Dinas berhasil disimpan.');
    }

    public function ubah(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new DisposisiPerjalananDinasModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', 'Data tidak ditemukan.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'));
        }

        $pelaksanaIds = $this->request->getPost('pelaksana_id');
        if (is_array($pelaksanaIds)) {
            $pelaksanaIds = array_filter(array_map('intval', $pelaksanaIds));
            $pelaksanaIds = array_values(array_unique($pelaksanaIds));
        } else {
            $pelaksanaIds = [];
        }

        $periodeMulai = trim((string) $this->request->getPost('periode_mulai'));
        $periodeSelesai = trim((string) $this->request->getPost('periode_selesai'));
        $kotaTujuan = trim((string) $this->request->getPost('kota_tujuan'));
        $tujuan = trim((string) $this->request->getPost('tujuan'));
        $transportasiList = $this->request->getPost('transportasi');
        if (is_array($transportasiList)) {
            $transportasi = implode(', ', array_filter(array_map('trim', $transportasiList)));
        } else {
            $transportasi = trim((string) $transportasiList);
        }
        $perihal = trim((string) $this->request->getPost('perihal'));
        $menyetujuiId = (int) $this->request->getPost('menyetujui_pegawai_id');
        $diketahuiId = (int) $this->request->getPost('diketahui_pegawai_id');

        $errors = [];
        if (empty($pelaksanaIds)) {
            $errors[] = 'Pelaksana SPPD minimal harus dipilih 1 orang.';
        }
        if ($periodeMulai === '') {
            $errors[] = 'Periode Mulai Perjalanan Dinas wajib diisi.';
        }
        if ($periodeSelesai === '') {
            $errors[] = 'Periode Selesai Perjalanan Dinas wajib diisi.';
        }
        if ($kotaTujuan === '') {
            $errors[] = 'Kota/Kab. Tujuan Perjalanan Dinas wajib diisi.';
        }
        if ($tujuan === '') {
            $errors[] = 'Tujuan Perjalanan Dinas wajib diisi.';
        }
        if ($transportasi === '') {
            $errors[] = 'Transportasi wajib diisi.';
        }
        if ($perihal === '') {
            $errors[] = 'Perihal wajib diisi.';
        }
        if ($menyetujuiId <= 0) {
            $errors[] = 'Pejabat Pembuat Komitmen (Menyetujui) wajib dipilih.';
        }
        if ($diketahuiId <= 0) {
            $errors[] = 'Kepala Satuan Kerja (Diketahui) wajib dipilih.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', implode(' ', $errors));
        }

        $pegawaiOptions = $this->loadPegawaiOptions();
        $pelaksanaRows = $this->buildPegawaiRowsByIds($pegawaiOptions, $pelaksanaIds);
        $username = trim((string) session()->get('username'));

        $data = [
            'pelaksana_json'        => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'periode_mulai'         => $periodeMulai,
            'periode_selesai'       => $periodeSelesai,
            'kota_tujuan'           => $kotaTujuan,
            'tujuan'                => $tujuan,
            'transportasi'          => $transportasi,
            'perihal'               => $perihal,
            'menyetujui_pegawai_id' => $menyetujuiId,
            'diketahui_pegawai_id'  => $diketahuiId,
            'updated_by'            => $username,
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        if ($model->update($id, $data) === false) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', 'Gagal memperbarui data.');
        }

        // Sync with laporan_perjalanan_dinas table
        $laporanModel = new LaporanPerjalananDinasModel();
        $existingLaporan = $laporanModel->where('disposisi_id', $id)->first();
        $satkerData = $this->getPegawaiSignatureData($diketahuiId);

        $laporanPayload = [
            'periode_mulai'       => $periodeMulai !== '' ? $periodeMulai : null,
            'periode_selesai'     => $periodeSelesai !== '' ? $periodeSelesai : null,
            'kota_tujuan'         => $kotaTujuan,
            'tujuan'              => $tujuan,
            'pelaksana_json'      => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'diketahui_oleh_json' => json_encode($satkerData, JSON_UNESCAPED_UNICODE),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if ($existingLaporan) {
            $laporanModel->update($existingLaporan['id'], $laporanPayload);
        } else {
            $laporanPayload['disposisi_id'] = $id;
            $laporanPayload['nomor_surat_tugas'] = '';
            $laporanPayload['sasaran'] = '';
            $laporanPayload['laporan_hasil'] = '';
            $laporanPayload['foto_dokumentasi_json'] = '[]';
            $laporanPayload['dokumen_pendukung_json'] = '[]';
            $laporanPayload['creator_name'] = session()->get('fullName') ?: session()->get('username') ?: 'system';
            $laporanPayload['creator_pegawai_json'] = '[]';
            $laporanPayload['is_final'] = 0;
            $laporanPayload['created_at'] = date('Y-m-d H:i:s');
            $laporanModel->insert($laporanPayload);
        }

        return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('success', 'Disposisi Perjalanan Dinas berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new DisposisiPerjalananDinasModel();

        // Delete linked laporan draft before deleting disposisi
        $laporanModel = new LaporanPerjalananDinasModel();
        $laporanModel->where('disposisi_id', $id)->delete();

        if ($model->delete($id) === false) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', 'Gagal menghapus data.');
        }

        return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('success', 'Data Disposisi Perjalanan Dinas berhasil dihapus.');
    }

    public function pdf(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new DisposisiPerjalananDinasModel();
        $data = $model->find($id);

        if (! is_array($data)) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', 'Data tidak ditemukan.');
        }

        $menyetujui = $this->getPegawaiSignatureData((int) $data['menyetujui_pegawai_id']);
        $diketahui = $this->getPegawaiSignatureData((int) $data['diketahui_pegawai_id']);

        $html = view('admin/surat/disposisi_perjalanan_dinas_pdf', [
            'data'       => $data,
            'menyetujui' => $menyetujui,
            'diketahui'  => $diketahui,
        ]);

        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="disposisi_perjalanan_dinas_' . $id . '.pdf"')
            ->setBody($dompdf->output());
    }

    private function canAccess(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function isDataTableRequest(): bool
    {
        return $this->request->getGet('draw') !== null
            && $this->request->getGet('start') !== null
            && $this->request->getGet('length') !== null;
    }

    private function getDataTableDraw(): int
    {
        return max(0, (int) $this->request->getGet('draw'));
    }

    private function getDataTableStart(): int
    {
        return max(0, (int) $this->request->getGet('start'));
    }

    private function getDataTableLength(): int
    {
        $length = (int) $this->request->getGet('length');
        return $length > 0 ? $length : 10;
    }

    private function getDataTableSearchTerm(): string
    {
        $search = $this->request->getGet('search');
        if (! is_array($search)) {
            return '';
        }
        return trim((string) ($search['value'] ?? ''));
    }

    private function loadPegawaiOptions(): array
    {
        if (! db_connect()->tableExists('mst_pegawai')) {
            return [];
        }

        $rows = (new MstPegawaiModel())
            ->select('mst_pegawai.id, mst_pegawai.nip, mst_pegawai.nama, mst_pegawai.jabatan_utama_id, ju.jabatan AS jabatan_label, mst_pegawai.is_active')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.is_active', 1)
            ->orderBy('mst_pegawai.nama', 'ASC')
            ->orderBy('mst_pegawai.nip', 'ASC')
            ->findAll();

        return array_map(static function (array $row): array {
            $display = trim((string) ($row['nama'] ?? 'Pegawai'));
            $nip = trim((string) ($row['nip'] ?? ''));
            $jabatan = trim((string) ($row['jabatan_label'] ?? ''));

            if ($nip !== '') {
                $display .= ' | NIP ' . $nip;
            }
            if ($jabatan !== '') {
                $display .= ' | ' . $jabatan;
            }

            $row['display_label'] = $display;
            return $row;
        }, $rows);
    }

    private function buildPegawaiRowsByIds(array $pegawaiRows, array $ids): array
    {
        $rowsById = [];
        foreach ($pegawaiRows as $row) {
            $rowsById[(int) ($row['id'] ?? 0)] = $row;
        }

        $rows = [];
        foreach ($ids as $id) {
            if (! isset($rowsById[(int) $id])) {
                continue;
            }

            $row = $rowsById[(int) $id];
            $rows[] = [
                'id'      => (int) ($row['id'] ?? 0),
                'nama'    => (string) ($row['nama'] ?? ''),
                'nip'     => (string) ($row['nip'] ?? ''),
                'jabatan' => (string) ($row['jabatan_label'] ?? ''),
            ];
        }

        return $rows;
    }

    private function getPegawaiSignatureData(int $id): array
    {
        if ($id <= 0) {
            return ['nama' => '', 'nip' => '', 'jabatan' => ''];
        }
        $db = db_connect();
        $row = $db->table('mst_pegawai')
            ->select('mst_pegawai.nama, mst_pegawai.nip, ju.jabatan AS jabatan_label')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.id', $id)
            ->get()
            ->getRowArray();
        return $row ? [
            'nama'    => (string) ($row['nama'] ?? ''),
            'nip'     => (string) ($row['nip'] ?? ''),
            'jabatan' => (string) ($row['jabatan_label'] ?? ''),
        ] : ['nama' => '', 'nip' => '', 'jabatan' => ''];
    }

    private function loadKabupatenOptions(): array
    {
        $db = db_connect();

        if ($db->tableExists('mst_kabupaten')) {
            $rows = $db->table('mst_kabupaten')
                ->select('nama_kabupaten')
                ->where('nama_kabupaten IS NOT NULL', null, false)
                ->where('nama_kabupaten !=', '')
                ->groupBy('nama_kabupaten')
                ->orderBy('nama_kabupaten', 'ASC')
                ->get()
                ->getResultArray();

            $options = array_values(array_filter(array_map(static function (array $row): string {
                return trim((string) ($row['nama_kabupaten'] ?? ''));
            }, $rows), static fn (string $value): bool => $value !== ''));

            if ($options !== []) {
                return $options;
            }
        }

        if (! $db->tableExists('mst_sekolah')) {
            return [];
        }

        $rows = $db->table('mst_sekolah')
            ->select('kabupaten')
            ->where('kabupaten IS NOT NULL', null, false)
            ->where('kabupaten !=', '')
            ->groupBy('kabupaten')
            ->orderBy('kabupaten', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static function (array $row): string {
            return trim((string) ($row['kabupaten'] ?? ''));
        }, $rows), static fn (string $value): bool => $value !== ''));
    }
}
