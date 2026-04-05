@extends('layouts.app')

@section('title', 'За нас')
@section('description', 'Научете повече за нас — кои сме, какво правим и защо можете да ни се доверите.')

@section('content')

@php
    $heroTitle = trim((string) ($hero?->title ?? ''));

    $rawContent = trim((string) ($content?->content ?? ''));
    $contentIsHtml = $rawContent !== ''
        && preg_match('/<[a-z][^>]*>/i', $rawContent) === 1;

    $contentHasCta = filled($content?->button_text) && filled($content?->button_url);

    $contentImageAlt = filled($content?->title)
        ? $content->title
        : ($heroTitle !== '' ? $heroTitle : 'За нас');
@endphp

    {{-- ── Hero: first /about implementation baseline + left align, subtle subtitle, no body/CTA ── --}}
    <section
        id="about-top"
        class="scroll-mt-24 relative flex min-h-[min(55vh,520px)] items-center overflow-hidden font-playfair md:scroll-mt-28"
    >
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/about/Banner - Екип-За нас-(Compressify.io).webp') }}');"
            role="presentation"
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/88 via-petrova-deep/72 to-petrova-main/92"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 py-20 text-left">
            <h1 class="text-4xl font-bold tracking-tight text-petrova-primary sm:text-5xl">
                {{ $heroTitle !== '' ? $heroTitle : 'За нас' }}
            </h1>

            <p class="mt-6 max-w-2xl text-sm leading-relaxed text-petrova-secondary/80 sm:text-base">
                <a
                    href="#about-top"
                    class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded"
                >Начало</a>
                <span aria-hidden="true"> / </span>
                <a
                    href="#about-team"
                    class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded"
                >Екип</a>
            </p>
        </div>
    </section>

    {{-- ── Content: 2-col — image left, beige text card right (+ overlap lg) ─ --}}
    <section class="border-t border-petrova-gold/25 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-20 lg:py-24">
            <div
                class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-6 lg:overflow-visible"
            >
                <div class="relative z-0 order-1 min-w-0">
                    <img
                        src="{{ asset('images/about/стр. За нас - Img-(Compressify.io).webp') }}"
                        alt="{{ $contentImageAlt }}"
                        class="aspect-[4/3] w-full rounded-2xl object-cover shadow-[0_12px_40px_-12px_rgba(20,28,46,0.18)] ring-1 ring-petrova-deep/[0.06] lg:aspect-[4/5] lg:max-h-[min(520px,65vh)] lg:object-cover"
                        loading="lazy"
                    >
                </div>

                <div
                    class="relative z-10 order-2 min-w-0 lg:-ml-10 lg:pl-0 xl:-ml-14"
                >
                    <div
                        class="rounded-lg bg-petrova-primary px-8 py-10 shadow-[0_8px_30px_-8px_rgba(20,28,46,0.12)] ring-1 ring-petrova-deep/[0.07]"
                    >
                        @if (filled($content?->subtitle))
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.22em] text-petrova-mid/90 sm:text-xs"
                            >
                                {{ $content->subtitle }}
                            </p>
                        @endif

                        @if (filled($content?->title))
                            <h2
                                class="mt-2.5 font-playfair text-3xl font-bold leading-[1.15] tracking-tight text-petrova-deep sm:text-4xl lg:text-[2.35rem]"
                            >
                                {{ $content->title }}
                            </h2>
                        @endif

                        @if ($rawContent !== '')
                            @if ($contentIsHtml)
                                <div
                                    class="mt-5 max-w-prose text-[0.9375rem] leading-[1.7] text-petrova-mid
                                        [&_p]:mt-4 [&_p:first-child]:mt-0
                                        [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5
                                        [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5
                                        [&_li]:mt-1
                                        [&_strong]:font-semibold [&_strong]:text-petrova-deep
                                        [&_a]:font-medium [&_a]:text-petrova-deep [&_a]:underline [&_a]:underline-offset-[3px] decoration-petrova-gold/50 hover:[&_a]:text-petrova-deep"
                                >
                                    {!! $rawContent !!}
                                </div>
                            @else
                                <div
                                    class="mt-5 max-w-prose whitespace-pre-line text-[0.9375rem] leading-[1.7] text-petrova-mid"
                                >
                                    {{ $rawContent }}
                                </div>
                            @endif
                        @endif

                        <div class="mt-10">
                            <span
                                class="inline-flex cursor-default select-none items-center gap-2 rounded-lg border border-petrova-gold/50 bg-petrova-deep px-8 py-3 text-sm font-medium tracking-normal text-petrova-primary shadow-[0_12px_28px_-10px_rgba(20,28,46,0.55)] pointer-events-none"
                            >
                                Онлайн консултация
                                <svg
                                    class="h-4 w-4 shrink-0 text-petrova-primary opacity-90"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
                                    />
                                </svg>
                            </span>
                        </div>

                        @if ($contentHasCta)
                            <div class="mt-10">
                                <a
                                    href="{{ section_url($content->button_url) }}"
                                    class="inline-flex rounded-lg border border-petrova-deep/15 bg-white/70 px-7 py-3 text-sm font-semibold tracking-wide text-petrova-deep shadow-sm backdrop-blur-[2px] transition hover:border-petrova-gold/55 hover:bg-white hover:text-petrova-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-primary"
                                >
                                    {{ $content->button_text }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Team: static cards + local assets ─────────────────────────────── --}}
    <section
        id="about-team"
        class="scroll-mt-24 border-t border-white/10 bg-petrova-main py-16 lg:py-20 font-playfair md:scroll-mt-28"
    >
        <div class="mx-auto max-w-6xl px-4">
            <h2 class="text-center text-3xl font-bold tracking-tight text-petrova-primary sm:text-4xl">
                Екип
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-center text-base text-petrova-secondary/90">
                Запознайте се с част от екипа на кантората.
            </p>

            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['name' => 'Мария Петрова', 'role' => 'Управляващ партньор', 'phone' => '+359 888 123 456', 'email' => 'maria.petrova@example.bg'],
                    ['name' => 'Георги Димитров', 'role' => 'Старши адвокат', 'phone' => '+359 888 234 567', 'email' => 'georgi.dimitrov@example.bg'],
                    ['name' => 'Елена Стоянова', 'role' => 'Адвокат', 'phone' => '+359 888 345 678', 'email' => 'elena.stoyanova@example.bg'],
                ] as $member)
                    <article
                        class="flex flex-col rounded-2xl border border-white/10 bg-petrova-deep/35 p-6 shadow-sm backdrop-blur-sm"
                    >
                        <img
                            src="{{ asset('images/about/стр. За нас - Примерна снимка - Колеги-(Compressify.io).webp') }}"
                            alt=""
                            class="mb-5 aspect-[4/5] w-full rounded-xl object-cover"
                            loading="lazy"
                        >
                        <h3 class="text-xl font-semibold tracking-tight text-petrova-primary">
                            {{ $member['name'] }}
                        </h3>
                        <p class="mt-1 text-sm text-petrova-secondary">
                            {{ $member['role'] }}
                        </p>

                        <div class="mt-5 space-y-3 border-t border-white/10 pt-5 text-sm text-petrova-secondary/95">
                            <a
                                href="tel:{{ preg_replace('/[^\d+]/', '', $member['phone']) }}"
                                class="flex items-center gap-3 transition hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/50 rounded"
                            >
                                <img
                                    src="{{ asset('images/about/Екип - Phone - Icon.svg') }}"
                                    alt=""
                                    class="h-5 w-5 shrink-0 opacity-90"
                                >
                                <span>{{ $member['phone'] }}</span>
                            </a>
                            <a
                                href="mailto:{{ $member['email'] }}"
                                class="flex items-center gap-3 break-all transition hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/50 rounded"
                            >
                                <img
                                    src="{{ asset('images/about/Екип - Email - Icon.svg') }}"
                                    alt=""
                                    class="h-5 w-5 shrink-0 opacity-90"
                                >
                                <span>{{ $member['email'] }}</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

@endsection
