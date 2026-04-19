@extends('layouts.app')

@section('title', 'Галерия')
@section('description', 'Разгледайте снимки от нашите проекти и свършена работа.')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<section class="py-20">
    <div class="mx-auto max-w-6xl px-4">

        <h1 class="text-4xl font-bold tracking-tight text-gray-900">
            {{ $hero?->title ?? 'Галерия' }}
        </h1>

        @if($hero?->subtitle)
            <p class="mt-4 max-w-2xl text-lg text-gray-600">
                {{ $hero->subtitle }}
            </p>
        @endif

    </div>
</section>

{{-- ── Gallery ──────────────────────────────────────────────── --}}
<section class="pb-24">
    <div class="mx-auto max-w-6xl px-4">

        @php
            $hasRenderable = $items->contains(
                fn($item) => $item->isImage() ? (bool) $item->image_path : (bool) $item->video_url
            );
            $galleryTabs = [
                'all' => ['label' => 'Всички', 'query' => []],
                'image' => ['label' => 'Снимки', 'query' => ['type' => 'image']],
                'video' => ['label' => 'Видео', 'query' => ['type' => 'video']],
            ];
        @endphp

        @if($hasAnyActiveGalleryItems)
            <nav class="mb-10 flex flex-wrap gap-2" aria-label="Филтър на галерията">
                @foreach($galleryTabs as $key => $tab)
                    @php
                        $isActive = $galleryFilter === $key;
                    @endphp
                    <a href="{{ route('gallery', $tab['query']) }}"
                       @if($isActive) aria-current="page" @endif
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-medium transition-colors
                              {{ $isActive
                                  ? 'border-gray-900 bg-gray-900 text-white'
                                  : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400 hover:text-gray-900' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        @endif

        @if(! $hasRenderable)

            <div class="rounded-xl border border-gray-100 bg-gray-50 px-8 py-16 text-center">
                @if(! $hasAnyActiveGalleryItems)
                    <p class="text-sm font-medium text-gray-500">Все още няма добавени снимки или видеа.</p>
                @elseif($galleryFilter === 'image')
                    <p class="text-sm font-medium text-gray-500">Няма активни снимки в галерията.</p>
                @elseif($galleryFilter === 'video')
                    <p class="text-sm font-medium text-gray-500">Няма активни видеа в галерията.</p>
                @else
                    <p class="text-sm font-medium text-gray-500">Няма съдържание за показване.</p>
                @endif
            </div>

        @else

            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">

                @foreach($items as $item)

                    @if($item->isImage())

                        {{-- Image card --}}
                        @if($item->image_path)
                            <div class="relative flex flex-col">
                                <span class="pointer-events-none absolute left-2 top-2 z-10 rounded-full bg-black/65 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                    Снимка
                                </span>
                                <a
                                    href="{{ upload_url($item->image_path) }}"
                                    data-fancybox="gallery"
                                    @if($item->title) data-caption="{{ $item->title }}" @endif
                                    class="aspect-square block overflow-hidden rounded-xl border border-gray-100 bg-gray-50 cursor-zoom-in"
                                >
                                    <img
                                        src="{{ upload_url($item->image_path) }}"
                                        alt="{{ $item->title ?? '' }}"
                                        class="h-full w-full object-cover transition-opacity hover:opacity-90"
                                        loading="lazy"
                                    >
                                </a>
                                @if($item->title)
                                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $item->title }}</p>
                                @endif
                            </div>
                        @endif

                    @elseif($item->video_url)

                        <div class="relative flex flex-col">
                            <span class="pointer-events-none absolute left-2 top-2 z-10 rounded-full bg-black/65 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                Видео
                            </span>
                            @include('partials.gallery-video-card', ['item' => $item])
                        </div>

                    @endif

                @endforeach

            </div>

        @endif

    </div>
</section>

{{-- ── Contact CTA — only shown when current filter has items ─ --}}
@if($items->isNotEmpty())
<section class="bg-gray-50 py-16">
    <div class="mx-auto max-w-6xl px-4">

        <p class="text-lg font-semibold text-gray-900">Харесва ви това, което виждате?</p>

        <div class="mt-6">
            @include('partials.action-buttons')
        </div>

        <p class="mt-4 text-sm text-gray-400">
            или
            <a href="{{ route('contacts') }}"
               class="underline underline-offset-2 hover:text-gray-700 transition-colors">
                изпратете запитване
            </a>
        </p>

    </div>
</section>
@endif

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox/fancybox.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox/fancybox.umd.js"></script>
<script>
Fancybox.bind('[data-fancybox="gallery"]', {
    Hash: false,
    Thumbs: false,
    Slideshow: false,
    Toolbar: {
        display: {
            left:   ['infobar'],
            middle: [],
            right:  ['zoom', 'close'],
        },
    },
});
</script>
@endpush
