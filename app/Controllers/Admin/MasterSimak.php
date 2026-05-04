<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MasterSimakKonsultasiItemModel;
use App\Models\MasterSimakKonstruksiItemModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class MasterSimak extends BaseController
{
    private const MENU_LINK_KONSTRUKSI = 'admin/master/simak/konstruksi';
    private const MENU_LINK_KONSULTASI = 'admin/master/simak/konsultasi';

    public function konstruksi()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSTRUKSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSTRUKSI);

        if (! $db->tableExists('mst_simak_konstruksi_item')) {
            return view('admin/master/simak_konstruksi', [
                'pageTitle' => 'Master SIMAK Konstruksi',
                'pageSubtitle' => 'Tabel master belum tersedia. Jalankan migration terbaru.',
                'itemsTree' => [],
                'itemsFlat' => [],
                'parentOptions' => [],
                'can_add' => false,
                'can_edit' => false,
                'can_delete' => false,
                'shareVisibilityAvailable' => false,
            ]);
        }

        $shareVisibilityAvailable = $db->fieldExists('is_hidden_share', 'mst_simak_konstruksi_item');

        $rows = (new MasterSimakKonstruksiItemModel())
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $itemsTree = $this->buildTree($rows);
        $itemsFlat = $this->flattenTree($itemsTree);
        $parentOptions = $this->buildParentOptions($itemsFlat);

        return view('admin/master/simak_konstruksi', [
            'pageTitle' => 'Master SIMAK Konstruksi',
            'pageSubtitle' => 'Master pertanyaan, hirarki, dan section verifikasi SIMAK konstruksi.',
            'itemsTree' => $itemsTree,
            'itemsFlat' => $itemsFlat,
            'parentOptions' => $parentOptions,
            'can_add' => $this->canManageMasterData() && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $this->canManageMasterData() && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete' => $this->canManageMasterData() && (bool) ($menuPermissions['delete'] ?? false),
            'shareVisibilityAvailable' => $shareVisibilityAvailable,
        ]);
    }

    public function konstruksiCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSTRUKSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSTRUKSI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['add'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk menambah master SIMAK konstruksi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Anda tidak memiliki akses untuk menambah master SIMAK konstruksi.');
        }

        if (! $this->validate([
            'uraian' => 'required',
            'row_kind' => 'required|in_list[section,group,question,text,separator]',
        ])) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Data master belum valid.',
                    'errors' => $this->validator?->getErrors() ?? [],
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->withInput()->with('error', 'Data master belum valid.');
        }

        $model = new MasterSimakKonstruksiItemModel();
        $parentId = (int) ($this->request->getPost('parent_id') ?? 0);
        $parentId = $parentId > 0 ? $parentId : null;
        $rowKind = trim((string) $this->request->getPost('row_kind'));

        $nextRowNo = (int) (($model->selectMax('row_no', 'max_row')->first()['max_row'] ?? 0) + 1);

        $orderingBuilder = $model->selectMax('ordering', 'max_ordering');
        if ($parentId === null) {
            $orderingBuilder->where('parent_id', null);
        } else {
            $orderingBuilder->where('parent_id', $parentId);
        }
        $nextOrdering = (int) (($orderingBuilder->first()['max_ordering'] ?? 0) + 1);

        $model->insert([
            'parent_id' => $parentId,
            'row_no' => $nextRowNo,
            'display_no' => trim((string) $this->request->getPost('display_no')),
            'uraian' => trim((string) $this->request->getPost('uraian')),
            'row_kind' => $rowKind,
            'has_question' => $rowKind === 'question' ? 1 : ((int) ($this->request->getPost('has_question') ? 1 : 0)),
            'ordering' => $nextOrdering,
            'is_active' => 1,
        ]);

        if ($db->fieldExists('is_hidden_share', 'mst_simak_konstruksi_item')) {
            $model->update((int) $model->getInsertID(), ['is_hidden_share' => 0]);
        }

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Item master SIMAK konstruksi berhasil ditambahkan.',
                'id' => (int) $model->getInsertID(),
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konstruksi')->with('success', 'Item master SIMAK konstruksi berhasil ditambahkan.');
    }

    public function konstruksiUpdate(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSTRUKSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSTRUKSI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah master SIMAK konstruksi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Anda tidak memiliki akses untuk mengubah master SIMAK konstruksi.');
        }

        if (! $this->validate([
            'uraian' => 'required',
            'row_kind' => 'required|in_list[section,group,question,text,separator]',
        ])) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Data master belum valid.',
                    'errors' => $this->validator?->getErrors() ?? [],
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->withInput()->with('error', 'Data master belum valid.');
        }

        $model = new MasterSimakKonstruksiItemModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                    'status' => 'error',
                    'message' => 'Item master tidak ditemukan.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Item master tidak ditemukan.');
        }

        $parentId = (int) ($this->request->getPost('parent_id') ?? 0);
        $parentId = $parentId > 0 ? $parentId : null;
        if ($parentId === $id) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Parent tidak boleh sama dengan item saat ini.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Parent tidak boleh sama dengan item saat ini.');
        }

        $rowKind = trim((string) $this->request->getPost('row_kind'));

        $payload = [
            'parent_id' => $parentId,
            'display_no' => trim((string) $this->request->getPost('display_no')),
            'uraian' => trim((string) $this->request->getPost('uraian')),
            'row_kind' => $rowKind,
            'has_question' => $rowKind === 'question' ? 1 : ((int) ($this->request->getPost('has_question') ? 1 : 0)),
        ];

        $model->update($id, $payload);

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Item master SIMAK konstruksi berhasil diubah.',
                'id' => $id,
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konstruksi')->with('success', 'Item master SIMAK konstruksi berhasil diubah.');
    }

    public function konstruksiUpdateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSTRUKSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSTRUKSI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah status master SIMAK konstruksi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Anda tidak memiliki akses untuk mengubah status master SIMAK konstruksi.');
        }

        $status = (int) ($this->request->getPost('is_active') ?? -1);
        if (! in_array($status, [0, 1], true)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Status item tidak valid.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Status item tidak valid.');
        }

        $model = new MasterSimakKonstruksiItemModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                    'status' => 'error',
                    'message' => 'Item master tidak ditemukan.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Item master tidak ditemukan.');
        }

        $model->update($id, ['is_active' => $status]);

        $message = $status === 1 ? 'Item berhasil diaktifkan.' : 'Item berhasil dinonaktifkan.';

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => $message,
                'id' => $id,
                'is_active' => $status,
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konstruksi')->with('success', $message);
    }

    public function konstruksiUpdateShareVisibility(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSTRUKSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();
        if (! $db->fieldExists('is_hidden_share', 'mst_simak_konstruksi_item')) {
            $message = 'Fitur sembunyikan share belum tersedia. Jalankan migration terbaru.';
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_CONFLICT)->setJSON([
                    'status' => 'error',
                    'message' => $message,
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', $message);
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSTRUKSI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah visibilitas share master SIMAK konstruksi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Anda tidak memiliki akses untuk mengubah visibilitas share master SIMAK konstruksi.');
        }

        $visibility = (int) ($this->request->getPost('is_hidden_share') ?? -1);
        if (! in_array($visibility, [0, 1], true)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Status visibilitas share tidak valid.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Status visibilitas share tidak valid.');
        }

        $model = new MasterSimakKonstruksiItemModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                    'status' => 'error',
                    'message' => 'Item master tidak ditemukan.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konstruksi')->with('error', 'Item master tidak ditemukan.');
        }

        $model->update($id, ['is_hidden_share' => $visibility]);

        $message = $visibility === 1
            ? 'Item berhasil disembunyikan dari share link.'
            : 'Item berhasil ditampilkan di share link.';

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => $message,
                'id' => $id,
                'is_hidden_share' => $visibility,
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konstruksi')->with('success', $message);
    }

    public function konstruksiSaveHierarchy()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSTRUKSI);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ]);
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSTRUKSI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk mengubah hirarki.',
            ]);
        }

        $rawTree = $this->request->getPost('tree');
        if (! is_string($rawTree) || trim($rawTree) === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status' => 'error',
                'message' => 'Payload hirarki tidak valid.',
            ]);
        }

        $tree = json_decode($rawTree, true);
        if (! is_array($tree)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status' => 'error',
                'message' => 'Format hirarki tidak valid.',
            ]);
        }

        $flattened = [];
        $this->flattenHierarchyPayload($tree, null, $flattened);

        $db = db_connect();
        $db->transStart();
        foreach ($flattened as $row) {
            $db->table('mst_simak_konstruksi_item')
                ->where('id', (int) $row['id'])
                ->update([
                    'parent_id' => $row['parent_id'],
                    'ordering' => (int) $row['ordering'],
                ]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan hirarki.',
            ] + $this->csrfPayload());
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Hirarki berhasil disimpan.',
        ] + $this->csrfPayload());
    }

    public function konsultasi()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSULTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSULTASI);

        if (! $db->tableExists('mst_simak_konsultasi_item')) {
            return view('admin/master/simak_konsultasi', [
                'pageTitle' => 'Master SIMAK Konsultasi',
                'pageSubtitle' => 'Tabel master belum tersedia. Jalankan migration terbaru.',
                'itemsTree' => [],
                'itemsFlat' => [],
                'parentOptions' => [],
                'can_add' => false,
                'can_edit' => false,
                'can_delete' => false,
                'shareVisibilityAvailable' => false,
            ]);
        }

        $shareVisibilityAvailable = $db->fieldExists('is_hidden_share', 'mst_simak_konsultasi_item');

        $rows = (new MasterSimakKonsultasiItemModel())
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $itemsTree = $this->buildTree($rows);
        $itemsFlat = $this->flattenTree($itemsTree);
        $parentOptions = $this->buildParentOptions($itemsFlat);

        return view('admin/master/simak_konsultasi', [
            'pageTitle' => 'Master SIMAK Konsultasi',
            'pageSubtitle' => 'Master pertanyaan, hirarki, dan section verifikasi SIMAK konsultasi.',
            'itemsTree' => $itemsTree,
            'itemsFlat' => $itemsFlat,
            'parentOptions' => $parentOptions,
            'can_add' => $this->canManageMasterData() && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $this->canManageMasterData() && (bool) ($menuPermissions['edit'] ?? false),
            'can_delete' => $this->canManageMasterData() && (bool) ($menuPermissions['delete'] ?? false),
            'shareVisibilityAvailable' => $shareVisibilityAvailable,
        ]);
    }

    public function konsultasiCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSULTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSULTASI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['add'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk menambah master SIMAK konsultasi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Anda tidak memiliki akses untuk menambah master SIMAK konsultasi.');
        }

        if (! $this->validate([
            'uraian' => 'required',
            'row_kind' => 'required|in_list[section,group,question,text,separator]',
        ])) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Data master belum valid.',
                    'errors' => $this->validator?->getErrors() ?? [],
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->withInput()->with('error', 'Data master belum valid.');
        }

        $model = new MasterSimakKonsultasiItemModel();
        $parentId = (int) ($this->request->getPost('parent_id') ?? 0);
        $parentId = $parentId > 0 ? $parentId : null;
        $rowKind = trim((string) $this->request->getPost('row_kind'));

        $nextRowNo = (int) (($model->selectMax('row_no', 'max_row')->first()['max_row'] ?? 0) + 1);

        $orderingBuilder = $model->selectMax('ordering', 'max_ordering');
        if ($parentId === null) {
            $orderingBuilder->where('parent_id', null);
        } else {
            $orderingBuilder->where('parent_id', $parentId);
        }
        $nextOrdering = (int) (($orderingBuilder->first()['max_ordering'] ?? 0) + 1);

        $model->insert([
            'parent_id' => $parentId,
            'row_no' => $nextRowNo,
            'display_no' => trim((string) $this->request->getPost('display_no')),
            'uraian' => trim((string) $this->request->getPost('uraian')),
            'bentuk_dokumen' => trim((string) $this->request->getPost('bentuk_dokumen')),
            'referensi' => trim((string) $this->request->getPost('referensi')),
            'kriteria_administrasi' => trim((string) $this->request->getPost('kriteria_administrasi')),
            'kriteria_substansi' => trim((string) $this->request->getPost('kriteria_substansi')),
            'sumber_dokumen_hasil_integrasi' => trim((string) $this->request->getPost('sumber_dokumen_hasil_integrasi')),
            'row_kind' => $rowKind,
            'has_question' => $rowKind === 'question' ? 1 : ((int) ($this->request->getPost('has_question') ? 1 : 0)),
            'ordering' => $nextOrdering,
            'is_active' => 1,
        ]);

        if ($db->fieldExists('is_hidden_share', 'mst_simak_konsultasi_item')) {
            $model->update((int) $model->getInsertID(), ['is_hidden_share' => 0]);
        }

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Item master SIMAK konsultasi berhasil ditambahkan.',
                'id' => (int) $model->getInsertID(),
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konsultasi')->with('success', 'Item master SIMAK konsultasi berhasil ditambahkan.');
    }

    public function konsultasiUpdate(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSULTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSULTASI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah master SIMAK konsultasi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Anda tidak memiliki akses untuk mengubah master SIMAK konsultasi.');
        }

        if (! $this->validate([
            'uraian' => 'required',
            'row_kind' => 'required|in_list[section,group,question,text,separator]',
        ])) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Data master belum valid.',
                    'errors' => $this->validator?->getErrors() ?? [],
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->withInput()->with('error', 'Data master belum valid.');
        }

        $model = new MasterSimakKonsultasiItemModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                    'status' => 'error',
                    'message' => 'Item master tidak ditemukan.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Item master tidak ditemukan.');
        }

        $parentId = (int) ($this->request->getPost('parent_id') ?? 0);
        $parentId = $parentId > 0 ? $parentId : null;
        if ($parentId === $id) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Parent tidak boleh sama dengan item saat ini.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Parent tidak boleh sama dengan item saat ini.');
        }

        $rowKind = trim((string) $this->request->getPost('row_kind'));

        $model->update($id, [
            'parent_id' => $parentId,
            'display_no' => trim((string) $this->request->getPost('display_no')),
            'uraian' => trim((string) $this->request->getPost('uraian')),
            'bentuk_dokumen' => trim((string) $this->request->getPost('bentuk_dokumen')),
            'referensi' => trim((string) $this->request->getPost('referensi')),
            'kriteria_administrasi' => trim((string) $this->request->getPost('kriteria_administrasi')),
            'kriteria_substansi' => trim((string) $this->request->getPost('kriteria_substansi')),
            'sumber_dokumen_hasil_integrasi' => trim((string) $this->request->getPost('sumber_dokumen_hasil_integrasi')),
            'row_kind' => $rowKind,
            'has_question' => $rowKind === 'question' ? 1 : ((int) ($this->request->getPost('has_question') ? 1 : 0)),
        ]);

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Item master SIMAK konsultasi berhasil diubah.',
                'id' => $id,
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konsultasi')->with('success', 'Item master SIMAK konsultasi berhasil diubah.');
    }

    public function konsultasiUpdateStatus(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSULTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSULTASI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah status master SIMAK konsultasi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Anda tidak memiliki akses untuk mengubah status master SIMAK konsultasi.');
        }

        $status = (int) ($this->request->getPost('is_active') ?? -1);
        if (! in_array($status, [0, 1], true)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Status item tidak valid.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Status item tidak valid.');
        }

        $model = new MasterSimakKonsultasiItemModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                    'status' => 'error',
                    'message' => 'Item master tidak ditemukan.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Item master tidak ditemukan.');
        }

        $model->update($id, ['is_active' => $status]);

        $message = $status === 1 ? 'Item berhasil diaktifkan.' : 'Item berhasil dinonaktifkan.';

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => $message,
                'id' => $id,
                'is_active' => $status,
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konsultasi')->with('success', $message);
    }

    public function konsultasiUpdateShareVisibility(int $id)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSULTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $db = db_connect();
        if (! $db->fieldExists('is_hidden_share', 'mst_simak_konsultasi_item')) {
            $message = 'Fitur sembunyikan share belum tersedia. Jalankan migration terbaru.';
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_CONFLICT)->setJSON([
                    'status' => 'error',
                    'message' => $message,
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', $message);
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSULTASI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah visibilitas share master SIMAK konsultasi.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Anda tidak memiliki akses untuk mengubah visibilitas share master SIMAK konsultasi.');
        }

        $visibility = (int) ($this->request->getPost('is_hidden_share') ?? -1);
        if (! in_array($visibility, [0, 1], true)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                    'status' => 'error',
                    'message' => 'Status visibilitas share tidak valid.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Status visibilitas share tidak valid.');
        }

        $model = new MasterSimakKonsultasiItemModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            if ($this->wantsJsonResponse()) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                    'status' => 'error',
                    'message' => 'Item master tidak ditemukan.',
                ] + $this->csrfPayload());
            }

            return redirect()->to('/admin/master/simak/konsultasi')->with('error', 'Item master tidak ditemukan.');
        }

        $model->update($id, ['is_hidden_share' => $visibility]);

        $message = $visibility === 1
            ? 'Item berhasil disembunyikan dari share link.'
            : 'Item berhasil ditampilkan di share link.';

        if ($this->wantsJsonResponse()) {
            return $this->response->setJSON([
                'status' => 'ok',
                'message' => $message,
                'id' => $id,
                'is_hidden_share' => $visibility,
            ] + $this->csrfPayload());
        }

        return redirect()->to('/admin/master/simak/konsultasi')->with('success', $message);
    }

    public function konsultasiSaveHierarchy()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KONSULTASI);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ] + $this->csrfPayload());
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_KONSULTASI);
        if (! $this->canManageMasterData() || ! (bool) ($permissions['edit'] ?? false)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk mengubah hirarki.',
            ] + $this->csrfPayload());
        }

        $rawTree = $this->request->getPost('tree');
        if (! is_string($rawTree) || trim($rawTree) === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status' => 'error',
                'message' => 'Payload hirarki tidak valid.',
            ] + $this->csrfPayload());
        }

        $tree = json_decode($rawTree, true);
        if (! is_array($tree)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status' => 'error',
                'message' => 'Format hirarki tidak valid.',
            ] + $this->csrfPayload());
        }

        $flattened = [];
        $this->flattenHierarchyPayload($tree, null, $flattened);

        $db = db_connect();
        $db->transStart();
        foreach ($flattened as $row) {
            $db->table('mst_simak_konsultasi_item')
                ->where('id', (int) $row['id'])
                ->update([
                    'parent_id' => $row['parent_id'],
                    'ordering' => (int) $row['ordering'],
                ]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan hirarki.',
            ] + $this->csrfPayload());
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Hirarki berhasil disimpan.',
        ] + $this->csrfPayload());
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

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));

        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if (strpos($normalized, 'super') !== false) {
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

        return match ($normalized) {
            'admin' => 1,
            'editor' => 2,
            default => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower(trim($menuLink)))
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
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
            'add' => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit' => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete' => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export' => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import' => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function canManageMasterData(): bool
    {
        $role = strtolower(trim((string) session()->get('role')));

        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function wantsJsonResponse(): bool
    {
        if ($this->request->isAJAX()) {
            return true;
        }

        $accept = strtolower(trim($this->request->getHeaderLine('Accept')));

        return str_contains($accept, 'application/json');
    }

    private function csrfPayload(): array
    {
        return [
            'csrf' => [
                'name' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ];
    }

    private function buildTree(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $row['children'] = [];
            $map[(int) $row['id']] = $row;
        }

        $roots = [];
        foreach ($map as $id => $row) {
            $parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
            if ($parentId > 0 && isset($map[$parentId])) {
                $map[$parentId]['children'][] = &$map[$id];
                continue;
            }

            $roots[] = &$map[$id];
        }

        $sortFn = function (array &$items) use (&$sortFn): void {
            usort($items, static function (array $a, array $b): int {
                $orderingCmp = ((int) ($a['ordering'] ?? 0)) <=> ((int) ($b['ordering'] ?? 0));
                if ($orderingCmp !== 0) {
                    return $orderingCmp;
                }

                return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
            });

            foreach ($items as &$item) {
                if (! empty($item['children'])) {
                    $sortFn($item['children']);
                }
            }
            unset($item);
        };

        $sortFn($roots);

        return $roots;
    }

    private function flattenTree(array $tree): array
    {
        $result = [];

        $walker = function (array $nodes, int $depth) use (&$walker, &$result): void {
            foreach ($nodes as $node) {
                $node['depth'] = $depth;
                $node['children_count'] = is_array($node['children'] ?? null) ? count($node['children']) : 0;
                $result[] = $node;

                if (! empty($node['children'])) {
                    $walker($node['children'], $depth + 1);
                }
            }
        };

        $walker($tree, 0);

        return $result;
    }

    private function buildParentOptions(array $flatRows): array
    {
        $options = [];
        foreach ($flatRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $indent = str_repeat('-- ', (int) ($row['depth'] ?? 0));
            $displayNo = trim((string) ($row['display_no'] ?? ''));
            $uraian = trim((string) ($row['uraian'] ?? ''));
            $label = trim($displayNo . ' ' . $uraian);
            $options[] = [
                'id' => $id,
                'label' => trim($indent . $label),
            ];
        }

        return $options;
    }

    private function flattenHierarchyPayload(array $nodes, ?int $parentId, array &$out): void
    {
        $order = 1;
        foreach ($nodes as $node) {
            $id = (int) ($node['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $out[] = [
                'id' => $id,
                'parent_id' => $parentId,
                'ordering' => $order,
            ];
            $order++;

            $children = $node['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $this->flattenHierarchyPayload($children, $id, $out);
            }
        }
    }

}