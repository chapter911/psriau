<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstRuanganModel;
use App\Models\InventarisSatkerModel;
use CodeIgniter\HTTP\RedirectResponse;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarisDbr extends BaseController
{
    private const MENU_LINK = 'admin/inventaris/dbr';

    public function index()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();

        // Query rooms with item & unit count
        $ruanganList = $db->table('mst_ruangan r')
            ->select('r.*, COUNT(s.id) AS total_aset, COALESCE(SUM(s.jumlah), 0) AS total_unit')
            ->join('trn_inventaris_satker s', 's.ruangan_id = r.id', 'left')
            ->groupBy('r.id')
            ->orderBy('r.kode_ruangan', 'ASC')
            ->orderBy('r.nama_ruangan', 'ASC')
            ->get()
            ->getResultArray();

        // Get employees for dropdown
        $pegawaiList = [];
        if ($db->tableExists('mst_pegawai')) {
            $builder = $db->table('mst_pegawai')->select('id, nama, nip');
            if ($db->fieldExists('is_active', 'mst_pegawai')) {
                $builder->where('is_active', 1);
            }
            $pegawaiList = $builder->orderBy('nama', 'ASC')->get()->getResultArray();
        }

        // Summary metrics
        $totalRuangan = count($ruanganList);
        $totalAsetBerlokasi = (int) $db->table('trn_inventaris_satker')
            ->where('ruangan_id IS NOT NULL', null, false)
            ->where('ruangan_id >', 0)
            ->countAllResults();

        $totalAsetBelum = (int) $db->table('trn_inventaris_satker')
            ->groupStart()
                ->where('ruangan_id IS NULL', null, false)
                ->orWhere('ruangan_id', 0)
            ->groupEnd()
            ->countAllResults();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        return view('admin/inventaris/dbr/index', [
            'pageTitle'           => 'Daftar Barang Ruangan (DBR)',
            'ruanganList'         => $ruanganList,
            'pegawaiList'         => $pegawaiList,
            'totalRuangan'        => $totalRuangan,
            'totalAsetBerlokasi'  => $totalAsetBerlokasi,
            'totalAsetBelum'      => $totalAsetBelum,
            'can_add'             => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'            => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'          => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'          => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    public function createRuangan()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['add'] ?? false)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Anda tidak memiliki hak akses untuk menambah ruangan.');
        }

        $rules = [
            'kode_ruangan' => 'required|max_length[50]',
            'nama_ruangan' => 'required|max_length[150]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/dbr')->withInput()->with('error', 'Kode dan Nama Ruangan wajib diisi.');
        }

        $pegawaiId = (int) $this->request->getPost('pegawai_id') ?: null;
        $pjNama = trim((string) $this->request->getPost('penanggung_jawab_nama'));
        $pjNip  = trim((string) $this->request->getPost('penanggung_jawab_nip'));

        $db = db_connect();
        if ($pegawaiId !== null && $pegawaiId > 0 && $db->tableExists('mst_pegawai')) {
            $peg = $db->table('mst_pegawai')->where('id', $pegawaiId)->get()->getRowArray();
            if (is_array($peg)) {
                $pjNama = $peg['nama'] ?? $pjNama;
                $pjNip  = $peg['nip'] ?? $pjNip;
            }
        }

        $userId = (int) (session()->get('userId') ?? 0);
        $model = new MstRuanganModel();
        $model->insert([
            'kode_ruangan'          => trim((string) $this->request->getPost('kode_ruangan')),
            'nama_ruangan'          => trim((string) $this->request->getPost('nama_ruangan')),
            'pegawai_id'            => $pegawaiId,
            'penanggung_jawab_nama' => $pjNama ?: null,
            'penanggung_jawab_nip'  => $pjNip ?: null,
            'lokasi_lantai'         => trim((string) $this->request->getPost('lokasi_lantai')) ?: null,
            'keterangan'            => trim((string) $this->request->getPost('keterangan')) ?: null,
            'created_by'            => $userId ?: null,
            'updated_by'            => $userId ?: null,
        ]);

        return redirect()->to('/admin/inventaris/dbr')->with('message', 'Data ruangan berhasil ditambahkan.');
    }

    public function editRuangan(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['edit'] ?? false)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Anda tidak memiliki hak akses untuk mengubah ruangan.');
        }

        $model = new MstRuanganModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Data ruangan tidak ditemukan.');
        }

        $rules = [
            'kode_ruangan' => 'required|max_length[50]',
            'nama_ruangan' => 'required|max_length[150]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/inventaris/dbr')->withInput()->with('error', 'Kode dan Nama Ruangan wajib diisi.');
        }

        $pegawaiId = (int) $this->request->getPost('pegawai_id') ?: null;
        $pjNama = trim((string) $this->request->getPost('penanggung_jawab_nama'));
        $pjNip  = trim((string) $this->request->getPost('penanggung_jawab_nip'));

        $db = db_connect();
        if ($pegawaiId !== null && $pegawaiId > 0 && $db->tableExists('mst_pegawai')) {
            $peg = $db->table('mst_pegawai')->where('id', $pegawaiId)->get()->getRowArray();
            if (is_array($peg)) {
                $pjNama = $peg['nama'] ?? $pjNama;
                $pjNip  = $peg['nip'] ?? $pjNip;
            }
        }

        $userId = (int) (session()->get('userId') ?? 0);
        $namaRuanganBaru = trim((string) $this->request->getPost('nama_ruangan'));

        $model->update($id, [
            'kode_ruangan'          => trim((string) $this->request->getPost('kode_ruangan')),
            'nama_ruangan'          => $namaRuanganBaru,
            'pegawai_id'            => $pegawaiId,
            'penanggung_jawab_nama' => $pjNama ?: null,
            'penanggung_jawab_nip'  => $pjNip ?: null,
            'lokasi_lantai'         => trim((string) $this->request->getPost('lokasi_lantai')) ?: null,
            'keterangan'            => trim((string) $this->request->getPost('keterangan')) ?: null,
            'updated_by'            => $userId ?: null,
        ]);

        // Sync string lokasi_ruangan in trn_inventaris_satker
        $db->table('trn_inventaris_satker')
            ->where('ruangan_id', $id)
            ->update(['lokasi_ruangan' => $namaRuanganBaru]);

        return redirect()->to('/admin/inventaris/dbr')->with('message', 'Data ruangan berhasil diperbarui.');
    }

    public function deleteRuangan(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['delete'] ?? false)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Anda tidak memiliki hak akses untuk menghapus ruangan.');
        }

        $model = new MstRuanganModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Data ruangan tidak ditemukan.');
        }

        $db = db_connect();
        // Unassign assets from this room before deleting
        $db->table('trn_inventaris_satker')
            ->where('ruangan_id', $id)
            ->update([
                'ruangan_id'     => null,
                'lokasi_ruangan' => 'Belum berlokasi',
            ]);

        $model->delete($id);

        return redirect()->to('/admin/inventaris/dbr')->with('message', 'Data ruangan berhasil dihapus.');
    }

    public function detail(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $roomModel = new MstRuanganModel();
        $room = $roomModel->find($ruanganId);
        if (! is_array($room)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Data ruangan tidak ditemukan.');
        }

        $db = db_connect();

        // 1. Grouped items for DBR view (aggregating identical kode_barang, nama_barang, merk_tipe)
        $dbrItems = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe, COUNT(id) AS nup_count, SUM(jumlah) AS total_jumlah, satuan, MIN(kondisi) AS sample_kondisi, GROUP_CONCAT(nup ORDER BY CAST(NULLIF(nup, "") AS UNSIGNED) ASC SEPARATOR ", ") AS nup_list, MIN(tahun_perolehan) AS tahun_perolehan, SUM(nilai_perolehan) AS total_nilai, MAX(no_psp) AS no_psp, MAX(status_bmn) AS status_bmn')
            ->where('ruangan_id', $ruanganId)
            ->groupBy('kode_barang, nama_barang, merk_tipe, satuan')
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('nama_barang', 'ASC')
            ->get()
            ->getResultArray();

        // 2. Individual asset list in this room
        $individualItems = $db->table('trn_inventaris_satker')
            ->where('ruangan_id', $ruanganId)
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();

        // 3. Unallocated assets available to be assigned to this room
        $unallocatedAssets = $db->table('trn_inventaris_satker')
            ->select('id, kode_barang, nup, kode_register, nama_barang, merk_tipe, kondisi, jumlah, satuan, nilai_perolehan, no_psp')
            ->groupStart()
                ->where('ruangan_id IS NULL', null, false)
                ->orWhere('ruangan_id', 0)
            ->groupEnd()
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->get()
            ->getResultArray();

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);

        $totalUnit = 0;
        $totalNilaiRuangan = 0;
        foreach ($dbrItems as $it) {
            $totalUnit += (int) ($it['total_jumlah'] ?? 0);
            $totalNilaiRuangan += (float) ($it['total_nilai'] ?? 0);
        }

        return view('admin/inventaris/dbr/detail', [
            'pageTitle'          => 'Detail Daftar Barang Ruangan (DBR)',
            'room'               => $room,
            'dbrItems'           => $dbrItems,
            'individualItems'    => $individualItems,
            'unallocatedAssets'  => $unallocatedAssets,
            'totalUnit'          => $totalUnit,
            'totalNilaiRuangan'  => $totalNilaiRuangan,
            'can_add'            => (bool) ($menuPermissions['add'] ?? false),
            'can_edit'           => (bool) ($menuPermissions['edit'] ?? false),
            'can_delete'         => (bool) ($menuPermissions['delete'] ?? false),
            'can_export'         => (bool) ($menuPermissions['export'] ?? false),
        ]);
    }

    public function alokasiBarang(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $roomModel = new MstRuanganModel();
        $room = $roomModel->find($ruanganId);
        if (! is_array($room)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Ruangan tidak ditemukan.');
        }

        $assetIds = $this->request->getPost('asset_ids');
        if (empty($assetIds) || ! is_array($assetIds)) {
            return redirect()->to("/admin/inventaris/dbr/{$ruanganId}")->with('error', 'Pilih minimal satu barang untuk dialokasikan.');
        }

        $cleanIds = array_map('intval', $assetIds);
        $cleanIds = array_filter($cleanIds, static fn ($id) => $id > 0);

        if (empty($cleanIds)) {
            return redirect()->to("/admin/inventaris/dbr/{$ruanganId}")->with('error', 'Data barang tidak valid.');
        }

        $db = db_connect();
        $userId = (int) (session()->get('userId') ?? 0);

        $db->table('trn_inventaris_satker')
            ->whereIn('id', $cleanIds)
            ->update([
                'ruangan_id'     => $ruanganId,
                'lokasi_ruangan' => $room['nama_ruangan'],
                'updated_at'     => date('Y-m-d H:i:s'),
                'updated_by'     => $userId ?: null,
            ]);

        $count = count($cleanIds);
        return redirect()->to("/admin/inventaris/dbr/{$ruanganId}")
            ->with('message', "Berhasil mengalokasikan {$count} aset ke {$room['nama_ruangan']}.");
    }

    public function keluarkanBarang(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $roomModel = new MstRuanganModel();
        $room = $roomModel->find($ruanganId);
        if (! is_array($room)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Ruangan tidak ditemukan.');
        }

        $assetIds = $this->request->getPost('asset_ids');
        if (empty($assetIds)) {
            $singleId = (int) $this->request->getPost('asset_id');
            if ($singleId > 0) {
                $assetIds = [$singleId];
            }
        }

        if (empty($assetIds) || ! is_array($assetIds)) {
            return redirect()->to("/admin/inventaris/dbr/{$ruanganId}")->with('error', 'Pilih minimal satu barang untuk dikeluarkan.');
        }

        $cleanIds = array_map('intval', $assetIds);
        $cleanIds = array_filter($cleanIds, static fn ($id) => $id > 0);

        $db = db_connect();
        $userId = (int) (session()->get('userId') ?? 0);

        $db->table('trn_inventaris_satker')
            ->whereIn('id', $cleanIds)
            ->where('ruangan_id', $ruanganId)
            ->update([
                'ruangan_id'     => null,
                'lokasi_ruangan' => 'Belum berlokasi',
                'updated_at'     => date('Y-m-d H:i:s'),
                'updated_by'     => $userId ?: null,
            ]);

        $count = count($cleanIds);
        return redirect()->to("/admin/inventaris/dbr/{$ruanganId}")
            ->with('message', "{$count} barang berhasil dikeluarkan dari ruangan.");
    }

    public function scanLookup(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi login telah berakhir atau akses ditolak.'])->setStatusCode(403);
        }

        $code = trim((string) $this->request->getPost('code'));
        if ($code === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kode scan tidak boleh kosong.'])->setStatusCode(400);
        }

        $db = db_connect();

        // 1. Cari berdasarkan kode_register exact
        $item = $db->table('trn_inventaris_satker')
            ->where('kode_register', $code)
            ->get()
            ->getRowArray();

        // 2. Jika kode berupa/mengandung hash 32 karakter hex (Format Kode Register SIMAN BMN)
        if (! $item && preg_match('/([a-f0-9]{32})/i', $code, $matches)) {
            $hexCode = strtoupper($matches[1]);
            $item = $db->table('trn_inventaris_satker')
                ->where('kode_register', $hexCode)
                ->get()
                ->getRowArray();
        }

        // 3. Jika kode mengandung pattern Kode Barang (10 digit) dan NUP (e.g. 3050104001.1 atau 3050104001-1)
        if (! $item && preg_match('/(\d{10})[^\d]+(\d+)/', $code, $matches)) {
            $kb = $matches[1];
            $nup = (int) $matches[2];
            $item = $db->table('trn_inventaris_satker')
                ->where('kode_barang', $kb)
                ->where('nup', (string) $nup)
                ->get()
                ->getRowArray();
        }

        // 4. Jika hanya NUP atau hanya Kode Barang
        if (! $item) {
            $item = $db->table('trn_inventaris_satker')
                ->where('kode_barang', $code)
                ->orderBy('nup', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        if (! $item) {
            return $this->response->setJSON([
                'status'   => 'not_found',
                'message'  => "Barang dengan kode \"{$code}\" tidak ditemukan dalam database inventaris satker.",
                'csrfHash' => csrf_hash(),
            ]);
        }

        // Ambil info nama ruangan saat ini jika ada
        $currentRoomName = $item['lokasi_ruangan'] ?: 'Belum berlokasi';
        if (! empty($item['ruangan_id'])) {
            $rModel = new MstRuanganModel();
            $currRoom = $rModel->find($item['ruangan_id']);
            if ($currRoom) {
                $currentRoomName = $currRoom['nama_ruangan'];
            }
        }

        $isInThisRoom = ((int) ($item['ruangan_id'] ?? 0) === $ruanganId);

        return $this->response->setJSON([
            'status'   => 'success',
            'csrfHash' => csrf_hash(),
            'data'     => [
                'id'                      => (int) $item['id'],
                'kode_barang'             => $item['kode_barang'],
                'nup'                     => $item['nup'],
                'kode_register'           => $item['kode_register'],
                'nama_barang'             => $item['nama_barang'],
                'merk_tipe'               => $item['merk_tipe'] ?: '-',
                'kategori'                => $item['kategori'],
                'kondisi'                 => $item['kondisi'],
                'tahun_perolehan'         => $item['tahun_perolehan'] ?: '-',
                'satuan'                  => $item['satuan'] ?? 'Unit',
                'ruangan_id'              => $item['ruangan_id'] ? (int) $item['ruangan_id'] : null,
                'lokasi_ruangan'          => $currentRoomName,
                'is_already_in_this_room' => $isInThisRoom,
            ],
        ]);
    }

    public function scanAlokasi(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi login telah berakhir atau akses ditolak.', 'csrfHash' => csrf_hash()])->setStatusCode(403);
        }

        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK);
        if (! (bool) ($menuPermissions['add'] ?? false) && ! (bool) ($menuPermissions['edit'] ?? false)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengalokasikan barang.', 'csrfHash' => csrf_hash()])->setStatusCode(403);
        }

        $assetId = (int) $this->request->getPost('asset_id');
        if ($assetId <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID aset tidak valid.', 'csrfHash' => csrf_hash()])->setStatusCode(400);
        }

        $roomModel = new MstRuanganModel();
        $room = $roomModel->find($ruanganId);
        if (! is_array($room)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data ruangan tidak ditemukan.', 'csrfHash' => csrf_hash()])->setStatusCode(404);
        }

        $db = db_connect();
        $item = $db->table('trn_inventaris_satker')->where('id', $assetId)->get()->getRowArray();
        if (! $item) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Barang tidak ditemukan di database.', 'csrfHash' => csrf_hash()])->setStatusCode(404);
        }

        if ((int) ($item['ruangan_id'] ?? 0) === $ruanganId) {
            return $this->response->setJSON([
                'status'   => 'warning',
                'message'  => "Barang \"{$item['nama_barang']}\" (NUP: {$item['nup']}) sudah berada di ruangan ini.",
                'csrfHash' => csrf_hash(),
            ]);
        }

        $userId = (int) (session()->get('userId') ?? 0);
        $db->table('trn_inventaris_satker')
            ->where('id', $assetId)
            ->update([
                'ruangan_id'     => $ruanganId,
                'lokasi_ruangan' => $room['nama_ruangan'],
                'updated_at'     => date('Y-m-d H:i:s'),
                'updated_by'     => $userId ?: null,
            ]);

        // Hitung total unit terbaru di ruangan ini
        $totalUnitNow = (int) $db->table('trn_inventaris_satker')
            ->where('ruangan_id', $ruanganId)
            ->countAllResults();

        return $this->response->setJSON([
            'status'         => 'success',
            'message'        => "Berhasil menempatkan \"{$item['nama_barang']}\" (NUP: {$item['nup']}) ke {$room['nama_ruangan']}.",
            'total_unit_now' => $totalUnitNow,
            'csrfHash'       => csrf_hash(),
            'asset'          => [
                'id'            => (int) $item['id'],
                'kode_barang'   => $item['kode_barang'],
                'nup'           => $item['nup'],
                'kode_register' => $item['kode_register'],
                'nama_barang'   => $item['nama_barang'],
            ],
        ]);
    }

    public function cetakPdf(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $roomModel = new MstRuanganModel();
        $room = $roomModel->find($ruanganId);
        if (! is_array($room)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Data ruangan tidak ditemukan.');
        }

        $db = db_connect();

        // Query barang ruangan dengan NUP agregat, kondisi, tahun perolehan, nilai & legalitas PSP
        $rawItems = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe, satuan, kondisi,
                      MIN(tahun_perolehan) AS tahun_perolehan,
                      COUNT(id) AS nup_count,
                      SUM(jumlah) AS total_jumlah,
                      MIN(nup) AS min_nup,
                      MAX(nup) AS max_nup,
                      GROUP_CONCAT(nup ORDER BY CAST(NULLIF(nup, "") AS UNSIGNED) ASC SEPARATOR ", ") AS daftar_nup,
                      SUM(nilai_perolehan) AS total_nilai,
                      MAX(no_psp) AS no_psp,
                      MAX(status_bmn) AS status_bmn')
            ->where('ruangan_id', $ruanganId)
            ->groupBy('kode_barang, nama_barang, merk_tipe, satuan, kondisi')
            ->orderBy('nama_barang', 'ASC')
            ->orderBy('kode_barang', 'ASC')
            ->get()
            ->getResultArray();

        $dbrItems = [];
        $totalUnit = 0;
        $totalNilai = 0;

        foreach ($rawItems as $it) {
            $count = (int) ($it['nup_count'] ?? 1);
            $minNup = (int) ($it['min_nup'] ?? 1);
            $maxNup = (int) ($it['max_nup'] ?? $minNup);

            // Format label NUP
            if ($count === 1) {
                $nupLabel = 'NUP: ' . str_pad((string) $minNup, 4, '0', STR_PAD_LEFT);
            } elseif (($maxNup - $minNup + 1) === $count) {
                $nupLabel = 'NUP: ' . str_pad((string) $minNup, 4, '0', STR_PAD_LEFT) . ' s.d ' . str_pad((string) $maxNup, 4, '0', STR_PAD_LEFT);
            } else {
                $nupList = array_map(static fn($n) => str_pad(trim($n), 4, '0', STR_PAD_LEFT), explode(',', (string) $it['daftar_nup']));
                if (count($nupList) > 4) {
                    $nupLabel = 'NUP: ' . implode(', ', array_slice($nupList, 0, 4)) . ' ... (' . count($nupList) . ' unit)';
                } else {
                    $nupLabel = 'NUP: ' . implode(', ', $nupList);
                }
            }

            $it['nup_label'] = $nupLabel;
            $dbrItems[] = $it;

            $totalUnit += (int) ($it['total_jumlah'] ?? 0);
            $totalNilai += (float) ($it['total_nilai'] ?? 0);
        }

        // Ambil Kop Surat dari Master Kop (kop_surat)
        $kopSuratImg = '';
        if (function_exists('kop_surat_img_tag')) {
            $kopSuratImg = kop_surat_img_tag('', 'width: 100%; max-height: 115px; object-fit: contain;', 'Kop Surat Instansi');
        }

        // Logo PU Base64 (Fallback jika master kop tidak ada)
        $logoPath = FCPATH . 'uploads/branding/1774740768_77e8482499660c14c637.png';
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Format tanggal Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tglHariIni = date('j') . ' ' . ($bulanIndo[(int) date('n')] ?? date('F')) . ' ' . date('Y');

        $nomorDokumen = 'DBR/690835/' . strtoupper(preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $room['kode_ruangan'])) . '/' . date('Y');

        // Official UAKPB & Kasatker credentials
        $data = [
            'namaUakpb'     => 'PELAKSANAAN PRASARANA STRATEGIS PROVINSI RIAU',
            'kodeUakpb'     => '145060900691285000KP',
            'room'          => $room,
            'items'         => $dbrItems,
            'totalUnit'     => $totalUnit,
            'totalNilai'    => $totalNilai,
            'nomorDokumen'  => $nomorDokumen,
            'tglPenetapan'  => $tglHariIni,
            'waktuCetak'    => date('d/m/Y H:i') . ' WIB',
            'kopSuratImg'   => $kopSuratImg,
            'logoBase64'    => $logoBase64,
            'kasatker'      => [
                'nama'    => 'Muhammad Yudi Prasetya, S.T.',
                'nip'     => '198002142014121002',
                'jabatan' => 'Kepala Kuasa Pengguna Barang',
            ],
            'tahun'         => date('Y'),
        ];

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $data['isMultiPage'] = false;
        $html = view('admin/inventaris/dbr/pdf_dbr', $data);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pageCount = $dompdf->getCanvas()->get_page_count();
        if ($pageCount > 1) {
            $data['isMultiPage'] = true;
            $data['totalPages'] = $pageCount;
            $html = view('admin/inventaris/dbr/pdf_dbr', $data);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
        }

        $safeRoomName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $room['nama_ruangan']);
        $fileName = 'DBR_' . $room['kode_ruangan'] . '_' . $safeRoomName . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->setBody($dompdf->output());
    }

    public function exportExcel(int $ruanganId)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $roomModel = new MstRuanganModel();
        $room = $roomModel->find($ruanganId);
        if (! is_array($room)) {
            return redirect()->to('/admin/inventaris/dbr')->with('error', 'Data ruangan tidak ditemukan.');
        }

        $db = db_connect();
        $items = $db->table('trn_inventaris_satker')
            ->select('kode_barang, nama_barang, merk_tipe, satuan, kondisi,
                      MIN(tahun_perolehan) AS tahun_perolehan,
                      COUNT(id) AS nup_count,
                      SUM(jumlah) AS total_jumlah,
                      MIN(nup) AS min_nup,
                      MAX(nup) AS max_nup,
                      GROUP_CONCAT(nup ORDER BY CAST(NULLIF(nup, "") AS UNSIGNED) ASC SEPARATOR ", ") AS daftar_nup,
                      SUM(nilai_perolehan) AS total_nilai,
                      MAX(no_psp) AS no_psp,
                      MAX(status_bmn) AS status_bmn')
            ->where('ruangan_id', $ruanganId)
            ->groupBy('kode_barang, nama_barang, merk_tipe, satuan, kondisi')
            ->orderBy('nama_barang', 'ASC')
            ->orderBy('kode_barang', 'ASC')
            ->get()
            ->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Barang Ruangan');

        // Header Title (Row 1-3)
        $sheet->setCellValue('A1', 'KEMENTERIAN PEKERJAAN UMUM');
        $sheet->setCellValue('A2', 'DIREKTORAT JENDERAL PRASARANA STRATEGIS');
        $sheet->setCellValue('A3', 'SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        // Subheader Title (Row 5)
        $sheet->mergeCells('A5:K5');
        $sheet->setCellValue('A5', 'REKAP DAFTAR BARANG RUANGAN (DBR)');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata UAKPB & Ruangan (Row 7-8)
        $sheet->setCellValue('A7', 'NAMA UAKPB');
        $sheet->setCellValue('B7', ': PELAKSANAAN PRASARANA STRATEGIS RIAU');
        $sheet->setCellValue('E7', 'NAMA RUANGAN');
        $sheet->setCellValue('F7', ': ' . strtoupper($room['nama_ruangan']));

        $sheet->setCellValue('A8', 'KODE UAKPB');
        $sheet->setCellValue('B8', ': 145060900691285000KP');
        $sheet->setCellValue('E8', 'KODE RUANGAN');
        $sheet->setCellValue('F8', ': ' . strtoupper($room['kode_ruangan']));

        $sheet->getStyle('A7:F8')->getFont()->setBold(true);

        // Table Header (Row 10)
        $headers = [
            'A10' => 'NO',
            'B10' => 'KODE BARANG',
            'C10' => 'NAMA BARANG',
            'D10' => 'NUP / RENTANG NUP',
            'E10' => 'MERK / TYPE',
            'F10' => 'THN',
            'G10' => 'KONDISI',
            'H10' => 'JML',
            'I10' => 'SATUAN',
            'J10' => 'NILAI PEROLEHAN (RP)',
            'K10' => 'NO SK PSP / STATUS BMN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A10:K10')->getFont()->setBold(true);
        $sheet->getStyle('A10:K10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10:K10')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A10:K10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(22);
        $sheet->getColumnDimension('K')->setWidth(24);

        $rowNum = 11;
        $no = 1;
        $totalQty = 0;
        $totalNilai = 0;
        foreach ($items as $item) {
            $qty = (int) ($item['total_jumlah'] ?? 1);
            $totalQty += $qty;
            $nilai = (float) ($item['total_nilai'] ?? 0);
            $totalNilai += $nilai;

            $count = (int) ($item['nup_count'] ?? 1);
            $minNup = (int) ($item['min_nup'] ?? 1);
            $maxNup = (int) ($item['max_nup'] ?? $minNup);
            if ($count === 1) {
                $nupLabel = 'NUP ' . str_pad((string) $minNup, 4, '0', STR_PAD_LEFT);
            } elseif (($maxNup - $minNup + 1) === $count) {
                $nupLabel = 'NUP ' . str_pad((string) $minNup, 4, '0', STR_PAD_LEFT) . ' s.d ' . str_pad((string) $maxNup, 4, '0', STR_PAD_LEFT);
            } else {
                $nupLabel = 'NUP: ' . (string) $item['daftar_nup'];
            }

            $noPsp = trim((string) ($item['no_psp'] ?? ''));
            $statusBmn = trim((string) ($item['status_bmn'] ?? 'Aktif'));
            $legalitas = $noPsp !== '' ? ($noPsp . ' (' . $statusBmn . ')') : $statusBmn;

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValueExplicit('B' . $rowNum, (string) ($item['kode_barang'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, (string) ($item['nama_barang'] ?? ''));
            $sheet->setCellValue('D' . $rowNum, $nupLabel);
            $sheet->setCellValue('E' . $rowNum, (string) ($item['merk_tipe'] ?? '-'));
            $sheet->setCellValue('F' . $rowNum, (string) ($item['tahun_perolehan'] ?? '-'));
            $sheet->setCellValue('G' . $rowNum, ucfirst((string) ($item['kondisi'] ?? 'Baik')));
            $sheet->setCellValue('H' . $rowNum, $qty);
            $sheet->setCellValue('I' . $rowNum, (string) ($item['satuan'] ?? 'Buah'));
            $sheet->setCellValue('J' . $rowNum, $nilai);
            $sheet->setCellValue('K' . $rowNum, $legalitas);

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // Total Row
        $sheet->mergeCells("A{$rowNum}:G{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL KESELURUHAN');
        $sheet->setCellValue("H{$rowNum}", $totalQty);
        $sheet->setCellValue("I{$rowNum}", 'Unit');
        $sheet->setCellValue("J{$rowNum}", $totalNilai);
        $sheet->setCellValue("K{$rowNum}", '-');
        $sheet->getStyle("A{$rowNum}:K{$rowNum}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("J{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("K{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Border for data table
        $sheet->getStyle("A10:K{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Catatan (Row + 2)
        $catatanRow = $rowNum + 2;
        $sheet->mergeCells("A{$catatanRow}:K" . ($catatanRow + 1));
        $sheet->setCellValue("A{$catatanRow}", "Catatan:\nTidak dibenarkan memindahkan barang-barang yang ada pada daftar ruangan ini tanpa sepengetahuan penanggung jawab Unit Akuntansi Kuasa Pengguna Barang (UAKPB) dan penanggung jawab ruangan ini.");
        $sheet->getStyle("A{$catatanRow}")->getAlignment()->setWrapText(true);

        // Signatures (Row + 4)
        $sigRow = $catatanRow + 3;
        $sheet->setCellValue("A{$sigRow}", 'Penanggung Jawab UAKPB');
        $sheet->setCellValue("H{$sigRow}", 'Pekanbaru,                       ' . date('Y'));

        $sheet->setCellValue("A" . ($sigRow + 1), 'Kepala Kuasa Pengguna Barang');
        $sheet->setCellValue("H" . ($sigRow + 1), 'Penanggung Jawab Ruangan');

        $sheet->setCellValue("A" . ($sigRow + 5), 'Muhammad Yudi Prasetya, S.T.');
        $pjName = ! empty($room['penanggung_jawab_nama']) ? $room['penanggung_jawab_nama'] : '( .................................................... )';
        $sheet->setCellValue("H" . ($sigRow + 5), $pjName);

        $sheet->setCellValue("A" . ($sigRow + 6), 'NIP. 198002142014121002');
        $pjNip = ! empty($room['penanggung_jawab_nip']) ? ('NIP. ' . $room['penanggung_jawab_nip']) : 'NIP. .............................................';
        $sheet->setCellValue("H" . ($sigRow + 6), $pjNip);

        $sheet->getStyle("A{$sigRow}:H" . ($sigRow + 6))->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $safeRoomName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $room['nama_ruangan']);
        $fileName = 'Rekap_DBR_' . $room['kode_ruangan'] . '_' . $safeRoomName . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
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

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($roleId === null || $menuId === null) {
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

        return null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
