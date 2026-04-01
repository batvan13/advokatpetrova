@php
    $section = page_section('home', 'services_preview');
@endphp

<section class="border-t border-white/10 bg-petrova-main py-20 font-playfair">
    <div class="mx-auto max-w-6xl px-4">

        {{-- Section heading --}}
        <div class="max-w-2xl">
            <h2 class="text-3xl font-bold tracking-tight text-petrova-primary">
                {{ $section?->title ?? 'Нашите услуги' }}
            </h2>

            @if($section?->subtitle)
                <p class="mt-4 text-lg leading-relaxed text-petrova-secondary">
                    {{ $section->subtitle }}
                </p>
            @endif

            @if($section?->content)
                <p class="mt-3 text-base leading-relaxed text-petrova-secondary/90">
                    {{ $section->content }}
                </p>
            @endif
        </div>

        {{-- Services grid --}}
        @if($homeServices->isEmpty())

            <p class="mt-12 text-sm text-petrova-secondary/65">Все още няма добавени услуги.</p>

        @else

            <div class="mt-14 grid gap-8 sm:grid-cols-2 md:grid-cols-3 md:gap-10">
                @foreach($homeServices as $service)
                    <div class="rounded-lg border border-white/10 bg-petrova-deep/30 p-6 transition-colors duration-200 hover:border-petrova-gold/20">

                        @if($service->icon)
                            <p class="mb-3 text-xs font-mono tracking-wide text-petrova-gold/75">{{ $service->icon }}</p>
                        @endif

                        <h3 class="text-base font-semibold text-petrova-primary">
                            <a href="{{ route('services.show', $service->slug) }}"
                               class="transition-colors hover:text-petrova-gold-hover focus:outline-none focus-visible:text-petrova-gold-hover focus-visible:ring-2 focus-visible:ring-petrova-gold/40 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main rounded-sm">
                                {{ $service->title }}
                            </a>
                        </h3>

                        @if($service->short_description)
                            <p class="mt-2 text-sm leading-relaxed text-petrova-secondary">
                                {{ $service->short_description }}
                            </p>
                        @endif

                    </div>
                @endforeach
            </div>

        @endif

        {{-- CTA button — only if both text and url are set --}}
        @if($section?->button_text && $section?->button_url)
            <div class="mt-12">
                <a href="{{ section_url($section->button_url) }}"
                   class="inline-flex rounded-lg bg-petrova-gold px-6 py-3 text-sm font-semibold text-petrova-deep shadow-none transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main">
                    {{ $section->button_text }}
                </a>
            </div>
        @endif

    </div>
</section>
