@php
    $section  = page_section('home', 'contact_preview');

    $email    = setting('contact_email');
    $phone    = setting('contact_phone');
    $address  = setting('address');
    $mapsUrl  = setting('google_maps_url');

    $tel = $phone ? ('tel:' . preg_replace('/[^\d+]/', '', $phone)) : null;
    $facebook  = setting('facebook_url');
    $instagram = setting('instagram_url');
    $youtube   = setting('youtube_url');

    $hasActions = $phone || $email || $mapsUrl;
    $hasSocial  = $facebook || $instagram || $youtube;
@endphp

<section class="border-t border-white/10 bg-petrova-deep py-20">
    <div class="mx-auto max-w-6xl px-4">
        <div class="grid items-start gap-14 md:grid-cols-2">

            {{-- Left: section text + optional CMS button --}}
            <div>
                <h2 class="font-playfair text-3xl font-bold tracking-tight text-petrova-primary">
                    {{ $section?->title ?? 'Контакти' }}
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

                @if($section?->button_text && $section?->button_url)
                    <div class="mt-8">
                        <a href="{{ section_url($section->button_url) }}"
                           class="inline-flex rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-petrova-primary transition hover:border-white/35 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep">
                            {{ $section->button_text }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- Right: action buttons + supporting info --}}
            <div class="space-y-6">

                {{-- Primary actions — or guaranteed fallback if nothing configured --}}
                @if($hasActions)
                    @include('partials.action-buttons', ['variant' => 'dark'])
                @else
                    <a href="{{ route('contacts') }}"
                       class="inline-flex rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-petrova-primary transition hover:border-white/35 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep">
                        Свържете се с нас
                    </a>
                @endif

                {{-- Address: informational only, not covered by action-buttons --}}
                @if($address)
                    <p class="text-sm leading-relaxed text-petrova-secondary/80 whitespace-pre-line">{{ $address }}</p>
                @endif

                {{-- Social links --}}
                @if($hasSocial)
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-petrova-secondary/90">
                        @if($facebook)
                            <a href="{{ $facebook }}" target="_blank" rel="noopener"
                               class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/40 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded">Facebook</a>
                        @endif
                        @if($instagram)
                            <a href="{{ $instagram }}" target="_blank" rel="noopener"
                               class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/40 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded">Instagram</a>
                        @endif
                        @if($youtube)
                            <a href="{{ $youtube }}" target="_blank" rel="noopener"
                               class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/40 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded">YouTube</a>
                        @endif
                    </div>
                @endif

            </div>

        </div>
    </div>
</section>
