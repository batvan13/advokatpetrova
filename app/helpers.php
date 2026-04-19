<?php

use App\Models\PageSection;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('upload_url')) {

    /**
     * Return the public URL for a file stored by GalleryImageProcessor.
     *
     * Locally falls back to the standard Storage public disk URL (/storage/...).
     * On production, reads UPLOAD_PUBLIC_URL from .env so no symlink is required.
     *
     * Usage in Blade: {{ upload_url($model->image_field) }}
     */
    function upload_url(?string $path): ?string
    {
        return \App\Support\GalleryImageProcessor::url($path);
    }

}

if (! function_exists('setting')) {

    /**
     * Get a site setting value by key.
     * Results are cached statically for the duration of the request.
     *
     * Usage: setting('site_name')  /  setting('site_name', 'Default')
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $cache = null;

        if ($cache === null) {
            $cache = SiteSetting::pluck('value', 'key');
        }

        return $cache->get($key, $default);
    }

}

if (! function_exists('section_url')) {

    /**
     * Resolve a CMS section button URL for safe use in href attributes.
     *
     * Handles three formats stored in the database:
     *   "contacts"   — bare route name  → route('contacts')
     *   "/contacts"  — legacy path      → route('contacts')  (single-segment only)
     *   "https://…"  — full URL         → returned as-is
     *
     * Using route() means the URL is always absolute and correct in both
     * subfolder installs (localhost/template/public) and standard domains.
     */
    function section_url(string $url): string
    {
        // Bare route name: "contacts", "services", "gallery"
        if (Route::has($url)) {
            return route($url);
        }

        // Legacy single-segment path: "/contacts" → try "contacts" as route name
        if (preg_match('#^/([a-z0-9_-]+)$#', $url, $m) && Route::has($m[1])) {
            return route($m[1]);
        }

        // Full URLs, external links, or unrecognised paths — return unchanged.
        return $url;
    }

}

if (! function_exists('page_section')) {

    function page_section(string $page, string $section): ?PageSection
    {
        static $sections = null;

        if ($sections === null) {
            $sections = PageSection::all()
                ->keyBy(fn ($item) => $item->page . '.' . $item->section);
        }

        return $sections[$page . '.' . $section] ?? null;
    }

}

if (! function_exists('admin_page_section_page_label')) {

    /**
     * Human-readable admin label for a PageSection "page" key (Bulgarian where mapped).
     */
    function admin_page_section_page_label(string $page): string
    {
        return match ($page) {
            'home' => 'Начало',
            'about' => 'За нас',
            'services' => 'Услуги',
            'gallery' => 'Галерия',
            'contacts' => 'Контакти',
            'consultation' => 'Онлайн консултация',
            'practice' => 'Моята практика',
            'terms' => 'Общи условия',
            'cookie_policy' => 'Политика за бисквитки',
            'privacy' => 'Политика за поверителност',
            default => Str::title(str_replace('_', ' ', $page)),
        };
    }
}

if (! function_exists('admin_page_section_section_label')) {

    /**
     * Human-readable admin label for a PageSection "section" key (Bulgarian where mapped).
     * Pass the optional $page to get page-specific overrides.
     */
    function admin_page_section_section_label(string $section, ?string $page = null): string
    {
        // Page-specific overrides take priority over generic section labels.
        if ($page === 'about' && $section === 'hero') {
            return 'Горен банер (hero)';
        }

        if ($page === 'about' && $section === 'content') {
            return 'Основно съдържание';
        }

        if ($page === 'about' && $section === 'team') {
            return 'Секция „Екип“ (заглавие и текст)';
        }

        if ($page === 'home' && $section === 'about_preview') {
            return 'Начална страница — За нас (кратко представяне)';
        }

        return match ($section) {
            'hero'             => 'Хедър',
            'content'          => 'Основен текст',
            'services_preview' => 'Преглед: услуги',
            'about_preview'    => 'За нас (кратко представяне)',
            'gallery_preview'  => 'Преглед: галерия',
            'contact_preview'  => 'Преглед: контакти',
            'faq'              => 'ЧЗВ',
            default            => Str::title(str_replace('_', ' ', $section)),
        };
    }
}

if (! function_exists('admin_page_section_caption')) {

    /**
     * Combined context line shown as subtitle in the admin section edit screen.
     * Specific page+section combos get a richer, editorial-friendly description.
     */
    function admin_page_section_caption(string $page, string $section): string
    {
        return match (true) {
            $page === 'about' && $section === 'hero'
                => 'За нас — горен банер на страницата',
            $page === 'about' && $section === 'content'
                => 'За нас — основно съдържание на страницата',
            $page === 'about' && $section === 'team'
                => 'За нас — заглавие и въведение за секцията „Екип“. Снимките и контактите на членовете се управляват от „Екип“ в админ менюто.',
            $page === 'home' && $section === 'about_preview'
                => 'Начална страница — За нас (кратко представяне) · Само за началната страница (/), не за отделната страница „За нас“. Кратък teaser. Качи снимка за split layout.',
            default
                => admin_page_section_page_label($page).' — '.admin_page_section_section_label($section),
        };
    }
}

/*
 * Maps helpers — contacts page
 * maps_iframe_src: само истински Google Maps embed URL (path съдържа /maps/embed)
 * maps_fallback_href: за други UI при нужда; contacts ползва само maps_iframe_src()
 */
if (! function_exists('maps_iframe_src')) {

    /**
     * Normalize a Google Maps URL for use as an iframe src. Returns null if unsafe or invalid.
     * Does not accept HTML or iframe markup — URL strings only.
     */
    function maps_iframe_src(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_contains($url, '<') || stripos($url, 'iframe') !== false) {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! is_string($scheme) || strtolower($scheme) !== 'https') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $hostLower = strtolower($host);
        $isGoogleHost = $hostLower === 'google.com' || str_ends_with($hostLower, '.google.com');
        if (! $isGoogleHost) {
            return null;
        }

        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
        if (str_contains($path, '/maps/embed')) {
            return $url;
        }

        return null;
    }
}

if (! function_exists('maps_fallback_href')) {

    /**
     * Safe href for “Open in Google Maps” when the value is not an embed URL.
     * Stricter than arbitrary strings: valid URL + known Maps-related hosts only.
     */
    function maps_fallback_href(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_contains($url, '<') || stripos($url, 'iframe') !== false) {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! is_string($scheme) || strtolower($scheme) !== 'https') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $hostLower = strtolower($host);
        $isGoogleHost = $hostLower === 'google.com' || str_ends_with($hostLower, '.google.com');
        $isGooGl = $hostLower === 'goo.gl' || str_ends_with($hostLower, '.goo.gl');

        if (! $isGoogleHost && ! $isGooGl) {
            return null;
        }

        return $url;
    }
}
