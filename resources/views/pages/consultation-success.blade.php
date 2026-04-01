@extends('layouts.app')

@section('title', 'Благодарим ви')
@section('description', 'Вашата заявка за онлайн консултация е приета. Ще се свържем с вас при необходимост.')

@section('content')

    <div class="flex min-h-[65vh] flex-col items-center justify-center px-4 py-16 sm:py-20">
        <div class="w-full max-w-md text-center">

            <p class="text-sm font-semibold uppercase tracking-widest text-gray-400">Успешно</p>

            <h1 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                Заявката е приета
            </h1>

            <p class="mt-5 text-base leading-relaxed text-gray-600">
                Благодарим ви. Ако сте оставили контакт, ще получите потвърждение или допълнителна информация на посочения имейл.
            </p>

            <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                При въпроси можете да се свържете с нас по всяко време.
            </p>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-center">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-800 transition hover:border-gray-400 hover:text-gray-900">
                    Начало
                </a>
                <a href="{{ route('contacts') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-800 transition hover:border-gray-400 hover:text-gray-900">
                    Контакти
                </a>
                <a href="{{ route('consultation') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-black px-5 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">
                    Онлайн консултация
                </a>
            </div>

        </div>
    </div>

@endsection
