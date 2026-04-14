@extends('layouts.app')

@section('title', $service->title)
@section('description', $seoDescription !== '' ? $seoDescription : setting('site_tagline', ''))

@section('content')

    {{-- Hero: back link + title + optional lead (petrova band) --}}
    <section
        class="border-b border-white/10 bg-gradient-to-b from-petrova-top via-petrova-mid to-petrova-main"
        aria-labelledby="service-title-heading"
    >
        <div class="mx-auto max-w-6xl px-4 pt-6 pb-14 md:pt-8 md:pb-20">
            <nav aria-label="Услуги" class="mb-10 md:mb-12">
                <a
                    href="{{ route('services') }}"
                    class="inline-flex text-sm font-medium text-petrova-secondary transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-mid rounded-sm"
                >
                    ← Всички услуги
                </a>
            </nav>

            @if(filled($service->image))
                <div class="grid gap-10 lg:grid-cols-2 lg:items-center lg:gap-12">
                    <div class="min-w-0">
                        <h1
                            id="service-title-heading"
                            class="font-cormorant text-4xl font-bold tracking-tight text-petrova-primary sm:text-5xl lg:text-[2.75rem] lg:leading-[1.12]"
                        >
                            {{ $service->title }}
                        </h1>

                        @if($service->short_description)
                            <p class="mt-6 max-w-3xl text-lg leading-relaxed text-petrova-secondary md:text-xl md:leading-relaxed">
                                {{ $service->short_description }}
                            </p>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <img
                            src="{{ asset('storage/' . $service->image) }}"
                            alt="{{ $service->title }}"
                            class="w-full rounded-2xl object-cover shadow-[0_12px_40px_-12px_rgba(0,0,0,0.35)] ring-1 ring-white/10 aspect-[4/3] lg:aspect-[5/4] lg:max-h-[min(420px,50vh)] lg:mx-auto lg:max-w-xl"
                            loading="eager"
                        >
                    </div>
                </div>
            @else
                <h1
                    id="service-title-heading"
                    class="font-cormorant text-4xl font-bold tracking-tight text-petrova-primary sm:text-5xl lg:text-[2.75rem] lg:leading-[1.12]"
                >
                    {{ $service->title }}
                </h1>

                @if($service->short_description)
                    <p class="mt-6 max-w-3xl text-lg leading-relaxed text-petrova-secondary md:text-xl md:leading-relaxed">
                        {{ $service->short_description }}
                    </p>
                @endif
            @endif
        </div>
    </section>

    <article>

        @if($service->full_description)
            <section
                class="border-t border-petrova-gold/25 bg-white py-16 md:py-20"
                aria-labelledby="service-full-desc-heading"
            >
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="mx-auto max-w-3xl lg:max-w-4xl">
                        <h2
                            id="service-full-desc-heading"
                            class="font-cormorant text-2xl font-semibold tracking-tight text-petrova-deep sm:text-3xl"
                        >
                            Описание
                        </h2>
                        <div
                            class="mt-8 border-t border-petrova-deep/10 pt-8 text-base leading-relaxed text-petrova-mid sm:text-[1.0625rem] sm:leading-relaxed"
                        >
                            <p class="whitespace-pre-line">{{ $service->full_description }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @elseif(! $service->short_description)
            <section class="border-t border-petrova-gold/25 bg-white py-16 md:py-20">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm leading-relaxed text-petrova-mid/80">
                        Подробно описание за тази услуга предстои да бъде добавено.
                    </p>
                </div>
            </section>
        @endif

        @if(count($faqItems) > 0)
            <section
                class="border-t border-white/10 bg-petrova-main py-16 md:py-20"
                aria-labelledby="service-faq-heading"
            >
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <h2
                        id="service-faq-heading"
                            class="font-cormorant text-2xl font-bold tracking-tight text-petrova-primary sm:text-3xl"
                    >
                        Често задавани въпроси
                    </h2>
                    <div class="mt-10 max-w-3xl space-y-0 divide-y divide-white/10 lg:max-w-4xl">
                        @foreach($faqItems as $item)
                            <div class="py-8 first:pt-0">
                                <h3 class="text-base font-semibold text-petrova-primary">
                                    {{ $item['question'] }}
                                </h3>
                                <p class="mt-3 text-base leading-relaxed text-petrova-secondary whitespace-pre-line">
                                    {{ $item['answer'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section
            class="border-t border-white/10 bg-petrova-deep py-16 md:py-20"
            aria-labelledby="service-cta-heading"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2
                    id="service-cta-heading"
                    class="font-cormorant text-2xl font-bold tracking-tight text-petrova-primary sm:text-3xl"
                >
                    Запитване
                </h2>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-petrova-secondary">
                    Свържете се с нас за повече информация или оферта за тази услуга.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    @include('partials.action-buttons', ['variant' => 'dark'])
                    <a
                        href="{{ route('contacts') }}"
                        class="inline-flex items-center rounded-lg border border-white/25 px-5 py-2.5 text-sm font-semibold text-petrova-primary transition-colors hover:border-petrova-gold/55 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep"
                    >
                        Форма за контакт
                    </a>
                </div>
            </div>
        </section>

    </article>

@endsection

@push('scripts')
@php
    $siteName = setting('site_name') ?: config('app.name', 'Website');
    $jsonDesc = trim(preg_replace('/\s+/', ' ', strip_tags(
        $service->short_description ?: $service->full_description ?: $service->title
    )));
    $jsonDesc = \Illuminate\Support\Str::limit($jsonDesc, 500, '');
    $serviceLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $service->title,
        'description' => $jsonDesc !== '' ? $jsonDesc : null,
        'url' => route('services.show', $service->slug),
        'provider' => [
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
        ],
    ]);
@endphp
<script type="application/ld+json">{!! json_encode($serviceLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@if(count($faqItems) > 0)
@php
    $faqLd = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static function (array $item) {
            return [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ];
        }, $faqItems),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
@endpush
