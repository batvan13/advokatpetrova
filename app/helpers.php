<?php

use App\Models\PageSection;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
            $page === 'home' && $section === 'about_preview'
                => 'Начална страница — За нас (кратко представяне) · Само за началната страница (/), не за отделната страница „За нас“. Кратък teaser. Качи снимка за split layout.',
            default
                => admin_page_section_page_label($page).' — '.admin_page_section_section_label($section),
        };
    }
}
