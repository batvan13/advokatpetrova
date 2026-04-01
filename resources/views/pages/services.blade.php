@extends('layouts.app')

@section('title', 'Услуги')
@section('description', 'Разгледайте пълния списък с услуги, които предлагаме.')

@section('content')

    <section class="border-t border-white/10 bg-petrova-main py-20 font-playfair">
        <div class="mx-auto max-w-6xl px-4">

            {{-- Page heading --}}
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold tracking-tight text-petrova-primary">
                    {{ $page?->title ?? 'Нашите услуги' }}
                </h1>

                @if($page?->subtitle)
                    <p class="mt-4 text-lg leading-relaxed text-petrova-secondary">
                        {{ $page->subtitle }}
                    </p>
                @endif

                @if($page?->content)
                    <p class="mt-3 text-base leading-relaxed text-petrova-secondary/90">
                        {{ $page->content }}
                    </p>
                @endif
            </div>

            {{-- Services grid --}}
            @if($services->isEmpty())

                <p class="mt-12 text-sm text-petrova-secondary/65">Все още няма добавени услуги.</p>

            @else

                <div class="mt-14 grid gap-8 sm:grid-cols-2 md:grid-cols-3 md:gap-10">
                    @foreach($services as $service)
                        @include('partials.service-card', ['service' => $service])
                    @endforeach
                </div>

            @endif

        </div>
    </section>

    {{-- ── Contact CTA ─────────────────────────────────────────── --}}
    <section class="bg-white py-16">
        <div class="mx-auto max-w-6xl px-4">

            <p class="text-lg font-semibold text-gray-900">Интересувате се от някоя от нашите услуги?</p>

            <div class="mt-6">
                @include('partials.action-buttons')
            </div>

            <p class="mt-4 text-sm text-gray-400">
                или
                <a href="{{ route('contacts') }}"
                   class="underline underline-offset-2 hover:text-gray-700 transition-colors">
                    изпратете запитване
                </a>
            </p>

        </div>
    </section>

@endsection
