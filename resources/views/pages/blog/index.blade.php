@extends('layouts.app')

@section('title', 'Блог')
@section('description', 'Новини, съвети и полезна информация от нашия екип.')

@section('content')

    {{-- Hero: same structure as About (static banner + overlay + breadcrumb) --}}
    <section
        id="blog-top"
        class="scroll-mt-24 relative flex min-h-[min(55vh,520px)] items-center overflow-hidden font-playfair md:scroll-mt-28"
    >
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/blog/banner-blog.webp') }}');"
            role="presentation"
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/88 via-petrova-deep/72 to-petrova-main/92"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 py-20 text-left">
            <h1 class="text-4xl font-bold tracking-tight text-petrova-primary sm:text-5xl">
                Блог
            </h1>

            <p class="mt-6 max-w-2xl text-sm leading-relaxed text-petrova-secondary/80 sm:text-base">
                <a
                    href="{{ route('home') }}"
                    class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded"
                >Начало</a>
                <span aria-hidden="true"> / </span>
                <span class="text-petrova-primary" aria-current="page">Блог</span>
            </p>
        </div>
    </section>

    <section class="border-t border-white/10 bg-petrova-deep py-20">
        <div class="mx-auto max-w-6xl px-4">

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    @include('partials.blog-card', ['post' => $post])
                @empty
                    <div class="col-span-full text-center text-petrova-secondary">
                        Няма публикации.
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="mt-16 flex justify-center">
                    {{ $posts->links('pagination::tailwind') }}
                </div>
            @endif

        </div>
    </section>

@endsection
