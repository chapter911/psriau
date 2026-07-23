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

        $this->ensureTableSchema();

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
        $this->ensureTableSchema();
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

            // Format Status Badges (PPK & Kasatker)
            $statusM = trim((string) ($row['status_menyetujui'] ?? 'pending'));
            $statusD = trim((string) ($row['status_diketahui'] ?? 'pending'));
            $statusOverall = trim((string) ($row['status'] ?? 'pending'));

            $badgeM = match ($statusM) {
                'disetujui' => '<span class="badge badge-success px-2 py-1" title="Pejabat Pembuat Komitmen: Disetujui"><i class="fas fa-check"></i> PPK</span>',
                'ditolak'   => '<span class="badge badge-danger px-2 py-1" title="Pejabat Pembuat Komitmen: Ditolak"><i class="fas fa-times"></i> PPK</span>',
                default     => '<span class="badge badge-secondary px-2 py-1" title="Pejabat Pembuat Komitmen: Pending"><i class="fas fa-clock"></i> PPK</span>',
            };

            $badgeD = match ($statusD) {
                'disetujui' => '<span class="badge badge-success px-2 py-1" title="Kepala Satker: Disetujui"><i class="fas fa-check"></i> Kasatker</span>',
                'ditolak'   => '<span class="badge badge-danger px-2 py-1" title="Kepala Satker: Ditolak"><i class="fas fa-times"></i> Kasatker</span>',
                default     => '<span class="badge badge-secondary px-2 py-1" title="Kepala Satker: Pending"><i class="fas fa-clock"></i> Kasatker</span>',
            };

            $badgeOverall = match ($statusOverall) {
                'disetujui' => '<span class="badge badge-success px-2 py-1 mt-1 d-inline-block"><i class="fas fa-check-double"></i> Disetujui (Lengkap)</span>',
                'ditolak'   => '<span class="badge badge-danger px-2 py-1 mt-1 d-inline-block" title="' . esc($row['catatan_penolakan'] ?? '') . '"><i class="fas fa-times"></i> Ditolak</span>',
                default     => '<span class="badge badge-warning px-2 py-1 mt-1 d-inline-block"><i class="fas fa-hourglass-half"></i> Pending</span>',
            };

            $statusBadge = '<div class="text-center" style="white-space:nowrap;">' . $badgeM . ' ' . $badgeD . '<br>' . $badgeOverall . '</div>';

            $menyetujuiData = $this->getPegawaiSignatureData((int) ($row['menyetujui_pegawai_id'] ?? 0));
            $diketahuiData = $this->getPegawaiSignatureData((int) ($row['diketahui_pegawai_id'] ?? 0));

            // Action Buttons
            $actionHtml = '<div class="doc-btn-group">';
            $actionHtml .= '<a href="' . site_url('admin/surat/perjalanan-dinas/disposisi/' . $row['id'] . '/pdf') . '" class="btn btn-sm btn-danger btn-pdf" title="Cetak Disposisi (PDF)" target="_blank"><i class="fas fa-file-pdf"></i> Cetak</a>';
            $actionHtml .= '<button type="button" class="btn btn-sm btn-warning btn-send-email ml-1" ' .
                'data-id="' . $row['id'] . '" ' .
                'data-url="' . site_url('admin/surat/perjalanan-dinas/disposisi/' . $row['id'] . '/kirim-email') . '" ' .
                'data-ppk-nama="' . esc($menyetujuiData['nama']) . '" ' .
                'data-ppk-email="' . esc($menyetujuiData['email']) . '" ' .
                'data-kasatker-nama="' . esc($diketahuiData['nama']) . '" ' .
                'data-kasatker-email="' . esc($diketahuiData['email']) . '" ' .
                'title="Kirim Ulang 2 Email Persetujuan"><i class="fas fa-envelope"></i> Email</button>';
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
                'status'          => $statusOverall,
                'status_badge'    => $statusBadge,
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

        $this->ensureTableSchema();

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
        if ($periodeMulai !== '' && $periodeSelesai !== '' && $periodeSelesai < $periodeMulai) {
            $errors[] = 'Periode Selesai tidak boleh kurang dari Periode Mulai.';
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

        $tokenMenyetujui = bin2hex(random_bytes(16));
        $tokenDiketahui = bin2hex(random_bytes(16));

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
            'status_menyetujui'     => 'pending',
            'status_diketahui'     => 'pending',
            'token_menyetujui'      => $tokenMenyetujui,
            'token_diketahui'       => $tokenDiketahui,
            'status'                => 'pending',
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

        // Send 2 Emails for Approval (PPK & Kasatker)
        $res1 = $this->sendApprovalEmail((int) $insertId, 'menyetujui');
        $res2 = $this->sendApprovalEmail((int) $insertId, 'diketahui');

        $successMsg = 'Disposisi Perjalanan Dinas berhasil disimpan.';
        if ($res1['success'] && $res2['success']) {
            $successMsg .= ' 2 Email persetujuan telah dikirim ke masing-masing pejabat (PPK: ' . $res1['recipient'] . ', Kasatker: ' . $res2['recipient'] . ').';
        } else {
            $successMsg .= ' (Pengiriman email: PPK=' . ($res1['success'] ? $res1['recipient'] : $res1['message']) . ', Kasatker=' . ($res2['success'] ? $res2['recipient'] : $res2['message']) . ')';
        }

        return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('success', $successMsg);
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
        if ($periodeMulai !== '' && $periodeSelesai !== '' && $periodeSelesai < $periodeMulai) {
            $errors[] = 'Periode Selesai tidak boleh kurang dari Periode Mulai.';
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
            return ['nama' => '', 'nip' => '', 'jabatan' => '', 'email' => ''];
        }
        $db = db_connect();
        $row = $db->table('mst_pegawai')
            ->select('mst_pegawai.nama, mst_pegawai.nip, mst_pegawai.email, ju.jabatan AS jabatan_label')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.id', $id)
            ->get()
            ->getRowArray();
        return $row ? [
            'nama'    => (string) ($row['nama'] ?? ''),
            'nip'     => (string) ($row['nip'] ?? ''),
            'jabatan' => (string) ($row['jabatan_label'] ?? ''),
            'email'   => trim((string) ($row['email'] ?? '')),
        ] : ['nama' => '', 'nip' => '', 'jabatan' => '', 'email' => ''];
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

    public function setujui(int $id)
    {
        $role = trim((string) $this->request->getGet('role'));
        if (! in_array($role, ['menyetujui', 'diketahui'], true)) {
            $role = 'menyetujui';
        }

        $token = trim((string) $this->request->getGet('token'));
        $model = new DisposisiPerjalananDinasModel();
        $disposisi = $model->find($id);

        if (! is_array($disposisi)) {
            return view('admin/surat/disposisi_approval_response', [
                'title'   => 'Data Tidak Ditemukan',
                'status'  => 'error',
                'message' => 'Data Disposisi Perjalanan Dinas tidak ditemukan.',
            ]);
        }

        $isLoggedIn = $this->canAccess();
        $tokenField = 'token_' . $role;
        $expectedToken = trim((string) ($disposisi[$tokenField] ?? ''));

        if (! $isLoggedIn) {
            if ($token === '' || $expectedToken === '' || ! hash_equals($expectedToken, $token)) {
                return view('admin/surat/disposisi_approval_response', [
                    'title'     => 'Token Tidak Valid',
                    'status'    => 'error',
                    'message'   => 'Token persetujuan tidak valid atau sudah kedaluwarsa. Silakan periksa kembali link pada email Anda.',
                    'disposisi' => $disposisi,
                ]);
            }
        } else {
            if ($token !== '' && $expectedToken !== '' && ! hash_equals($expectedToken, $token)) {
                return view('admin/surat/disposisi_approval_response', [
                    'title'     => 'Token Tidak Valid',
                    'status'    => 'error',
                    'message'   => 'Token persetujuan tidak valid atau sudah kedaluwarsa.',
                    'disposisi' => $disposisi,
                ]);
            }
        }

        $statusField = 'status_' . $role;
        $roleLabel = $role === 'menyetujui' ? 'Pejabat Pembuat Komitmen (Menyetujui)' : 'Kepala Satuan Kerja (Diketahui)';

        $disposisi[$statusField] = 'disetujui';

        $s1 = $disposisi['status_menyetujui'] ?? 'pending';
        $s2 = $disposisi['status_diketahui'] ?? 'pending';

        if ($s1 === 'disetujui' && $s2 === 'disetujui') {
            $newStatus = 'disetujui';
        } elseif ($s1 === 'ditolak' || $s2 === 'ditolak') {
            $newStatus = 'ditolak';
        } else {
            $newStatus = 'pending';
        }

        $model->update($id, [
            $statusField => 'disetujui',
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $disposisi['status'] = $newStatus;

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Disposisi berhasil disetujui sebagai ' . $roleLabel . '.',
            ]);
        }

        $msg = 'Disposisi #' . $id . ' telah disetujui oleh ' . $roleLabel . '.';
        if ($newStatus === 'disetujui') {
            $msg .= ' Kedua pejabat (PPK & Kasatker) telah memberikan persetujuan lengkap!';
        } else {
            $msg .= ' Menunggu persetujuan dari pejabat yang belum menyetujui.';
        }

        return view('admin/surat/disposisi_approval_response', [
            'title'     => 'Persetujuan Diberikan',
            'status'    => $newStatus === 'disetujui' ? 'disetujui' : 'info',
            'message'   => $msg,
            'role'      => $role,
            'disposisi' => $disposisi,
        ]);
    }

    public function tolak(int $id)
    {
        $role = trim((string) $this->request->getGet('role'));
        if (! in_array($role, ['menyetujui', 'diketahui'], true)) {
            $role = 'menyetujui';
        }

        $token = trim((string) $this->request->getGet('token'));
        $catatan = trim((string) ($this->request->getPost('catatan') ?: $this->request->getGet('catatan')));

        $model = new DisposisiPerjalananDinasModel();
        $disposisi = $model->find($id);

        if (! is_array($disposisi)) {
            return view('admin/surat/disposisi_approval_response', [
                'title'   => 'Data Tidak Ditemukan',
                'status'  => 'error',
                'message' => 'Data Disposisi Perjalanan Dinas tidak ditemukan.',
            ]);
        }

        $isLoggedIn = $this->canAccess();
        $tokenField = 'token_' . $role;
        $expectedToken = trim((string) ($disposisi[$tokenField] ?? ''));

        if (! $isLoggedIn) {
            if ($token === '' || $expectedToken === '' || ! hash_equals($expectedToken, $token)) {
                return view('admin/surat/disposisi_approval_response', [
                    'title'     => 'Token Tidak Valid',
                    'status'    => 'error',
                    'message'   => 'Token persetujuan tidak valid atau sudah kedaluwarsa. Silakan periksa kembali link pada email Anda.',
                    'disposisi' => $disposisi,
                ]);
            }
        } else {
            if ($token !== '' && $expectedToken !== '' && ! hash_equals($expectedToken, $token)) {
                return view('admin/surat/disposisi_approval_response', [
                    'title'     => 'Token Tidak Valid',
                    'status'    => 'error',
                    'message'   => 'Token persetujuan tidak valid atau sudah kedaluwarsa.',
                    'disposisi' => $disposisi,
                ]);
            }
        }

        $statusField = 'status_' . $role;
        $roleLabel = $role === 'menyetujui' ? 'Pejabat Pembuat Komitmen (Menyetujui)' : 'Kepala Satuan Kerja (Diketahui)';
        $reason = $catatan !== '' ? $catatan : null;

        $model->update($id, [
            $statusField        => 'ditolak',
            'status'            => 'ditolak',
            'catatan_penolakan' => $reason,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $disposisi[$statusField] = 'ditolak';
        $disposisi['status'] = 'ditolak';
        $disposisi['catatan_penolakan'] = $reason;

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Disposisi Perjalanan Dinas telah ditolak.',
            ]);
        }

        return view('admin/surat/disposisi_approval_response', [
            'title'     => 'Disposisi Ditolak',
            'status'    => 'ditolak',
            'message'   => 'Pengajuan Disposisi Perjalanan Dinas #' . $id . ' telah ditolak oleh ' . $roleLabel . '.',
            'role'      => $role,
            'disposisi' => $disposisi,
        ]);
    }

    public function kirimEmail(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $res1 = $this->sendApprovalEmail($id, 'menyetujui');
        $res2 = $this->sendApprovalEmail($id, 'diketahui');

        if ($res1['success'] || $res2['success']) {
            return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('success', 'Email persetujuan berhasil dikirim ke masing-masing pejabat (PPK: ' . $res1['recipient'] . ', Kasatker: ' . $res2['recipient'] . ').');
        }

        return redirect()->to(site_url('admin/surat/perjalanan-dinas/disposisi'))->with('error', 'Gagal mengirim email: ' . $res1['message'] . ' / ' . $res2['message']);
    }

    private function sendApprovalEmail(int $id, string $role = 'menyetujui'): array
    {
        $model = new DisposisiPerjalananDinasModel();
        $disposisi = $model->find($id);
        if (! is_array($disposisi)) {
            return ['success' => false, 'message' => 'Data tidak ditemukan.', 'recipient' => ''];
        }

        $tokenField = 'token_' . $role;
        $token = trim((string) ($disposisi[$tokenField] ?? ''));
        if ($token === '') {
            $token = bin2hex(random_bytes(16));
            $model->update($id, [$tokenField => $token]);
            $disposisi[$tokenField] = $token;
        }

        $menyetujui = $this->getPegawaiSignatureData((int) $disposisi['menyetujui_pegawai_id']);
        $diketahui = $this->getPegawaiSignatureData((int) $disposisi['diketahui_pegawai_id']);

        if ($role === 'menyetujui') {
            $subjectRole = '[Persetujuan PPK]';
            $roleLabel = 'Pejabat Pembuat Komitmen (Menyetujui)';
            $officialName = $menyetujui['nama'] ?: 'Pejabat Pembuat Komitmen';
            $officialJabatan = $menyetujui['jabatan'] ?: 'PPK';
            $recipientEmail = trim((string) ($menyetujui['email'] ?? ''));
        } else {
            $subjectRole = '[Persetujuan Kepala Satker]';
            $roleLabel = 'Kepala Satuan Kerja (Diketahui)';
            $officialName = $diketahui['nama'] ?: 'Kepala Satuan Kerja';
            $officialJabatan = $diketahui['jabatan'] ?: 'Kasatker';
            $recipientEmail = trim((string) ($diketahui['email'] ?? ''));
        }

        if ($recipientEmail === '') {
            return [
                'success'   => false,
                'message'   => 'Email untuk ' . $roleLabel . ' (' . $officialName . ') belum diisi pada Master Pegawai.',
                'recipient' => '',
            ];
        }

        $pelaksanaList = json_decode((string) ($disposisi['pelaksana_json'] ?? '[]'), true);
        $pelaksanaNames = [];
        foreach ($pelaksanaList as $p) {
            $pelaksanaNames[] = esc($p['nama'] ?? '') . (!empty($p['nip']) ? ' (NIP ' . esc($p['nip']) . ')' : '');
        }
        $pelaksanaStr = !empty($pelaksanaNames) ? implode('<br>', $pelaksanaNames) : '-';

        $tglMulai = $disposisi['periode_mulai'] ? date('d-m-Y', strtotime($disposisi['periode_mulai'])) : '-';
        $tglSelesai = $disposisi['periode_selesai'] ? date('d-m-Y', strtotime($disposisi['periode_selesai'])) : '-';
        $periodeStr = $tglMulai === $tglSelesai ? $tglMulai : $tglMulai . ' s/d ' . $tglSelesai;

        $approveUrl = site_url('admin/surat/perjalanan-dinas/disposisi/' . $id . '/setujui?role=' . $role . '&token=' . $token);
        $rejectUrl = site_url('admin/surat/perjalanan-dinas/disposisi/' . $id . '/tolak?role=' . $role . '&token=' . $token);

        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f9; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
                .header { background-color: #1e3a8a; color: #ffffff; padding: 20px; text-align: center; }
                .header h2 { margin: 0; font-size: 20px; }
                .role-badge { display: inline-block; background: #fbbf24; color: #78350f; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 13px; margin-top: 8px; }
                .content { padding: 24px; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
                .info-table td { padding: 8px 12px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
                .info-table td.label { font-weight: bold; width: 35%; color: #4a5568; background-color: #f8fafc; }
                .btn-group { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .btn { display: inline-block; padding: 12px 28px; margin: 0 8px; font-weight: bold; text-decoration: none; border-radius: 6px; font-size: 15px; text-align: center; }
                .btn-approve { background-color: #16a34a; color: #ffffff !important; }
                .btn-reject { background-color: #dc2626; color: #ffffff !important; }
                .footer { background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>SATKER PPS RIAU</h2>
                    <div class="role-badge">Tindakan Sebagai: ' . esc($roleLabel) . '</div>
                </div>
                <div class="content">
                    <p>Yth. Bapak/Ibu <strong>' . esc($officialName) . '</strong> (' . esc($officialJabatan) . '),</p>
                    <p>Terdapat pengajuan <strong>Disposisi Perjalanan Dinas</strong> baru yang membutuhkan persetujuan Anda sebagai <strong>' . esc($roleLabel) . '</strong>:</p>
                    
                    <table class="info-table">
                        <tr>
                            <td class="label">Perihal</td>
                            <td><strong>' . esc($disposisi['perihal']) . '</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Kota/Kab. Tujuan</td>
                            <td>' . esc($disposisi['kota_tujuan']) . ' (' . esc($disposisi['tujuan']) . ')</td>
                        </tr>
                        <tr>
                            <td class="label">Periode</td>
                            <td>' . $periodeStr . '</td>
                        </tr>
                        <tr>
                            <td class="label">Transportasi</td>
                            <td>' . esc($disposisi['transportasi']) . '</td>
                        </tr>
                        <tr>
                            <td class="label">Pelaksana SPPD</td>
                            <td>' . $pelaksanaStr . '</td>
                        </tr>
                        <tr>
                            <td class="label">Pejabat Menyetujui (PPK)</td>
                            <td>' . esc($menyetujui['nama']) . '</td>
                        </tr>
                        <tr>
                            <td class="label">Kepala Satker</td>
                            <td>' . esc($diketahui['nama']) . '</td>
                        </tr>
                    </table>

                    <p style="text-align: center; font-weight: bold; margin-top: 20px;">Silakan klik tombol persetujuan di bawah ini:</p>

                    <div class="btn-group">
                        <a href="' . $approveUrl . '" class="btn btn-approve">✓ SETUJUI DISPOSISI</a>
                        <a href="' . $rejectUrl . '" class="btn btn-reject">✕ TOLAK DISPOSISI</a>
                    </div>
                </div>
                <div class="footer">
                    Email ini dikirim otomatis oleh Sistem Informasi Administrasi SATKER PPS Riau.<br>
                    Persyaratan lengkap: Wajib disetujui oleh Pejabat Pembuat Komitmen dan Kepala Satker.
                </div>
            </div>
        </body>
        </html>';

        try {
            $email = \Config\Services::email();
            $emailConfig = config('Email');
            $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
            $fromName = trim((string) ($emailConfig->fromName ?? 'SATKER PPS Riau'));

            if ($fromEmail === '') {
                $fromEmail = 'no-reply@psriau.com';
            }

            $email->clear(true);
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($recipientEmail);
            $email->setSubject($subjectRole . ' Disposisi Perjalanan Dinas: ' . $disposisi['perihal']);
            $email->setMessage($htmlBody);
            $email->setMailType('html');

            if (! $email->send()) {
                $debugRaw = (string) $email->printDebugger(['headers', 'subject', 'body']);
                log_message('error', 'Failed sending ' . $role . ' approval email to ' . $recipientEmail . ': ' . $debugRaw);
                return ['success' => false, 'message' => 'Mail error (' . $role . '): ' . strip_tags($debugRaw), 'recipient' => $recipientEmail];
            }

            return ['success' => true, 'message' => 'Email terkirim (' . $role . ')', 'recipient' => $recipientEmail];
        } catch (\Throwable $e) {
            log_message('error', 'Exception sending ' . $role . ' approval email: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'recipient' => $recipientEmail];
        }
    }

    private function ensureTableSchema(): void
    {
        $db = db_connect();
        if (! $db->tableExists('disposisi_perjalanan_dinas')) {
            return;
        }

        $fields = [];

        if (! $db->fieldExists('status_menyetujui', 'disposisi_perjalanan_dinas')) {
            $fields['status_menyetujui'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'diketahui_pegawai_id',
            ];
        }

        if (! $db->fieldExists('status_diketahui', 'disposisi_perjalanan_dinas')) {
            $fields['status_diketahui'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'status_menyetujui',
            ];
        }

        if (! $db->fieldExists('token_menyetujui', 'disposisi_perjalanan_dinas')) {
            $fields['token_menyetujui'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status_diketahui',
            ];
        }

        if (! $db->fieldExists('token_diketahui', 'disposisi_perjalanan_dinas')) {
            $fields['token_diketahui'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'token_menyetujui',
            ];
        }

        if (! $db->fieldExists('catatan_penolakan', 'disposisi_perjalanan_dinas')) {
            $fields['catatan_penolakan'] = [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'token_diketahui',
            ];
        }

        if (! $db->fieldExists('status', 'disposisi_perjalanan_dinas')) {
            $fields['status'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'catatan_penolakan',
            ];
        }

        if (! $db->fieldExists('approval_token', 'disposisi_perjalanan_dinas')) {
            $fields['approval_token'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status',
            ];
        }

        if ($fields !== []) {
            $forge = \Config\Database::forge();
            $forge->addColumn('disposisi_perjalanan_dinas', $fields);
        }
    }
}
