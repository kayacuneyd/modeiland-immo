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
            // Türkçe
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's',
            'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ş' => 's',
            // Almanca (ö, ü, Ö, Ü TR ile örtüşür — aynı dönüşüm)
            'ä' => 'a', 'Ä' => 'a', 'ß' => 'ss',
            // Ortak TR+DE
            'ö' => 'o', 'ü' => 'u', 'Ö' => 'o', 'Ü' => 'u',
        ];

        $text = strtr($text, $trMap);
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);

        return trim($text, '-');
    }
}
