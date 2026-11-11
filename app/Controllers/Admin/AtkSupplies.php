<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AtkExcelExporter;
use App\Models\AtkBarangModel;
use App\Models\AtkDokumenTtdModel;
use App\Models\AtkPengadaanItemModel;
use App\Models\AtkPengadaanModel;
use App\Models\AtkPenyediaModel;
use App\Models\MstMataAnggaranModel;
use App\Models\MstPegawaiModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AtkSupplies extends BaseController
{
    protected AtkPenyediaModel $penyediaModel;
    protected AtkBarangModel $barangModel;
    protected AtkPengadaanModel $pengadaanModel;
    protected AtkPengadaanItemModel $itemModel;
    protected AtkDokumenTtdModel $dokumenTtdModel;

    public function __construct()
    {
        $this->penyediaModel   = new AtkPenyediaModel();
        $this->barangModel     = new AtkBarangModel();
        $this->pengadaanModel  = new AtkPengadaanModel();
        $this->itemModel       = new AtkPengadaanItemModel();
        $this->dokumenTtdModel = new AtkDokumenTtdModel();
    }

    // =========================================================================
    // 1. PENGADAAN & SPJ
    // =========================================================================

    public function pengadaanIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        $tahun       = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $bulan       = trim((string) ($this->request->getGet('bulan') ?? ''));
        $kategori    = trim((string) ($this->request->getGet('kategori') ?? ''));
        $status      = trim((string) ($this->request->getGet('status') ?? ''));

        $builder = $this->pengadaanModel
            ->select('trn_atk_pengadaan.*, mst_atk_penyedia.nama_perusahaan')
            ->join('mst_atk_penyedia', 'mst_atk_penyedia.id = trn_atk_pengadaan.penyedia_id', 'left');

        if ($tahun > 0) {
            $builder->where('trn_atk_pengadaan.tahun_anggaran', $tahun);
        }
        if ($bulan !== '' && is_numeric($bulan)) {
            $builder->where('trn_atk_pengadaan.bulan', (int) $bulan);
        }
        if ($kategori !== '') {
            $builder->where('trn_atk_pengadaan.kategori', $kategori);
        }
        if ($status !== '') {
            $builder->where('trn_atk_pengadaan.status', $status);
        }

        $pengadaanList = $builder
            ->orderBy('trn_atk_pengadaan.tahun_anggaran', 'DESC')
            ->orderBy('trn_atk_pengadaan.bulan', 'DESC')
            ->orderBy('trn_atk_pengadaan.id', 'DESC')
            ->findAll();

        // Hitung jumlah item & dokumen TTD per transaksi
        $ids = array_column($pengadaanList, 'id');
        $itemCounts = [];
        $docCounts  = [];
        if ($ids !== []) {
            $db = db_connect();
            foreach ($db->table('trn_atk_pengadaan_item')->select('pengadaan_id, COUNT(*) AS cnt')->whereIn('pengadaan_id', $ids)->groupBy('pengadaan_id')->get()->getResultArray() as $r) {
                $itemCounts[(int) $r['pengadaan_id']] = (int) $r['cnt'];
            }
            foreach ($db->table('trn_atk_dokumen_ttd')->select('pengadaan_id, COUNT(*) AS cnt')->whereIn('pengadaan_id', $ids)->groupBy('pengadaan_id')->get()->getResultArray() as $r) {
                $docCounts[(int) $r['pengadaan_id']] = (int) $r['cnt'];
            }
        }

        $totalNilai     = 0.0;
        $totalDisetujui = 0;
        $totalMenunggu  = 0;
        foreach ($pengadaanList as &$row) {
            $pid = (int) $row['id'];
            $row['item_count'] = $itemCounts[$pid] ?? 0;
            $row['doc_count']  = $docCounts[$pid] ?? 0;
            $totalNilai += (float) ($row['total_nilai'] ?? 0);
            if (in_array((string) $row['status'], ['disetujui', 'dibayar'], true)) {
                $totalDisetujui++;
            } else {
                $totalMenunggu++;
            }
        }
        unset($row);

        $masterRefs = $this->loadFormMasterReferences();

        return view('admin/atk/pengadaan', array_merge($masterRefs, [
            'title'          => 'Pengadaan ATK & Supplies',
            'pengadaanList'  => $pengadaanList,
            'permissions'    => $permissions,
            'filterTahun'    => $tahun,
            'filterBulan'    => $bulan,
            'filterKategori' => $kategori,
            'filterStatus'   => $status,
            'summary'        => [
                'total_transaksi' => count($pengadaanList),
                'total_nilai'     => $totalNilai,
                'total_disetujui' => $totalDisetujui,
                'total_menunggu'  => $totalMenunggu,
            ],
        ]));
    }

    public function pengadaanDetail(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        $bundle      = $this->loadBundle($id);
        if ($bundle === null) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $dokumenTtdList = $this->dokumenTtdModel
            ->where('pengadaan_id', $id)
            ->orderBy('id', 'DESC')
            ->findAll();

        $masterRefs = $this->loadFormMasterReferences();

        return view('admin/atk/pengadaan_detail', array_merge($masterRefs, [
            'title'          => 'Detail Pengadaan ATK & Supplies',
            'pengadaan'      => $bundle['pengadaan'],
            'items'          => $bundle['items'],
            'penyedia'       => $bundle['penyedia'],
            'dokumenTtdList' => $dokumenTtdList,
            'permissions'    => $permissions,
        ]));
    }

    public function pengadaanStore()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['add']) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Akses ditolak. Anda tidak memiliki izin menambah pengadaan.');
        }

        $data = $this->extractPengadaanHeaderFromPost();
        $data['status']     = 'draft';
        $data['created_by'] = (int) (session()->get('userId') ?? 0) ?: null;

        $db = db_connect();
        $db->transBegin();

        try {
            $this->pengadaanModel->insert($data);
            $pengadaanId = (int) $this->pengadaanModel->getInsertID();
            $this->saveItemsAndRecalculateTotal($pengadaanId);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengadaan ATK.');
            }
            $db->transCommit();
            return redirect()->to('/admin/atk/pengadaan/' . $pengadaanId)->with('success', 'Pengadaan ATK & Supplies berhasil dibuat. Silakan cetak dokumen SPJ atau unggah dokumen bertanda tangan basah.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function pengadaanUpdate(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['edit']) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Akses ditolak. Anda tidak memiliki izin mengubah pengadaan.');
        }

        $existing = $this->pengadaanModel->find($id);
        if (! $existing) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $data = $this->extractPengadaanHeaderFromPost();

        $db = db_connect();
        $db->transBegin();
        try {
            $this->pengadaanModel->update($id, $data);
            $this->saveItemsAndRecalculateTotal($id);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data pengadaan.');
            }
            $db->transCommit();
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('success', 'Data pengadaan berhasil diperbarui.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function pengadaanDelete(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['delete']) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Akses ditolak. Anda tidak memiliki izin menghapus pengadaan.');
        }

        $existing = $this->pengadaanModel->find($id);
        if (! $existing) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        // Hapus file fisik dokumen TTD jika ada
        $docs = $this->dokumenTtdModel->where('pengadaan_id', $id)->findAll();
        foreach ($docs as $doc) {
            $abs = $this->resolveUploadPath((string) ($doc['file_path'] ?? ''));
            if ($abs !== null && is_file($abs)) {
                @unlink($abs);
            }
        }

        $this->dokumenTtdModel->where('pengadaan_id', $id)->delete();
        $this->itemModel->where('pengadaan_id', $id)->delete();
        $this->pengadaanModel->delete($id);

        return redirect()->to('/admin/atk/pengadaan')->with('success', 'Data pengadaan berhasil dihapus.');
    }

    // =========================================================================
    // 2. UPLOAD DOKUMEN TANDA TANGAN BASAH & APPROVAL / PENOLAKAN
    // =========================================================================

    public function uploadDokumenTtd(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['edit'] && ! $permissions['add'] && ! $permissions['approval']) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Akses ditolak untuk mengunggah dokumen.');
        }

        $pengadaan = $this->pengadaanModel->find($id);
        if (! $pengadaan) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $file = $this->request->getFile('file_dokumen');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Silakan pilih file dokumen (PDF/JPG/PNG) yang valid.');
        }

        $ext = strtolower($file->getExtension());
        if (! in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Format file tidak didukung. Gunakan PDF, JPG, atau PNG.');
        }

        $targetDir = FCPATH . 'uploads/atk_ttd/' . $id;
        if (! is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $origName = $file->getClientName();
        $safeName = date('YmdHis') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $origName);
        $fileSize = (int) $file->getSize();
        $file->move($targetDir, $safeName);

        $jenisDokumen = trim((string) ($this->request->getPost('jenis_dokumen') ?? 'semua_gabungan'));
        $catatan      = trim((string) ($this->request->getPost('catatan') ?? ''));
        $autoApprove  = ((string) ($this->request->getPost('auto_approve') ?? '1')) === '1';

        $userId = (int) (session()->get('userId') ?? 0) ?: null;
        $now    = date('Y-m-d H:i:s');

        $statusVerif = $autoApprove ? 'disetujui' : 'menunggu';

        $this->dokumenTtdModel->insert([
            'pengadaan_id'      => $id,
            'jenis_dokumen'     => $jenisDokumen,
            'file_path'         => 'public/uploads/atk_ttd/' . $id . '/' . $safeName,
            'file_name'         => $origName,
            'file_size'         => $fileSize,
            'catatan'           => $catatan,
            'status_verifikasi' => $statusVerif,
            'verified_by'       => $autoApprove ? $userId : null,
            'verified_at'       => $autoApprove ? $now : null,
            'uploaded_by'       => $userId,
        ]);

        if ($autoApprove) {
            $this->pengadaanModel->update($id, [
                'status'           => 'disetujui',
                'approved_by'      => $userId,
                'approved_at'      => $now,
                'rejection_reason' => null,
                'rejected_by'      => null,
                'rejected_at'      => null,
            ]);
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('success', 'Dokumen bertanda tangan basah berhasil diunggah dan status otomatis Disetujui.');
        }

        $this->pengadaanModel->update($id, [
            'status'           => 'menunggu_ttd',
            'rejection_reason' => null,
        ]);
        return redirect()->to('/admin/atk/pengadaan/' . $id)->with('success', 'Dokumen bertanda tangan basah berhasil diunggah dan menunggu verifikasi.');
    }

    public function setujui(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['approval']) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Akses ditolak. Anda tidak memiliki izin persetujuan (FiturApproval).');
        }

        $pengadaan = $this->pengadaanModel->find($id);
        if (! $pengadaan) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $userId   = (int) (session()->get('userId') ?? 0) ?: null;
        $now      = date('Y-m-d H:i:s');
        $markPaid = ((string) ($this->request->getPost('mark_paid') ?? '0')) === '1';

        $update = [
            'status'           => $markPaid ? 'dibayar' : 'disetujui',
            'approved_by'      => $userId,
            'approved_at'      => $now,
            'rejection_reason' => null,
            'rejected_by'      => null,
            'rejected_at'      => null,
        ];
        if ($markPaid && empty($pengadaan['tgl_lunas_bayar'])) {
            $update['tgl_lunas_bayar'] = date('Y-m-d');
        }

        $this->pengadaanModel->update($id, $update);
        $this->dokumenTtdModel
            ->where('pengadaan_id', $id)
            ->where('status_verifikasi', 'menunggu')
            ->set([
                'status_verifikasi' => 'disetujui',
                'verified_by'       => $userId,
                'verified_at'       => $now,
            ])
            ->update();

        return redirect()->to('/admin/atk/pengadaan/' . $id)->with('success', $markPaid ? 'Pengadaan ditandai Lunas / Dibayar.' : 'Pengadaan berhasil disetujui.');
    }

    public function tolak(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['approval']) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Akses ditolak. Anda tidak memiliki izin penolakan (FiturApproval).');
        }

        $pengadaan = $this->pengadaanModel->find($id);
        if (! $pengadaan) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $alasan = trim((string) ($this->request->getPost('rejection_reason') ?? ''));
        if ($alasan === '') {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Alasan penolakan wajib diisi.');
        }

        $userId = (int) (session()->get('userId') ?? 0) ?: null;
        $now    = date('Y-m-d H:i:s');
        $docId  = (int) ($this->request->getPost('dokumen_id') ?? 0);

        $this->pengadaanModel->update($id, [
            'status'           => 'ditolak',
            'rejection_reason' => $alasan,
            'rejected_by'      => $userId,
            'rejected_at'      => $now,
        ]);

        $docQuery = $this->dokumenTtdModel->where('pengadaan_id', $id);
        if ($docId > 0) {
            $docQuery->where('id', $docId);
        }
        $docQuery->set([
            'status_verifikasi' => 'ditolak',
            'rejection_reason'  => $alasan,
            'verified_by'       => $userId,
            'verified_at'       => $now,
        ])->update();

        return redirect()->to('/admin/atk/pengadaan/' . $id)->with('success', 'Pengadaan / dokumen ditolak untuk direvisi.');
    }

    public function viewDokumenTtd(int $docId)
    {
        $doc = $this->dokumenTtdModel->find($docId);
        if (! $doc) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $absPath = $this->resolveUploadPath((string) ($doc['file_path'] ?? ''));
        if ($absPath === null || ! is_file($absPath)) {
            return redirect()->back()->with('error', 'Berkas fisik tidak ditemukan di server.');
        }

        $ext  = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'         => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            default       => 'application/octet-stream',
        };

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename((string) $doc['file_name']) . '"')
            ->setBody((string) file_get_contents($absPath));
    }

    public function deleteDokumenTtd(int $docId)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        $doc         = $this->dokumenTtdModel->find($docId);
        if (! $doc) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Dokumen tidak ditemukan.');
        }

        $pengadaanId = (int) $doc['pengadaan_id'];
        if (! $permissions['delete'] && ! $permissions['edit']) {
            return redirect()->to('/admin/atk/pengadaan/' . $pengadaanId)->with('error', 'Akses ditolak untuk menghapus dokumen.');
        }

        $absPath = $this->resolveUploadPath((string) ($doc['file_path'] ?? ''));
        if ($absPath !== null && is_file($absPath)) {
            @unlink($absPath);
        }
        $this->dokumenTtdModel->delete($docId);

        return redirect()->to('/admin/atk/pengadaan/' . $pengadaanId)->with('success', 'Dokumen TTD berhasil dihapus.');
    }

    // =========================================================================
    // 3. CETAK PDF & EXPORT EXCEL (6 DOKUMEN SPJ)
    // =========================================================================

    public function cetakPdf(int $id, string $jenis = 'all')
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['export']) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Akses ditolak. Anda tidak memiliki izin ekspor/cetak (FiturExport).');
        }

        $bundle = $this->loadBundle($id);
        if ($bundle === null) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $validJenis = ['all', 'usulan_ppk', 'spb', 'bast', 'lamp_bastp', 'faktur', 'kuitansi_ls'];
        if (! in_array($jenis, $validJenis, true)) {
            $jenis = 'all';
        }

        $html = view('admin/atk/pdf_bundle', [
            'pengadaan'  => $bundle['pengadaan'],
            'items'      => $bundle['items'],
            'penyedia'   => $bundle['penyedia'],
            'kopDataUri' => $this->resolveKopImageDataUri((int) ($bundle['pengadaan']['kop_surat_id'] ?? 0)),
            'jenis'      => $jenis,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $p        = $bundle['pengadaan'];
        $katSlug  = strtolower(str_replace(' ', '_', (string) $p['kategori']));
        $filename = sprintf('SPJ_%s_%s_%02d_%s.pdf', strtoupper($jenis), $katSlug, (int) $p['bulan'], (int) $p['tahun_anggaran']);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function exportExcel(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['export']) {
            return redirect()->to('/admin/atk/pengadaan/' . $id)->with('error', 'Akses ditolak. Anda tidak memiliki izin ekspor (FiturExport).');
        }

        $bundle = $this->loadBundle($id);
        if ($bundle === null) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Data pengadaan tidak ditemukan.');
        }

        $bundle['kop_path'] = $this->resolveKopImageFilePath((int) ($bundle['pengadaan']['kop_surat_id'] ?? 0));
        $exporter           = new AtkExcelExporter();
        $spreadsheet        = $exporter->buildSpjWorkbook([$bundle]);

        $p        = $bundle['pengadaan'];
        $katSlug  = strtolower(str_replace(' ', '_', (string) $p['kategori']));
        $filename = sprintf('SPJ_ATK_%s_%02d_%s.xlsx', $katSlug, (int) $p['bulan'], (int) $p['tahun_anggaran']);

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    public function exportExcelBatch()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/pengadaan');
        if (! $permissions['export']) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Akses ditolak. Anda tidak memiliki izin ekspor (FiturExport).');
        }

        $tahun    = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $bulan    = trim((string) ($this->request->getGet('bulan') ?? ''));
        $kategori = trim((string) ($this->request->getGet('kategori') ?? ''));

        $builder = $this->pengadaanModel->where('tahun_anggaran', $tahun);
        if ($bulan !== '' && is_numeric($bulan)) {
            $builder->where('bulan', (int) $bulan);
        }
        if ($kategori !== '') {
            $builder->where('kategori', $kategori);
        }

        $rows = $builder->orderBy('bulan', 'ASC')->orderBy('id', 'ASC')->findAll();
        if ($rows === []) {
            return redirect()->to('/admin/atk/pengadaan')->with('error', 'Tidak ada data pengadaan untuk diekspor.');
        }

        $bundles = [];
        foreach ($rows as $row) {
            $b = $this->loadBundle((int) $row['id']);
            if ($b !== null) {
                $b['kop_path'] = $this->resolveKopImageFilePath((int) ($row['kop_surat_id'] ?? 0));
                $bundles[]     = $b;
            }
        }

        $exporter    = new AtkExcelExporter();
        $spreadsheet = $exporter->buildSpjWorkbook($bundles);
        $filename    = "SPJ_ATK_SUPPLIES_{$tahun}.xlsx";

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    // =========================================================================
    // 4. KATALOG BARANG & HARGA
    // =========================================================================

    public function katalogIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/katalog');
        $kategori    = trim((string) ($this->request->getGet('kategori') ?? ''));
        $tahun       = (int) ($this->request->getGet('tahun') ?: 0);

        $builder = $this->barangModel
            ->select('mst_atk_barang.*, mst_atk_penyedia.nama_perusahaan')
            ->join('mst_atk_penyedia', 'mst_atk_penyedia.id = mst_atk_barang.penyedia_id', 'left');

        if ($kategori !== '') {
            $builder->where('mst_atk_barang.kategori', $kategori);
        }
        if ($tahun > 0) {
            $builder->where('mst_atk_barang.tahun_anggaran', $tahun);
        }

        $barangList   = $builder->orderBy('mst_atk_barang.kategori', 'ASC')->orderBy('mst_atk_barang.nama_barang', 'ASC')->findAll();
        $penyediaList = $this->penyediaModel->where('is_active', 1)->orderBy('nama_perusahaan', 'ASC')->findAll();

        return view('admin/atk/katalog', [
            'title'          => 'Katalog Barang & Harga ATK',
            'barangList'     => $barangList,
            'penyediaList'   => $penyediaList,
            'permissions'    => $permissions,
            'filterKategori' => $kategori,
            'filterTahun'    => $tahun,
        ]);
    }

    public function katalogStore()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/katalog');
        if (! $permissions['add']) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Akses ditolak. Anda tidak memiliki izin menambah barang.');
        }

        $nama = trim((string) $this->request->getPost('nama_barang'));
        if ($nama === '') {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Nama barang wajib diisi.');
        }

        $this->barangModel->insert([
            'penyedia_id'    => ! empty($this->request->getPost('penyedia_id')) ? (int) $this->request->getPost('penyedia_id') : null,
            'tahun_anggaran' => (int) ($this->request->getPost('tahun_anggaran') ?: date('Y')),
            'kategori'       => trim((string) ($this->request->getPost('kategori') ?: 'ATK')),
            'nama_barang'    => $nama,
            'satuan'         => trim((string) ($this->request->getPost('satuan') ?: 'Buah')),
            'harga_satuan'   => (float) str_replace([',', '.'], ['', ''], (string) ($this->request->getPost('harga_satuan') ?? 0)),
            'termasuk_pajak' => ((string) ($this->request->getPost('termasuk_pajak') ?? '1')) === '1' ? 1 : 0,
            'keterangan'     => trim((string) ($this->request->getPost('keterangan') ?? '')),
            'is_active'      => 1,
        ]);

        return redirect()->to('/admin/atk/katalog')->with('success', 'Barang berhasil ditambahkan ke katalog.');
    }

    public function katalogUpdate(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/katalog');
        if (! $permissions['edit']) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Akses ditolak. Anda tidak memiliki izin mengubah barang.');
        }

        if (! $this->barangModel->find($id)) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Barang tidak ditemukan.');
        }

        $this->barangModel->update($id, [
            'penyedia_id'    => ! empty($this->request->getPost('penyedia_id')) ? (int) $this->request->getPost('penyedia_id') : null,
            'tahun_anggaran' => (int) ($this->request->getPost('tahun_anggaran') ?: date('Y')),
            'kategori'       => trim((string) ($this->request->getPost('kategori') ?: 'ATK')),
            'nama_barang'    => trim((string) $this->request->getPost('nama_barang')),
            'satuan'         => trim((string) $this->request->getPost('satuan') ?: 'Buah'),
            'harga_satuan'   => (float) preg_replace('/[^0-9]/', '', (string) ($this->request->getPost('harga_satuan') ?? 0)),
            'termasuk_pajak' => ((string) ($this->request->getPost('termasuk_pajak') ?? '1')) === '1' ? 1 : 0,
            'keterangan'     => trim((string) ($this->request->getPost('keterangan') ?? '')),
            'is_active'      => ((string) ($this->request->getPost('is_active') ?? '1')) === '1' ? 1 : 0,
        ]);

        return redirect()->to('/admin/atk/katalog')->with('success', 'Data barang katalog berhasil diperbarui.');
    }

    public function katalogDelete(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/katalog');
        if (! $permissions['delete']) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Akses ditolak. Anda tidak memiliki izin menghapus barang.');
        }

        $this->barangModel->delete($id);
        return redirect()->to('/admin/atk/katalog')->with('success', 'Barang berhasil dihapus dari katalog.');
    }

    public function katalogTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $s           = $spreadsheet->getActiveSheet();
        $s->setTitle('Template Katalog ATK');
        $s->fromArray(['Kategori (ATK/Supplies Komputer)', 'Nama Barang', 'Satuan', 'Harga Satuan', 'Tahun Anggaran', 'Keterangan'], null, 'A1');
        $s->fromArray(['ATK', 'Kertas HVS A4 75 gr Merk Paper One', 'Kotak', 349000, 2026, 'Harga termasuk pajak'], null, 'A2');
        $s->fromArray(['Supplies Komputer', 'Tinta Epson 003 Hitam', 'Botol', 135000, 2026, 'Harga termasuk pajak'], null, 'A3');
        foreach (range('A', 'F') as $col) {
            $s->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->streamSpreadsheet($spreadsheet, 'Template_Katalog_ATK.xlsx');
    }

    public function katalogImport()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/katalog');
        if (! $permissions['import']) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Akses ditolak. Anda tidak memiliki izin import (FiturImport).');
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'File Excel tidak valid.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $rows        = $spreadsheet->getActiveSheet()->toArray();
            $penyediaId  = ! empty($this->request->getPost('penyedia_id')) ? (int) $this->request->getPost('penyedia_id') : null;
            $inserted    = 0;

            foreach ($rows as $idx => $row) {
                if ($idx === 0) {
                    continue;
                }
                $nama = trim((string) ($row[1] ?? ''));
                if ($nama === '') {
                    continue;
                }
                $katRaw   = trim((string) ($row[0] ?? 'ATK'));
                $kategori = stripos($katRaw, 'suppl') !== false ? 'Supplies Komputer' : 'ATK';
                $satuan   = trim((string) ($row[2] ?? 'Buah')) ?: 'Buah';
                $harga    = (float) preg_replace('/[^0-9]/', '', (string) ($row[3] ?? '0'));
                $tahun    = (int) ($row[4] ?? date('Y')) ?: (int) date('Y');
                $ket      = trim((string) ($row[5] ?? ''));

                $this->barangModel->insert([
                    'penyedia_id'    => $penyediaId,
                    'tahun_anggaran' => $tahun,
                    'kategori'       => $kategori,
                    'nama_barang'    => $nama,
                    'satuan'         => $satuan,
                    'harga_satuan'   => $harga,
                    'termasuk_pajak' => 1,
                    'keterangan'     => $ket,
                    'is_active'      => 1,
                ]);
                $inserted++;
            }

            return redirect()->to('/admin/atk/katalog')->with('success', "Berhasil mengimpor {$inserted} item ke katalog barang.");
        } catch (\Throwable $e) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    public function katalogExportExcel()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/katalog');
        if (! $permissions['export']) {
            return redirect()->to('/admin/atk/katalog')->with('error', 'Akses ditolak. Anda tidak memiliki izin ekspor.');
        }

        $rows = $this->barangModel
            ->select('mst_atk_barang.*, mst_atk_penyedia.nama_perusahaan')
            ->join('mst_atk_penyedia', 'mst_atk_penyedia.id = mst_atk_barang.penyedia_id', 'left')
            ->orderBy('kategori', 'ASC')
            ->orderBy('nama_barang', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $s           = $spreadsheet->getActiveSheet();
        $s->setTitle('Katalog ATK');
        $s->fromArray(['No', 'Tahun', 'Kategori', 'Nama Barang', 'Satuan', 'Harga Satuan', 'Penyedia', 'Keterangan'], null, 'A1');
        $r = 2;
        foreach ($rows as $i => $row) {
            $s->fromArray([
                $i + 1,
                (int) $row['tahun_anggaran'],
                $row['kategori'],
                $row['nama_barang'],
                $row['satuan'],
                (float) $row['harga_satuan'],
                $row['nama_perusahaan'] ?? '-',
                $row['keterangan'] ?? '',
            ], null, "A{$r}");
            $s->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $r++;
        }
        foreach (range('A', 'H') as $col) {
            $s->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->streamSpreadsheet($spreadsheet, 'Katalog_Barang_ATK_' . date('Ymd') . '.xlsx');
    }

    // =========================================================================
    // 5. PENYEDIA (VENDOR)
    // =========================================================================

    public function penyediaIndex()
    {
        $permissions  = $this->resolveMenuAksesPermissions('admin/atk/penyedia');
        $penyediaList = $this->penyediaModel->orderBy('id', 'ASC')->findAll();

        return view('admin/atk/penyedia', [
            'title'        => 'Data Penyedia (Vendor) ATK',
            'penyediaList' => $penyediaList,
            'permissions'  => $permissions,
        ]);
    }

    public function penyediaStore()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/penyedia');
        if (! $permissions['add']) {
            return redirect()->to('/admin/atk/penyedia')->with('error', 'Akses ditolak. Anda tidak memiliki izin menambah penyedia.');
        }

        $nama = trim((string) $this->request->getPost('nama_perusahaan'));
        if ($nama === '') {
            return redirect()->to('/admin/atk/penyedia')->with('error', 'Nama perusahaan wajib diisi.');
        }

        $this->penyediaModel->insert([
            'nama_perusahaan'  => $nama,
            'alamat'           => trim((string) $this->request->getPost('alamat')),
            'kota'             => trim((string) ($this->request->getPost('kota') ?: 'Pekanbaru')),
            'telepon'          => trim((string) $this->request->getPost('telepon')),
            'email'            => trim((string) $this->request->getPost('email')),
            'nama_direktur'    => trim((string) $this->request->getPost('nama_direktur')),
            'jabatan_pimpinan' => trim((string) ($this->request->getPost('jabatan_pimpinan') ?: 'Direktur')),
            'npwp'             => trim((string) $this->request->getPost('npwp')),
            'is_active'        => 1,
        ]);

        return redirect()->to('/admin/atk/penyedia')->with('success', 'Data penyedia berhasil ditambahkan.');
    }

    public function penyediaUpdate(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/penyedia');
        if (! $permissions['edit']) {
            return redirect()->to('/admin/atk/penyedia')->with('error', 'Akses ditolak. Anda tidak memiliki izin mengubah penyedia.');
        }

        if (! $this->penyediaModel->find($id)) {
            return redirect()->to('/admin/atk/penyedia')->with('error', 'Penyedia tidak ditemukan.');
        }

        $this->penyediaModel->update($id, [
            'nama_perusahaan'  => trim((string) $this->request->getPost('nama_perusahaan')),
            'alamat'           => trim((string) $this->request->getPost('alamat')),
            'kota'             => trim((string) ($this->request->getPost('kota') ?: 'Pekanbaru')),
            'telepon'          => trim((string) $this->request->getPost('telepon')),
            'email'            => trim((string) $this->request->getPost('email')),
            'nama_direktur'    => trim((string) $this->request->getPost('nama_direktur')),
            'jabatan_pimpinan' => trim((string) ($this->request->getPost('jabatan_pimpinan') ?: 'Direktur')),
            'npwp'             => trim((string) $this->request->getPost('npwp')),
            'is_active'        => ((string) ($this->request->getPost('is_active') ?? '1')) === '1' ? 1 : 0,
        ]);

        return redirect()->to('/admin/atk/penyedia')->with('success', 'Data penyedia berhasil diperbarui.');
    }

    public function penyediaDelete(int $id)
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/penyedia');
        if (! $permissions['delete']) {
            return redirect()->to('/admin/atk/penyedia')->with('error', 'Akses ditolak. Anda tidak memiliki izin menghapus penyedia.');
        }

        $this->penyediaModel->delete($id);
        return redirect()->to('/admin/atk/penyedia')->with('success', 'Data penyedia berhasil dihapus.');
    }

    // =========================================================================
    // 6. REKAPITULASI & LAPORAN
    // =========================================================================

    public function laporanIndex()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/laporan');
        $tahun       = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $rows        = $this->getRekapRows($tahun);

        $perBulan = [];
        for ($m = 1; $m <= 12; $m++) {
            $perBulan[$m] = ['atk' => 0.0, 'supplies' => 0.0, 'total' => 0.0];
        }

        $totalAtk      = 0.0;
        $totalSupplies = 0.0;
        foreach ($rows as $row) {
            $m   = (int) $row['bulan'];
            $val = (float) $row['total_nilai'];
            if ($row['kategori'] === 'Supplies Komputer') {
                $perBulan[$m]['supplies'] += $val;
                $totalSupplies            += $val;
            } else {
                $perBulan[$m]['atk'] += $val;
                $totalAtk            += $val;
            }
            $perBulan[$m]['total'] += $val;
        }

        return view('admin/atk/laporan', [
            'title'         => 'Rekapitulasi & Laporan ATK & Supplies',
            'tahun'         => $tahun,
            'rows'          => $rows,
            'perBulan'      => $perBulan,
            'totalAtk'      => $totalAtk,
            'totalSupplies' => $totalSupplies,
            'grandTotal'    => $totalAtk + $totalSupplies,
            'permissions'   => $permissions,
        ]);
    }

    public function laporanExportExcel()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/laporan');
        if (! $permissions['export']) {
            return redirect()->to('/admin/atk/laporan')->with('error', 'Akses ditolak. Anda tidak memiliki izin ekspor.');
        }

        $tahun       = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $rows        = $this->getRekapRows($tahun);
        $exporter    = new AtkExcelExporter();
        $spreadsheet = $exporter->buildRekapWorkbook($rows, $tahun);

        return $this->streamSpreadsheet($spreadsheet, "Rekap_ATK_Supplies_{$tahun}.xlsx");
    }

    public function laporanExportPdf()
    {
        $permissions = $this->resolveMenuAksesPermissions('admin/atk/laporan');
        if (! $permissions['export']) {
            return redirect()->to('/admin/atk/laporan')->with('error', 'Akses ditolak. Anda tidak memiliki izin ekspor.');
        }

        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $rows  = $this->getRekapRows($tahun);
        $html  = view('admin/atk/laporan_pdf', [
            'tahun'      => $tahun,
            'rows'       => $rows,
            'kopDataUri' => $this->resolveKopImageDataUri(null),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="Rekap_ATK_Supplies_' . $tahun . '.pdf"')
            ->setBody($dompdf->output());
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function getRekapRows(int $tahun): array
    {
        return $this->pengadaanModel
            ->select('trn_atk_pengadaan.*, mst_atk_penyedia.nama_perusahaan')
            ->join('mst_atk_penyedia', 'mst_atk_penyedia.id = trn_atk_pengadaan.penyedia_id', 'left')
            ->where('trn_atk_pengadaan.tahun_anggaran', $tahun)
            ->orderBy('trn_atk_pengadaan.bulan', 'ASC')
            ->orderBy('trn_atk_pengadaan.kategori', 'ASC')
            ->orderBy('trn_atk_pengadaan.id', 'ASC')
            ->findAll();
    }

    private function loadFormMasterReferences(): array
    {
        $penyediaList = $this->penyediaModel->where('is_active', 1)->orderBy('nama_perusahaan', 'ASC')->findAll();
        $barangList   = $this->barangModel->where('is_active', 1)->orderBy('kategori', 'ASC')->orderBy('nama_barang', 'ASC')->findAll();

        $pegawaiList = [];
        try {
            $pegawaiList = (new MstPegawaiModel())->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();
        } catch (\Throwable $e) {
        }

        $mataAnggaranList = [];
        try {
            $mataAnggaranList = (new MstMataAnggaranModel())->orderBy('id', 'DESC')->findAll();
        } catch (\Throwable $e) {
        }

        $kopSuratList = [];
        $db           = db_connect();
        try {
            if ($db->tableExists('kop_surat')) {
                $kopSuratList = $db->table('kop_surat')
                    ->select('id, title, image_url, is_active')
                    ->orderBy('is_active', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getResultArray();
            }
        } catch (\Throwable $e) {
        }

        return [
            'penyediaList'     => $penyediaList,
            'barangList'       => $barangList,
            'pegawaiList'      => $pegawaiList,
            'mataAnggaranList' => $mataAnggaranList,
            'kopSuratList'     => $kopSuratList,
        ];
    }

    private function extractPengadaanHeaderFromPost(): array
    {
        $ppkId       = ! empty($this->request->getPost('ppk_pegawai_id')) ? (int) $this->request->getPost('ppk_pegawai_id') : null;
        $bendaharaId = ! empty($this->request->getPost('bendahara_pegawai_id')) ? (int) $this->request->getPost('bendahara_pegawai_id') : null;
        $maId        = ! empty($this->request->getPost('mata_anggaran_id')) ? (int) $this->request->getPost('mata_anggaran_id') : null;

        $ppkNama = trim((string) ($this->request->getPost('ppk_nama') ?? ''));
        $ppkNip  = trim((string) ($this->request->getPost('ppk_nip') ?? ''));
        $benNama = trim((string) ($this->request->getPost('bendahara_nama') ?? ''));
        $benNip  = trim((string) ($this->request->getPost('bendahara_nip') ?? ''));
        $kodeMa  = trim((string) ($this->request->getPost('kode_mata_anggaran') ?? '7987.FAD.002.101.A.521211'));

        try {
            $pegModel = new MstPegawaiModel();
            if ($ppkId && ($ppkRow = $pegModel->find($ppkId))) {
                $ppkNama = $ppkRow['nama'];
                $ppkNip  = $ppkRow['nip'] ?? $ppkNip;
            }
            if ($bendaharaId && ($benRow = $pegModel->find($bendaharaId))) {
                $benNama = $benRow['nama'];
                $benNip  = $benRow['nip'] ?? $benNip;
            }
            if ($maId && ($maRow = (new MstMataAnggaranModel())->find($maId))) {
                $kodeMa = $maRow['mata_anggaran'] ?? $kodeMa;
            }
        } catch (\Throwable $e) {
        }

        $nullableDate = static fn (?string $v): ?string => ($v !== null && trim($v) !== '') ? trim($v) : null;

        return [
            'tahun_anggaran'       => (int) ($this->request->getPost('tahun_anggaran') ?: date('Y')),
            'bulan'                => (int) ($this->request->getPost('bulan') ?: date('n')),
            'kategori'             => trim((string) ($this->request->getPost('kategori') ?: 'ATK')),
            'kegiatan_label'       => trim((string) ($this->request->getPost('kegiatan_label') ?: 'Dukungan Kegiatan Prasarana Strategis')),
            'mata_anggaran_id'     => $maId,
            'kode_mata_anggaran'   => $kodeMa,
            'penyedia_id'          => ! empty($this->request->getPost('penyedia_id')) ? (int) $this->request->getPost('penyedia_id') : null,
            'kop_surat_id'         => ! empty($this->request->getPost('kop_surat_id')) ? (int) $this->request->getPost('kop_surat_id') : null,
            'no_usulan'            => trim((string) ($this->request->getPost('no_usulan') ?? '')),
            'tgl_usulan'           => $nullableDate($this->request->getPost('tgl_usulan')),
            'no_pesanan'           => trim((string) ($this->request->getPost('no_pesanan') ?? '')),
            'tgl_pesanan'          => $nullableDate($this->request->getPost('tgl_pesanan')),
            'tgl_batas_terima'     => $nullableDate($this->request->getPost('tgl_batas_terima')),
            'persen_denda_harian'  => (float) ($this->request->getPost('persen_denda_harian') ?: 2),
            'no_bastp'             => trim((string) ($this->request->getPost('no_bastp') ?? '')),
            'tgl_bastp'            => $nullableDate($this->request->getPost('tgl_bastp')),
            'no_faktur'            => trim((string) ($this->request->getPost('no_faktur') ?? '')),
            'tgl_faktur'           => $nullableDate($this->request->getPost('tgl_faktur')),
            'no_bukti_kuitansi'    => trim((string) ($this->request->getPost('no_bukti_kuitansi') ?? '')),
            'tgl_kuitansi'         => $nullableDate($this->request->getPost('tgl_kuitansi')),
            'tgl_lunas_bayar'      => $nullableDate($this->request->getPost('tgl_lunas_bayar')),
            'ppk_pegawai_id'       => $ppkId,
            'ppk_nama'             => $ppkNama ?: 'NURHIDAYAT NUGROHO, S.Ars.',
            'ppk_nip'              => $ppkNip ?: '199012212018021001',
            'bendahara_pegawai_id' => $bendaharaId,
            'bendahara_nama'       => $benNama ?: 'KH. SRI HANDAYANI, S.Si., M.T.',
            'bendahara_nip'        => $benNip ?: '198204022014122002',
            'catatan'              => trim((string) ($this->request->getPost('catatan') ?? '')),
        ];
    }

    private function saveItemsAndRecalculateTotal(int $pengadaanId): void
    {
        helper('custom');

        $barangIds = (array) ($this->request->getPost('item_barang_id') ?? []);
        $names     = (array) ($this->request->getPost('item_nama_barang') ?? []);
        $satuans   = (array) ($this->request->getPost('item_satuan') ?? []);
        $volumes   = (array) ($this->request->getPost('item_volume') ?? []);
        $hargas    = (array) ($this->request->getPost('item_harga_satuan') ?? []);
        $kondisis  = (array) ($this->request->getPost('item_kondisi') ?? []);
        $kets      = (array) ($this->request->getPost('item_keterangan') ?? []);

        $this->itemModel->where('pengadaan_id', $pengadaanId)->delete();

        $total  = 0.0;
        $urutan = 1;
        foreach ($names as $idx => $namaRaw) {
            $nama = trim((string) $namaRaw);
            if ($nama === '') {
                continue;
            }
            $vol      = max(0, (float) ($volumes[$idx] ?? 1));
            $harga    = max(0, (float) preg_replace('/[^0-9]/', '', (string) ($hargas[$idx] ?? '0')));
            $subtotal = $vol * $harga;
            $total   += $subtotal;

            $this->itemModel->insert([
                'pengadaan_id'   => $pengadaanId,
                'barang_id'      => ! empty($barangIds[$idx]) ? (int) $barangIds[$idx] : null,
                'nama_barang'    => $nama,
                'satuan'         => trim((string) ($satuans[$idx] ?? 'Buah')) ?: 'Buah',
                'volume'         => $vol,
                'harga_satuan'   => $harga,
                'subtotal'       => $subtotal,
                'kondisi_barang' => trim((string) ($kondisis[$idx] ?? 'Baik')) ?: 'Baik',
                'keterangan'     => trim((string) ($kets[$idx] ?? '')),
                'urutan'         => $urutan++,
            ]);
        }

        $terbilangText = $total > 0 ? ucwords(terbilang_angka((int) round($total))) . ' Rupiah,-' : 'Nol Rupiah,-';
        $this->pengadaanModel->update($pengadaanId, [
            'total_nilai' => $total,
            'terbilang'   => $terbilangText,
        ]);
    }

    private function loadBundle(int $id): ?array
    {
        $pengadaan = $this->pengadaanModel->find($id);
        if (! $pengadaan) {
            return null;
        }

        $items = $this->itemModel
            ->where('pengadaan_id', $id)
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $penyedia = [];
        if (! empty($pengadaan['penyedia_id'])) {
            $penyedia = $this->penyediaModel->find((int) $pengadaan['penyedia_id']) ?? [];
        }

        return [
            'pengadaan' => $pengadaan,
            'items'     => $items,
            'penyedia'  => $penyedia,
        ];
    }

    private function resolveKopImageFilePath(?int $kopSuratId): string
    {
        helper('custom');
        $url = kop_surat_url($kopSuratId ?: null);
        if ($url === '') {
            return '';
        }

        $pathPart = parse_url($url, PHP_URL_PATH) ?: '';
        $rel      = ltrim($pathPart, '/');
        $candidates = [
            FCPATH . $rel,
            ROOTPATH . $rel,
            ROOTPATH . 'public/' . $rel,
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        return '';
    }

    private function resolveKopImageDataUri(?int $kopSuratId): string
    {
        $file = $this->resolveKopImageFilePath($kopSuratId);
        if ($file === '' || ! is_file($file)) {
            return '';
        }
        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        $data = @file_get_contents($file);
        return $data ? ('data:' . $mime . ';base64,' . base64_encode($data)) : '';
    }

    private function resolveUploadPath(string $storedPath): ?string
    {
        $clean = ltrim($storedPath, '/');
        if ($clean === '') {
            return null;
        }
        $candidates = [
            ROOTPATH . $clean,
            FCPATH . preg_replace('/^public\//', '', $clean),
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        return null;
    }

    private function streamSpreadsheet(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }

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
            if (! $db->tableExists($table)) {
                continue;
            }
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
        $row        = $db->table('menu_akses')
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

