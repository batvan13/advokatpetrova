@extends('layouts.app')

@section('title', 'Онлайн консултация')
@section('description', 'Платена онлайн консултация — запазете час и получете професионален отговор на вашия казус.')

@section('content')

<div class="consultation-page font-playfair">

    {{-- ── Hero ──────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden">

        {{-- Background image --}}
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/consultation/landing_background.webp') }}');"
            role="presentation"
        ></div>

        {{-- Dark overlay --}}
        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/90 via-petrova-deep/80 to-petrova-main/95"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 pt-16 pb-10">

            {{-- Breadcrumb --}}
            <nav class="mb-8 flex items-center gap-2 text-sm text-petrova-secondary/70" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-petrova-primary transition-colors">
                    <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Начало
                </a>
                <span aria-hidden="true">/</span>
                <span class="text-petrova-secondary/50">Онлайн консултация</span>
            </nav>

            {{-- Page title --}}
            <h1 class="text-4xl font-bold italic tracking-tight text-petrova-primary sm:text-5xl">
                Онлайн консултация
            </h1>

            {{-- Trust badges --}}
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">

                <div class="flex items-center gap-2.5 rounded border border-petrova-gold/30 bg-petrova-deep/60 px-4 py-2.5 text-sm text-petrova-secondary">
                    <svg class="h-4 w-4 flex-shrink-0 text-petrova-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Подходяща за конкретни въпроси и бързи казуси
                </div>

                <div class="flex items-center gap-2.5 rounded border border-petrova-gold/30 bg-petrova-deep/60 px-4 py-2.5 text-sm text-petrova-secondary">
                    <svg class="h-4 w-4 flex-shrink-0 text-petrova-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Спестява време и излишни разходи
                </div>

                <div class="flex items-center gap-2.5 rounded border border-petrova-gold/30 bg-petrova-deep/60 px-4 py-2.5 text-sm text-petrova-secondary">
                    <svg class="h-4 w-4 flex-shrink-0 text-petrova-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Провежда се изцяло онлайн — удобно и сигурно
                </div>

            </div>

            {{-- ── Consultation cards ──────────────────────────────────── --}}
            <div class="mt-10 pb-16 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                {{-- PHONE --}}
                @php $phone = $consultationPricing['phone'] ?? null; @endphp
                <article class="flex flex-col rounded-lg border border-petrova-gold/20 bg-white/95 p-5 shadow-lg">

                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/consultation/icon_phone.svg') }}" alt="" class="h-12 w-12 flex-shrink-0" aria-hidden="true">
                        <h2 class="text-lg font-bold italic leading-tight text-petrova-main">
                            Телефонна<br>консултация
                        </h2>
                    </div>

                    <p class="mt-2 text-sm font-semibold italic text-petrova-gold">
                        Кратки въпроси и спешни казуси
                    </p>

                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Говорете директно с адвокат и получете бърз отговор на Вашия въпрос.
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Продължителност: до 30 минути</p>

                    <div class="mt-4 flex-1 flex items-end">
                        @if ($phone)
                            <p class="text-2xl font-bold italic tracking-tight text-petrova-main">
                                {{ number_format((float) $phone->price_eur, 2, ',', '.') }}&nbsp;€
                                @if ($phone->show_bgn_price)
                                    / {{ number_format((float) $phone->price_bgn, 2, ',', '.') }}&nbsp;лв.
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('contacts') }}"
                           class="block w-full rounded bg-petrova-gold px-4 py-2.5 text-center text-sm font-semibold text-petrova-deep transition hover:bg-petrova-gold-hover">
                            Запазете час
                        </a>
                    </div>

                </article>

                {{-- CHAT --}}
                @php $chat = $consultationPricing['chat'] ?? null; @endphp
                <article class="flex flex-col rounded-lg border border-petrova-gold/20 bg-white/95 p-5 shadow-lg">

                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/consultation/icon_chat.svg') }}" alt="" class="h-12 w-12 flex-shrink-0" aria-hidden="true">
                        <h2 class="text-lg font-bold italic leading-tight text-petrova-main">
                            Онлайн чат<br>консултация
                        </h2>
                    </div>

                    <p class="mt-2 text-sm font-semibold italic text-petrova-gold">
                        За изясняване на бързи въпроси
                    </p>

                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Обсъдете Вашия казус в реално време чрез писмен чат.
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Продължителност: до 30 минути</p>

                    <div class="mt-4 flex-1 flex items-end">
                        @if ($chat)
                            <p class="text-2xl font-bold italic tracking-tight text-petrova-main">
                                {{ number_format((float) $chat->price_eur, 2, ',', '.') }}&nbsp;€
                                @if ($chat->show_bgn_price)
                                    / {{ number_format((float) $chat->price_bgn, 2, ',', '.') }}&nbsp;лв.
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('contacts') }}"
                           class="block w-full rounded bg-petrova-gold px-4 py-2.5 text-center text-sm font-semibold text-petrova-deep transition hover:bg-petrova-gold-hover">
                            Запазете час
                        </a>
                    </div>

                </article>

                {{-- WRITTEN --}}
                @php $written = $consultationPricing['written'] ?? null; @endphp
                <article class="flex flex-col rounded-lg border border-petrova-gold/20 bg-white/95 p-5 shadow-lg">

                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/consultation/icon_written.svg') }}" alt="" class="h-12 w-12 flex-shrink-0" aria-hidden="true">
                        <h2 class="text-lg font-bold italic leading-tight text-petrova-main">
                            Писмена<br>консултация
                        </h2>
                    </div>

                    <p class="mt-2 text-sm font-semibold italic text-petrova-gold">
                        По-сложни случаи и документи
                    </p>

                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Изпратете Вашия казус и получете подробен правен анализ по имейл.
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Получаване на отговор: до 48 часа</p>

                    <div class="mt-4 flex-1 flex items-end">
                        @if ($written)
                            <p class="text-2xl font-bold italic tracking-tight text-petrova-main">
                                {{ number_format((float) $written->price_eur, 2, ',', '.') }}&nbsp;€
                                @if ($written->show_bgn_price)
                                    / {{ number_format((float) $written->price_bgn, 2, ',', '.') }}&nbsp;лв.
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('contacts') }}"
                           class="block w-full rounded bg-petrova-gold px-4 py-2.5 text-center text-sm font-semibold text-petrova-deep transition hover:bg-petrova-gold-hover">
                            Изпратете запитване
                        </a>
                    </div>

                </article>

                {{-- VIDEO --}}
                @php $video = $consultationPricing['video'] ?? null; @endphp
                <article class="flex flex-col rounded-lg border border-petrova-gold/20 bg-white/95 p-5 shadow-lg">

                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/consultation/icon_video_viber.svg') }}" alt="" class="h-12 w-12 flex-shrink-0" aria-hidden="true">
                        <h2 class="text-lg font-bold italic leading-tight text-petrova-main">
                            (Viber) Видео<br>консултация
                        </h2>
                    </div>

                    <p class="mt-2 text-sm font-semibold italic text-petrova-gold">
                        Подробно обсъждане на казуси
                    </p>

                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Лична Видео консултация с адвокат, провеждана директно през Viber
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Продължителност: 30 или 60 минути</p>

                    {{-- Price display — switches with toggle --}}
                    @if ($video)
                        <div class="mt-4 flex-1 flex items-end">
                            <p class="text-2xl font-bold italic tracking-tight text-petrova-main" id="video-price-display">
                                {{-- Populated by JS on load --}}
                            </p>
                        </div>

                        {{-- Duration toggle --}}
                        <div class="mt-3 flex gap-2" role="group" aria-label="Продължителност">
                            <button
                                type="button"
                                id="video-btn-30"
                                class="flex-1 rounded px-3 py-2 text-sm font-semibold transition"
                                data-eur="{{ number_format((float) $video->price_eur, 2, ',', '.') }}"
                                data-bgn="{{ number_format((float) $video->price_bgn, 2, ',', '.') }}"
                                data-show-bgn="{{ $video->show_bgn_price ? '1' : '0' }}"
                            >
                                30 минути
                            </button>
                            <button
                                type="button"
                                id="video-btn-60"
                                class="flex-1 rounded px-3 py-2 text-sm font-semibold transition"
                                data-eur="{{ number_format((float) $video->price_eur_60, 2, ',', '.') }}"
                                data-bgn="{{ number_format((float) $video->price_bgn_60, 2, ',', '.') }}"
                                data-show-bgn="{{ $video->show_bgn_price ? '1' : '0' }}"
                            >
                                60 минути
                            </button>
                        </div>
                    @endif

                    <div class="mt-5">
                        <a href="{{ route('contacts') }}"
                           class="block w-full rounded bg-petrova-gold px-4 py-2.5 text-center text-sm font-semibold text-petrova-deep transition hover:bg-petrova-gold-hover">
                            Запазете час
                        </a>
                    </div>

                </article>

            </div>
        </div>
    </section>

