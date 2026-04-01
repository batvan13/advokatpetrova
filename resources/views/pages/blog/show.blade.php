@extends('layouts.app')

@section('title', $post->title)
@section('description', $seoDescription)

@section('content')

    <div class="border-b border-petrova-deep/10 bg-petrova-primary">
        <div class="mx-auto max-w-6xl px-4 py-4">
            <nav aria-label="Блог">
                <a href="{{ route('blog') }}"
                   class="text-sm font-medium text-petrova-mid underline-offset-2 transition-colors hover:text-petrova-deep hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/35 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-primary rounded-sm">
                    ← Всички публикации
                </a>
            </nav>
        </div>
    </div>

    <article class="border-t border-petrova-deep/10 bg-petrova-primary py-16">
        <div class="mx-auto max-w-3xl px-4">

            <header class="border-b border-petrova-deep/10 pb-10">
                <p class="text-xs font-medium uppercase tracking-wide text-petrova-secondary">
                    {{ $post->published_at->format('d.m.Y H:i') }}
                </p>
                <h1 class="mt-3 font-playfair text-3xl font-bold tracking-tight text-petrova-deep md:text-4xl">
                    {{ $post->title }}
                </h1>
                @if ($post->excerpt)
                    <p class="mt-6 text-lg leading-relaxed text-petrova-mid whitespace-pre-line">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </header>

            @if ($post->featured_image)
                <div class="py-10">
                    <img src="{{ asset('storage/'.$post->featured_image) }}"
                         alt="{{ $post->title }}"
                         class="w-full max-h-[28rem] rounded-xl border border-petrova-deep/10 object-cover">
                </div>
            @endif

            @php
                $blogBody = (string) $post->content;
                $blogBodyIsHtml = (bool) preg_match('/<[a-z][^>]*>/i', $blogBody);
            @endphp

            @if ($blogBodyIsHtml)
                <div class="py-8 text-base leading-relaxed text-petrova-deep/90
                    [&_p]:mt-4 [&_p:first-child]:mt-0
                    [&_h2]:mt-8 [&_h2]:font-playfair [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-petrova-deep
                    [&_h3]:mt-6 [&_h3]:font-playfair [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:text-petrova-deep
                    [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5
                    [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5
                    [&_li]:mt-1
                    [&_a]:font-medium [&_a]:text-petrova-deep [&_a]:underline [&_a]:underline-offset-2 [&_a]:transition-colors hover:[&_a]:text-petrova-gold-hover
                    [&_strong]:font-semibold [&_strong]:text-petrova-deep
                    [&_blockquote]:mt-4 [&_blockquote]:border-l-4 [&_blockquote]:border-petrova-gold/45 [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-petrova-mid
                    [&_img]:my-6 [&_img]:block [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-xl [&_img]:border [&_img]:border-petrova-deep/10
                    [&_iframe]:my-6 [&_iframe]:block [&_iframe]:w-full [&_iframe]:max-w-full [&_iframe]:rounded-lg [&_iframe]:border [&_iframe]:border-petrova-deep/10">
                    {!! $blogBody !!}
                </div>
            @else
                <div class="py-8 text-base leading-relaxed text-petrova-deep/90 whitespace-pre-line">
                    {{ $blogBody }}
                </div>
            @endif

        </div>
    </article>

@endsection

@push('scripts')
@php
    $siteName = setting('site_name') ?: config('app.name', 'Website');
    $jsonDesc = trim(preg_replace('/\s+/', ' ', strip_tags(
        $post->excerpt ?: $post->content ?: $post->title
    )));
    $jsonDesc = \Illuminate\Support\Str::limit($jsonDesc, 500, '');
    $blogLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $jsonDesc !== '' ? $jsonDesc : null,
        'datePublished' => $post->published_at?->toIso8601String(),
        'url' => route('blog.show', $post->slug),
        'image' => $seoOgImage,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
        ],
    ]);
@endphp
<script type="application/ld+json">{!! json_encode($blogLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
