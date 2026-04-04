@php
    $section = page_section('home', 'about_preview');

    $sectionTitle      = trim((string) ($section?->title      ?? ''));
    $sectionSubtitle   = trim((string) ($section?->subtitle   ?? ''));
    $sectionContent    = trim((string) ($section?->content    ?? ''));
    $sectionButtonText = trim((string) ($section?->button_text ?? ''));
    $sectionButtonUrl  = trim((string) ($section?->button_url  ?? ''));
    $sectionImagePath  = $section?->image_path ?? null;

    $hasCta   = $sectionButtonText !== '' && $sectionButtonUrl !== '';
    $hasImage = $sectionImagePath !== null && $sectionImagePath !== '';

    // If the preview section has no text of its own, pull from the About page data.
    // home.about_preview text fields act as an override; about.hero / about.content are the fallback.
    $hasPreviewText = $sectionTitle !== '' || $sectionSubtitle !== '' || $sectionContent !== '';

    if (! $hasPreviewText) {
        $aboutHero    = page_section('about', 'hero');
        $aboutContent = page_section('about', 'content');

        if ($sectionTitle === '') {
            $sectionTitle = trim((string) ($aboutHero?->title ?? $aboutContent?->title ?? ''));
        }

        if ($sectionSubtitle === '') {
            $sectionSubtitle = trim((string) ($aboutHero?->subtitle ?? ''));
        }

        if ($sectionContent === '') {
            $rawBody = trim((string) ($aboutContent?->content ?? ''));
            if ($rawBody !== '') {
                $sectionContent = \Illuminate\Support\Str::limit(strip_tags($rawBody), 200);
            }
        }
    }

    $hasContent = $sectionTitle !== '' || $sectionSubtitle !== '' || $sectionContent !== '' || $hasCta;
@endphp

@if ($hasContent || $hasImage)

<section class="overflow-hidden bg-petrova-main font-playfair">

    @if ($hasImage)

        {{-- ── SPLIT LAYOUT (image present) ──────────────────────────── --}}
        <div class="grid min-h-[360px] grid-cols-1 lg:min-h-[440px] lg:grid-cols-2">

            {{-- Left: text content --}}
            <div class="flex items-center px-8 py-16 lg:px-16 lg:py-20">
                <div class="max-w-xl">
                    @if ($sectionTitle !== '')
                        <h2 class="text-3xl font-bold leading-tight tracking-tight text-petrova-primary sm:text-4xl lg:text-5xl">
                            {{ $sectionTitle }}
                        </h2>
                    @endif

                    @if ($sectionSubtitle !== '')
                        <p class="mt-6 text-lg leading-relaxed text-petrova-secondary">
                            {{ $sectionSubtitle }}
                        </p>
                    @endif

                    @if ($sectionContent !== '')
                        <p class="mt-4 text-base leading-relaxed text-petrova-secondary/85">
                            {{ $sectionContent }}
                        </p>
                    @endif

                    @if ($hasCta)
                        <div class="mt-8">
                            <a
                                href="{{ section_url($sectionButtonUrl) }}"
                                class="inline-flex rounded-lg border border-petrova-gold/50 bg-petrova-deep/30 px-6 py-3 text-sm font-semibold text-petrova-primary backdrop-blur-sm transition hover:border-petrova-gold-hover hover:bg-petrova-mid/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main"
                            >
                                {{ $sectionButtonText }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: image fills full cell to edge --}}
            <div class="relative min-h-[280px] lg:min-h-0">
                <img
                    src="{{ asset('storage/' . $sectionImagePath) }}"
                    alt="{{ $sectionTitle ?: 'За нас' }}"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            </div>

        </div>

    @else

        {{-- ── FALLBACK LAYOUT (no image — text on dark background) ───── --}}
        <div class="mx-auto max-w-6xl px-4 py-20">
            <div class="max-w-3xl">

                @if ($sectionTitle !== '')
                    <h2 class="text-3xl font-bold leading-tight tracking-tight text-petrova-primary sm:text-4xl lg:text-5xl">
                        {{ $sectionTitle }}
                    </h2>
                @endif

                @if ($sectionSubtitle !== '')
                    <p class="mt-6 text-lg leading-relaxed text-petrova-secondary">
                        {{ $sectionSubtitle }}
                    </p>
                @endif

                @if ($sectionContent !== '')
                    <p class="mt-4 text-base leading-relaxed text-petrova-secondary/85">
                        {{ $sectionContent }}
                    </p>
                @endif

                @if ($hasCta)
                    <div class="mt-8">
                        <a
                            href="{{ section_url($sectionButtonUrl) }}"
                            class="inline-flex rounded-lg border border-petrova-gold/50 bg-petrova-deep/30 px-6 py-3 text-sm font-semibold text-petrova-primary backdrop-blur-sm transition hover:border-petrova-gold-hover hover:bg-petrova-mid/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main"
                        >
                            {{ $sectionButtonText }}
                        </a>
                    </div>
                @endif

            </div>
        </div>

    @endif

</section>

@endif
