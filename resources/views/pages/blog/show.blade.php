@extends('layouts.app')

@section('title', $post->title)
@section('description', $seoDescription)

@section('content')

    <article>

        <div class="bg-white py-12">
            <div class="mx-auto max-w-6xl px-4">
                @if ($post->featured_image)
                    <img
                        src="{{ asset('storage/'.$post->featured_image) }}"
                        alt="{{ $post->title }}"
                        class="h-72 w-full rounded-xl object-cover md:h-96"
                    >
                @else
                    <div class="h-72 w-full rounded-xl bg-petrova-secondary/10 md:h-96" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        <div class="bg-white py-12">
            <div class="mx-auto max-w-3xl px-4 text-center">

                <h1 class="font-playfair text-3xl text-petrova-deep md:text-4xl">
                    {{ $post->title }}
                </h1>

                <div class="mt-4 text-sm text-petrova-secondary">
                    <a
                        href="{{ route('home') }}"
                        class="transition-colors hover:text-petrova-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/35 focus-visible:ring-offset-2 focus-visible:ring-offset-white rounded-sm"
                    >Начало</a>
                    <span class="mx-2" aria-hidden="true">/</span>
                    <a
                        href="{{ route('blog') }}"
                        class="transition-colors hover:text-petrova-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/35 focus-visible:ring-offset-2 focus-visible:ring-offset-white rounded-sm"
                    >Блог</a>
                </div>
            </div>
        </div>

        @php
            $shareUrl = urlencode(url()->current());
            $shareTitle = urlencode($post->title);
        @endphp

        <div class="border-t border-petrova-deep/10 bg-white">
            <div class="mx-auto flex max-w-3xl flex-col gap-4 px-4 py-6 text-sm text-petrova-secondary sm:flex-row sm:items-center sm:justify-between">
                <div class="text-center sm:text-left">
                    @if ($post->published_at)
                        {{ $post->published_at->format('d.m.Y') }}
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 sm:justify-end">
                    <span class="shrink-0 text-petrova-secondary">Споделяне:</span>
                    <div class="flex items-center gap-2">
                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-petrova-gold/20 text-petrova-deep transition hover:bg-petrova-gold/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                            aria-label="Споделяне във Facebook"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                                <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>
                        <a
                            href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-petrova-gold/20 text-petrova-deep transition hover:bg-petrova-gold/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                            aria-label="Споделяне в X"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                        <a
                            href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-petrova-gold/20 text-petrova-deep transition hover:bg-petrova-gold/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                            aria-label="Споделяне в LinkedIn"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white py-16">
            <div class="mx-auto max-w-3xl px-4">

                @if ($post->excerpt)
                    <p class="border-b border-petrova-deep/10 pb-10 text-lg leading-relaxed text-petrova-mid whitespace-pre-line">
                        {{ $post->excerpt }}
                    </p>
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
        </div>

        <div class="border-t border-petrova-deep/10 pt-12">
            @include('partials.consultation-promo-card')
        </div>

        @if ($relatedPosts->isNotEmpty())
            <section class="border-t border-petrova-deep/10 bg-white pt-16 pb-20" aria-labelledby="blog-related-heading">
                <div class="mx-auto max-w-6xl px-4">
                    <h2 id="blog-related-heading" class="font-playfair text-3xl italic text-petrova-deep md:text-4xl">
                        Още от блога
                    </h2>
                    <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($relatedPosts as $item)
                            @include('partials.blog-card', ['post' => $item])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

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
