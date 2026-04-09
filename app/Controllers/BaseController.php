<?php

namespace App\Controllers;

use App\Models\HomeSettingModel;
use App\Models\AppSettingModel;
use App\Models\MenuAksesModel;
use App\Models\MenuLv1Model;
use App\Models\MenuLv2Model;
use App\Models\MenuLv3Model;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $helpers = ['form', 'url'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        $defaults = [
            'official_name'        => 'Satker PPS Kementerian PU',
            'logo_url'             => '',
            'hero_title'           => 'Satker PPS Kementerian PU',
            'hero_subtitle'        => 'Mewujudkan infrastruktur yang terencana untuk Indonesia yang tangguh.',
            'about_intro'          => 'Unit kerja perencanaan prasarana strategis Kementerian Pekerjaan Umum.',
            'contact_email'        => 'info@satkerpps.pu.go.id',
            'contact_phone'        => '(0761) 000000',
            'contact_address'      => 'Pekanbaru, Riau',
            'contact_map_url'      => 'https://maps.google.com',
            'instagram_profile_url' => 'https://www.instagram.com/pu_prasaranastrategis_riau/',
            'instagram_post_urls'  => '',
            'default_event_image'  => '',
            'default_article_image'=> '',
        ];

        $globalSetting = $defaults;
        $appSetting = [
            'app_name'             => 'PLN EPM-Digi',
            'primary_color'        => '#0A66C2',
            'sidebar_bg_color'     => '#2F3A45',
            'sidebar_text_color'   => '#C2CBD5',
            'sidebar_active_bg_color' => '#0A66C2',
            'sidebar_active_text_color' => '#FFFFFF',
            'app_logo_url'         => '',
            'login_background_url' => '',
            'auto_logout_minutes'  => 60,
        ];

        try {
            $setting = (new HomeSettingModel())->first();
            if (is_array($setting)) {
                $globalSetting = array_merge($globalSetting, $setting);
            }
        } catch (\Throwable $e) {
            // Keep defaults when table does not exist yet.
        }

        try {
            $setting = (new AppSettingModel())->first();
            if (is_array($setting)) {
                $appSetting = array_merge($appSetting, $setting);
            }
        } catch (\Throwable $e) {
            // Keep defaults when table does not exist yet.
        }

        $adminSidebarMenus = [];
        $currentMenuPermissions = [
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
            'approval' => false,
        ];

        try {
            $role = (string) (session()->get('role') ?? '');
            $roleId = $this->resolveGroupIdByRole($role);
            $allowedMenuIds = null;

            $database = db_connect();

            if ($roleId !== null && $database->tableExists('menu_akses')) {
                $roleColumn = $database->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
                $aksesRows = (new MenuAksesModel())
                    ->select('menu_id')
                    ->where($roleColumn, $roleId)
                    ->findAll();

                $allowedMenuIds = array_map(static fn (array $row): string => (string) $row['menu_id'], $aksesRows);
            }

            $menuLv1Rows = (new MenuLv1Model())
                ->orderBy('ordering', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            $menuLv2Rows = (new MenuLv2Model())
                ->orderBy('ordering', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            $menuLv3Rows = (new MenuLv3Model())
                ->orderBy('ordering', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            $allowedMenuIds = $this->expandAllowedMenuIds($allowedMenuIds, $menuLv1Rows, $menuLv2Rows, $menuLv3Rows);

            $currentMenuPermissions = $this->resolveCurrentMenuPermissions($database, $roleId, $request, $menuLv1Rows, $menuLv2Rows, $menuLv3Rows);

            if (! empty($menuLv1Rows)) {
                $lv3ByHeader = [];
                foreach ($menuLv3Rows as $row) {
                    if ($allowedMenuIds !== null && ! in_array((string) $row['id'], $allowedMenuIds, true)) {
                        continue;
                    }
                    $lv3ByHeader[(string) $row['header']][] = [
                        'id'             => (string) $row['id'],
                        'label'          => (string) $row['label'],
                        'url'            => $row['link'],
                        'icon'           => 'far fa-dot-circle',
                        'active_pattern' => $this->toActivePattern($row['link']),
                        'children'       => [],
                    ];
                }

                $lv2ByHeader = [];
                foreach ($menuLv2Rows as $row) {
                    if ($allowedMenuIds !== null && ! in_array((string) $row['id'], $allowedMenuIds, true)) {
                        continue;
                    }
                    $menuId = (string) $row['id'];
                    $lv2ByHeader[(string) $row['header']][] = [
                        'id'             => $menuId,
                        'label'          => (string) $row['label'],
                        'url'            => $row['link'],
                        'icon'           => 'far fa-circle',
                        'active_pattern' => $this->toActivePattern($row['link']),
                        'children'       => $lv3ByHeader[$menuId] ?? [],
                    ];
                }

                foreach ($menuLv1Rows as $row) {
                    if ($allowedMenuIds !== null && ! in_array((string) $row['id'], $allowedMenuIds, true)) {
                        continue;
                    }

                    $menuId = (string) $row['id'];
                    $adminSidebarMenus[] = [
                        'id'             => $menuId,
                        'label'          => (string) $row['label'],
                        'url'            => $row['link'],
                        'icon'           => $row['icon'],
                        'active_pattern' => $this->toActivePattern($row['link']),
                        'children'       => $lv2ByHeader[$menuId] ?? [],
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Keep fallback menus when table does not exist yet.
        }

        if (empty($adminSidebarMenus)) {
            $adminSidebarMenus = [
                [
                    'label'          => 'Dashboard',
                    'url'            => '/admin',
                    'icon'           => 'fas fa-gauge-high',
                    'active_pattern' => 'admin',
                    'children'       => [],
                ],
                [
                    'label'          => 'Halaman Utama PPS',
                    'url'            => null,
                    'icon'           => 'fas fa-house',
                    'active_pattern' => null,
                    'children'       => [
                        [
                            'label'          => 'Pengaturan Halaman',
                            'url'            => '/admin/pengaturan-home',
                            'icon'           => 'far fa-circle',
                            'active_pattern' => 'admin/pengaturan-home',
                            'children'       => [],
                        ],
                        [
                            'label'          => 'Kelola Acara',
                            'url'            => '/admin/acara',
                            'icon'           => 'far fa-circle',
                            'active_pattern' => 'admin/acara*',
                            'children'       => [],
                        ],
                        [
                            'label'          => 'Kelola Berita',
                            'url'            => '/admin/berita',
                            'icon'           => 'far fa-circle',
                            'active_pattern' => 'admin/berita*',
                            'children'       => [],
                        ],
                    ],
                ],
                [
                    'label'          => 'Pengaturan',
                    'url'            => null,
                    'icon'           => 'fas fa-gear',
                    'active_pattern' => null,
                    'children'       => [
                        [
                            'label'          => 'Application',
                            'url'            => '/admin/pengaturan/application',
                            'icon'           => 'far fa-circle',
                            'active_pattern' => 'admin/pengaturan/application',
                            'children'       => [],
                        ],
                        [
                            'label'          => 'Menus',
                            'url'            => '/admin/pengaturan/menus',
                            'icon'           => 'far fa-circle',
                            'active_pattern' => 'admin/pengaturan/menus*',
                            'children'       => [],
                        ],
                    ],
                ],
            ];
        }

        service('renderer')->setVar('globalSetting', $globalSetting);
        service('renderer')->setVar('appSetting', $appSetting);
        service('renderer')->setVar('adminSidebarMenus', $adminSidebarMenus);
        service('renderer')->setVar('currentMenuPermissions', $currentMenuPermissions);
    }

    private function resolveGroupIdByRole(string $role): ?int
    {
        $normalizedRole = strtolower(trim($role));

        if ($normalizedRole === '') {
            return null;
        }

        try {
            $db = db_connect();
            if ($db->tableExists('access_roles')) {
                $variants = [$normalizedRole];
                if (strpos($normalizedRole, 'super') !== false) {
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
        } catch (\Throwable $e) {
            // Fallback below.
        }

        if ($normalizedRole === 'admin') {
            return 1;
        }

        if ($normalizedRole === 'editor') {
            return 2;
        }

        return null;
    }

    private function resolveCurrentMenuPermissions($database, ?int $roleId, RequestInterface $request, array $menuLv1Rows, array $menuLv2Rows, array $menuLv3Rows): array
    {
        $default = [
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
            'approval' => false,
        ];

        if ($roleId === null || ! $database->tableExists('menu_akses')) {
            return $default;
        }

        $path = trim(strtolower((string) $request->getUri()->getPath()), '/');
        if (strpos($path, 'admin') !== 0) {
            return $default;
        }

        $allLinks = [];
        foreach ([$menuLv1Rows, $menuLv2Rows, $menuLv3Rows] as $menuRows) {
            foreach ($menuRows as $row) {
                $link = trim(strtolower((string) ($row['link'] ?? '')), '/');
                if ($link !== '' && $link !== '#') {
                    $allLinks[$link] = (string) ($row['id'] ?? '');
                }
            }
        }

        $bestMenuId = null;
        $bestLength = -1;
        foreach ($allLinks as $link => $menuId) {
            if ($path === $link || strpos($path, $link . '/') === 0) {
                $length = strlen($link);
                if ($length > $bestLength) {
                    $bestLength = $length;
                    $bestMenuId = $menuId;
                }
            }
        }

        if ($bestMenuId === null || $bestMenuId === '') {
            return $default;
        }

        $roleColumn = $database->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $database->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $bestMenuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add' => (int) ($row['FiturAdd'] ?? 0) === 1,
            'edit' => (int) ($row['FiturEdit'] ?? 0) === 1,
            'delete' => (int) ($row['FiturDelete'] ?? 0) === 1,
            'export' => (int) ($row['FiturExport'] ?? 0) === 1,
            'import' => (int) ($row['FiturImport'] ?? 0) === 1,
            'approval' => (int) ($row['FiturApproval'] ?? 0) === 1,
        ];
    }

    private function toActivePattern(?string $link): ?string
    {
        if ($link === null) {
            return null;
        }

        $normalized = trim((string) $link, '/');

        if ($normalized === '' || $normalized === '#') {
            return null;
        }

        // Keep dashboard exact so it is not highlighted on every /admin/* page.
        if ($normalized === 'admin') {
            return 'admin';
        }

        return $normalized . '*';
    }

    private function expandAllowedMenuIds(?array $allowedMenuIds, array $menuLv1Rows, array $menuLv2Rows, array $menuLv3Rows): ?array
    {
        if ($allowedMenuIds === null) {
            return null;
        }

        $expanded = [];
        foreach ($allowedMenuIds as $menuId) {
            $expanded[(string) $menuId] = true;
        }

        $lv2Parents = [];
        foreach ($menuLv2Rows as $row) {
            $lv2Parents[(string) $row['id']] = (string) $row['header'];
        }

        $lv1Parents = [];
        foreach ($menuLv1Rows as $row) {
            $lv1Parents[(string) $row['id']] = true;
        }

        $lv3Parents = [];
        foreach ($menuLv3Rows as $row) {
            $lv3Parents[(string) $row['id']] = (string) $row['header'];
        }

        foreach (array_keys($expanded) as $menuId) {
            if (isset($lv2Parents[$menuId])) {
                $parentLv1 = $lv2Parents[$menuId];
                if ($parentLv1 !== '' && isset($lv1Parents[$parentLv1])) {
                    $expanded[$parentLv1] = true;
                }
            }

            if (isset($lv3Parents[$menuId])) {
                $parentLv2 = $lv3Parents[$menuId];
                if ($parentLv2 !== '' && isset($lv2Parents[$parentLv2])) {
                    $expanded[$parentLv2] = true;

                    $parentLv1 = $lv2Parents[$parentLv2];
                    if ($parentLv1 !== '' && isset($lv1Parents[$parentLv1])) {
                        $expanded[$parentLv1] = true;
                    }
                }
            }
        }

        return array_keys($expanded);
    }
}
