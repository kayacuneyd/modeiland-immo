<?php

use App\Core\Settings\SettingsService;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        static $service = null;
        if ($service === null) {
            $service = new SettingsService();
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('component')) {
    function component(string $name, array $data = []): string
    {
        return view('web/components/' . $name, $data);
    }
}

if (! function_exists('admin_component')) {
    function admin_component(string $name, array $data = []): string
    {
        return view('admin/components/' . $name, $data);
    }
}

if (! function_exists('seo_tags')) {
    function seo_tags(array $seoData = []): string
    {
        return view('web/components/seo', ['seo' => $seoData]);
    }
}

if (! function_exists('media_url')) {
    function media_url(array $row, string $size = 'original'): string
    {
        $service = new \App\Core\Media\MediaService();
        return $service->getUrl($row, $size);
    }
}

if (! function_exists('slug')) {
    function slug(string $text): string
    {
        $trMap = [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u',
            'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'Ä' => 'a', 'Ö' => 'o', 'Ü' => 'u', 'ß' => 'ss',
        ];

        $text = strtr($text, $trMap);
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);

        return trim($text, '-');
    }
}
