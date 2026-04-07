<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuLv1Model;
use App\Models\MenuLv2Model;
use App\Models\MenuLv3Model;
use App\Models\UserModel;

class Utility extends BaseController
{
    public function user()
    {
        return view('admin/utility/user', [
            'can_edit' => $this->canManageUsers(),
        ]);
    }

    public function userList()
    {
        if (! $this->canManageUsers()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki akses untuk melihat data user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $users = (new UserModel())
                ->select('id, username, full_name, role, is_active')
                ->orderBy('full_name', 'ASC')
                ->findAll();
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memuat data user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $users,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function userRoleAccess(int $roleId)
    {
        if (! $this->canManageUsers()) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'Tidak memiliki akses untuk mengubah menu role.',
            ]);
        }

        $role = $this->resolveMenuAccessRole($roleId);
        if ($role === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Role tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'role_id' => $roleId,
            'role' => $role,
            'rows' => $this->buildMenuAccessRows($roleId),
        ]);
    }

    public function userRoleAccessSave()
    {
        if (! $this->canManageUsers()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki akses untuk mengubah menu role.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $roleId = (int) $this->request->getPost('role_id');
        $role = $this->resolveMenuAccessRole($roleId);
        if ($role === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Role tidak valid.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $menuIds = array_values(array_filter(array_map('strval', (array) $this->request->getPost('menu_ids')), static function (string $value): bool {
            return trim($value) !== '';
        }));
        $akses = (array) $this->request->getPost('akses');
        $add = (array) $this->request->getPost('fitur_add');
        $edit = (array) $this->request->getPost('fitur_edit');
        $delete = (array) $this->request->getPost('fitur_delete');
        $approval = (array) $this->request->getPost('fitur_approval');
        $export = (array) $this->request->getPost('fitur_export');
        $import = (array) $this->request->getPost('fitur_import');

        $db = db_connect();
        $roleColumn = $this->menuAccessRoleColumn($db);
        $db->transStart();

        $db->table('menu_akses')->where($roleColumn, $roleId)->delete();

        if ($menuIds !== []) {
            $rows = [];
            foreach ($menuIds as $menuId) {
                if (! isset($akses[$menuId])) {
                    continue;
                }

                $rows[] = [
                    $roleColumn => $roleId,
                    'menu_id' => $menuId,
                    'FiturAdd' => isset($add[$menuId]) ? 1 : 0,
                    'FiturEdit' => isset($edit[$menuId]) ? 1 : 0,
                    'FiturDelete' => isset($delete[$menuId]) ? 1 : 0,
                    'FiturExport' => isset($export[$menuId]) ? 1 : 0,
                    'FiturImport' => isset($import[$menuId]) ? 1 : 0,
                    'FiturApproval' => isset($approval[$menuId]) ? 1 : 0,
                ];
            }

            if ($rows !== []) {
                $db->table('menu_akses')->insertBatch($rows);
            }
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan akses menu.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Akses menu berhasil disimpan untuk ' . $role['label'] . '.',
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function roleCreate()
    {
        if (! $this->canManageUsers()) {
            return redirect()->to('/admin/utility/role')->with('error', 'Tidak memiliki akses untuk menambah role.');
        }

        $label = trim((string) $this->request->getPost('label'));
        $roleKey = strtolower(trim((string) $this->request->getPost('role_key')));

        if ($label === '' || $roleKey === '') {
            return redirect()->to('/admin/utility/role')->withInput()->with('error', 'Nama role dan key role wajib diisi.');
        }

        if (! preg_match('/^[a-z0-9_\-]{3,50}$/', $roleKey)) {
            return redirect()->to('/admin/utility/role')->withInput()->with('error', 'Key role hanya boleh huruf kecil, angka, underscore, atau dash (3-50 karakter).');
        }

        $db = db_connect();
        if (! $db->tableExists('access_roles')) {
            return redirect()->to('/admin/utility/role')->with('error', 'Tabel role belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $exists = $db->table('access_roles')->where('role_key', $roleKey)->countAllResults();
        if ($exists > 0) {
            return redirect()->to('/admin/utility/role')->withInput()->with('error', 'Key role sudah digunakan.');
        }

        $db->table('access_roles')->insert([
            'label' => $label,
            'role_key' => $roleKey,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $newRoleId = (int) $db->insertID();
        if ($newRoleId > 0 && $this->isSuperAdministratorRoleKey($roleKey)) {
            $this->grantFullMenuAccessForRole($newRoleId);
        }

        return redirect()->to('/admin/utility/role')->with('message', 'Role baru berhasil ditambahkan.');
    }

    public function userCreate()
    {
        if (! $this->canManageUsers()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki akses untuk menambah user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
            'full_name' => 'required|min_length[3]|max_length[150]',
            'role' => 'required|in_list[admin,editor,super administrator,super_administrator,super-admin,superadmin]',
            'password' => 'required|min_length[6]|max_length[72]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $model = new UserModel();
        $username = strtolower(trim((string) $this->request->getPost('username')));
        $fullName = trim((string) $this->request->getPost('full_name'));
        $role = trim((string) $this->request->getPost('role'));
        $password = (string) $this->request->getPost('password');

        if ($model->where('username', $username)->countAllResults() > 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => ['username' => 'Username sudah digunakan.'],
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $model->insert([
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role,
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambahkan user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'User berhasil ditambahkan.',
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function userUpdate(int $id)
    {
        if (! $this->canManageUsers()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki akses untuk mengubah user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
            'full_name' => 'required|min_length[3]|max_length[150]',
            'role' => 'required|in_list[admin,editor,super administrator,super_administrator,super-admin,superadmin]',
            'password' => 'permit_empty|min_length[6]|max_length[72]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $model = new UserModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $username = strtolower(trim((string) $this->request->getPost('username')));
        $fullName = trim((string) $this->request->getPost('full_name'));
        $role = trim((string) $this->request->getPost('role'));
        $password = (string) $this->request->getPost('password');

        $duplicate = $model->where('username', $username)->where('id !=', $id)->countAllResults();
        if ($duplicate > 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => ['username' => 'Username sudah digunakan.'],
                'csrfHash' => csrf_hash(),
            ]);
        }

        $data = [
            'username' => $username,
            'full_name' => $fullName,
            'role' => $role,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (trim($password) !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $model->update($id, $data);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'User berhasil diperbarui.',
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function userDelete(int $id)
    {
        if (! $this->canManageUsers()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki akses untuk menghapus user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $model = new UserModel();
        $existing = $model->find($id);
        if (! is_array($existing)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $model->delete($id);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus user.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'User berhasil dihapus.',
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function userRole()
    {
        return view('admin/utility/user_role', [
            'roles' => $this->getMenuAccessRoles(),
            'can_edit' => $this->canManageUsers(),
        ]);
    }

    public function userGroup()
    {
        return $this->userRole();
    }

    public function userGroupAccess(int $groupId)
    {
        return $this->userRoleAccess($groupId);
    }

    public function userGroupAccessSave()
    {
        return $this->userRoleAccessSave();
    }

    private function canManageUsers(): bool
    {
        $role = strtolower(trim((string) session('role')));

        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function getMenuAccessRoles(): array
    {
        $roles = [
            ['role_id' => 1, 'label' => 'Admin', 'role' => 'admin'],
            ['role_id' => 2, 'label' => 'Editor', 'role' => 'editor'],
            ['role_id' => 3, 'label' => 'Super Administrator', 'role' => 'super_administrator'],
        ];

        $db = db_connect();
        if ($db->tableExists('access_roles')) {
            $rows = $db->table('access_roles')
                ->select('id AS role_id, label, role_key AS role')
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            if ($rows !== []) {
                $roles = $rows;
            }
        }

        if (! $db->tableExists('menu_akses')) {
            return array_map(static function (array $role): array {
                $role['menu_count'] = 0;
                return $role;
            }, $roles);
        }

        $roleColumn = $this->menuAccessRoleColumn($db);

        foreach ($roles as &$role) {
            $role['menu_count'] = (int) $db->table('menu_akses')
                ->where($roleColumn, $role['role_id'])
                ->countAllResults();
        }
        unset($role);

        return $roles;
    }

    private function resolveMenuAccessRole(int $roleId): ?array
    {
        foreach ($this->getMenuAccessRoles() as $role) {
            if ((int) ($role['role_id'] ?? 0) === $roleId) {
                return $role;
            }
        }

        return null;
    }

    private function buildMenuAccessRows(int $roleId): array
    {
        $db = db_connect();
        $roleColumn = $this->menuAccessRoleColumn($db);
        $lv1Rows = (new MenuLv1Model())
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
        $lv2Rows = (new MenuLv2Model())
            ->orderBy('header', 'ASC')
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
        $lv3Rows = (new MenuLv3Model())
            ->orderBy('header', 'ASC')
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $accessRows = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->get()
            ->getResultArray();

        $accessMap = [];
        foreach ($accessRows as $row) {
            $menuId = (string) ($row['menu_id'] ?? '');
            if ($menuId === '') {
                continue;
            }

            $accessMap[$menuId] = [
                'akses' => true,
                'add' => (int) ($row['FiturAdd'] ?? 0) === 1,
                'edit' => (int) ($row['FiturEdit'] ?? 0) === 1,
                'delete' => (int) ($row['FiturDelete'] ?? 0) === 1,
                'approval' => (int) ($row['FiturApproval'] ?? 0) === 1,
                'export' => (int) ($row['FiturExport'] ?? 0) === 1,
                'import' => (int) ($row['FiturImport'] ?? 0) === 1,
            ];
        }

        $lv2ByParent = [];
        foreach ($lv2Rows as $row) {
            $lv2ByParent[(string) $row['header']][] = $row;
        }

        $lv3ByParent = [];
        foreach ($lv3Rows as $row) {
            $lv3ByParent[(string) $row['header']][] = $row;
        }

        $rows = [];
        foreach ($lv1Rows as $row) {
            $menuId = (string) $row['id'];
            $rows[] = $this->buildAccessRow($menuId, (string) $row['label'], 1, $accessMap[$menuId] ?? []);

            foreach ($lv2ByParent[$menuId] ?? [] as $child) {
                $childId = (string) $child['id'];
                $rows[] = $this->buildAccessRow($childId, (string) $child['label'], 2, $accessMap[$childId] ?? []);

                foreach ($lv3ByParent[$childId] ?? [] as $grand) {
                    $grandId = (string) $grand['id'];
                    $rows[] = $this->buildAccessRow($grandId, (string) $grand['label'], 3, $accessMap[$grandId] ?? []);
                }
            }
        }

        return $rows;
    }

    private function menuAccessRoleColumn($db): string
    {
        return $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
    }

    private function isSuperAdministratorRoleKey(string $roleKey): bool
    {
        $normalized = strtolower(trim($roleKey));

        return in_array($normalized, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function grantFullMenuAccessForRole(int $roleId): void
    {
        if ($roleId <= 0) {
            return;
        }

        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $this->menuAccessRoleColumn($db);
        $menuIds = [];

        if ($db->tableExists('menu_lv1')) {
            $menuIds = array_merge($menuIds, array_map(static fn (array $row): string => (string) $row['id'], (new MenuLv1Model())->select('id')->findAll()));
        }
        if ($db->tableExists('menu_lv2')) {
            $menuIds = array_merge($menuIds, array_map(static fn (array $row): string => (string) $row['id'], (new MenuLv2Model())->select('id')->findAll()));
        }
        if ($db->tableExists('menu_lv3')) {
            $menuIds = array_merge($menuIds, array_map(static fn (array $row): string => (string) $row['id'], (new MenuLv3Model())->select('id')->findAll()));
        }

        $menuIds = array_values(array_unique(array_filter($menuIds, static fn (string $id): bool => trim($id) !== '')));
        if ($menuIds === []) {
            return;
        }

        $rows = [];
        foreach ($menuIds as $menuId) {
            $rows[] = [
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => 1,
                'FiturEdit' => 1,
                'FiturDelete' => 1,
                'FiturExport' => 1,
                'FiturImport' => 1,
                'FiturApproval' => 1,
            ];
        }

        $db->transStart();
        $db->table('menu_akses')->where($roleColumn, $roleId)->delete();
        $db->table('menu_akses')->insertBatch($rows);
        $db->transComplete();
    }

    private function buildAccessRow(string $menuId, string $label, int $level, array $flags): array
    {
        return [
            'menu_id' => $menuId,
            'label' => $label,
            'level' => $level,
            'akses' => (bool) ($flags['akses'] ?? false),
            'add' => (bool) ($flags['add'] ?? false),
            'edit' => (bool) ($flags['edit'] ?? false),
            'delete' => (bool) ($flags['delete'] ?? false),
            'approval' => (bool) ($flags['approval'] ?? false),
            'export' => (bool) ($flags['export'] ?? false),
            'import' => (bool) ($flags['import'] ?? false),
        ];
    }
}
