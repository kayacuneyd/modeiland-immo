<?php

namespace App\Core\Modules;

use App\Core\Auth\AuthService;

class ModuleRegistry
{
    /**
     * @var list<array<string, mixed>>|null
     */
    private static ?array $modules = null;

    /**
     * @return list<array<string, mixed>>
     */
    public static function modules(): array
    {
        if (self::$modules !== null) {
            return self::$modules;
        }

        $modules = [];
        foreach (glob(APPPATH . 'Modules/*/module.json') ?: [] as $manifestPath) {
            $moduleDir = dirname($manifestPath);
            $manifest  = self::readManifest($manifestPath);

            if (($manifest['enabled'] ?? true) === false) {
                continue;
            }

            $manifest['name'] = $manifest['name'] ?? basename($moduleDir);
            $manifest['slug'] = $manifest['slug'] ?? strtolower((string) $manifest['name']);
            $manifest['path'] = $moduleDir;

            $modules[] = $manifest;
        }

        usort($modules, static fn (array $a, array $b): int => strcasecmp((string) $a['name'], (string) $b['name']));

        return self::$modules = $modules;
    }

    /**
     * @return list<string>
     */
    public static function routeFiles(): array
    {
        $modules = array_filter(
            self::modules(),
            static fn (array $module): bool => isset($module['routes']) && is_file($module['path'] . '/' . $module['routes'])
        );

        usort($modules, static function (array $a, array $b): int {
            $priority = ((int) ($a['routePriority'] ?? 50)) <=> ((int) ($b['routePriority'] ?? 50));

            return $priority !== 0 ? $priority : strcasecmp((string) $a['name'], (string) $b['name']);
        });

        return array_map(static fn (array $module): string => $module['path'] . '/' . $module['routes'], $modules);
    }

    /**
     * @return list<array{href: string, label: string, icon: string, order: int}>
     */
    public static function adminNav(?AuthService $auth = null): array
    {
        $items = self::coreAdminNav();

        foreach (self::modules() as $module) {
            if (! isset($module['adminMenu']) || ! is_array($module['adminMenu'])) {
                continue;
            }

            $item = self::normalizeAdminMenu($module['adminMenu']);
            if ($item === null) {
                continue;
            }

            if (isset($module['adminMenu']['permission']) && $auth !== null && ! $auth->can((string) $module['adminMenu']['permission'])) {
                continue;
            }

            $items[] = $item;
        }

        usort($items, static function (array $a, array $b): int {
            $order = $a['order'] <=> $b['order'];

            return $order !== 0 ? $order : strcasecmp($a['label'], $b['label']);
        });

        return $items;
    }

    /**
     * @return list<array{href: string, label: string, icon: string, order: int}>
     */
    private static function coreAdminNav(): array
    {
        return [
            [
                'href'  => site_url('admin/dashboard'),
                'label' => lang('Common.nav_dashboard'),
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'order' => 0,
            ],
            [
                'href'  => site_url('admin/media'),
                'label' => lang('Common.nav_media'),
                'icon'  => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                'order' => 900,
            ],
            [
                'href'  => site_url('admin/settings'),
                'label' => lang('Common.nav_settings'),
                'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'order' => 910,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $menu
     *
     * @return array{href: string, label: string, icon: string, order: int}|null
     */
    private static function normalizeAdminMenu(array $menu): ?array
    {
        if (! isset($menu['url'], $menu['icon']) || (! isset($menu['label']) && ! isset($menu['labelKey']))) {
            return null;
        }

        $label = isset($menu['labelKey'])
            ? lang((string) $menu['labelKey'])
            : (string) $menu['label'];

        return [
            'href'  => site_url((string) $menu['url']),
            'label' => $label,
            'icon'  => (string) $menu['icon'],
            'order' => (int) ($menu['order'] ?? 500),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function readManifest(string $path): array
    {
        $json = file_get_contents($path);
        if ($json === false) {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}
