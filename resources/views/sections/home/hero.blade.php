@php
    $hero = page_section('home', 'hero');

    $heroTitle = trim((string) ($hero?->title ?? ''));
    $heroSubtitle = trim((string) ($hero?->subtitle ?? ''));
    $heroContent = trim((string) ($hero?->content ?? ''));
    $heroButtonText = trim((string) ($hero?->button_text ?? ''));
    $heroButtonUrl = trim((string) ($hero?->button_url ?? ''));

    $hasCta = $heroButtonText !== '' && $heroButtonUrl !== '';
    $hasHeroContent = $heroTitle !== '' || $heroSubtitle !== '' || $heroContent !== '' || $hasCta;
@endphp

<section
    class="relative flex min-h-[60vh] items-center justify-center overflow-hidden bg-petrova-hero font-playfair"
>
    {{-- Decorative layer (Mode A + B); non-interactive --}}
    <svg
        class="pointer-events-none absolute inset-y-0 right-0 h-full w-[min(55vw,32rem)] text-petrova-primary/[0.08]"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 400 720"
        preserveAspectRatio="xMaxYMid slice"
        aria-hidden="true"
        focusable="false"
    >
        <g fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round">
            <path d="M420 0 C280 120 200 240 380 360 C520 480 360 600 200 720" />
            <path d="M440 40 C300 160 220 280 400 400 C540 520 380 640 220 720" opacity="0.85" />
            <path d="M460 80 C320 200 240 320 420 440 C560 560 400 680 240 720" opacity="0.7" />
            <path d="M400 0 C260 140 180 280 360 420 C500 540 340 660 180 720" opacity="0.55" />
        </g>
        <g fill="currentColor" opacity="0.04">
            <circle cx="320" cy="180" r="120" />
            <circle cx="360" cy="420" r="90" />
        </g>
    </svg>

    @if ($hasHeroContent)
        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 py-16">
            <div class="mx-auto w-full max-w-3xl text-center">
                @if ($heroTitle !== '')
                    <h1 class="text-4xl font-bold tracking-tight text-petrova-primary sm:text-5xl">
                        {{ $heroTitle }}
                    </h1>
                @endif

                @if ($heroSubtitle !== '')
                    <p class="mt-6 text-lg leading-8 text-petrova-secondary">
                        {{ $heroSubtitle }}
                    </p>
                @endif

                @if ($heroContent !== '')
                    <p class="mt-4 text-base leading-7 text-petrova-secondary/90">
                        {{ $heroContent }}
                    </p>
                @endif

                @if ($hasCta)
                    <div class="mt-8">
                        <a
                            href="{{ section_url($heroButtonUrl) }}"
                            class="inline-flex rounded-lg border border-petrova-gold/50 bg-petrova-deep/30 px-6 py-3 text-sm font-semibold text-petrova-primary backdrop-blur-sm transition hover:border-petrova-gold-hover hover:bg-petrova-mid/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep"
                        >
                            {{ $heroButtonText }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</section>
