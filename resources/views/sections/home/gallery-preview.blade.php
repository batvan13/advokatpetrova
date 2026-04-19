@php
    $section = page_section('home', 'gallery_preview');
@endphp

@if(isset($galleryPreviewItems) && $galleryPreviewItems->isNotEmpty())
<section class="bg-gray-50 py-16">
    <div class="mx-auto max-w-6xl px-4">

        {{-- Section heading --}}
        <div class="max-w-2xl">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                {{ $section?->title ?? 'Галерия' }}
            </h2>

            @if($section?->subtitle)
                <p class="mt-4 text-lg text-gray-600">
                    {{ $section->subtitle }}
                </p>
            @endif

            @if($section?->content)
                <p class="mt-3 text-base text-gray-500 leading-relaxed">
                    {{ $section->content }}
                </p>
            @endif
        </div>

        {{-- Preview grid --}}
        <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3">

            @foreach($galleryPreviewItems as $item)

                    <a href="{{ route('gallery') }}"
                       class="group relative block aspect-square overflow-hidden rounded-xl bg-gray-100">

                        @if($item->isImage() && $item->image_path)

                            {{-- Image item --}}
                            <img
                                src="{{ upload_url($item->image_path) }}"
                                alt="{{ $item->title ?? '' }}"
                                class="h-full w-full object-cover transition-opacity duration-200 group-hover:opacity-90"
                                loading="lazy"
                            >

                        @else

                            {{-- Video item: YouTube thumbnail or clean placeholder --}}
                            @if($item->videoThumbnailUrl())
                                <img
                                    src="{{ $item->videoThumbnailUrl() }}"
                                    alt="{{ $item->title ?? '' }}"
                                    class="h-full w-full object-cover transition-opacity duration-200 group-hover:opacity-90"
                                    loading="lazy"
                                >
                            @else
                                {{-- Placeholder for videos without a thumbnail --}}
                                <div class="h-full w-full flex items-center justify-center bg-gray-100">
                                    @if($item->title)
                                        <span class="px-4 text-center text-xs text-gray-400">{{ $item->title }}</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Play badge overlay (always shown on video items) --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white">
                                    <svg class="h-5 w-5 ml-0.5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </span>
                            </div>

                        @endif

                    </a>

            @endforeach

        </div>

        {{-- CTA --}}
        @if($section?->button_text && $section?->button_url)
            <div class="mt-8">
                <a href="{{ section_url($section->button_url) }}"
                   class="inline-flex rounded-lg bg-black px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">
                    {{ $section->button_text }}
                </a>
            </div>
        @endif

    </div>
</section>
@endif
