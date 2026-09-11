<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventarisAuditModel;
use App\Models\InventarisAuditItemModel;
use App\Models\MstRuanganModel;
use App\Models\MstSekolahModel;
use App\Models\MstPaketModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisAudit extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/audit';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $auditModel = new InventarisAuditModel();
        $ruanganModel = new MstRuanganModel();
        $paketModel = new MstPaketModel();
        $sekolahModel = new MstSekolahModel();

        $filterStatus  = trim((string) ($this->request->getGet('status') ?? ''));
        $filterLingkup = trim((string) ($this->request->getGet('lingkup') ?? ''));
        $searchKeyword = trim((string) ($this->request->getGet('q') ?? ''));

        $builder = $auditModel->orderBy('id', 'DESC');

        if ($filterStatus !== '' && $filterStatus !== 'semua') {
            $builder->where('status', $filterStatus);
        }
        if ($filterLingkup !== '' && $filterLingkup !== 'semua') {
            $builder->where('lingkup_audit', $filterLingkup);
        }
        if ($searchKeyword !== '') {
            $builder->groupStart()
                ->like('judul_audit', $searchKeyword)
                ->orLike('kode_audit', $searchKeyword)
                ->orLike('auditor_nama', $searchKeyword)
                ->orLike('ruangan_nama', $searchKeyword)
                ->orLike('sekolah_nama', $searchKeyword)
                ->groupEnd();
        }

        $audits = $builder->findAll();
        $summaryKPI = $auditModel->getSummaryKPI();

        // Master data untuk modal Buat Sesi Audit
        $ruanganList = $ruanganModel->orderBy('nama_ruangan', 'ASC')->findAll();
        $paketList   = $paketModel->where('is_active', 1)->orderBy('nama_paket', 'ASC')->findAll();
        $sekolahList = $sekolahModel->orderBy('nama', 'ASC')->findAll();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/audit/index', [
            'pageTitle'       => 'Audit & Stock Opname Aset',
            'audits'          => $audits,
            'summaryKPI'      => $summaryKPI,
            'ruanganList'     => $ruanganList,
            'paketList'       => $paketList,
            'sekolahList'     => $sekolahList,
            'filterStatus'    => $filterStatus,
            'filterLingkup'   => $filterLingkup,
            'searchKeyword'   => $searchKeyword,
            'menuPermissions' => $menuPermissions,
        ]);
    }

    public function create()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Anda tidak memiliki hak akses untuk membuat sesi audit.');
        }

        $rules = [
            'judul_audit'   => 'required|min_length[3]|max_length[255]',
            'lingkup_audit' => 'required|in_list[kantor_ruangan,kantor_seluruh,sekolah]',
            'tanggal_audit' => 'required|valid_date[Y-m-d]',
            'auditor_nama'  => 'required|min_length[2]|max_length[150]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/audit')
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $lingkupAudit = trim((string) $this->request->getPost('lingkup_audit'));
        $ruanganId    = $this->request->getPost('ruangan_id');
        $ruanganId    = (! empty($ruanganId) && is_numeric($ruanganId)) ? (int) $ruanganId : null;
        $ruanganNama  = null;

        if ($lingkupAudit === 'kantor_ruangan') {
            if ($ruanganId === null || $ruanganId <= 0) {
                return redirect()->to('/admin/inventaris/audit')
                    ->withInput()
                    ->with('error', 'Silakan pilih Ruangan Kantor yang akan diaudit.');
            }
            $r = (new MstRuanganModel())->find($ruanganId);
            $ruanganNama = $r ? $r['nama_ruangan'] : null;
        }

        $paketId     = $this->request->getPost('paket_id');
        $paketId     = (! empty($paketId) && is_numeric($paketId)) ? (int) $paketId : null;
        $sekolahNpsn = trim((string) ($this->request->getPost('sekolah_npsn') ?? '')) ?: null;
        if (empty($sekolahNpsn)) {
            $sekolahNpsn = trim((string) ($this->request->getPost('sekolah_id') ?? '')) ?: null;
        }
        $sekolahNama = null;

        if ($lingkupAudit === 'sekolah' && ! empty($sekolahNpsn)) {
            $s = (new MstSekolahModel())->where('npsn', $sekolahNpsn)->first();
            $sekolahNama = $s ? $s['nama'] : null;
        }

        $db = db_connect();
        $auditModel = new InventarisAuditModel();
        $auditItemModel = new InventarisAuditItemModel();

        $kodeAudit = $auditModel->generateKodeAudit();
        $userId    = (int) (session()->get('userId') ?? 0);

        // 1. Simpan Parent Sesi Audit
        $auditId = $auditModel->insert([
            'kode_audit'    => $kodeAudit,
            'judul_audit'   => trim((string) $this->request->getPost('judul_audit')),
            'lingkup_audit' => $lingkupAudit,
            'ruangan_id'    => $ruanganId,
            'ruangan_nama'  => $ruanganNama,
            'paket_id'      => $paketId,
            'sekolah_npsn'  => $sekolahNpsn,
            'sekolah_nama'  => $sekolahNama,
            'tanggal_audit' => trim((string) $this->request->getPost('tanggal_audit')),
            'auditor_nama'  => trim((string) $this->request->getPost('auditor_nama')),
            'auditor_nip'   => trim((string) ($this->request->getPost('auditor_nip') ?? '')) ?: null,
            'status'        => 'berjalan',
            'catatan'       => trim((string) ($this->request->getPost('catatan') ?? '')) ?: null,
            'created_by'    => $userId ?: null,
            'updated_by'    => $userId ?: null,
        ]);

        if (! $auditId) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Gagal membuat sesi audit.');
        }

        // 2. Ambil peta aset yang sedang dipinjam pakai secara aktif
        $activeLoans = [];
        if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
            $loanRows = $db->table('trn_inventaris_pinjam_pakai')
                ->select('inventaris_id, nama_peminjam, no_surat')
                ->where('status', 'dipinjam')
                ->get()
                ->getResultArray();
            foreach ($loanRows as $lr) {
                if (! empty($lr['inventaris_id'])) {
                    $activeLoans[(int) $lr['inventaris_id']] = $lr;
                }
            }
        }

        // 3. Tarik aset target sesuai lingkup yang dipilih
        $builder = $db->table('trn_inventaris_satker');

        if ($lingkupAudit === 'kantor_ruangan') {
            $builder->where('ruangan_id', $ruanganId);
        } elseif ($lingkupAudit === 'kantor_seluruh') {
            $builder->groupStart()
                ->where('peruntukan', 'kantor')
                ->orWhere('peruntukan IS NULL', null, false)
                ->orWhere('peruntukan', '')
                ->groupEnd();
        } elseif ($lingkupAudit === 'sekolah') {
            $builder->where('peruntukan', 'mobiler');
        }

        $assets = $builder->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();

        // 4. Siapkan item audit dengan snapshot kondisi & status pinjam saat ini
        $now = date('Y-m-d H:i:s');
        $batchItems = [];

        foreach ($assets as $asset) {
            $invId = (int) ($asset['id'] ?? 0);
            $isLoaned = isset($activeLoans[$invId]);
            $loanData = $isLoaned ? $activeLoans[$invId] : null;

            $batchItems[] = [
                'audit_id'             => $auditId,
                'inventaris_id'        => $invId ?: null,
                'kode_barang'          => $asset['kode_barang'] ?? '',
                'nup'                  => $asset['nup'] ?? null,
                'nama_barang'          => $asset['nama_barang'] ?? '',
                'merk_tipe'            => $asset['merk_tipe'] ?? null,
                'satuan'               => $asset['satuan'] ?: 'Unit',
                'peruntukan'           => $asset['peruntukan'] ?: 'kantor',
                'ruangan_sistem_id'    => ! empty($asset['ruangan_id']) ? (int) $asset['ruangan_id'] : null,
                'ruangan_sistem_nama'  => $asset['lokasi_ruangan'] ?? null,
                'kondisi_sistem'       => $asset['kondisi'] ?: 'Baik',
                'status_pinjam_sistem' => $isLoaned ? 'dipinjam' : 'tidak',
                'peminjam_nama'        => $loanData ? ($loanData['nama_peminjam'] ?? null) : null,
                'no_surat_pinjam'      => $loanData ? ($loanData['no_surat'] ?? null) : null,
                'status_audit'         => 'belum_diperiksa',
                'kondisi_fisik'        => null,
                'ruangan_fisik_id'     => null,
                'ruangan_fisik_nama'   => null,
                'catatan_pemeriksaan'  => null,
                'audited_at'           => null,
                'audited_by'           => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];

            if (count($batchItems) >= 500) {
                $db->table('trn_inventaris_audit_item')->insertBatch($batchItems);
                $batchItems = [];
            }
        }

        if (! empty($batchItems)) {
            $db->table('trn_inventaris_audit_item')->insertBatch($batchItems);
        }

        // 5. Rekalkulasi metrik awal
        $auditModel->recalculateStats($auditId);

        return redirect()->to('/admin/inventaris/audit/' . $auditId)
            ->with('message', "Sesi audit \"{$kodeAudit}\" berhasil dibuat dengan " . count($assets) . " item target.");
    }

    /**
     * Shortcut 1-klik langsung dari halaman Detail DBR
     */
    public function mulaiRuangan(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/dbr/' . $ruanganId)->with('error', 'Anda tidak memiliki hak akses untuk membuat sesi audit.');
        }

        $room = (new MstRuanganModel())->find($ruanganId);
        if (! $room) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Data ruangan tidak ditemukan.');
        }

        $auditModel = new InventarisAuditModel();
        $kodeAudit  = $auditModel->generateKodeAudit();
        $userNama   = session()->get('name') ?: (session()->get('username') ?: 'Petugas Auditor');
        $userId     = (int) (session()->get('userId') ?? 0);
        $today      = date('Y-m-d');
        $judul      = 'Stock Opname ' . $room['nama_ruangan'] . ' (' . date('d/m/Y') . ')';

        $db = db_connect();

        $auditId = $auditModel->insert([
            'kode_audit'    => $kodeAudit,
            'judul_audit'   => $judul,
            'lingkup_audit' => 'kantor_ruangan',
            'ruangan_id'    => $ruanganId,
            'ruangan_nama'  => $room['nama_ruangan'],
            'tanggal_audit' => $today,
            'auditor_nama'  => $userNama,
            'auditor_nip'   => null,
            'status'        => 'berjalan',
            'catatan'       => 'Inisiasi audit cepat dari halaman DBR.',
            'created_by'    => $userId ?: null,
            'updated_by'    => $userId ?: null,
        ]);

        // Ambil peta aset dipinjam
        $activeLoans = [];
        if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
            $loanRows = $db->table('trn_inventaris_pinjam_pakai')
                ->select('inventaris_id, nama_peminjam, no_surat')
                ->where('status', 'dipinjam')
                ->get()
                ->getResultArray();
            foreach ($loanRows as $lr) {
                if (! empty($lr['inventaris_id'])) {
                    $activeLoans[(int) $lr['inventaris_id']] = $lr;
                }
            }
        }

        $assets = $db->table('trn_inventaris_satker')
            ->where('ruangan_id', $ruanganId)
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();

        $now = date('Y-m-d H:i:s');
        $batchItems = [];
        foreach ($assets as $asset) {
            $invId = (int) ($asset['id'] ?? 0);
            $isLoaned = isset($activeLoans[$invId]);
            $loanData = $isLoaned ? $activeLoans[$invId] : null;

            $batchItems[] = [
                'audit_id'             => $auditId,
                'inventaris_id'        => $invId ?: null,
                'kode_barang'          => $asset['kode_barang'] ?? '',
                'nup'                  => $asset['nup'] ?? null,
                'nama_barang'          => $asset['nama_barang'] ?? '',
                'merk_tipe'            => $asset['merk_tipe'] ?? null,
                'satuan'               => $asset['satuan'] ?: 'Unit',
                'peruntukan'           => $asset['peruntukan'] ?: 'kantor',
                'ruangan_sistem_id'    => $ruanganId,
                'ruangan_sistem_nama'  => $room['nama_ruangan'],
                'kondisi_sistem'       => $asset['kondisi'] ?: 'Baik',
                'status_pinjam_sistem' => $isLoaned ? 'dipinjam' : 'tidak',
                'peminjam_nama'        => $loanData ? ($loanData['nama_peminjam'] ?? null) : null,
                'no_surat_pinjam'      => $loanData ? ($loanData['no_surat'] ?? null) : null,
                'status_audit'         => 'belum_diperiksa',
                'kondisi_fisik'        => null,
                'ruangan_fisik_id'     => null,
                'ruangan_fisik_nama'   => null,
                'catatan_pemeriksaan'  => null,
                'audited_at'           => null,
                'audited_by'           => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        if (! empty($batchItems)) {
            $db->table('trn_inventaris_audit_item')->insertBatch($batchItems);
        }

        $auditModel->recalculateStats($auditId);

        return redirect()->to('/admin/inventaris/audit/' . $auditId)
            ->with('message', "Sesi audit \"{$room['nama_ruangan']}\" berhasil dimulai dengan " . count($assets) . " item.");
    }

    public function detail(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($id);
        if (! $audit) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Sesi audit tidak ditemukan.');
        }

        $itemModel = new InventarisAuditItemModel();
        $ruanganModel = new MstRuanganModel();

        $activeRuangan      = trim((string) ($this->request->getGet('ruangan') ?? 'all'));
        $filterStatusAudit  = trim((string) ($this->request->getGet('status_audit') ?? 'semua'));
        $filterStatusPinjam = trim((string) ($this->request->getGet('status_pinjam') ?? 'semua'));
        $keyword            = trim((string) ($this->request->getGet('q') ?? ''));

        $roomStats = $itemModel->getRoomStatsByAudit($id);

        $selectedRoomInfo = null;
        if ($activeRuangan !== 'all') {
            foreach ($roomStats as $rs) {
                if ($rs['ruangan_key'] === $activeRuangan) {
                    $selectedRoomInfo = $rs;
                    break;
                }
            }
        }

        $items = $itemModel->getItemsByAudit($id, [
            'status_audit'  => $filterStatusAudit,
            'status_pinjam' => $filterStatusPinjam,
            'ruangan_id'    => $activeRuangan,
            'keyword'       => $keyword,
        ]);

        $ruanganList = $ruanganModel->orderBy('nama_ruangan', 'ASC')->findAll();
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/audit/detail', [
            'pageTitle'          => 'Workspace Audit: ' . $audit['kode_audit'],
            'audit'              => $audit,
            'items'              => $items,
            'roomStats'          => $roomStats,
            'activeRuangan'      => $activeRuangan,
            'selectedRoomInfo'   => $selectedRoomInfo,
            'ruanganList'        => $ruanganList,
            'filterStatusAudit'  => $filterStatusAudit,
            'filterStatusPinjam' => $filterStatusPinjam,
            'keyword'            => $keyword,
            'menuPermissions'    => $menuPermissions,
        ]);
    }

    /**
     * Endpoint AJAX untuk update item (baik checklist manual maupun scan QR)
     */
    public function updateItemAjax(int $auditId)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya menerima request AJAX.']);
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesi audit tidak ditemukan.']);
        }

        if ($audit['status'] === 'selesai') {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesi audit ini telah ditutup/selesai dan terkunci.']);
        }

        $itemModel = new InventarisAuditItemModel();
        $itemId    = (int) $this->request->getPost('item_id');
        $scanKey   = trim((string) ($this->request->getPost('scan_keyword') ?? ''));

        $item = null;
        if ($itemId > 0) {
            $item = $itemModel->where('audit_id', $auditId)->find($itemId);
        } elseif ($scanKey !== '') {
            $item = $itemModel->lookupScan($auditId, $scanKey);
        }

        if (! $item) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Item barang tidak ditemukan pada daftar target audit ini.'
            ]);
        }

        $statusAudit = trim((string) ($this->request->getPost('status_audit') ?? ''));
        if ($statusAudit === '' && $scanKey !== '') {
            // Jika scan langsung tanpa parameter status, defaultkan ke 'sesuai' (atau terkonfirmasi dipinjam jika dipinjam)
            if ($item['status_pinjam_sistem'] === 'dipinjam') {
                $statusAudit = 'terkonfirmasi_dipinjam';
            } else {
                $statusAudit = 'sesuai';
            }
        }

        $kondisiFisik = trim((string) ($this->request->getPost('kondisi_fisik') ?? ''));
        if ($kondisiFisik === '') {
            $kondisiFisik = $item['kondisi_sistem'];
        }

        $ruanganFisikId = $this->request->getPost('ruangan_fisik_id');
        $ruanganFisikId = (! empty($ruanganFisikId) && is_numeric($ruanganFisikId)) ? (int) $ruanganFisikId : null;
        $ruanganFisikNama = null;

        if ($ruanganFisikId !== null && $ruanganFisikId > 0) {
            $rf = (new MstRuanganModel())->find($ruanganFisikId);
            $ruanganFisikNama = $rf ? $rf['nama_ruangan'] : null;
        }

        $catatan = trim((string) ($this->request->getPost('catatan_pemeriksaan') ?? ''));
        $userId  = (int) (session()->get('userId') ?? 0);

        $updateData = [
            'status_audit'        => $statusAudit ?: 'sesuai',
            'kondisi_fisik'       => $kondisiFisik,
            'ruangan_fisik_id'    => $ruanganFisikId,
            'ruangan_fisik_nama'  => $ruanganFisikNama,
            'catatan_pemeriksaan' => $catatan ?: null,
            'audited_at'          => date('Y-m-d H:i:s'),
            'audited_by'          => $userId ?: null,
        ];

        $itemModel->update($item['id'], $updateData);
        $auditModel->recalculateStats($auditId);

        $updatedAudit = $auditModel->find($auditId);
        $updatedItem  = $itemModel->find($item['id']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status barang "' . esc($item['nama_barang']) . ' (NUP: ' . esc($item['nup']) . ')" berhasil diperbarui menjadi: ' . strtoupper(str_replace('_', ' ', $statusAudit)),
            'item'    => $updatedItem,
            'stats'   => [
                'total_item'     => (int) $updatedAudit['total_item'],
                'total_sesuai'   => (int) $updatedAudit['total_sesuai'],
                'total_berubah'  => (int) $updatedAudit['total_berubah'],
                'total_selisih'  => (int) $updatedAudit['total_selisih'],
                'total_dipinjam' => (int) $updatedAudit['total_dipinjam'],
                'total_belum'    => (int) $updatedAudit['total_belum'],
            ],
        ]);
    }

    /**
     * Tandai semua sisa item yang belum diperiksa menjadi Sesuai
     */
    public function markRemainingSesuai(int $auditId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit || $audit['status'] === 'selesai') {
            return redirect()->to('/admin/inventaris/audit/' . $auditId)->with('error', 'Sesi audit tidak dapat diubah.');
        }

        $db = db_connect();
        $userId = (int) (session()->get('userId') ?? 0);
        $now = date('Y-m-d H:i:s');

        // Untuk item yang sedang dipinjam sistem, tandai terkonfirmasi_dipinjam
        $db->table('trn_inventaris_audit_item')
            ->where('audit_id', $auditId)
            ->where('status_audit', 'belum_diperiksa')
            ->where('status_pinjam_sistem', 'dipinjam')
            ->update([
                'status_audit'  => 'terkonfirmasi_dipinjam',
                'kondisi_fisik' => new \CodeIgniter\Database\RawSql('kondisi_sistem'),
                'audited_at'    => $now,
                'audited_by'    => $userId ?: null,
            ]);

        // Untuk item lainnya, tandai sesuai
        $db->table('trn_inventaris_audit_item')
            ->where('audit_id', $auditId)
            ->where('status_audit', 'belum_diperiksa')
            ->update([
                'status_audit'  => 'sesuai',
                'kondisi_fisik' => new \CodeIgniter\Database\RawSql('kondisi_sistem'),
                'audited_at'    => $now,
                'audited_by'    => $userId ?: null,
            ]);

        $auditModel->recalculateStats($auditId);

        return redirect()->to('/admin/inventaris/audit/' . $auditId)
            ->with('message', 'Seluruh sisa item berhasil diverifikasi dan ditandai Sesuai.');
    }

    public function markRuanganRemainingSesuai(int $auditId, string $ruanganKey)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/audit/' . $auditId)->with('error', 'Anda tidak memiliki hak akses untuk memverifikasi item.');
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit || $audit['status'] === 'selesai') {
            return redirect()->to('/admin/inventaris/audit/' . $auditId)->with('error', 'Sesi audit tidak dapat diubah atau telah terkunci.');
        }

        $itemModel = new InventarisAuditItemModel();
        $userId = (int) (session()->get('userId') ?? 0);
        $userName = (string) (session()->get('fullName') ?: session()->get('username'));

        $count = $itemModel->markRuanganRemainingSesuai($auditId, $ruanganKey, $userId, $userName);
        $auditModel->recalculateStats($auditId);

        $ruanganParam = $ruanganKey !== 'all' ? '?ruangan=' . urlencode($ruanganKey) : '';

        return redirect()->to('/admin/inventaris/audit/' . $auditId . $ruanganParam)
            ->with('message', "Sebanyak {$count} item sisa pada ruangan terpilih berhasil diverifikasi menjadi Sesuai.");
    }

    public function selesai(int $auditId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Sesi audit tidak ditemukan.');
        }

        $auditModel->recalculateStats($auditId);
        $auditModel->update($auditId, [
            'status'     => 'selesai',
            'updated_by' => (int) (session()->get('userId') ?? 0) ?: null,
        ]);

        return redirect()->to('/admin/inventaris/audit/' . $auditId)
            ->with('message', 'Sesi audit telah selesai dan dikunci.');
    }

    public function bukaKembali(int $auditId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $auditModel = new InventarisAuditModel();
        $auditModel->update($auditId, [
            'status'     => 'berjalan',
            'updated_by' => (int) (session()->get('userId') ?? 0) ?: null,
        ]);

        return redirect()->to('/admin/inventaris/audit/' . $auditId)
            ->with('message', 'Sesi audit telah dibuka kembali untuk pemeriksaan lanjutan.');
    }

    public function delete(int $auditId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['delete'] ?? false)) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Anda tidak memiliki hak akses untuk menghapus sesi audit.');
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Sesi audit tidak ditemukan.');
        }

        $db = db_connect();
        $db->table('trn_inventaris_audit_item')->where('audit_id', $auditId)->delete();
        $db->table('trn_inventaris_audit')->where('id', $auditId)->delete();

        return redirect()->to('/admin/inventaris/audit')->with('message', 'Sesi audit ' . esc($audit['kode_audit']) . ' berhasil dihapus.');
    }

    public function cetakPdf(int $auditId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/inventaris/audit/' . $auditId)->with('error', 'Anda tidak memiliki hak akses untuk mencetak laporan.');
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Sesi audit tidak ditemukan.');
        }

        $itemModel = new InventarisAuditItemModel();
        $roomStats = $itemModel->getRoomStatsByAudit($auditId);

        $items = $itemModel->where('audit_id', $auditId)
            ->orderBy('CASE WHEN ruangan_sistem_nama IS NULL OR ruangan_sistem_nama = "" THEN 1 ELSE 0 END', 'ASC', false)
            ->orderBy('ruangan_sistem_nama', 'ASC')
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->findAll();

        $groupedItems = [];
        foreach ($items as $item) {
            $rKey = ! empty($item['ruangan_sistem_id']) ? (string) $item['ruangan_sistem_id'] : 'non_ruangan';
            $groupedItems[$rKey][] = $item;
        }

        $logoPuPath = FCPATH . 'assets/img/logo-pu.png';
        if (! file_exists($logoPuPath)) {
            $logoPuPath = FCPATH . 'assets/img/logo.png';
        }
        $logoPuBase64 = '';
        if (file_exists($logoPuPath)) {
            $logoPuBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPuPath));
        }

        $data = [
            'audit'        => $audit,
            'items'        => $items,
            'groupedItems' => $groupedItems,
            'roomStats'    => $roomStats,
            'logoPuBase64' => $logoPuBase64,
            'tanggalCetak' => date('d F Y'),
        ];

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $html = view('admin/inventaris/audit/pdf_berita_acara', $data);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Berita_Acara_Audit_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $audit['kode_audit']) . '.pdf';

        if (ob_get_length()) {
            ob_clean();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function exportExcel(int $auditId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! ($menuPermissions['export'] ?? false)) {
            return redirect()->to('/admin/inventaris/audit/' . $auditId)->with('error', 'Anda tidak memiliki hak akses untuk mengunduh Excel.');
        }

        $auditModel = new InventarisAuditModel();
        $audit = $auditModel->find($auditId);
        if (! $audit) {
            return redirect()->to('/admin/inventaris/audit')->with('error', 'Sesi audit tidak ditemukan.');
        }

        $itemModel = new InventarisAuditItemModel();
        $items = $itemModel->where('audit_id', $auditId)
            ->orderBy('CASE WHEN ruangan_sistem_nama IS NULL OR ruangan_sistem_nama = "" THEN 1 ELSE 0 END', 'ASC', false)
            ->orderBy('ruangan_sistem_nama', 'ASC')
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Audit Stock Opname');

        // Header Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'BERITA ACARA PEMERIKSAAN FISIK (STOCK OPNAME) ASET BMN');
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'SATUAN KERJA PELAKSANAAN PRASARANA PERMUKIMAN STRATEGIS PROVINSI RIAU');
        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', 'Kode: ' . $audit['kode_audit'] . ' | Judul: ' . $audit['judul_audit'] . ' | Tanggal: ' . date('d/m/Y', strtotime($audit['tanggal_audit'])));

        $sheet->getStyle('A1:J2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Ringkasan KPI
        $sheet->mergeCells('A4:J4');
        $sheet->setCellValue('A4', 'Target: ' . $audit['total_item'] . ' Aset | Sesuai: ' . $audit['total_sesuai'] . ' | Berubah/Pindah: ' . $audit['total_berubah'] . ' | Dipinjam: ' . $audit['total_dipinjam'] . ' | Tidak Ditemukan: ' . $audit['total_selisih'] . ' | Belum Dicek: ' . $audit['total_belum']);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column Headers
        $headers = [
            'A6' => 'NO',
            'B6' => 'KODE BARANG',
            'C6' => 'NUP',
            'D6' => 'NAMA BARANG / JENIS',
            'E6' => 'MERK / TIPE',
            'F6' => 'LOKASI RUANGAN',
            'G6' => 'KONDISI SISTEM',
            'H6' => 'STATUS AUDIT / FISIK',
            'I6' => 'KONDISI FISIK TEMUAN',
            'J6' => 'CATATAN PEMERIKSAAN',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];
        $sheet->getStyle('A6:J6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(28);

        $rowNum = 7;
        $no = 1;
        $lastRoom = null;

        foreach ($items as $item) {
            $roomName = $item['ruangan_sistem_nama'] ?: ($item['peruntukan'] === 'mobiler' ? 'Sekolah / Mobiler' : 'Gudang / Belum Berlokasi');

            // Jika ruangan berganti, sisipkan baris pemisah header ruangan
            if ($lastRoom !== $roomName) {
                $lastRoom = $roomName;
                $no = 1;

                $sheet->mergeCells('A' . $rowNum . ':J' . $rowNum);
                $sheet->setCellValue('A' . $rowNum, '  🏢 RUANGAN: ' . strtoupper($roomName));
                $sheet->getStyle('A' . $rowNum . ':J' . $rowNum)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0F172A'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                $sheet->getRowDimension($rowNum)->setRowHeight(22);
                $rowNum++;
            }

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValueExplicit('B' . $rowNum, $item['kode_barang'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNum, $item['nup'] ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowNum, $item['nama_barang']);
            $sheet->setCellValue('E' . $rowNum, $item['merk_tipe'] ?: '-');
            $sheet->setCellValue('F' . $rowNum, $roomName);
            $sheet->setCellValue('G' . $rowNum, $item['kondisi_sistem']);

            $statusText = match ($item['status_audit']) {
                'sesuai'                 => 'Sesuai & Ada',
                'terkonfirmasi_dipinjam' => 'Sedang Dipinjam Sah',
                'kondisi_berubah'        => 'Kondisi Berubah',
                'salah_lokasi'           => 'Pindah Ruangan',
                'tidak_ditemukan'        => 'Tidak Ditemukan (Hilang)',
                default                  => 'Belum Diperiksa',
            };
            $sheet->setCellValue('H' . $rowNum, $statusText);
            $sheet->setCellValue('I' . $rowNum, $item['kondisi_fisik'] ?: '-');

            $catatan = $item['catatan_pemeriksaan'] ?: '';
            if ($item['status_pinjam_sistem'] === 'dipinjam') {
                $catatan = 'Dipinjam: ' . ($item['peminjam_nama'] ?: 'Pegawai') . ' (Surat: ' . ($item['no_surat_pinjam'] ?: '-') . ')';
            }
            $sheet->setCellValue('J' . $rowNum, $catatan);

            $borderStyle = [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                ],
            ];
            $sheet->getStyle('A' . $rowNum . ':J' . $rowNum)->applyFromArray($borderStyle);

            if ($item['status_audit'] === 'sesuai') {
                $sheet->getStyle('H' . $rowNum)->getFont()->getColor()->setRGB('15803D');
            } elseif ($item['status_audit'] === 'terkonfirmasi_dipinjam') {
                $sheet->getStyle('H' . $rowNum)->getFont()->getColor()->setRGB('B45309');
            } elseif ($item['status_audit'] === 'tidak_ditemukan') {
                $sheet->getStyle('H' . $rowNum)->getFont()->getColor()->setRGB('B91C1C');
            }

            $rowNum++;
        }

        // Auto width
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Blok Tanda Tangan
        $signRow = $rowNum + 3;
        $sheet->setCellValue('H' . $signRow, 'Pekanbaru, ' . date('d F Y', strtotime($audit['tanggal_audit'])));
        $sheet->setCellValue('H' . ($signRow + 1), 'Petugas Auditor / Pemeriksa Aset');
        $sheet->setCellValue('H' . ($signRow + 2), 'Satker Pelaksanaan Prasarana Strategis Riau');

        $auditorNama = $audit['auditor_nama'] ?: 'Petugas Auditor';
        $auditorNip  = $audit['auditor_nip'] ? 'NIP. ' . $audit['auditor_nip'] : '-';

        $sheet->setCellValue('H' . ($signRow + 6), $auditorNama);
        $sheet->setCellValue('H' . ($signRow + 7), $auditorNip);

        $sheet->getStyle('H' . ($signRow + 6))->getFont()->setBold(true)->setUnderline(true);

        $filename = 'Hasil_Audit_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $audit['kode_audit']) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        $rawRole = (string) session()->get('role');
        $normalizedRole = strtolower(trim($rawRole));

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            if (in_array($normalizedRole, ['super administrator', 'super_administrator', 'superadmin', 'super-admin', 'admin', 'administrator'], true)) {
                return [
                    'add'      => true,
                    'edit'     => true,
                    'delete'   => true,
                    'export'   => true,
                    'import'   => true,
                    'approval' => true,
                ];
            }
            return $default;
        }

        $roleId = $this->resolveRoleId($rawRole, $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($roleId === null || $menuId === null) {
            if (in_array($normalizedRole, ['super administrator', 'super_administrator', 'superadmin', 'super-admin', 'admin', 'administrator'], true)) {
                return [
                    'add'      => true,
                    'edit'     => true,
                    'delete'   => true,
                    'export'   => true,
                    'import'   => true,
                    'approval' => true,
                ];
            }
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            if (in_array($roleId, [1, 2], true) || in_array($normalizedRole, ['super administrator', 'super_administrator', 'superadmin', 'super-admin', 'admin', 'administrator'], true)) {
                return [
                    'add'      => true,
                    'edit'     => true,
                    'delete'   => true,
                    'export'   => true,
                    'import'   => true,
                    'approval' => true,
                ];
            }
            return $default;
        }

        return [
            'add'      => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit'     => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete'   => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export'   => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import'   => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if (is_numeric($normalized)) {
            return (int) $normalized;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator') {
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalized === 'super_administrator' || $normalized === 'super-admin' || $normalized === 'superadmin') {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
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

        if ($db->tableExists('groups')) {
            $group = $db->table('groups')
                ->select('id')
                ->where('name', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($group) && ! empty($group['id'])) {
                return (int) $group['id'];
            }
        }

        $map = [
            'super_administrator' => 1,
            'super administrator' => 1,
            'superadmin'          => 1,
            'super-admin'         => 1,
            'admin'               => 2,
            'administrator'       => 2,
            'verifikator'         => 3,
            'staf'                => 4,
            'kepala_satker'       => 5,
        ];

        return $map[$normalized] ?? null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $cleanLink = trim(strtolower($menuLink), '/');

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(TRIM(link))', $cleanLink)
                ->get()
                ->getRowArray();

            if (is_array($row) && ! empty($row['id'])) {
                return (string) $row['id'];
            }
        }

        return '11-06';
    }
}