</div>

@push('scripts')
<script>
(function () {
    var btn30    = document.getElementById('video-btn-30');
    var btn60    = document.getElementById('video-btn-60');
    var display  = document.getElementById('video-price-display');

    if (!btn30 || !btn60 || !display) return;

    var activeClass   = ['bg-petrova-gold', 'text-petrova-deep'];
    var inactiveClass = ['bg-gray-100', 'text-gray-600', 'hover:bg-gray-200'];

    function setActive(activeBtn, inactiveBtn) {
        activeClass.forEach(function (c) {
            activeBtn.classList.add(c);
            inactiveBtn.classList.remove(c);
        });
        inactiveClass.forEach(function (c) {
            inactiveBtn.classList.add(c);
            activeBtn.classList.remove(c);
        });

        var eur     = activeBtn.dataset.eur;
        var bgn     = activeBtn.dataset.bgn;
        var showBgn = activeBtn.dataset.showBgn === '1';

        display.textContent = eur + '\u00A0€' + (showBgn ? ' / ' + bgn + '\u00A0лв.' : '');
    }

    btn30.addEventListener('click', function () { setActive(btn30, btn60); });
    btn60.addEventListener('click', function () { setActive(btn60, btn30); });

    // Default: 30 min active on load
    setActive(btn30, btn60);
}());
</script>
@endpush

@endsection
