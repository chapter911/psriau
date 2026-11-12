<?php

namespace App\Services;

use Config\Database;

class ApiAuth
{
    private static ?array $user = null;
    private static ?string $token = null;

    public static function setUser(array $user): void
    {
        self::$user = $user;
    }

    public static function getUser(): ?array
    {
        return self::$user;
    }

    public static function setToken(string $token): void
    {
        self::$token = $token;
    }

    public static function getToken(): ?string
    {
        return self::$token;
    }

    /**
     * Memeriksa hak akses user API terhadap menu berdasarkan tabel `menu_akses`.
     *
     * @param string|string[] $menuLinks
     */
    public static function hasMenuAccess(string|array $menuLinks, string $feature = 'view'): bool
    {
        $user = self::$user;
        if (! is_array($user)) {
            return false;
        }

        $role = strtolower(trim((string) ($user['role'] ?? '')));
        if ($role === 'superadmin' || $role === 'super_admin' || $role === 'super administrator') {
            return true;
        }

        $db = Database::connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $links = is_array($menuLinks) ? $menuLinks : [$menuLinks];
        $links = array_values(array_filter(array_map(
            static fn (string $link): string => trim($link, '/'),
            $links
        )));

        if ($links === []) {
            return true;
        }

        $menuId = self::resolveMenuIdByLinks($db, $links);
        if ($menuId === null) {
            return true;
        }

        $roleId = self::resolveRoleId($db, $role);
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

        $column = match (strtolower($feature)) {
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

    private static function resolveMenuIdByLinks($db, array $links): ?string
    {
        foreach (['menu_lv1', 'menu_lv2', 'menu_lv3'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            foreach ($links as $link) {
                $row = $db->table($table)
                    ->select('id')
                    ->groupStart()
                        ->where('link', $link)
                        ->orWhere('link', '/' . $link)
                    ->groupEnd()
                    ->get()
                    ->getRowArray();

                if (is_array($row) && isset($row['id'])) {
                    return (string) $row['id'];
                }
            }
        }

        return null;
    }

    private static function resolveRoleId($db, string $role): ?int
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
}
