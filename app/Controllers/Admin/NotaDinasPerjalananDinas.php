<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotaDinasPerjalananDinasModel;
use App\Models\MstPegawaiModel;

class NotaDinasPerjalananDinas extends BaseController
{
    private const MENU_LINK = 'admin/surat/perjalanan-dinas/nota-dinas';
    private const MENU_ID   = '10-03-04';

    public function index()
    {
        $this->ensureTableSchema();

        if (! $this->hasMenuAccess(self::MENU_LINK)) {
            if ($this->request->isAJAX() || $this->isDataTableRequest()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Akses ditolak.',
                ]);
            }
            return redirect()->to(site_url('/admin'))->with('error', 'Anda tidak memiliki akses ke halaman Nota Dinas Perjalanan Dinas.');
        }

        if ($this->request->isAJAX() || $this->isDataTableRequest()) {
            return $this->dataTable();
        }

        $perms = $this->resolveMenuPermissions(self::MENU_LINK);
        $pegawaiRows = $this->loadPegawaiOptions();
        $kabupatenOptions = $this->loadKabupatenOptions();

        return view('admin/surat/nota_dinas_perjalanan_dinas', [
            'title'             => 'Nota Dinas Perjalanan Dinas',
            'can_add'           => $perms['can_add'],
            'can_edit'          => $perms['can_edit'],
            'can_delete'        => $perms['can_delete'],
            'can_export'        => $perms['can_export'],
            'pegawai_options'   => $pegawaiRows,
            'kabupaten_options' => $kabupatenOptions,
        ]);
    }

    private function dataTable()
    {
        $this->ensureTableSchema();
        $draw   = $this->getDataTableDraw();
        $start  = $this->getDataTableStart();
        $length = $this->getDataTableLength();
        $search = $this->getDataTableSearchTerm();

        $db = db_connect();

        if (! $db->tableExists('nota_dinas_perjalanan_dinas')) {
            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
        }

        $perms   = $this->resolveMenuPermissions(self::MENU_LINK);
        $builder = $db->table('nota_dinas_perjalanan_dinas');
        $recordsTotal = (int) $builder->countAllResults(false);

        $startDate   = trim((string) $this->request->getGet('filter_start_date'));
        $endDate     = trim((string) $this->request->getGet('filter_end_date'));
        $kota        = trim((string) $this->request->getGet('filter_kota'));
        $pelaksanaId = (int) $this->request->getGet('filter_pelaksana');

        if ($startDate !== '') {
            $builder->where('periode_selesai >=', $startDate);
        }
        if ($endDate !== '') {
            $builder->where('periode_mulai <=', $endDate);
        }
        if ($kota !== '') {
            $builder->groupStart()
                ->like('tujuan', $kota)
                ->orLike('pelaksana_json', $kota)
                ->groupEnd();
        }
        if ($pelaksanaId > 0) {
            $builder->groupStart()
                ->like('pelaksana_json', '"id":' . $pelaksanaId . ',')
                ->orLike('pelaksana_json', '"id":' . $pelaksanaId . '}')
                ->groupEnd();
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('nomor_nota_dinas', $search)
                ->orLike('hal', $search)
                ->orLike('isi_nota_dinas', $search)
                ->orLike('tujuan', $search)
                ->orLike('pelaksana_json', $search)
                ->groupEnd();
        }

        $recordsFiltered = (int) $builder->countAllResults(false);

        $rows = $builder->orderBy('id', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($rows as $row) {
            $pelaksanaList = json_decode((string) ($row['pelaksana_json'] ?? '[]'), true);
            $pelaksanaNames = [];
            $tooltipLines   = [];
            $periodeLines   = [];
            $tujuanLines    = [];

            $fallbackMulai   = (string) ($row['periode_mulai'] ?? '');
            $fallbackSelesai = (string) ($row['periode_selesai'] ?? '');
            $fallbackTujuan  = (string) ($row['tujuan'] ?? '');

            if (! empty($pelaksanaList) && is_array($pelaksanaList)) {
                foreach ($pelaksanaList as $idx => &$p) {
                    $num  = $idx + 1;
                    $nama = trim((string) ($p['nama'] ?? ''));
                    if (empty($p['periode_mulai']) && $fallbackMulai !== '') {
                        $p['periode_mulai'] = $fallbackMulai;
                    }
                    if (empty($p['periode_selesai']) && $fallbackSelesai !== '') {
                        $p['periode_selesai'] = $fallbackSelesai;
                    }
                    if (! isset($p['tujuan']) || trim((string) $p['tujuan']) === '') {
                        $p['tujuan'] = $fallbackTujuan;
                    }

                    $pm = ! empty($p['periode_mulai']) ? date('d-m-Y', strtotime((string) $p['periode_mulai'])) : '-';
                    $ps = ! empty($p['periode_selesai']) ? date('d-m-Y', strtotime((string) $p['periode_selesai'])) : '-';
                    $pPeriode = ($pm === $ps) ? $pm : ($pm . ' s/d ' . $ps);
                    $pTujuan  = trim((string) ($p['tujuan'] ?? '-'));

                    if ($nama !== '') {
                        $pelaksanaNames[] = $num . '. ' . $nama;
                        $tooltipLines[]   = $num . '. ' . $nama
                            . (! empty($p['nip']) ? ' (NIP ' . $p['nip'] . ')' : '')
                            . ' — ' . $pPeriode . ' (' . $pTujuan . ')';
                        $periodeLines[]   = $num . '. ' . $pPeriode;
                        $tujuanLines[]    = $num . '. ' . $pTujuan;
                    }
                }
                unset($p);
            }

            if (! empty($pelaksanaNames)) {
                $singleLineText = implode(', ', $pelaksanaNames);
                $tooltipText    = implode('<br>', array_map('esc', $tooltipLines));
                $pelaksanaHtml  = '<div class="pelaksana-single-line" data-toggle="tooltip" data-html="true" data-placement="top" title="' . esc($tooltipText, 'attr') . '">' . esc($singleLineText) . '</div>';
            } else {
                $pelaksanaHtml = '-';
            }

            // Format Periode column (shows each or range with tooltip)
            $uniquePeriodes = array_unique(array_map(static function ($p) {
                $pm = ! empty($p['periode_mulai']) ? date('d-m-Y', strtotime((string) $p['periode_mulai'])) : '-';
                $ps = ! empty($p['periode_selesai']) ? date('d-m-Y', strtotime((string) $p['periode_selesai'])) : '-';
                return ($pm === $ps) ? $pm : ($pm . ' s/d ' . $ps);
            }, is_array($pelaksanaList) ? $pelaksanaList : []));

            if (count($uniquePeriodes) === 1) {
                $periodeDisplay = (string) reset($uniquePeriodes);
            } elseif (count($uniquePeriodes) > 1) {
                $periodeDisplay = implode(', ', $periodeLines);
            } else {
                $tglMulai   = ! empty($row['periode_mulai']) ? date('d-m-Y', strtotime($row['periode_mulai'])) : '-';
                $tglSelesai = ! empty($row['periode_selesai']) ? date('d-m-Y', strtotime($row['periode_selesai'])) : '-';
                $periodeDisplay = $tglMulai === $tglSelesai ? $tglMulai : $tglMulai . ' s/d ' . $tglSelesai;
            }
            $periodeTooltip = ! empty($periodeLines) ? implode('<br>', array_map('esc', $periodeLines)) : esc($periodeDisplay);
            $periodeHtml    = '<div class="pelaksana-single-line" data-toggle="tooltip" data-html="true" data-placement="top" title="' . esc($periodeTooltip, 'attr') . '">' . esc($periodeDisplay) . '</div>';

            // Format Tujuan column
            $uniqueTujuans = array_unique(array_filter(array_map(static fn ($p) => trim((string) ($p['tujuan'] ?? '')), is_array($pelaksanaList) ? $pelaksanaList : [])));
            if (count($uniqueTujuans) === 1) {
                $tujuanDisplay = (string) reset($uniqueTujuans);
            } elseif (count($uniqueTujuans) > 1) {
                $tujuanDisplay = implode(', ', $tujuanLines);
            } else {
                $tujuanDisplay = (string) ($row['tujuan'] ?? '-');
            }

            $tglNota = ! empty($row['tanggal_nota_dinas']) ? date('d-m-Y', strtotime($row['tanggal_nota_dinas'])) : '-';

            $actionHtml = '<div class="doc-btn-group">';
            if ($perms['can_export']) {
                $actionHtml .= '<a href="' . site_url('admin/surat/perjalanan-dinas/nota-dinas/' . $row['id'] . '/pdf') . '" class="btn btn-sm btn-danger btn-pdf" title="Cetak Nota Dinas (PDF)" target="_blank"><i class="fas fa-file-pdf"></i> Cetak</a>';
            }
            if ($perms['can_edit']) {
                $actionHtml .= '<button type="button" class="btn btn-sm btn-info btn-edit ml-1" data-id="' . $row['id'] . '" title="Ubah Nota Dinas"><i class="fas fa-edit"></i> Ubah</button>';
            }
            if ($perms['can_delete']) {
                $actionHtml .= '<button type="button" class="btn btn-sm btn-danger btn-delete ml-1" data-id="' . $row['id'] . '" title="Hapus Nota Dinas"><i class="fas fa-trash"></i> Hapus</button>';
            }
            $actionHtml .= '</div>';

            $data[] = [
                'id'                 => (int) $row['id'],
                'nomor_nota_dinas'   => (string) ($row['nomor_nota_dinas'] ?? ''),
                'kepada_yth'         => (string) ($row['kepada_yth'] ?? ''),
                'dari'               => (string) ($row['dari'] ?? ''),
                'hal'                => (string) ($row['hal'] ?? ''),
                'tanggal_nota_dinas' => (string) ($row['tanggal_nota_dinas'] ?? ''),
                'tanggal_nota_html'  => esc($tglNota),
                'isi_nota_dinas'     => (string) ($row['isi_nota_dinas'] ?? ''),
                'pelaksana_html'     => $pelaksanaHtml,
                'pelaksana_raw'      => is_array($pelaksanaList) ? $pelaksanaList : [],
                'periode_html'       => $periodeHtml,
                'periode_mulai'      => (string) ($row['periode_mulai'] ?? ''),
                'periode_selesai'    => (string) ($row['periode_selesai'] ?? ''),
                'tujuan'             => $tujuanDisplay,
                'ppk_pegawai_id'     => (int) ($row['ppk_pegawai_id'] ?? 0),
                'action_html'        => $actionHtml,
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
        $this->ensureTableSchema();

        if (! $this->hasMenuAccess(self::MENU_LINK)) {
            return redirect()->to(site_url('/admin'))->with('error', 'Akses ditolak.');
        }

        $perms = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! $perms['can_add']) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk menambah Nota Dinas.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url(self::MENU_LINK));
        }

        [$pelaksanaRows, $minMulai, $maxSelesai, $summaryTujuan, $errors] = $this->parseAndValidateSubmission();

        if ($errors !== []) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', implode(' ', $errors));
        }

        $nomorNotaDinas   = trim((string) $this->request->getPost('nomor_nota_dinas'));
        $kepadaYth        = trim((string) $this->request->getPost('kepada_yth')) ?: 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $dari             = trim((string) $this->request->getPost('dari')) ?: 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
        $hal              = trim((string) $this->request->getPost('hal')) ?: 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
        $tanggalNotaDinas = trim((string) $this->request->getPost('tanggal_nota_dinas'));
        $isiNotaDinas     = trim((string) $this->request->getPost('isi_nota_dinas'));
        $ppkPegawaiId     = (int) $this->request->getPost('ppk_pegawai_id');
        $username         = trim((string) session()->get('username'));

        $model = new NotaDinasPerjalananDinasModel();
        $data = [
            'nomor_nota_dinas'   => $nomorNotaDinas !== '' ? $nomorNotaDinas : null,
            'kepada_yth'         => $kepadaYth,
            'dari'               => $dari,
            'hal'                => $hal,
            'tanggal_nota_dinas' => $tanggalNotaDinas !== '' ? $tanggalNotaDinas : null,
            'isi_nota_dinas'     => $isiNotaDinas,
            'pelaksana_json'     => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'periode_mulai'      => $minMulai,
            'periode_selesai'    => $maxSelesai,
            'tujuan'             => $summaryTujuan,
            'ppk_pegawai_id'     => $ppkPegawaiId,
            'created_by'         => $username,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if ($model->insert($data) === false) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Gagal menyimpan data Nota Dinas.');
        }

        return redirect()->to(site_url(self::MENU_LINK))->with('success', 'Nota Dinas Perjalanan Dinas berhasil disimpan.');
    }

    public function ubah(int $id)
    {
        $this->ensureTableSchema();

        if (! $this->hasMenuAccess(self::MENU_LINK)) {
            return redirect()->to(site_url('/admin'))->with('error', 'Akses ditolak.');
        }

        $perms = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! $perms['can_edit']) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk mengubah Nota Dinas.');
        }

        $model = new NotaDinasPerjalananDinasModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Data Nota Dinas tidak ditemukan.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url(self::MENU_LINK));
        }

        [$pelaksanaRows, $minMulai, $maxSelesai, $summaryTujuan, $errors] = $this->parseAndValidateSubmission();

        if ($errors !== []) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', implode(' ', $errors));
        }

        $nomorNotaDinas   = trim((string) $this->request->getPost('nomor_nota_dinas'));
        $kepadaYth        = trim((string) $this->request->getPost('kepada_yth')) ?: 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $dari             = trim((string) $this->request->getPost('dari')) ?: 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
        $hal              = trim((string) $this->request->getPost('hal')) ?: 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
        $tanggalNotaDinas = trim((string) $this->request->getPost('tanggal_nota_dinas'));
        $isiNotaDinas     = trim((string) $this->request->getPost('isi_nota_dinas'));
        $ppkPegawaiId     = (int) $this->request->getPost('ppk_pegawai_id');
        $username         = trim((string) session()->get('username'));

        $data = [
            'nomor_nota_dinas'   => $nomorNotaDinas !== '' ? $nomorNotaDinas : null,
            'kepada_yth'         => $kepadaYth,
            'dari'               => $dari,
            'hal'                => $hal,
            'tanggal_nota_dinas' => $tanggalNotaDinas !== '' ? $tanggalNotaDinas : null,
            'isi_nota_dinas'     => $isiNotaDinas,
            'pelaksana_json'     => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'periode_mulai'      => $minMulai,
            'periode_selesai'    => $maxSelesai,
            'tujuan'             => $summaryTujuan,
            'ppk_pegawai_id'     => $ppkPegawaiId,
            'updated_by'         => $username,
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if ($model->update($id, $data) === false) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Gagal memperbarui data Nota Dinas.');
        }

        return redirect()->to(site_url(self::MENU_LINK))->with('success', 'Nota Dinas Perjalanan Dinas berhasil diperbarui.');
    }

    /**
     * Parse per-employee rows (pelaksana_id[], item_periode_mulai[], item_periode_selesai[], item_tujuan[])
     */
    private function parseAndValidateSubmission(): array
    {
        $pelaksanaIds   = $this->request->getPost('pelaksana_id');
        $periodeMulais  = $this->request->getPost('item_periode_mulai');
        $periodeSelesais = $this->request->getPost('item_periode_selesai');
        $tujuans        = $this->request->getPost('item_tujuan');

        $isiNotaDinas = trim((string) $this->request->getPost('isi_nota_dinas'));
        $ppkPegawaiId = (int) $this->request->getPost('ppk_pegawai_id');

        $errors = [];
        if ($isiNotaDinas === '') {
            $errors[] = 'Isi Nota Dinas wajib diisi.';
        }
        if ($ppkPegawaiId <= 0) {
            $errors[] = 'Pejabat Pembuat Komitmen (Penandatangan) wajib dipilih.';
        }

        if (! is_array($pelaksanaIds) || empty($pelaksanaIds)) {
            $errors[] = 'Pegawai yang diusulkan minimal harus dipilih 1 orang.';
            return [[], null, null, '', $errors];
        }

        $pegawaiOptions = $this->loadPegawaiOptions();
        $rowsById = [];
        foreach ($pegawaiOptions as $row) {
            $rowsById[(int) ($row['id'] ?? 0)] = $row;
        }

        $pelaksanaRows = [];
        $minMulai      = null;
        $maxSelesai    = null;
        $tujuanList    = [];

        foreach ($pelaksanaIds as $idx => $rawPid) {
            $rowNum = $idx + 1;
            $pid    = (int) $rawPid;
            $pMulai   = is_array($periodeMulais) ? trim((string) ($periodeMulais[$idx] ?? '')) : '';
            $pSelesai = is_array($periodeSelesais) ? trim((string) ($periodeSelesais[$idx] ?? '')) : '';
            $pTujuan  = is_array($tujuans) ? trim((string) ($tujuans[$idx] ?? '')) : '';

            if ($pid <= 0 || ! isset($rowsById[$pid])) {
                $errors[] = "Baris #{$rowNum}: Pegawai wajib dipilih.";
                continue;
            }
            if ($pMulai === '') {
                $errors[] = "Baris #{$rowNum}: Periode Mulai wajib diisi.";
            }
            if ($pSelesai === '') {
                $errors[] = "Baris #{$rowNum}: Periode Selesai wajib diisi.";
            }
            if ($pMulai !== '' && $pSelesai !== '' && $pSelesai < $pMulai) {
                $errors[] = "Baris #{$rowNum}: Periode Selesai tidak boleh kurang dari Periode Mulai.";
            }
            if ($pTujuan === '') {
                $errors[] = "Baris #{$rowNum}: Tujuan Perjalanan wajib diisi.";
            }

            $emp = $rowsById[$pid];
            $pelaksanaRows[] = [
                'id'              => (int) ($emp['id'] ?? 0),
                'nama'            => (string) ($emp['nama'] ?? ''),
                'nip'             => (string) ($emp['nip'] ?? ''),
                'jabatan'         => (string) ($emp['jabatan_label'] ?? ''),
                'golongan'        => (string) ($emp['golongan'] ?? ''),
                'jenis_pegawai'   => (string) ($emp['jenis_pegawai'] ?? ''),
                'periode_mulai'   => $pMulai,
                'periode_selesai' => $pSelesai,
                'tujuan'          => $pTujuan,
            ];

            if ($pMulai !== '' && ($minMulai === null || $pMulai < $minMulai)) {
                $minMulai = $pMulai;
            }
            if ($pSelesai !== '' && ($maxSelesai === null || $pSelesai > $maxSelesai)) {
                $maxSelesai = $pSelesai;
            }
            if ($pTujuan !== '') {
                $tujuanList[] = $pTujuan;
            }
        }

        if (empty($pelaksanaRows) && empty($errors)) {
            $errors[] = 'Pegawai yang diusulkan minimal harus dipilih 1 orang.';
        }

        $summaryTujuan = implode(', ', array_unique($tujuanList));

        return [$pelaksanaRows, $minMulai, $maxSelesai, $summaryTujuan, $errors];
    }

    public function hapus(int $id)
    {
        $this->ensureTableSchema();

        if (! $this->hasMenuAccess(self::MENU_LINK)) {
            return redirect()->to(site_url('/admin'))->with('error', 'Akses ditolak.');
        }

        $perms = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! $perms['can_delete']) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk menghapus Nota Dinas.');
        }

        $model = new NotaDinasPerjalananDinasModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Data Nota Dinas tidak ditemukan.');
        }

        if ($model->delete($id) === false) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Gagal menghapus data Nota Dinas.');
        }

        return redirect()->to(site_url(self::MENU_LINK))->with('success', 'Data Nota Dinas Perjalanan Dinas berhasil dihapus.');
    }

    public function pdf(int $id)
    {
        $this->ensureTableSchema();

        if (! $this->hasMenuAccess(self::MENU_LINK)) {
            return redirect()->to(site_url('/admin'))->with('error', 'Akses ditolak.');
        }

        $perms = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! $perms['can_export']) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Anda tidak memiliki hak akses untuk mencetak Nota Dinas.');
        }

        $model = new NotaDinasPerjalananDinasModel();
        $data = $model->find($id);
        if (! is_array($data)) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Data Nota Dinas tidak ditemukan.');
        }

        $ppk = $this->getPegawaiSignatureData((int) ($data['ppk_pegawai_id'] ?? 0));

        // Enrich pelaksana_json with latest golongan/jenis_pegawai from mst_pegawai if missing
        $pelaksana = json_decode((string) ($data['pelaksana_json'] ?? '[]'), true);
        if (is_array($pelaksana) && ! empty($pelaksana)) {
            $db = db_connect();
            if ($db->tableExists('mst_pegawai')) {
                foreach ($pelaksana as &$p) {
                    $pid = (int) ($p['id'] ?? 0);
                    if ($pid > 0 && (empty($p['golongan']) || ! isset($p['jenis_pegawai']))) {
                        $empRow = $db->table('mst_pegawai')
                            ->select('golongan, jenis_pegawai, nip, nama')
                            ->where('id', $pid)
                            ->get()
                            ->getRowArray();
                        if (is_array($empRow)) {
                            if (empty($p['golongan'])) {
                                $p['golongan'] = (string) ($empRow['golongan'] ?? '');
                            }
                            if (! isset($p['jenis_pegawai'])) {
                                $p['jenis_pegawai'] = (string) ($empRow['jenis_pegawai'] ?? '');
                            }
                        }
                    }
                }
                unset($p);
            }
            $data['pelaksana_json'] = json_encode($pelaksana, JSON_UNESCAPED_UNICODE);
        }

        helper('pdf');
        $html = view('admin/surat/nota_dinas_perjalanan_dinas_pdf', [
            'data' => $data,
            'ppk'  => $ppk,
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
            ->setHeader('Content-Disposition', 'inline; filename="nota_dinas_spt_' . $id . '.pdf"')
            ->setBody($dompdf->output());
    }

    private function sortPelaksanaByStrukturOrganisasi(array $pelaksanaList): array
    {
        if (empty($pelaksanaList)) {
            return [];
        }

        $orderMap = (new \App\Models\StrukturOrganisasiModel())->getPegawaiOrderMap();
        $idMap    = $orderMap['id_map'] ?? [];
        $nipMap   = $orderMap['nip_map'] ?? [];
        $namaMap  = $orderMap['nama_map'] ?? [];

        usort($pelaksanaList, static function ($a, $b) use ($idMap, $nipMap, $namaMap) {
            $idA = (int) ($a['id'] ?? 0);
            $idB = (int) ($b['id'] ?? 0);

            $nipA = trim((string) ($a['nip'] ?? ''));
            $nipB = trim((string) ($b['nip'] ?? ''));

            $namaA = strtolower(trim((string) ($a['nama'] ?? '')));
            $namaB = strtolower(trim((string) ($b['nama'] ?? '')));

            $rankA = $idMap[$idA] ?? ($nipA !== '' ? ($nipMap[$nipA] ?? null) : null) ?? ($namaA !== '' ? ($namaMap[$namaA] ?? null) : null) ?? 999999;
            $rankB = $idMap[$idB] ?? ($nipB !== '' ? ($nipMap[$nipB] ?? null) : null) ?? ($namaB !== '' ? ($namaMap[$namaB] ?? null) : null) ?? 999999;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return strcmp($namaA, $namaB);
        });

        return array_values($pelaksanaList);
    }

    private function loadPegawaiOptions(): array
    {
        if (! db_connect()->tableExists('mst_pegawai')) {
            return [];
        }

        $rows = (new MstPegawaiModel())
            ->select('mst_pegawai.id, mst_pegawai.nip, mst_pegawai.nama, mst_pegawai.golongan, mst_pegawai.jenis_pegawai, mst_pegawai.jabatan_utama_id, ju.jabatan AS jabatan_label, mst_pegawai.is_active')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.is_active', 1)
            ->findAll();

        $rows = $this->sortPelaksanaByStrukturOrganisasi($rows);

        return array_map(static function (array $row): array {
            $display = trim((string) ($row['nama'] ?? 'Pegawai'));
            $nip     = trim((string) ($row['nip'] ?? ''));
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

    private function getPegawaiSignatureData(int $id): array
    {
        if ($id <= 0) {
            return ['nama' => 'Nurhidayat Nugroho, S.Ars.', 'nip' => '', 'jabatan' => 'Pejabat Pembuat Komitmen'];
        }
        $db = db_connect();
        $row = $db->table('mst_pegawai')
            ->select('mst_pegawai.nama, mst_pegawai.nip, mst_pegawai.jenis_pegawai, ju.jabatan AS jabatan_label')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->where('mst_pegawai.id', $id)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return ['nama' => 'Nurhidayat Nugroho, S.Ars.', 'nip' => '', 'jabatan' => 'Pejabat Pembuat Komitmen'];
        }

        return [
            'nama'          => (string) ($row['nama'] ?? ''),
            'nip'           => (string) ($row['nip'] ?? ''),
            'jabatan'       => (string) ($row['jabatan_label'] ?? ''),
            'jenis_pegawai' => (string) ($row['jenis_pegawai'] ?? ''),
        ];
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $role = (string) (session()->get('role') ?? '');
        $db = db_connect();
        $roleId = $this->resolveRoleId($db, $role);

        if ($roleId === 1) {
            return true;
        }

        if (! $db->tableExists('menu_akses') || $roleId === null) {
            return in_array(strtolower(trim($role)), ['admin', 'editor', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
        }

        $menuId = $this->resolveMenuIdByLink($db, $menuLink) ?? self::MENU_ID;
        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $count = (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults();

        return $count > 0;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $role = (string) (session()->get('role') ?? '');
        $db = db_connect();
        $roleId = $this->resolveRoleId($db, $role);
        $isSuper = ($roleId === 1) || in_array(strtolower(trim($role)), ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        $perms = [
            'can_add'      => $isSuper,
            'can_edit'     => $isSuper,
            'can_delete'   => $isSuper,
            'can_export'   => $isSuper,
            'can_import'   => $isSuper,
            'can_approval' => $isSuper,
        ];

        if (! $db->tableExists('menu_akses') || $roleId === null) {
            return $perms;
        }

        $menuId = $this->resolveMenuIdByLink($db, $menuLink) ?? self::MENU_ID;
        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $perms;
        }

        return [
            'can_add'      => (int) ($row['FiturAdd'] ?? 0) === 1,
            'can_edit'     => (int) ($row['FiturEdit'] ?? 0) === 1,
            'can_delete'   => (int) ($row['FiturDelete'] ?? 0) === 1,
            'can_export'   => (int) ($row['FiturExport'] ?? 0) === 1,
            'can_import'   => (int) ($row['FiturImport'] ?? 0) === 1,
            'can_approval' => (int) ($row['FiturApproval'] ?? 0) === 1,
        ];
    }

    private function resolveRoleId($db, string $role): ?int
    {
        $normalizedRole = strtolower(trim($role));
        if ($normalizedRole === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalizedRole];
            if (strpos($normalizedRole, 'super') !== false) {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalizedRole === 'admin' || $normalizedRole === 'administrator') {
                $variants[] = 'admin';
                $variants[] = 'administrator';
            }

            $row = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', array_values(array_unique($variants)))
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (int) $row['id'];
            }
        }

        return match ($normalizedRole) {
            'admin', 'administrator', 'super administrator', 'super_administrator', 'super-admin', 'superadmin' => 1,
            'editor' => 2,
            default => null,
        };
    }

    private function resolveMenuIdByLink($db, string $link): ?string
    {
        $cleanLink = trim(strtolower($link), '/');
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if ($db->tableExists($table)) {
                $row = $db->table($table)
                    ->select('id')
                    ->where('LOWER(TRIM(BOTH "/" FROM link))', $cleanLink)
                    ->get()
                    ->getRowArray();
                if (is_array($row) && isset($row['id'])) {
                    return (string) $row['id'];
                }
            }
        }
        return null;
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

    private function ensureTableSchema(): void
    {
        $db = db_connect();
        $forge = \Config\Database::forge();

        if (! $db->tableExists('nota_dinas_perjalanan_dinas')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nomor_nota_dinas' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'kepada_yth' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                ],
                'dari' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau',
                ],
                'hal' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan',
                ],
                'tanggal_nota_dinas' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'isi_nota_dinas' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'pelaksana_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'periode_mulai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'periode_selesai' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'tujuan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ppk_pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('periode_mulai');
            $forge->addKey('periode_selesai');
            $forge->createTable('nota_dinas_perjalanan_dinas', true);
        }

        if ($db->tableExists('menu_lv3')) {
            $exists = $db->table('menu_lv3')->where('id', self::MENU_ID)->countAllResults();
            if ($exists === 0) {
                $db->table('menu_lv3')->insert([
                    'id'       => self::MENU_ID,
                    'label'    => 'Nota Dinas',
                    'icon'     => 'far fa-circle',
                    'link'     => self::MENU_LINK,
                    'header'   => '10-03',
                    'ordering' => 4,
                ]);
            }
        }

        if ($db->tableExists('menu_akses')) {
            $aksesCount = $db->table('menu_akses')->where('menu_id', self::MENU_ID)->countAllResults();
            if ($aksesCount === 0) {
                $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
                foreach (['10-03-01', '10-03'] as $srcMenuId) {
                    $rows = $db->table('menu_akses')->where('menu_id', $srcMenuId)->get()->getResultArray();
                    foreach ($rows as $row) {
                        $roleId = $row[$roleColumn];
                        $existsRole = $db->table('menu_akses')
                            ->where($roleColumn, $roleId)
                            ->where('menu_id', self::MENU_ID)
                            ->countAllResults();
                        if ($existsRole === 0) {
                            $db->table('menu_akses')->insert([
                                $roleColumn     => $roleId,
                                'menu_id'       => self::MENU_ID,
                                'FiturAdd'      => $row['FiturAdd'] ?? 0,
                                'FiturEdit'     => $row['FiturEdit'] ?? 0,
                                'FiturDelete'   => $row['FiturDelete'] ?? 0,
                                'FiturExport'   => $row['FiturExport'] ?? 0,
                                'FiturImport'   => $row['FiturImport'] ?? 0,
                                'FiturApproval' => $row['FiturApproval'] ?? 0,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
