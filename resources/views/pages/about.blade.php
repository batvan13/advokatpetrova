@extends('layouts.app')

@section('title', 'За нас')
@section('description', 'Научете повече за нас — кои сме, какво правим и защо можете да ни се доверите.')

@section('content')

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16">
            <div class="max-w-2xl text-left">

                <h1 class="font-playfair text-3xl font-bold tracking-tight text-petrova-deep">
                    @if(filled($hero?->title))
                        {{ $hero->title }}
                    @elseif(filled($content?->title))
                        {{ $content->title }}
                    @else
                        За нас
                    @endif
                </h1>

                @if($hero?->subtitle)
                    <p class="mt-6 text-lg leading-relaxed text-petrova-mid">
                        {{ $hero->subtitle }}
                    </p>
                @endif

                @if($hero?->content && !filled($content?->content))
                    <p class="mt-4 text-base leading-relaxed text-petrova-deep/85">
                        {{ $hero->content }}
                    </p>
                @endif

                @if($content?->content)
                    <div class="mt-6 text-base leading-relaxed text-petrova-deep/85
                        [&_p]:mt-4 [&_p:first-child]:mt-0
                        [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5
                        [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5
                        [&_li]:mt-1
                        [&_a]:font-medium [&_a]:text-petrova-deep [&_a]:underline [&_a]:underline-offset-2 [&_a]:transition-colors hover:[&_a]:text-petrova-mid">
                        {!! $content->content !!}
                    </div>
                @endif

                @if(setting('contact_phone') || setting('contact_email') || setting('google_maps_url'))
                    <div class="mt-12">
                        @include('partials.action-buttons', ['variant' => 'light'])
                    </div>
                @endif

            </div>
        </div>
    </section>

@endsection
