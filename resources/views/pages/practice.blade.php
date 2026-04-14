@extends('layouts.app')

@section('title', filled($contentSection?->title) ? $contentSection->title : 'Моята практика')
@section('description', 'Информация за моята практика, подход и професионален фокус.')

@section('content')

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16">
            <div class="max-w-2xl text-left">

                <h1 class="font-cormorant text-3xl font-bold tracking-tight text-petrova-deep">
                    {{ filled($contentSection?->title) ? $contentSection->title : 'Моята практика' }}
                </h1>

                @if (filled($contentSection?->subtitle))
                    <p class="mt-6 text-lg leading-relaxed text-petrova-mid">
                        {{ $contentSection->subtitle }}
                    </p>
                @endif

                <div class="mt-6 text-base leading-relaxed text-petrova-deep/85
                    [&_p]:mt-4 [&_p:first-child]:mt-0
                    [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5
                    [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5
                    [&_li]:mt-1
                    [&_a]:font-medium [&_a]:text-petrova-deep [&_a]:underline [&_a]:underline-offset-2 [&_a]:transition-colors hover:[&_a]:text-petrova-mid">
                    @if (filled($contentSection?->content))
                        {!! $contentSection->content !!}
                    @else
                        <p>
                            Тук можете да публикувате представяне на вашата практика — опит, специализация, философия на работа и какво могат да очакват клиентите.
                        </p>
                        <p>
                            Съдържанието на тази страница подлежи на актуализация според нуждите на сайта и изискванията на клиента.
                        </p>
                    @endif
                </div>

            </div>
        </div>
    </section>

@endsection
