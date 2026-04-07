<?php

namespace App\Filters;

use Config\Database;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    private ?array $auditContext = null;

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/masuk')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $allowedRoles = [];
        if (is_array($arguments)) {
            foreach ($arguments as $argument) {
                foreach (explode(',', (string) $argument) as $role) {
                    $role = trim($role);
                    if ($role !== '') {
                        $allowedRoles[] = $role;
                    }
                }
            }
        }

        $userRole = $this->resolveCurrentUserRole();

        if ($allowedRoles !== []) {
            $effectiveAllowedRoles = $this->expandAllowedRoles($allowedRoles);
            if (! in_array($userRole, $effectiveAllowedRoles, true)) {
                return redirect()->to($this->forbiddenUrl($request));
            }
        }

        $path = trim((string) $request->getUri()->getPath(), '/');
        if (strpos($path, 'admin') === 0 && ! $this->canAccessMenuManagedPath($path, $userRole)) {
            return redirect()->to($this->forbiddenUrl($request));
        }

        if (strpos($path, 'admin') === 0 && ! $this->canAccessFeatureByRequest($request, $path, $userRole)) {
            return redirect()->to($this->forbiddenUrl($request));
        }

        $this->prepareAuditContext($request, $userRole);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $this->writeAuditLog($request, $response);
        return null;
    }

    private function canAccessMenuManagedPath(string $path, string $role): bool
    {
        $db = Database::connect();

        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $allLinks = $this->getAllMenuLinks($db);
        if ($allLinks === []) {
            return true;
        }

        if (! $this->matchesAnyMenuLink($path, $allLinks)) {
            // Path is not controlled by menu tables.
            return true;
        }

        $roleId = $this->resolveRoleId($db, $role);
        if ($roleId === null) {
            return false;
        }

        $allowedLinks = $this->getAllowedMenuLinksByRole($db, $roleId);
        if ($allowedLinks === []) {
            return false;
        }

        return $this->matchesAnyMenuLink($path, $allowedLinks);
    }

    private function matchesAnyMenuLink(string $path, array $links): bool
    {
        $normalizedPath = trim(strtolower($path), '/');
        foreach ($links as $link) {
            $normalizedLink = trim(strtolower((string) $link), '/');
            if ($normalizedLink === '') {
                continue;
            }

            if ($normalizedPath === $normalizedLink || strpos($normalizedPath, $normalizedLink . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    private function getAllMenuLinks($db): array
    {
        $links = [];
        foreach (['menu_lv1', 'menu_lv2', 'menu_lv3'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $rows = $db->table($table)
                ->select('link')
                ->where('link IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $link = trim((string) ($row['link'] ?? ''), '/');
                if ($link !== '' && $link !== '#') {
                    $links[$link] = $link;
                }
            }
        }

        return array_values($links);
    }

    private function getAllowedMenuLinksByRole($db, int $roleId): array
    {
        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $accessRows = $db->table('menu_akses')
            ->select('menu_id')
            ->where($roleColumn, $roleId)
            ->get()
            ->getResultArray();

        if ($accessRows === []) {
            return [];
        }

        $menuIds = array_values(array_unique(array_map(static fn (array $row): string => (string) ($row['menu_id'] ?? ''), $accessRows)));
        $menuIds = array_values(array_filter($menuIds, static fn (string $id): bool => trim($id) !== ''));
        if ($menuIds === []) {
            return [];
        }

        $links = [];
        foreach (['menu_lv1', 'menu_lv2', 'menu_lv3'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $rows = $db->table($table)
                ->select('id, link')
                ->whereIn('id', $menuIds)
                ->where('link IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $link = trim((string) ($row['link'] ?? ''), '/');
                if ($link !== '' && $link !== '#') {
                    $links[$link] = $link;
                }
            }
        }

        return array_values($links);
    }

    private function resolveRoleId($db, string $role): ?int
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

    private function resolveCurrentUserRole(): string
    {
        $sessionRole = strtolower(trim((string) session()->get('role')));
        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return $sessionRole;
        }

        try {
            $db = Database::connect();
            if (! $db->tableExists('users')) {
                return $sessionRole;
            }

            $row = $db->table('users')
                ->select('role')
                ->where('id', $userId)
                ->get()
                ->getRowArray();

            $dbRole = strtolower(trim((string) ($row['role'] ?? '')));
            if ($dbRole !== '' && $dbRole !== $sessionRole) {
                session()->set('role', $dbRole);
                return $dbRole;
            }
        } catch (\Throwable $e) {
            return $sessionRole;
        }

        return $sessionRole;
    }

    private function expandAllowedRoles(array $allowedRoles): array
    {
        $normalized = array_values(array_unique(array_map(static fn (string $role): string => strtolower(trim($role)), $allowedRoles)));

        if (in_array('admin', $normalized, true)) {
            $normalized[] = 'super administrator';
            $normalized[] = 'super_administrator';
            $normalized[] = 'super-admin';
            $normalized[] = 'superadmin';
        }

        return array_values(array_unique($normalized));
    }

    private function forbiddenUrl(RequestInterface $request): string
    {
        $path = trim((string) $request->getUri()->getPath(), '/');
        return '/forbidden?from=' . rawurlencode($path);
    }

    private function prepareAuditContext(RequestInterface $request, string $role): void
    {
        $path = trim((string) $request->getUri()->getPath(), '/');
        if (strpos($path, 'admin') !== 0) {
            return;
        }

        $action = $this->resolveAuditAction($request, $path);
        if ($action === null) {
            return;
        }

        $db = Database::connect();
        if (! $db->tableExists('audit_histories')) {
            return;
        }

        $target = $this->resolveAuditTarget($db, $request, $path);
        $before = null;
        if (is_array($target) && isset($target['table'], $target['pk'], $target['id']) && $target['table'] !== '' && $target['pk'] !== '' && $target['id'] !== '') {
            if ($db->tableExists($target['table'])) {
                $before = $db->table($target['table'])->where($target['pk'], $target['id'])->get()->getRowArray();
            }
        }

        $this->auditContext = [
            'action' => $action,
            'path' => $path,
            'role' => $role,
            'target' => $target,
            'before' => $before,
            'request' => $this->sanitizeRequestPayload((array) $request->getPost()),
            'user_id' => (int) session()->get('userId'),
            'username' => (string) session()->get('fullName') ?: (string) session()->get('username'),
        ];
    }

    private function writeAuditLog(RequestInterface $request, ResponseInterface $response): void
    {
        if (! is_array($this->auditContext)) {
            return;
        }

        try {
            $db = Database::connect();
            if (! $db->tableExists('audit_histories')) {
                return;
            }

            $target = $this->auditContext['target'] ?? null;
            $after = null;
            if (($this->auditContext['action'] ?? null) === 'edit' && is_array($target) && isset($target['table'], $target['pk'], $target['id']) && $target['table'] !== '' && $target['pk'] !== '' && $target['id'] !== '') {
                if ($db->tableExists($target['table'])) {
                    $after = $db->table($target['table'])->where($target['pk'], $target['id'])->get()->getRowArray();
                }
            }

            $db->table('audit_histories')->insert([
                'action_type' => (string) ($this->auditContext['action'] ?? 'edit'),
                'module_path' => (string) ($this->auditContext['path'] ?? ''),
                'table_name' => is_array($target) ? (string) ($target['table'] ?? '') : null,
                'record_id' => is_array($target) ? (string) ($target['id'] ?? '') : null,
                'user_id' => (int) ($this->auditContext['user_id'] ?? 0),
                'username' => (string) ($this->auditContext['username'] ?? ''),
                'role' => (string) ($this->auditContext['role'] ?? ''),
                'ip_address' => (string) ($request->getIPAddress() ?? ''),
                'user_agent' => substr((string) $request->getUserAgent(), 0, 255),
                'request_data_json' => json_encode($this->auditContext['request'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'before_data_json' => json_encode($this->auditContext['before'] ?? null, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'after_data_json' => json_encode($after, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'happened_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Do not break request flow.
        } finally {
            $this->auditContext = null;
        }
    }

    private function resolveAuditAction(RequestInterface $request, string $path): ?string
    {
        $method = strtoupper((string) $request->getMethod());
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        $normalized = trim(strtolower($path), '/');
        if (strpos($normalized, '/hapus') !== false || strpos($normalized, '/delete') !== false) {
            return 'delete';
        }

        if (strpos($normalized, '/ubah') !== false || strpos($normalized, '/update') !== false || strpos($normalized, '/status') !== false || strpos($normalized, '/save') !== false) {
            return 'edit';
        }

        return null;
    }

    private function resolveAuditTarget($db, RequestInterface $request, string $path): ?array
    {
        $normalized = trim(strtolower($path), '/');
        $mappings = [
            'admin/master/kop-surat' => ['table' => 'kop_surat', 'pk' => 'id'],
            'admin/utility/user' => ['table' => 'users', 'pk' => 'id'],
            'admin/laporan/harian/sekolah' => ['table' => 'laporan_sekolah', 'pk' => 'id'],
            'admin/laporan/harian' => ['table' => 'laporan_harian_reports', 'pk' => 'id'],
            'admin/laporan/mingguan' => ['table' => 'laporan_mingguan_reports', 'pk' => 'id'],
            'admin/kontrak/paket' => ['table' => 'trn_kontrak_paket', 'pk' => 'id'],
            'admin/kontrak/ki' => ['table' => 'trn_kontrak_ki', 'pk' => 'id'],
            'admin/acara' => ['table' => 'events', 'pk' => 'id'],
            'admin/berita' => ['table' => 'articles', 'pk' => 'id'],
            'admin/slide' => ['table' => 'home_slides', 'pk' => 'id'],
            'admin/pengaturan-home' => ['table' => 'home_settings', 'pk' => 'id'],
            'admin/pengaturan/application' => ['table' => 'app_settings', 'pk' => 'id'],
            'admin/pengaturan/menus' => ['table' => 'menu_dynamic', 'pk' => 'id'],
        ];

        $table = null;
        $pk = 'id';
        $prefixLength = -1;
        foreach ($mappings as $prefix => $map) {
            if ($normalized === $prefix || strpos($normalized, $prefix . '/') === 0) {
                $len = strlen($prefix);
                if ($len > $prefixLength) {
                    $prefixLength = $len;
                    $table = (string) ($map['table'] ?? '');
                    $pk = (string) ($map['pk'] ?? 'id');
                }
            }
        }

        $id = null;
        if (preg_match('#/([a-z0-9_\-]+)/(?:ubah|hapus|delete|status)$#i', $normalized, $m)) {
            $id = $m[1];
        } elseif (preg_match('#/kontrak/ki/(\d+)/(\d+)/ubah$#', $normalized, $m)) {
            $id = $m[2];
        } else {
            $post = (array) $request->getPost();
            foreach (['id', 'report_id', 'user_id'] as $key) {
                if (! isset($post[$key])) {
                    continue;
                }

                $value = trim((string) $post[$key]);
                if ($value !== '') {
                    $id = $value;
                    break;
                }
            }
        }

        if ($table === null || $table === '') {
            return null;
        }

        if ($table === 'menu_dynamic') {
            $table = $this->resolveMenuTableFromId($id);
            if ($table === null) {
                return null;
            }
        }

        if (($table === 'home_settings' || $table === 'app_settings') && ($id === null || $id === '')) {
            $id = '1';
        }

        return [
            'table' => $table,
            'pk' => $pk,
            'id' => $id,
        ];
    }

    private function resolveMenuTableFromId(?string $id): ?string
    {
        $id = trim((string) $id);
        if ($id === '') {
            return null;
        }

        $parts = explode('-', $id);
        $partCount = count($parts);

        if ($partCount <= 1) {
            return 'menu_lv1';
        }

        if ($partCount === 2) {
            return 'menu_lv2';
        }

        return 'menu_lv3';
    }

    private function sanitizeRequestPayload(array $payload): array
    {
        $sensitive = ['password', 'password_hash', 'old_password', 'new_password', 'confirm_password'];
        foreach ($sensitive as $key) {
            if (array_key_exists($key, $payload)) {
                $payload[$key] = '***';
            }
        }

        return $payload;
    }

    private function canAccessFeatureByRequest(RequestInterface $request, string $path, string $role): bool
    {
        $requiredFeature = $this->resolveRequiredFeature($request, $path);
        if ($requiredFeature === null) {
            return true;
        }

        $db = Database::connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $allLinks = $this->getAllMenuLinks($db);
        $matchedLink = $this->findMatchedMenuLink($path, $allLinks);
        if ($matchedLink === null) {
            return true;
        }

        $menuId = $this->resolveMenuIdByLink($db, $matchedLink);
        if ($menuId === null) {
            return false;
        }

        $roleId = $this->resolveRoleId($db, $role);
        if ($roleId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return false;
        }

        $column = match ($requiredFeature) {
            'add' => 'FiturAdd',
            'edit' => 'FiturEdit',
            'delete' => 'FiturDelete',
            'export' => 'FiturExport',
            'import' => 'FiturImport',
            'approval' => 'FiturApproval',
            default => null,
        };

        if ($column === null) {
            return true;
        }

        return (int) ($row[$column] ?? 0) === 1;
    }

    private function resolveRequiredFeature(RequestInterface $request, string $path): ?string
    {
        $method = strtoupper((string) $request->getMethod());
        $normalized = trim(strtolower($path), '/');

        if ($method === 'GET' || $method === 'HEAD' || $method === 'OPTIONS') {
            return null;
        }

        if (strpos($normalized, '/export') !== false) {
            return 'export';
        }

        if (strpos($normalized, '/import') !== false) {
            return 'import';
        }

        if (strpos($normalized, '/approval') !== false || strpos($normalized, '/approve') !== false) {
            return 'approval';
        }

        if (strpos($normalized, '/hapus') !== false || strpos($normalized, '/delete') !== false) {
            return 'delete';
        }

        if (strpos($normalized, '/tambah') !== false || strpos($normalized, '/create') !== false) {
            return 'add';
        }

        if (strpos($normalized, '/ubah') !== false || strpos($normalized, '/update') !== false || strpos($normalized, '/status') !== false || strpos($normalized, '/save') !== false) {
            return 'edit';
        }

        // Default POST/PUT/PATCH to edit permission.
        return 'edit';
    }

    private function findMatchedMenuLink(string $path, array $links): ?string
    {
        $normalizedPath = trim(strtolower($path), '/');
        $bestMatch = null;
        $bestLength = -1;

        foreach ($links as $link) {
            $normalizedLink = trim(strtolower((string) $link), '/');
            if ($normalizedLink === '') {
                continue;
            }

            if ($normalizedPath === $normalizedLink || strpos($normalizedPath, $normalizedLink . '/') === 0) {
                $length = strlen($normalizedLink);
                if ($length > $bestLength) {
                    $bestLength = $length;
                    $bestMatch = $normalizedLink;
                }
            }
        }

        return $bestMatch;
    }

    private function resolveMenuIdByLink($db, string $link): ?string
    {
        $normalizedLink = trim(strtolower($link), '/');
        if ($normalizedLink === '') {
            return null;
        }

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', $normalizedLink)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
