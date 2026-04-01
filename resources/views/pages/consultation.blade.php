@extends('layouts.app')

@section('title', 'Онлайн консултация')
@section('description', 'Платена онлайн консултация — запазете час и получете професионален отговор на вашия казус.')

@section('content')

<div class="consultation-page">

    {{-- 1. Hero --}}
    <section class="bg-white py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="max-w-3xl">

                <h1 class="text-4xl font-bold tracking-tight text-gray-900">
                    {{ $hero?->title ?? 'Онлайн консултация' }}
                </h1>

                @if($hero?->subtitle)
                    <p class="mt-6 text-lg text-gray-600">
                        {{ $hero->subtitle }}
                    </p>
                @endif

                @if($hero?->content)
                    <p class="mt-4 text-base text-gray-500 leading-relaxed">
                        {{ $hero->content }}
                    </p>
                @endif

            </div>
        </div>
    </section>

    {{-- 2. Optional intro (CMS) --}}
    @if($content?->title || $content?->subtitle || $content?->content)
        <section class="bg-gray-50 py-12 border-t border-gray-100">
            <div class="mx-auto max-w-6xl px-4">
                <div class="max-w-3xl">

                    @if($content?->title)
                        <h2 class="text-xl font-semibold tracking-tight text-gray-900">
                            {{ $content->title }}
                        </h2>
                    @endif

                    @if($content?->subtitle)
                        <p class="mt-3 text-base text-gray-600">
                            {{ $content->subtitle }}
                        </p>
                    @endif

                    @if($content?->content)
                        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                            {{ $content->content }}
                        </p>
                    @endif

                </div>
            </div>
        </section>
    @endif

    {{-- 3. Trust chips (static) --}}
    <section class="bg-white py-10 border-t border-gray-100">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700">
                    Поверителност
                </span>
                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700">
                    Онлайн среща
                </span>
                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700">
                    Ясен процес
                </span>
                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700">
                    Потвърждение по имейл
                </span>
            </div>
        </div>
    </section>

    {{-- 4. Pricing cards (static) --}}
    <section class="bg-gray-50 py-16 border-t border-gray-100">
        <div class="mx-auto max-w-6xl px-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-8 max-w-3xl">
                Пакети
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">

                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Стандартна консултация</h3>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900">60 лв.</p>
                    <p class="mt-1 text-sm text-gray-500">Еднократна сесия до 30 мин.</p>
                    <ul class="mt-5 space-y-2 text-sm text-gray-600 list-disc list-inside">
                        <li>Фокус върху един основен въпрос</li>
                        <li>Обобщение след срещата</li>
                    </ul>
                </article>

                <article class="rounded-xl border border-gray-900 bg-white p-6 shadow-sm ring-1 ring-gray-900">
                    <h3 class="text-base font-semibold text-gray-900">Разширена консултация</h3>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900">120 лв.</p>
                    <p class="mt-1 text-sm text-gray-500">До 60 мин., по-сложен казус</p>
                    <ul class="mt-5 space-y-2 text-sm text-gray-600 list-disc list-inside">
                        <li>По-подробен анализ</li>
                        <li>Препоръки за следващи стъпки</li>
                    </ul>
                </article>

            </div>
        </div>
    </section>

    {{-- 5. CTA from hero meta only --}}
    @if($hero?->button_text && $hero?->button_url)
        <section class="bg-white py-16 border-t border-gray-100">
            <div class="mx-auto max-w-6xl px-4">
                <div class="max-w-3xl">
                    <a href="{{ section_url($hero->button_url) }}"
                       class="inline-flex rounded-lg bg-black px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">
                        {{ $hero->button_text }}
                    </a>
                </div>
            </div>
        </section>
    @endif

</div>

@endsection
