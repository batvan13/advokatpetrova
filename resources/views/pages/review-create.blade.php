@extends('layouts.app')

@section('title', 'Добавяне на отзив')
@section('description', 'Споделете вашето мнение за Адвокатска кантора Петрова.')

@section('content')

    <section class="relative min-h-[min(70vh,720px)] overflow-hidden">
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/consultation/landing_background.webp') }}');"
            role="presentation"
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/90 via-petrova-deep/82 to-petrova-main/95"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto max-w-3xl px-4 py-16 sm:py-20 lg:py-24">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 class="font-cormorant text-4xl font-bold italic tracking-tight text-petrova-primary sm:text-5xl">
                    Добавяне на отзив
                </h1>

                <p class="font-sans text-sm leading-relaxed text-petrova-secondary/80 sm:text-right">
                    <a
                        href="{{ route('home') }}"
                        class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded"
                    >Начало</a>
                    <span aria-hidden="true"> / </span>
                    <span class="text-petrova-primary/95">Добавяне на отзив</span>
                </p>
            </div>

            @if ($errors->has('throttle'))
                <div class="mb-6 rounded-lg border border-red-400/40 bg-red-950/40 px-4 py-3">
                    <p class="text-sm text-red-200">{{ $errors->first('throttle') }}</p>
                </div>
            @endif

            <div class="rounded-xl border border-petrova-gold/25 bg-petrova-deep/70 p-6 shadow-[0_12px_40px_-12px_rgba(0,0,0,0.45)] sm:p-8">
                <form method="POST" action="{{ route('reviews.submit') }}" class="relative space-y-5" novalidate>
                    @csrf

                    @if ($errors->has('review'))
                        <div class="rounded-lg border border-red-400/40 bg-red-950/40 px-4 py-3">
                            <p class="text-sm text-red-200">{{ $errors->first('review') }}</p>
                        </div>
                    @endif

                    <input type="hidden" name="opened_at" value="{{ time() }}">

                    <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                        <label for="company">Фирма</label>
                        <input type="text" name="company" id="company" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="review_first_name" class="mb-1.5 block text-sm font-medium text-petrova-secondary">
                                Име <span class="text-red-300">*</span>
                            </label>
                            <input
                                type="text"
                                id="review_first_name"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                autocomplete="given-name"
                                required
                                placeholder="Вашето име"
                                class="w-full rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('first_name') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                            >
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="review_last_name" class="mb-1.5 block text-sm font-medium text-petrova-secondary">
                                Фамилия <span class="text-red-300">*</span>
                            </label>
                            <input
                                type="text"
                                id="review_last_name"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                autocomplete="family-name"
                                required
                                placeholder="Вашата фамилия"
                                class="w-full rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('last_name') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                            >
                            @error('last_name')
                                <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="review_email" class="mb-1.5 block text-sm font-medium text-petrova-secondary">
                            Имейл адрес <span class="text-red-300">*</span>
                        </label>
                        <input
                            type="email"
                            id="review_email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            placeholder="Вашият имейл"
                            class="w-full rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('email') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="review_body" class="mb-1.5 block text-sm font-medium text-petrova-secondary">
                            Отзив <span class="text-red-300">*</span>
                        </label>
                        <textarea
                            id="review_body"
                            name="body"
                            rows="6"
                            required
                            minlength="20"
                            maxlength="3000"
                            placeholder="Вашият отзив"
                            class="w-full resize-y rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('body') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                        >{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs leading-relaxed text-petrova-secondary/75">
                            Вашият имейл адрес няма да бъде публикуван.
                        </p>
                    </div>

                    <div class="flex items-start gap-3 pt-1">
                        <input
                            type="checkbox"
                            id="review_privacy"
                            name="privacy"
                            value="1"
                            @checked(old('privacy'))
                            required
                            class="mt-1 h-4 w-4 shrink-0 rounded border-white/35 bg-white text-petrova-gold focus:ring-2 focus:ring-petrova-gold/50 focus:ring-offset-2 focus:ring-offset-petrova-deep"
                        >
                        <label for="review_privacy" class="text-xs leading-relaxed text-petrova-secondary">
                            Прочетох и съм съгласен с
                            <a href="{{ route('privacy') }}" class="font-medium text-petrova-gold underline decoration-petrova-gold/50 underline-offset-2 transition-colors hover:text-petrova-primary hover:decoration-petrova-primary">Политиката за поверителност</a>.
                            <span class="text-red-300">*</span>
                        </label>
                    </div>
                    @error('privacy')
                        <p class="text-xs text-red-300">{{ $message }}</p>
                    @enderror

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-petrova-gold px-6 py-3.5 text-sm font-semibold text-petrova-deep shadow-none transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep"
                    >
                        <span>Изпращане</span>
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M8 3v10M4 7l4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </section>

@endsection
