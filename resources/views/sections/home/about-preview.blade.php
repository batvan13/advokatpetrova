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

    // Title/subtitle: page_section('home','about_preview') only — no about.hero / about.content fallback.
    // Content: primary = home.about_preview.content; if empty only, plain excerpt from about.content.content.
    if ($sectionContent === '') {
        $aboutBodySection = page_section('about', 'content');
        $rawBody          = trim((string) ($aboutBodySection?->content ?? ''));
        if ($rawBody !== '') {
            $sectionContent = \Illuminate\Support\Str::limit(strip_tags($rawBody), 200);
        }
    }

    $hasContent = $sectionTitle !== '' || $sectionSubtitle !== '' || $sectionContent !== '' || $hasCta;

    $previewContentIsHtml = $sectionContent !== ''
        && preg_match('/<[a-z][^>]*>/i', $sectionContent) === 1;
@endphp

@if ($hasContent || $hasImage)

<section class="bg-white py-16 lg:py-20">
    <div class="mx-auto w-full max-w-[1400px] px-6 lg:px-12">

        {{-- TOP ROW: text left / image right --}}
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-12 lg:items-start">

            {{-- Left: eyebrow + heading + content + CTA --}}
            <div class="flex flex-col">

                @if ($sectionSubtitle !== '')
                    <p class="mb-5 font-sans text-sm font-normal text-[#0D1A30]/50 tracking-wide">
                        {{ $sectionSubtitle }}
                    </p>
                @else
                    <p class="mb-5 font-sans text-sm font-normal text-[#0D1A30]/50 tracking-wide">
                        Нашите ценности
                    </p>
                @endif

                @if ($sectionTitle !== '')
                    <h2 class="font-cormorant text-4xl font-normal italic leading-tight text-[#0D1A30] lg:text-[2.75rem] lg:leading-[1.15] mb-8">
                        {!! nl2br(e($sectionTitle)) !!}
                    </h2>
                @else
                    <h2 class="font-cormorant text-4xl font-normal italic leading-tight text-[#0D1A30] lg:text-[2.75rem] lg:leading-[1.15] mb-8">
                        Основни принципи,<br>които определят нашата<br>практика
                    </h2>
                @endif

                @if ($sectionContent !== '')
                    @if ($previewContentIsHtml)
                        <div class="mb-8 font-sans text-sm leading-relaxed text-[#0D1A30]/60
                            [&_p]:mt-4 [&_p:first-child]:mt-0
                            [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5
                            [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5
                            [&_li]:mt-1
                            [&_strong]:text-[#0D1A30] [&_b]:text-[#0D1A30]
                            [&_a]:underline [&_a]:underline-offset-2">
                            {!! $sectionContent !!}
                        </div>
                    @else
                        <p class="mb-8 font-sans text-sm leading-relaxed text-[#0D1A30]/60 whitespace-pre-line">
                            {{ $sectionContent }}
                        </p>
                    @endif
                @endif

                @if ($hasCta)
                    <div>
                        <a
                            href="{{ section_url($sectionButtonUrl) }}"
                            class="inline-flex items-center justify-between bg-petrova-gold text-petrova-deep font-sans text-sm font-semibold px-6 py-3 min-w-[200px] hover:bg-petrova-gold-hover transition no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/60 focus-visible:ring-offset-2"
                        >
                            <span class="shrink-0 whitespace-nowrap">{{ $sectionButtonText }}</span>
                            <svg class="shrink-0 ml-4" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 11L11 1M11 1H3M11 1V9" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </a>
                    </div>
                @endif

            </div>

            {{-- Right: static homepage composition image + values grid below --}}
            <div class="w-full flex flex-col gap-8">

                <div class="overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('images/home/Homepage - IMG.webp') }}"
                        alt=""
                        class="h-[320px] w-full object-cover lg:h-[400px]"
                        width="700"
                        height="400"
                        loading="lazy"
                        aria-hidden="true"
                    >
                </div>

                {{-- PRINCIPLES GRID — static, 2×2, under the image --}}
                <div class="grid grid-cols-2 gap-x-8 gap-y-8">

                    {{-- 1. Изграждане на взаимоотношения --}}
                    <div>
                        <img
                            src="{{ asset('images/home/Начална страница - Изграждане на взаимоотношения - Icon.svg') }}"
                            alt=""
                            class="mb-3 h-8 w-auto"
                            aria-hidden="true"
                            loading="lazy"
                        >
                        <h3 class="font-cormorant text-sm font-normal italic text-[#0D1A30] mb-1.5">
                            Изграждане на взаимоотношения
                        </h3>
                        <p class="font-sans text-xs leading-relaxed text-[#0D1A30]/60">
                            Вярваме, че чрез истинско разбиране на нашите клиенти и техните индивидуални нужди създаваме партньорства, които водят до взаимни успехи и развитие.
                        </p>
                    </div>

                    {{-- 2. Уважение --}}
                    <div>
                        <img
                            src="{{ asset('images/home/Начална страница - Уважение - Icon.svg') }}"
                            alt=""
                            class="mb-3 h-8 w-auto"
                            aria-hidden="true"
                            loading="lazy"
                        >
                        <h3 class="font-cormorant text-sm font-normal italic text-[#0D1A30] mb-1.5">
                            Уважение
                        </h3>
                        <p class="font-sans text-xs leading-relaxed text-[#0D1A30]/60">
                            Уважението е в основата на всичките ни взаимоотношения. Към всеки — клиент, колега или партньор — подхождаме с внимание, коректност и професионализъм.
                        </p>
                    </div>

                    {{-- 3. Постигане на резултати --}}
                    <div>
                        <img
                            src="{{ asset('images/home/Начална страница - Постигане на резултати - Icon.svg') }}"
                            alt=""
                            class="mb-3 h-8 w-auto"
                            aria-hidden="true"
                            loading="lazy"
                        >
                        <h3 class="font-cormorant text-sm font-normal italic text-[#0D1A30] mb-1.5">
                            Постигане на резултати
                        </h3>
                        <p class="font-sans text-xs leading-relaxed text-[#0D1A30]/60">
                            Съчетаваме правна експертиза, отдаденост и постоянство, за да надминем очакванията на всеки клиент.
                        </p>
                    </div>

                    {{-- 4. Резултати --}}
                    <div>
                        <img
                            src="{{ asset('images/home/Начална страница - Резултати - Icon.svg') }}"
                            alt=""
                            class="mb-3 h-8 w-auto"
                            aria-hidden="true"
                            loading="lazy"
                        >
                        <h3 class="font-cormorant text-sm font-normal italic text-[#0D1A30] mb-1.5">
                            Резултати
                        </h3>
                        <p class="font-sans text-xs leading-relaxed text-[#0D1A30]/60">
                            Независимо колко сложен е Вашият казус, ние работим целенасочено и неуморно, за да постигнем най-добрия възможен резултат, съобразен с Вашата конкретна ситуация.
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>
</section>

@endif
