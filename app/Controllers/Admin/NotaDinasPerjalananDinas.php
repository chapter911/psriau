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

        return view('admin/surat/nota_dinas_perjalanan_dinas', [
            'title'           => 'Nota Dinas Perjalanan Dinas',
            'can_add'         => $perms['can_add'],
            'can_edit'        => $perms['can_edit'],
            'can_delete'      => $perms['can_delete'],
            'can_export'      => $perms['can_export'],
            'pegawai_options' => $pegawaiRows,
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
        $pelaksanaId = (int) $this->request->getGet('filter_pelaksana');

        if ($startDate !== '') {
            $builder->where('periode_mulai >=', $startDate);
        }
        if ($endDate !== '') {
            $builder->where('periode_selesai <=', $endDate);
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
            $tooltipNames = [];
            if (! empty($pelaksanaList) && is_array($pelaksanaList)) {
                foreach ($pelaksanaList as $idx => $p) {
                    $num = $idx + 1;
                    $nama = trim((string) ($p['nama'] ?? ''));
                    if ($nama !== '') {
                        $pelaksanaNames[] = $num . '. ' . $nama;
                        $tooltipNames[] = $num . '. ' . $nama . (! empty($p['nip']) ? ' (NIP ' . $p['nip'] . ')' : '');
                    }
                }
            }
            if (! empty($pelaksanaNames)) {
                $singleLineText = implode(', ', $pelaksanaNames);
                $tooltipText = implode('<br>', $tooltipNames);
                $pelaksanaHtml = '<div class="pelaksana-single-line" data-toggle="tooltip" data-html="true" data-placement="top" title="' . esc($tooltipText, 'attr') . '">' . esc($singleLineText) . '</div>';
            } else {
                $pelaksanaHtml = '-';
            }

            $tglMulai   = ! empty($row['periode_mulai']) ? date('d-m-Y', strtotime($row['periode_mulai'])) : '-';
            $tglSelesai = ! empty($row['periode_selesai']) ? date('d-m-Y', strtotime($row['periode_selesai'])) : '-';
            $periodeLabel = $tglMulai === $tglSelesai ? $tglMulai : $tglMulai . ' s/d ' . $tglSelesai;
            $periodeHtml  = '<span style="white-space: nowrap !important; display: inline-block;">' . esc($periodeLabel) . '</span>';

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
                'tujuan'             => (string) ($row['tujuan'] ?? ''),
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

        $pelaksanaIds = $this->request->getPost('pelaksana_id');
        if (is_array($pelaksanaIds)) {
            $pelaksanaIds = array_filter(array_map('intval', $pelaksanaIds));
            $pelaksanaIds = array_values(array_unique($pelaksanaIds));
        } else {
            $pelaksanaIds = [];
        }

        $nomorNotaDinas   = trim((string) $this->request->getPost('nomor_nota_dinas'));
        $kepadaYth        = trim((string) $this->request->getPost('kepada_yth'));
        $dari             = trim((string) $this->request->getPost('dari'));
        $hal              = trim((string) $this->request->getPost('hal'));
        $tanggalNotaDinas = trim((string) $this->request->getPost('tanggal_nota_dinas'));
        $isiNotaDinas     = trim((string) $this->request->getPost('isi_nota_dinas'));
        $periodeMulai     = trim((string) $this->request->getPost('periode_mulai'));
        $periodeSelesai   = trim((string) $this->request->getPost('periode_selesai'));
        $tujuan           = trim((string) $this->request->getPost('tujuan'));
        $ppkPegawaiId     = (int) $this->request->getPost('ppk_pegawai_id');

        if ($kepadaYth === '') {
            $kepadaYth = 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        }
        if ($dari === '') {
            $dari = 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
        }
        if ($hal === '') {
            $hal = 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
        }

        $errors = [];
        if (empty($pelaksanaIds)) {
            $errors[] = 'Pegawai yang diusulkan minimal harus dipilih 1 orang.';
        }
        if ($isiNotaDinas === '') {
            $errors[] = 'Isi Nota Dinas wajib diisi.';
        }
        if ($periodeMulai === '') {
            $errors[] = 'Periode Mulai Perjalanan wajib diisi.';
        }
        if ($periodeSelesai === '') {
            $errors[] = 'Periode Selesai Perjalanan wajib diisi.';
        }
        if ($periodeMulai !== '' && $periodeSelesai !== '' && $periodeSelesai < $periodeMulai) {
            $errors[] = 'Periode Selesai tidak boleh kurang dari Periode Mulai.';
        }
        if ($tujuan === '') {
            $errors[] = 'Tujuan Perjalanan wajib diisi.';
        }
        if ($ppkPegawaiId <= 0) {
            $errors[] = 'Pejabat Pembuat Komitmen (Penandatangan) wajib dipilih.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', implode(' ', $errors));
        }

        $pegawaiOptions = $this->loadPegawaiOptions();
        $pelaksanaRows  = $this->buildPegawaiRowsByIds($pegawaiOptions, $pelaksanaIds);
        $username       = trim((string) session()->get('username'));

        $model = new NotaDinasPerjalananDinasModel();
        $data = [
            'nomor_nota_dinas'   => $nomorNotaDinas !== '' ? $nomorNotaDinas : null,
            'kepada_yth'         => $kepadaYth,
            'dari'               => $dari,
            'hal'                => $hal,
            'tanggal_nota_dinas' => $tanggalNotaDinas !== '' ? $tanggalNotaDinas : null,
            'isi_nota_dinas'     => $isiNotaDinas,
            'pelaksana_json'     => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'periode_mulai'      => $periodeMulai,
            'periode_selesai'    => $periodeSelesai,
            'tujuan'             => $tujuan,
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

        $pelaksanaIds = $this->request->getPost('pelaksana_id');
        if (is_array($pelaksanaIds)) {
            $pelaksanaIds = array_filter(array_map('intval', $pelaksanaIds));
            $pelaksanaIds = array_values(array_unique($pelaksanaIds));
        } else {
            $pelaksanaIds = [];
        }

        $nomorNotaDinas   = trim((string) $this->request->getPost('nomor_nota_dinas'));
        $kepadaYth        = trim((string) $this->request->getPost('kepada_yth'));
        $dari             = trim((string) $this->request->getPost('dari'));
        $hal              = trim((string) $this->request->getPost('hal'));
        $tanggalNotaDinas = trim((string) $this->request->getPost('tanggal_nota_dinas'));
        $isiNotaDinas     = trim((string) $this->request->getPost('isi_nota_dinas'));
        $periodeMulai     = trim((string) $this->request->getPost('periode_mulai'));
        $periodeSelesai   = trim((string) $this->request->getPost('periode_selesai'));
        $tujuan           = trim((string) $this->request->getPost('tujuan'));
        $ppkPegawaiId     = (int) $this->request->getPost('ppk_pegawai_id');

        if ($kepadaYth === '') {
            $kepadaYth = 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        }
        if ($dari === '') {
            $dari = 'Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis Riau';
        }
        if ($hal === '') {
            $hal = 'Permohonan Penerbitan Surat Tugas Pelaksanaan Kegiatan';
        }

        $errors = [];
        if (empty($pelaksanaIds)) {
            $errors[] = 'Pegawai yang diusulkan minimal harus dipilih 1 orang.';
        }
        if ($isiNotaDinas === '') {
            $errors[] = 'Isi Nota Dinas wajib diisi.';
        }
        if ($periodeMulai === '') {
            $errors[] = 'Periode Mulai Perjalanan wajib diisi.';
        }
        if ($periodeSelesai === '') {
            $errors[] = 'Periode Selesai Perjalanan wajib diisi.';
        }
        if ($periodeMulai !== '' && $periodeSelesai !== '' && $periodeSelesai < $periodeMulai) {
            $errors[] = 'Periode Selesai tidak boleh kurang dari Periode Mulai.';
        }
        if ($tujuan === '') {
            $errors[] = 'Tujuan Perjalanan wajib diisi.';
        }
        if ($ppkPegawaiId <= 0) {
            $errors[] = 'Pejabat Pembuat Komitmen (Penandatangan) wajib dipilih.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', implode(' ', $errors));
        }

        $pegawaiOptions = $this->loadPegawaiOptions();
        $pelaksanaRows  = $this->buildPegawaiRowsByIds($pegawaiOptions, $pelaksanaIds);
        $username       = trim((string) session()->get('username'));

        $data = [
            'nomor_nota_dinas'   => $nomorNotaDinas !== '' ? $nomorNotaDinas : null,
            'kepada_yth'         => $kepadaYth,
            'dari'               => $dari,
            'hal'                => $hal,
            'tanggal_nota_dinas' => $tanggalNotaDinas !== '' ? $tanggalNotaDinas : null,
            'isi_nota_dinas'     => $isiNotaDinas,
            'pelaksana_json'     => json_encode($pelaksanaRows, JSON_UNESCAPED_UNICODE),
            'periode_mulai'      => $periodeMulai,
            'periode_selesai'    => $periodeSelesai,
            'tujuan'             => $tujuan,
            'ppk_pegawai_id'     => $ppkPegawaiId,
            'updated_by'         => $username,
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if ($model->update($id, $data) === false) {
            return redirect()->to(site_url(self::MENU_LINK))->with('error', 'Gagal memperbarui data Nota Dinas.');
        }

        return redirect()->to(site_url(self::MENU_LINK))->with('success', 'Nota Dinas Perjalanan Dinas berhasil diperbarui.');
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
                'id'            => (int) ($row['id'] ?? 0),
                'nama'          => (string) ($row['nama'] ?? ''),
                'nip'           => (string) ($row['nip'] ?? ''),
                'jabatan'       => (string) ($row['jabatan_label'] ?? ''),
                'golongan'      => (string) ($row['golongan'] ?? ''),
                'jenis_pegawai' => (string) ($row['jenis_pegawai'] ?? ''),
            ];
        }

        return $this->sortPelaksanaByStrukturOrganisasi($rows);
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

