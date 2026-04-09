@php
    $section = page_section('home', 'services_preview');

    $homeServiceFallbackImage = static function (\App\Models\Service $service): string {
        $slug = mb_strtolower((string) $service->slug, 'UTF-8');
        $title = mb_strtolower((string) $service->title, 'UTF-8');

        $labor = asset('images/home/service-labor-law.webp');
        $contract = asset('images/home/service-contract-law.webp');
        $realEstate = asset('images/home/service-real-estate.webp');
        $default = $contract;

        $slugLabor = ['trudovo-pravo', 'trudovo', 'trz', 'schetovodstvo', 'accounting', 'labor-law', 'labor'];
        $slugContract = ['dogovorno-pravo', 'dogovorno', 'contract-law', 'contract'];
        $slugRealEstate = ['nedvizhimi-imoti', 'imotni', 'imoti', 'nedvizhimi', 'real-estate', 'estate'];

        foreach ($slugRealEstate as $needle) {
            if ($needle !== '' && str_contains($slug, $needle)) {
                return $realEstate;
            }
        }
        foreach ($slugLabor as $needle) {
            if ($needle !== '' && str_contains($slug, $needle)) {
                return $labor;
            }
        }
        foreach ($slugContract as $needle) {
            if ($needle !== '' && str_contains($slug, $needle)) {
                return $contract;
            }
        }

        if (str_contains($title, 'недвижим') || str_contains($title, 'имот')) {
            return $realEstate;
        }
        if (
            str_contains($title, 'трудов')
            || str_contains($title, 'трз')
            || str_contains($title, 'счетовод')
        ) {
            return $labor;
        }
        if (str_contains($title, 'договор')) {
            return $contract;
        }

        return $default;
    };

    $headerHref = ($section?->button_url ?? '') !== ''
        ? section_url($section->button_url)
        : route('services');

    $headerButtonLabel = trim((string) ($section?->button_text ?? ''));
    if ($headerButtonLabel === '') {
        $headerButtonLabel = 'Вижте всички';
    }

    $sectionTitleRaw = trim((string) ($section?->title ?? ''));
@endphp

<section class="bg-[#EDE8E0] py-16 lg:py-24">
    <div class="mx-auto w-full max-w-7xl px-6 lg:px-16">
        <div class="mb-10 flex flex-col gap-6 md:mb-12 md:flex-row md:items-end md:justify-between lg:mb-16">
            <h2 class="max-w-[22rem] font-playfair text-3xl font-normal italic leading-tight tracking-tight text-[#0F172A] md:max-w-[28rem] md:text-4xl lg:max-w-none lg:text-[2.75rem] lg:leading-[1.12]">
                @if ($sectionTitleRaw !== '')
                    {!! nl2br(e($sectionTitleRaw)) !!}
                @else
                    Области на<br>правната практика
                @endif
            </h2>
            <a
                href="{{ $headerHref }}"
                class="inline-flex shrink-0 items-center gap-3 self-start rounded-lg bg-[#0F172A] px-6 py-3.5 font-sans text-sm font-medium leading-none text-[#EDE8E0] no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0F172A]/40 focus-visible:ring-offset-2 focus-visible:ring-offset-[#EDE8E0] md:self-auto"
            >
                <span>{{ $headerButtonLabel }}</span>
                <svg class="h-3.5 w-3.5 shrink-0 text-[#EDE8E0]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="14" height="14">
                    <path d="M4 12L12 4M12 4H6M12 4V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        @if ($homeServices->isEmpty())
            <p class="font-sans text-sm leading-relaxed text-[#64748B]">Все още няма добавени услуги.</p>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-8">
                @foreach ($homeServices as $service)
                    @php
                        $description = $service->short_description;
                        if (blank($description) && filled($service->full_description)) {
                            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($service->full_description)));
                            $description = \Illuminate\Support\Str::limit($plain, 200, '…');
                        }
                        $cardExcerpt = filled($description) ? trim((string) $description) : '';
                        $cardImage = filled($service->image)
                            ? asset('storage/' . $service->image)
                            : $homeServiceFallbackImage($service);
                    @endphp
                    <a
                        href="{{ route('services.show', $service->slug) }}"
                        class="flex h-full flex-col overflow-hidden rounded-xl border border-[#D6D0C4] bg-white no-underline shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0F172A]/25 focus-visible:ring-offset-2 focus-visible:ring-offset-[#EDE8E0]"
                    >
                        <div
                            class="relative h-[190px] w-full shrink-0 overflow-hidden bg-[#E8E4DC] rounded-tl-[12px] rounded-tr-[28px] rounded-br-[12px] rounded-bl-[12px]"
                        >
                            <img
                                src="{{ $cardImage }}"
                                alt="{{ $service->title }}"
                                width="376"
                                height="190"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        </div>
                        <div class="flex flex-col p-5">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <h3 class="min-w-0 flex-1 font-playfair text-xl font-normal italic leading-snug text-[#0F172A] lg:text-2xl">
                                    {{ $service->title }}
                                </h3>
                                <span class="mt-1 shrink-0 text-[#0F172A]" aria-hidden="true">
                                    <svg class="h-[18px] w-[18px]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M4 12L12 4M12 4H6M12 4V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            @if ($cardExcerpt !== '')
                                <p class="font-sans text-sm leading-relaxed text-[#475569]">
                                    {{ $cardExcerpt }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
