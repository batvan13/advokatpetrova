<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        // Safe fallbacks if settings are not yet populated
        $siteName    = setting('site_name')    ?: config('app.name', 'Website');
        $siteTagline = setting('site_tagline') ?: '';

        // ── <title> ──────────────────────────────────────────────────────
        // 1. @section('title') + " | " + site_name
        // 2. site_name alone (if no title section)
        $pageTitle = trim($__env->yieldContent('title'));
        $metaTitle = $pageTitle ? $pageTitle . ' | ' . $siteName : $siteName;

        // ── meta description ─────────────────────────────────────────────
        // 1. @section('description')
        // 2. site_tagline
        // 3. '' (safe — no broken content attribute)
        $metaDesc = trim($__env->yieldContent('description')) ?: $siteTagline;

        // ── og:title ─────────────────────────────────────────────────────
        // 1. @section('og_title')       — explicit OG-only override
        // 2. @section('title')          — page title without " | site_name"
        // 3. site_name
        $ogTitle = trim($__env->yieldContent('og_title'))
                ?: ($pageTitle ?: $siteName);

        // ── og:description ───────────────────────────────────────────────
        // 1. @section('og_description') — explicit OG-only override
        // 2. @section('description')    — same as meta description
        // 3. site_tagline
        $ogDesc = trim($__env->yieldContent('og_description'))
               ?: $metaDesc;

        // ── og:image ─────────────────────────────────────────────────────
        // 1. @section('og_image')       — Blade-level override
        // 2. $seoOgImage                — controller-passed variable
        // 3. global fallback image
        $ogImage = trim($__env->yieldContent('og_image'))
                ?: ($seoOgImage ?? null)
                ?: asset('images/shared/og-default.jpg');

        // ── canonical ────────────────────────────────────────────────────
        // Always the current request URL — no override needed
        $canonical = url()->current();
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description"      content="{{ $metaDesc }}">
    <meta name="robots"           content="index,follow">
    <link rel="canonical"         href="{{ $canonical }}">

    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="{{ $siteName }}">
    <meta property="og:url"         content="{{ $canonical }}">
    <meta property="og:title"       content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDesc }}">
    @if ($ogImage)
    <meta property="og:image"       content="{{ $ogImage }}">
    @endif

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" sizes="any">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-white font-sans text-gray-900 antialiased">

@include('components.header')

<main>
    @yield('content')
</main>

@include('components.footer')

@include('components.cookie-consent')

@stack('scripts')
</body>
</html>
