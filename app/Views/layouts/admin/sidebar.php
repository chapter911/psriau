<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <?php $appLogo = $globalSetting['logo_url'] ?? ''; ?>
    <a href="<?= site_url('/admin'); ?>" class="brand-link d-flex align-items-center">
        <?php if (! empty($appLogo)): ?>
            <img src="<?= esc($appLogo); ?>" alt="Logo <?= esc($globalSetting['official_name'] ?? 'Satker PPS Kementerian PU'); ?>" class="brand-image-custom">
        <?php else: ?>
            <span class="brand-fallback">SP</span>
        <?php endif; ?>
        <span class="brand-text font-weight-light">Panel Admin</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <?php
                $path = trim(uri_string(), '/');
                $sidebarMenus = $adminSidebarMenus ?? [];

                $menuUrlResolver = static function (?string $url): string {
                    if ($url === null || $url === '') {
                        return '#';
                    }

                    if (preg_match('/^https?:\/\//i', $url) === 1) {
                        return $url;
                    }

                    return site_url('/' . ltrim($url, '/'));
                };

                $matchesPattern = static function (?string $pattern, string $currentPath): bool {
                    if ($pattern === null || $pattern === '') {
                        return false;
                    }

                    $normalizedPattern = trim($pattern, '/');

                    if (str_ends_with($normalizedPattern, '*')) {
                        $prefix = rtrim(substr($normalizedPattern, 0, -1), '/');
                        if ($prefix === '') {
                            return false;
                        }

                        if ($currentPath === $prefix) {
                            return true;
                        }

                        return strpos($currentPath, $prefix . '/') === 0;
                    }

                    return $currentPath === $normalizedPattern;
                };

                $hasActiveChild = static function (array $children, string $currentPath) use (&$hasActiveChild, $matchesPattern): bool {
                    foreach ($children as $child) {
                        if ($matchesPattern($child['active_pattern'] ?? null, $currentPath)) {
                            return true;
                        }

                        if (! empty($child['children']) && $hasActiveChild($child['children'], $currentPath)) {
                            return true;
                        }
                    }

                    return false;
                };

                $renderMenuItems = static function (array $menus, bool $isSubmenu = false) use (&$renderMenuItems, $path, $menuUrlResolver, $matchesPattern, $hasActiveChild): void {
                    $ulClass = $isSubmenu ? 'nav nav-treeview' : 'nav nav-pills nav-sidebar flex-column';
                    $ulAttrs = $isSubmenu ? '' : ' data-widget="treeview" role="menu" data-accordion="false"';

                    echo '<ul class="' . $ulClass . '"' . $ulAttrs . '>';

                    foreach ($menus as $menu) {
                        $children = $menu['children'] ?? [];
                        $selfActive = $matchesPattern($menu['active_pattern'] ?? null, $path);
                        $childActive = ! empty($children) && $hasActiveChild($children, $path);
                        $isExpanded = $selfActive || $childActive;
                        $isTree = ! empty($children);
                        $menuIcon = trim((string) ($menu['icon'] ?? '')) !== '' ? $menu['icon'] : ($isSubmenu ? 'far fa-circle' : 'fas fa-circle');

                        if ($isTree) {
                            echo '<li class="nav-item has-treeview ' . ($isExpanded ? 'menu-open' : '') . '">';
                            echo '<a href="' . esc($menuUrlResolver($menu['url'] ?? null)) . '" class="nav-link ' . ($selfActive ? 'active' : '') . '">';
                            echo '<i class="nav-icon ' . esc($menuIcon) . '"></i>';
                            echo '<p>' . esc((string) ($menu['label'] ?? 'Menu')) . '<i class="right fas fa-angle-left"></i></p>';
                            echo '</a>';
                            $renderMenuItems($children, true);
                            echo '</li>';

                            continue;
                        }

                        echo '<li class="nav-item">';
                        echo '<a href="' . esc($menuUrlResolver($menu['url'] ?? null)) . '" class="nav-link ' . ($selfActive ? 'active' : '') . '">';
                        echo '<i class="nav-icon ' . esc($menuIcon) . '"></i>';
                        echo '<p>' . esc((string) ($menu['label'] ?? 'Menu')) . '</p>';
                        echo '</a>';
                        echo '</li>';
                    }

                    echo '</ul>';
                };
            ?>
            <?php $renderMenuItems($sidebarMenus); ?>
        </nav>
    </div>
</aside>
