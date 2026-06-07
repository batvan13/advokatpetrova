@extends('layouts.app')

@section('title', 'Благодарим ви')
@section('description', 'Вашият отзив беше получен и ще бъде публикуван след преглед.')

@section('content')

    <div class="flex min-h-[65vh] flex-col items-center justify-center bg-petrova-deep px-4 py-16 sm:py-20">
        <div class="w-full max-w-md text-center">

            <p class="text-sm font-semibold uppercase tracking-widest text-petrova-secondary/70">Успешно</p>

            <h1 class="mt-3 font-cormorant text-3xl font-bold italic tracking-tight text-petrova-primary sm:text-4xl">
                Благодарим ви
            </h1>

            <p class="mt-5 text-base leading-relaxed text-petrova-secondary/90">
                Вашият отзив е получен и ще бъде публикуван след преглед от нашия екип.
            </p>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-center">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-petrova-gold/30 bg-petrova-main/40 px-5 py-3 text-sm font-medium text-petrova-primary transition hover:border-petrova-gold/55 hover:bg-petrova-main/60">
                    Начало
                </a>
                <a href="{{ route('reviews.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-petrova-gold px-5 py-3 text-sm font-semibold text-petrova-deep transition hover:bg-petrova-gold-hover">
                    Добавете друг отзив
                </a>
            </div>

        </div>
    </div>

@endsection
