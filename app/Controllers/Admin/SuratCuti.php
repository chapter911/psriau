<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SuratCutiModel;
use App\Models\MstPegawaiModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;

class SuratCuti extends BaseController
{
    public function index()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if ($this->request->isAJAX() || $this->isDataTableRequest()) {
            return $this->dataTable();
        }

        $role = strtolower((string) session()->get('role'));
        $canApprove = in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        // Current logged-in employee details
        $pegawaiData = $this->getCurrentPegawaiData();

        // All active employees for dropdown selection
        $db = db_connect();
        $pegawaiList = [];
        if ($db->tableExists('mst_pegawai')) {
            $builder = $db->table('mst_pegawai');
            $builder->select('mst_pegawai.*, ju.jabatan AS jabatan_label');
            $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
            $builder->where('mst_pegawai.is_active', 1);
            $builder->orderBy('mst_pegawai.nama', 'ASC');
            $pegawaiList = $builder->get()->getResultArray();
        }

        return view('admin/surat/cuti', [
            'title' => 'Pengajuan Cuti',
            'can_edit' => $this->canAccess(),
            'can_approve' => $canApprove,
            'current_pegawai' => $pegawaiData,
            'pegawai_list' => $pegawaiList,
        ]);
    }

    private function dataTable()
    {
        $canEdit = $this->canAccess();
        $role = strtolower((string) session()->get('role'));
        $canApprove = in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        $draw = $this->getDataTableDraw();
        $start = $this->getDataTableStart();
        $length = $this->getDataTableLength();
        $search = $this->getDataTableSearchTerm();
        $orderIndex = $this->getDataTableOrderColumnIndex();
        $orderDirection = $this->getDataTableOrderDirection();

        $db = db_connect();

        if (! $db->tableExists('surat_cuti')) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $builder = $db->table('surat_cuti');
        $recordsTotal = (int) $builder->countAllResults(false);

        if ($search !== '') {
            $builder->groupStart();
            $builder->like('nip', $search);
            $builder->orLike('nama', $search);
            $builder->orLike('jenis_cuti', $search);
            $builder->orLike('alasan_cuti', $search);
            $builder->groupEnd();
        }

        $recordsFiltered = (int) $builder->countAllResults(false);

        $orderColumns = ['id', 'tanggal_pengajuan', 'nama', 'jenis_cuti', 'tanggal_mulai', 'status', 'id'];
        $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0];
        $builder->orderBy($orderColumn, $orderDirection);

        $rows = $builder->limit($length, $start)->get()->getResultArray();

        $data = array_map(function (array $row) use ($canEdit, $canApprove) {
            $status = trim((string) ($row['status'] ?? 'pending'));
            $statusBadge = match ($status) {
                'disetujui' => '<span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>',
                'ditolak' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>',
                default => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            };

            $row['status_badge'] = $statusBadge;
            $row['tanggal_pengajuan_formatted'] = !empty($row['tanggal_pengajuan']) ? date('d/m/Y', strtotime($row['tanggal_pengajuan'])) : '-';

            $tglMulai = !empty($row['tanggal_mulai']) ? date('d/m/Y', strtotime($row['tanggal_mulai'])) : '-';
            $tglSelesai = !empty($row['tanggal_selesai']) ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-';
            $lama = (int) ($row['lama_cuti_jumlah'] ?? 1) . ' ' . esc($row['lama_cuti_satuan'] ?? 'Hari');
            $row['periode_formatted'] = $tglMulai . ' s/d ' . $tglSelesai . '<br><small class="text-muted">(' . $lama . ')</small>';

            // Dokumen Export buttons (DOCX & PDF)
            $id = (int) ($row['id'] ?? 0);
            $dokumenHtml = '<div class="btn-group btn-group-sm" role="group">';
            $dokumenHtml .= '<a href="' . site_url('admin/surat/cuti/' . $id . '/export-docx') . '" class="btn btn-outline-primary" title="Export DOCX"><i class="fas fa-file-word"></i> DOCX</a>';
            $dokumenHtml .= '<a href="' . site_url('admin/surat/cuti/' . $id . '/export-pdf') . '" class="btn btn-outline-danger" title="Export PDF" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>';
            $dokumenHtml .= '</div>';
            $row['dokumen_html'] = $dokumenHtml;

            $actions = '';
            if ($canEdit || $canApprove) {
                $actions .= '<div class="d-flex justify-content-center align-items-center" style="gap: 5px; white-space: nowrap;">';
                if ($canEdit && $status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                }
                if ($canEdit) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . $id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }
                if ($canApprove && $status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . $id . '" title="Setujui"><i class="fas fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . $id . '" title="Tolak"><i class="fas fa-times"></i></button>';
                }
                $actions .= '</div>';
            }
            $row['action_html'] = $actions ?: '<span class="text-muted">-</span>';

            return $row;
        }, $rows);

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function buat()
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return redirect()->to(site_url('admin/surat/cuti'));
        }

        return $this->simpanCuti();
    }

    private function simpanCuti()
    {
        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $masaKerja = trim((string) $this->request->getPost('masa_kerja'));
        $unitKerja = trim((string) $this->request->getPost('unit_kerja')) ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $jenisCuti = trim((string) $this->request->getPost('jenis_cuti'));
        $alasanCuti = trim((string) $this->request->getPost('alasan_cuti'));
        $lamaJumlah = max(1, (int) $this->request->getPost('lama_cuti_jumlah'));
        $lamaSatuan = trim((string) $this->request->getPost('lama_cuti_satuan')) ?: 'Hari';
        $tanggalMulai = trim((string) $this->request->getPost('tanggal_mulai'));
        $tanggalSelesai = trim((string) $this->request->getPost('tanggal_selesai'));
        $alamat = trim((string) $this->request->getPost('alamat_selama_cuti'));
        $telepon = trim((string) $this->request->getPost('telepon'));

        $catatanN2 = max(0, (int) $this->request->getPost('catatan_cuti_n2'));
        $catatanN1 = max(0, (int) $this->request->getPost('catatan_cuti_n1'));
        $catatanN = max(0, (int) $this->request->getPost('catatan_cuti_n'));
        $catatanKet = trim((string) $this->request->getPost('catatan_cuti_keterangan'));

        $atasanNama = trim((string) $this->request->getPost('atasan_nama')) ?: 'Muhammad Yudi Prasetya, ST';
        $atasanNip = trim((string) $this->request->getPost('atasan_nip')) ?: '198002142014121002';
        $atasanJabatan = trim((string) $this->request->getPost('atasan_jabatan')) ?: 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau';

        $pejabatNama = trim((string) $this->request->getPost('pejabat_nama')) ?: 'Ir. Agung Hari Prabowo, M.T';
        $pejabatNip = trim((string) $this->request->getPost('pejabat_nip')) ?: '196910301998031005';
        $pejabatJabatan = trim((string) $this->request->getPost('pejabat_jabatan')) ?: 'Plt. Sekretariat Direktorat Jenderal Prasarana Strategis';

        // Tanggal pengajuan CANNOT be changed by user -> locked to current date
        $tanggalPengajuan = date('Y-m-d');

        $errors = [];
        if ($nama === '') {
            $errors[] = 'Nama pegawai wajib diisi.';
        }
        if ($nip === '') {
            $errors[] = 'NIP wajib diisi.';
        }
        if ($jenisCuti === '') {
            $errors[] = 'Jenis cuti wajib dipilih.';
        }
        if ($alasanCuti === '') {
            $errors[] = 'Alasan cuti wajib diisi.';
        }
        if ($tanggalMulai === '' || $tanggalSelesai === '') {
            $errors[] = 'Tanggal mulai dan selesai cuti wajib diisi.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', implode(' ', $errors));
        }

        $model = new SuratCutiModel();
        $username = trim((string) session()->get('username'));

        $data = [
            'tanggal_pengajuan' => $tanggalPengajuan,
            'pegawai_id' => (int) $this->request->getPost('pegawai_id') ?: null,
            'nama' => $nama,
            'nip' => $nip,
            'jabatan' => $jabatan,
            'masa_kerja' => $masaKerja,
            'unit_kerja' => $unitKerja,
            'jenis_cuti' => $jenisCuti,
            'alasan_cuti' => $alasanCuti,
            'lama_cuti_jumlah' => $lamaJumlah,
            'lama_cuti_satuan' => $lamaSatuan,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'alamat_selama_cuti' => $alamat,
            'telepon' => $telepon,
            'catatan_cuti_n2' => $catatanN2,
            'catatan_cuti_n1' => $catatanN1,
            'catatan_cuti_n' => $catatanN,
            'catatan_cuti_keterangan' => $catatanKet,
            'atasan_nama' => $atasanNama,
            'atasan_nip' => $atasanNip,
            'atasan_jabatan' => $atasanJabatan,
            'pejabat_nama' => $pejabatNama,
            'pejabat_nip' => $pejabatNip,
            'pejabat_jabatan' => $pejabatJabatan,
            'pertimbangan_atasan' => 'Pending',
            'keputusan_pejabat' => 'Pending',
            'status' => 'pending',
            'created_by' => $username,
        ];

        $insertId = $model->insert($data);

        if ($insertId === false) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Gagal menyimpan pengajuan cuti.');
        }

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil disimpan.');
    }

    public function detail(int $id)
    {
        if (! $this->canAccess()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak.']);
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data cuti tidak ditemukan.']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $row]);
    }

    public function ubah(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $masaKerja = trim((string) $this->request->getPost('masa_kerja'));
        $unitKerja = trim((string) $this->request->getPost('unit_kerja')) ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau';
        $jenisCuti = trim((string) $this->request->getPost('jenis_cuti'));
        $alasanCuti = trim((string) $this->request->getPost('alasan_cuti'));
        $lamaJumlah = max(1, (int) $this->request->getPost('lama_cuti_jumlah'));
        $lamaSatuan = trim((string) $this->request->getPost('lama_cuti_satuan')) ?: 'Hari';
        $tanggalMulai = trim((string) $this->request->getPost('tanggal_mulai'));
        $tanggalSelesai = trim((string) $this->request->getPost('tanggal_selesai'));
        $alamat = trim((string) $this->request->getPost('alamat_selama_cuti'));
        $telepon = trim((string) $this->request->getPost('telepon'));

        $catatanN2 = max(0, (int) $this->request->getPost('catatan_cuti_n2'));
        $catatanN1 = max(0, (int) $this->request->getPost('catatan_cuti_n1'));
        $catatanN = max(0, (int) $this->request->getPost('catatan_cuti_n'));
        $catatanKet = trim((string) $this->request->getPost('catatan_cuti_keterangan'));

        $data = [
            'nama' => $nama,
            'nip' => $nip,
            'jabatan' => $jabatan,
            'masa_kerja' => $masaKerja,
            'unit_kerja' => $unitKerja,
            'jenis_cuti' => $jenisCuti,
            'alasan_cuti' => $alasanCuti,
            'lama_cuti_jumlah' => $lamaJumlah,
            'lama_cuti_satuan' => $lamaSatuan,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'alamat_selama_cuti' => $alamat,
            'telepon' => $telepon,
            'catatan_cuti_n2' => $catatanN2,
            'catatan_cuti_n1' => $catatanN1,
            'catatan_cuti_n' => $catatanN,
            'catatan_cuti_keterangan' => $catatanKet,
        ];

        $model->update($id, $data);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

    public function setujui(int $id)
    {
        $role = strtolower((string) session()->get('role'));
        $canApprove = in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        if (! $canApprove) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses persetujuan.');
        }

        $model = new SuratCutiModel();
        $model->update($id, [
            'pertimbangan_atasan' => 'Disetujui',
            'keputusan_pejabat'   => 'Disetujui',
            'status'              => 'disetujui',
        ]);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti berhasil disetujui.');
    }

    public function tolak(int $id)
    {
        $role = strtolower((string) session()->get('role'));
        $canApprove = in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        if (! $canApprove) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Anda tidak memiliki hak akses persetujuan.');
        }

        $model = new SuratCutiModel();
        $model->update($id, [
            'pertimbangan_atasan' => 'Tidak Disetujui',
            'keputusan_pejabat'   => 'Tidak Disetujui',
            'status'              => 'ditolak',
        ]);

        return redirect()->to(site_url('admin/surat/cuti'))->with('success', 'Pengajuan cuti ditolak.');
    }

    public function exportDocx(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $templatePath = ROOTPATH . 'do_not_upload/form Surat Cuti.docx';
        if (! file_exists($templatePath)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Template form Surat Cuti.docx tidak ditemukan.');
        }

        // Create temporary copy to manipulate
        $tempDir = WRITEPATH . 'uploads/';
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $filename = 'Form_Cuti_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['nama']) . '_' . date('Ymd_His') . '.docx';
        $outputPath = $tempDir . $filename;
        @copy($templatePath, $outputPath);

        $zip = new \ZipArchive();
        if ($zip->open($outputPath) === true) {
            $xmlContent = $zip->getFromName('word/document.xml');

            if ($xmlContent !== false) {
                $xmlContent = $this->populateDocxXml($xmlContent, $row);
                $zip->addFromString('word/document.xml', $xmlContent);
            }
            $zip->close();

            // Download file
            return $this->response->download($outputPath, null)->setFileName($filename);
        }

        return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Gagal memproses file DOCX.');
    }

    private function populateDocxXml(string $xml, array $data): string
    {
        $tglPengajuan = !empty($data['tanggal_pengajuan']) ? date('d F Y', strtotime($data['tanggal_pengajuan'])) : date('d F Y');
        // Translate months to Indonesian
        $months = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
        foreach ($months as $en => $id) {
            $tglPengajuan = str_replace($en, $id, $tglPengajuan);
        }

        // Parse DOMDocument for precise cell content replacement
        $dom = new \DOMDocument();
        // Ignore errors due to Word processing namespaces
        libxml_use_internal_errors(true);
        $dom->loadXML($xml);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // Helper to replace text in cell
        $setCellText = function (\DOMElement $cell, string $newText) use ($dom, $xpath) {
            $paragraphs = $xpath->query('.//w:p', $cell);
            if ($paragraphs->length > 0) {
                // Keep first paragraph, clean its runs
                $p = $paragraphs->item(0);
                $runs = $xpath->query('.//w:r', $p);
                if ($runs->length > 0) {
                    $firstRun = $runs->item(0);
                    // Remove all text elements inside first run except set new text
                    $tNodes = $xpath->query('.//w:t', $firstRun);
                    if ($tNodes->length > 0) {
                        $tNodes->item(0)->nodeValue = htmlspecialchars($newText, ENT_XML1, 'UTF-8');
                    } else {
                        $tNode = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t', htmlspecialchars($newText, ENT_XML1, 'UTF-8'));
                        $firstRun->appendChild($tNode);
                    }
                    // Remove extra runs
                    for ($i = 1; $i < $runs->length; $i++) {
                        $p->removeChild($runs->item($i));
                    }
                } else {
                    $rNode = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r');
                    $tNode = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t', htmlspecialchars($newText, ENT_XML1, 'UTF-8'));
                    $rNode->appendChild($tNode);
                    $p->appendChild($rNode);
                }
                // Remove extra paragraphs
                for ($j = 1; $j < $paragraphs->length; $j++) {
                    $cell->removeChild($paragraphs->item($j));
                }
            }
        };

        // 1. Update top date "Pekanbaru, .............. 2026" -> "Pekanbaru, " . $tglPengajuan
        $tNodes = $xpath->query('//w:t');
        foreach ($tNodes as $tNode) {
            if (strpos($tNode->nodeValue, 'Pekanbaru') !== false) {
                $tNode->nodeValue = 'Pekanbaru, ' . $tglPengajuan;
                break;
            }
        }

        $tables = $xpath->query('//w:tbl');
        if ($tables->length >= 8) {
            // TABLE 1: DATA PEGAWAI
            // Row 2: Nama | [val] | NIP | [val]
            // Row 3: Jabatan | [val] | Masa Kerja | [val]
            // Row 4: Unit Kerja | [val]
            $tbl1 = $tables->item(0);
            $rows1 = $xpath->query('.//w:tr', $tbl1);
            if ($rows1->length >= 4) {
                $r2_cells = $xpath->query('.//w:tc', $rows1->item(1));
                if ($r2_cells->length >= 4) {
                    $setCellText($r2_cells->item(1), $data['nama'] ?? '');
                    $setCellText($r2_cells->item(3), $data['nip'] ?? '');
                }
                $r3_cells = $xpath->query('.//w:tc', $rows1->item(2));
                if ($r3_cells->length >= 4) {
                    $setCellText($r3_cells->item(1), $data['jabatan'] ?? '');
                    $setCellText($r3_cells->item(3), $data['masa_kerja'] ?? '');
                }
                $r4_cells = $xpath->query('.//w:tc', $rows1->item(3));
                if ($r4_cells->length >= 2) {
                    $setCellText($r4_cells->item(1), $data['unit_kerja'] ?? 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau');
                }
            }

            // TABLE 2: JENIS CUTI
            // Rows:
            // R2: Cuti Tahunan | [col2] | Cuti Besar | [col4]
            // R3: Cuti sakit | [col2] | Cuti Melahirkan | [col4]
            // R4: Cuti Karena Alasan Penting | [col2] | Cuti di Luar Tanggungan Negara | [col4]
            $tbl2 = $tables->item(1);
            $jenisMap = [
                'cuti tahunan' => [1, 1],
                'cuti besar' => [1, 3],
                'cuti sakit' => [2, 1],
                'cuti melahirkan' => [2, 3],
                'cuti karena alasan penting' => [3, 1],
                'cuti di luar tanggungan negara' => [3, 3],
            ];

            $selectedJenisKey = strtolower(trim((string) ($data['jenis_cuti'] ?? '')));
            $rows2 = $xpath->query('.//w:tr', $tbl2);

            foreach ($jenisMap as $key => [$rowIdx, $cellIdx]) {
                if ($rowIdx < $rows2->length) {
                    $cells = $xpath->query('.//w:tc', $rows2->item($rowIdx));
                    if ($cellIdx < $cells->length) {
                        $checkVal = ($key === $selectedJenisKey) ? '√' : '';
                        $setCellText($cells->item($cellIdx), $checkVal);
                    }
                }
            }

            // TABLE 3: ALASAN CUTI
            $tbl3 = $tables->item(2);
            $rows3 = $xpath->query('.//w:tr', $tbl3);
            if ($rows3->length >= 2) {
                $r2_cells = $xpath->query('.//w:tc', $rows3->item(1));
                if ($r2_cells->length >= 1) {
                    $setCellText($r2_cells->item(0), $data['alasan_cuti'] ?? '');
                }
            }

            // TABLE 4: LAMANYA CUTI
            // R2: Selama | [col2] | Mulai tanggal | [col4] | s/d | [col6]
            $tbl4 = $tables->item(3);
            $rows4 = $xpath->query('.//w:tr', $tbl4);
            if ($rows4->length >= 2) {
                $r2_cells = $xpath->query('.//w:tc', $rows4->item(1));
                if ($r2_cells->length >= 6) {
                    $lamaStr = ((int) ($data['lama_cuti_jumlah'] ?? 1)) . ' ' . ($data['lama_cuti_satuan'] ?? 'Hari');
                    $tglMulai = !empty($data['tanggal_mulai']) ? date('d/m/Y', strtotime($data['tanggal_mulai'])) : '';
                    $tglSelesai = !empty($data['tanggal_selesai']) ? date('d/m/Y', strtotime($data['tanggal_selesai'])) : '';

                    $setCellText($r2_cells->item(1), $lamaStr);
                    $setCellText($r2_cells->item(3), $tglMulai);
                    $setCellText($r2_cells->item(5), $tglSelesai);
                }
            }

            // TABLE 5: CATATAN CUTI
            // R3: Tahun | Sisa | Keterangan | 3. CUTI SAKIT | [val]
            // R4: [val] | [val] | [val] | 4. CUTI MELAHIRKAN | [val]
            // R5: [val] | [val] | [val] | 5. CUTI KARENA ALASAN PENTING | [val]
            // R6: [val] | [val] | [val] | CUTI DI LUAR TANGGUNGAN NEGARA | [val]
            $tbl5 = $tables->item(4);
            $rows5 = $xpath->query('.//w:tr', $tbl5);

            $currentYear = (int) date('Y');
            if ($rows5->length >= 6) {
                // R4: N-2
                $r4_cells = $xpath->query('.//w:tc', $rows5->item(3));
                if ($r4_cells->length >= 3) {
                    $setCellText($r4_cells->item(0), 'N-2 (' . ($currentYear - 2) . ')');
                    $setCellText($r4_cells->item(1), (string) ($data['catatan_cuti_n2'] ?? 0));
                    $setCellText($r4_cells->item(2), $data['catatan_cuti_keterangan'] ?? '');
                }
                // R5: N-1
                $r5_cells = $xpath->query('.//w:tc', $rows5->item(4));
                if ($r5_cells->length >= 3) {
                    $setCellText($r5_cells->item(0), 'N-1 (' . ($currentYear - 1) . ')');
                    $setCellText($r5_cells->item(1), (string) ($data['catatan_cuti_n1'] ?? 0));
                }
                // R6: N
                $r6_cells = $xpath->query('.//w:tc', $rows5->item(5));
                if ($r6_cells->length >= 3) {
                    $setCellText($r6_cells->item(0), 'N (' . $currentYear . ')');
                    $setCellText($r6_cells->item(1), (string) ($data['catatan_cuti_n'] ?? 0));
                }
            }

            // TABLE 6: ALAMAT SELAMA CUTI
            // R2: [alamat] | TELP | [telepon]
            // R3: [val] | Hormat saya,\n\n[nama]\nNIP. [nip]
            $tbl6 = $tables->item(5);
            $rows6 = $xpath->query('.//w:tr', $tbl6);
            if ($rows6->length >= 3) {
                $r2_cells = $xpath->query('.//w:tc', $rows6->item(1));
                if ($r2_cells->length >= 3) {
                    $setCellText($r2_cells->item(0), $data['alamat_selama_cuti'] ?? '');
                    $setCellText($r2_cells->item(2), $data['telepon'] ?? '');
                }
                $r3_cells = $xpath->query('.//w:tc', $rows6->item(2));
                if ($r3_cells->length >= 2) {
                    $ttdPemohon = "Hormat saya,\n\n\n" . ($data['nama'] ?? '') . "\nNIP. " . ($data['nip'] ?? '');
                    $setCellText($r3_cells->item(1), $ttdPemohon);
                }
            }

            // TABLE 7: PERTIMBANGAN ATASAN LANGSUNG
            // R3: [c1] | [c2] | [c3] | [c4] (Checkmarks for DISETUJUI, PERUBAHAN, DITANGGUHKAN, TIDAK DISETUJUI)
            // R4: C4 contains Signature block of Atasan
            $tbl7 = $tables->item(6);
            $rows7 = $xpath->query('.//w:tr', $tbl7);
            if ($rows7->length >= 4) {
                $r3_cells = $xpath->query('.//w:tc', $rows7->item(2));
                $pertimbangan = strtolower(trim((string) ($data['pertimbangan_atasan'] ?? '')));
                if ($r3_cells->length >= 4) {
                    $setCellText($r3_cells->item(0), ($pertimbangan === 'disetujui' || $pertimbangan === 'setuju') ? '√' : '');
                    $setCellText($r3_cells->item(1), ($pertimbangan === 'perubahan') ? '√' : '');
                    $setCellText($r3_cells->item(2), ($pertimbangan === 'ditangguhkan') ? '√' : '');
                    $setCellText($r3_cells->item(3), ($pertimbangan === 'tidak disetujui' || $pertimbangan === 'ditolak') ? '√' : '');
                }

                $r4_cells = $xpath->query('.//w:tc', $rows7->item(3));
                if ($r4_cells->length >= 4) {
                    $ttdAtasan = ($data['atasan_jabatan'] ?? 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau') . ",\n\n\n" . ($data['atasan_nama'] ?? 'Muhammad Yudi Prasetya, ST') . "\nNIP. " . ($data['atasan_nip'] ?? '198002142014121002');
                    $setCellText($r4_cells->item(3), $ttdAtasan);
                }
            }

            // TABLE 8: KEPUTUSAN PEJABAT YANG BERWENANG
            // R3: Checkmarks for DISETUJUI, PERUBAHAN, DITANGGUHKAN, TIDAK DISETUJUI
            $tbl8 = $tables->item(7);
            $rows8 = $xpath->query('.//w:tr', $tbl8);
            if ($rows8->length >= 3) {
                $r3_cells = $xpath->query('.//w:tc', $rows8->item(2));
                $keputusan = strtolower(trim((string) ($data['keputusan_pejabat'] ?? '')));
                if ($r3_cells->length >= 4) {
                    $setCellText($r3_cells->item(0), ($keputusan === 'disetujui' || $keputusan === 'setuju') ? '√' : '');
                    $setCellText($r3_cells->item(1), ($keputusan === 'perubahan') ? '√' : '');
                    $setCellText($r3_cells->item(2), ($keputusan === 'ditangguhkan') ? '√' : '');
                    $setCellText($r3_cells->item(3), ($keputusan === 'tidak disetujui' || $keputusan === 'ditolak') ? '√' : '');
                }
            }
        }

        return $dom->saveXML();
    }

    public function exportPdf(int $id)
    {
        if (! $this->canAccess()) {
            return redirect()->to(site_url('/admin'));
        }

        $model = new SuratCutiModel();
        $row = $model->find($id);

        if (! is_array($row)) {
            return redirect()->to(site_url('admin/surat/cuti'))->with('error', 'Data tidak ditemukan.');
        }

        $html = view('admin/surat/cuti_pdf', [
            'data' => $row,
        ]);

        if (class_exists(Dompdf::class)) {
            $dompdf = new Dompdf(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Form_Cuti_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['nama']) . '.pdf';
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setBody($dompdf->output());
        }

        return $this->response->setBody($html);
    }

    private function getCurrentPegawaiData(): array
    {
        $username = trim((string) session()->get('username'));
        $fullName = trim((string) (session()->get('fullName') ?? ''));

        $db = db_connect();
        if (! $db->tableExists('mst_pegawai')) {
            return [
                'nama' => $fullName ?: $username,
                'nip' => $username,
                'jabatan' => '',
                'masa_kerja' => '',
                'unit_kerja' => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            ];
        }

        $builder = $db->table('mst_pegawai');
        $builder->select('mst_pegawai.*, ju.jabatan AS jabatan_label');
        $builder->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
        $builder->where('mst_pegawai.is_active', 1);
        $builder->groupStart();
        $builder->where('mst_pegawai.nip', $username);
        $builder->orWhere('LOWER(mst_pegawai.nama)', strtolower($fullName));
        $builder->groupEnd();
        $builder->limit(1);

        $pegawai = $builder->get()->getRowArray();

        if (! is_array($pegawai)) {
            return [
                'nama' => $fullName ?: $username,
                'nip' => $username,
                'jabatan' => '',
                'masa_kerja' => '',
                'unit_kerja' => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
            ];
        }

        return [
            'nama' => trim((string) ($pegawai['nama'] ?? '')),
            'nip' => trim((string) ($pegawai['nip'] ?? '')),
            'jabatan' => trim((string) ($pegawai['jabatan_label'] ?? '')),
            'masa_kerja' => trim((string) ($pegawai['masa_kerja'] ?? '')),
            'unit_kerja' => trim((string) ($pegawai['unit_kerja'] ?? '')) ?: 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
        ];
    }

    private function canAccess(): bool
    {
        $role = strtolower((string) session()->get('role'));
        return in_array($role, ['admin', 'editor', 'staf', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
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

    private function getDataTableOrderColumnIndex(): int
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 0;
        }
        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 0;
        }
        return max(0, (int) ($first['column'] ?? 0));
    }

    private function getDataTableOrderDirection(): string
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 'DESC';
        }
        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 'DESC';
        }
        return strtolower((string) ($first['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
    }
}
