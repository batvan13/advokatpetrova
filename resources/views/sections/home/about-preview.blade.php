@php
    $section = page_section('home', 'about_preview');
@endphp

<section class="border-t border-petrova-deep/10 bg-petrova-primary py-20">
    <div class="mx-auto max-w-6xl px-4">
        <div class="max-w-2xl">

            <h2 class="font-playfair text-3xl font-bold tracking-tight text-petrova-deep">
                {{ $section?->title ?? 'За нас' }}
            </h2>

            @if($section?->subtitle)
                <p class="mt-4 text-lg leading-relaxed text-petrova-mid">
                    {{ $section->subtitle }}
                </p>
            @endif

            @if($section?->content)
                <p class="mt-3 text-base leading-relaxed text-petrova-deep/85">
                    {{ $section->content }}
                </p>
            @endif

            @if($section?->button_text && $section?->button_url)
                <div class="mt-8">
                    <a href="{{ section_url($section->button_url) }}"
                       class="inline-flex rounded-lg bg-petrova-gold px-6 py-3 text-sm font-semibold text-petrova-deep shadow-none transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-primary">
                        {{ $section->button_text }}
                    </a>
                </div>
            @endif

        </div>
    </div>
</section>
