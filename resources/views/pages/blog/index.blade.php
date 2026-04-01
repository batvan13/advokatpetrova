@extends('layouts.app')

@section('title', 'Блог')
@section('description', 'Новини, съвети и полезна информация от нашия екип.')

@section('content')

    <section class="border-t border-petrova-deep/10 bg-petrova-primary py-20">
        <div class="mx-auto max-w-6xl px-4">

            <div class="max-w-2xl">
                <h1 class="font-playfair text-3xl font-bold tracking-tight text-petrova-deep">
                    Блог
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-petrova-mid">
                    Новини, съвети и полезна информация.
                </p>
            </div>

            <div class="mt-12 space-y-10">
                @forelse ($posts as $post)
                    <article class="rounded-lg border border-petrova-deep/10 bg-white/90 p-8 shadow-sm transition-shadow duration-200 hover:shadow-md">
                        <p class="text-xs font-medium uppercase tracking-wide text-petrova-secondary">
                            {{ $post->published_at->format('d.m.Y') }}
                        </p>
                        <h2 class="mt-2 font-playfair text-xl font-semibold tracking-tight text-petrova-deep">
                            <a href="{{ route('blog.show', $post->slug) }}"
                               class="transition-colors hover:text-petrova-gold-hover focus:outline-none focus-visible:text-petrova-gold-hover focus-visible:ring-2 focus-visible:ring-petrova-gold/35 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-primary rounded-sm">
                                {{ $post->title }}
                            </a>
                        </h2>
                        @if ($post->excerpt)
                            <p class="mt-3 text-base leading-relaxed text-petrova-mid whitespace-pre-line">
                                {{ $post->excerpt }}
                            </p>
                        @endif
                        <div class="mt-4">
                            <a href="{{ route('blog.show', $post->slug) }}"
                               class="text-sm font-medium text-petrova-deep underline-offset-2 transition-colors hover:text-petrova-gold-hover hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/35 focus-visible:ring-offset-2 focus-visible:ring-offset-white rounded-sm">
                                Прочети повече →
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-petrova-deep/20 bg-white/80 px-8 py-16 text-center">
                        <p class="text-sm font-medium text-petrova-deep">Все още няма публикации</p>
                        <p class="mt-2 text-sm text-petrova-secondary">Скоро ще добавим съдържание тук.</p>
                        <a href="{{ route('home') }}"
                           class="mt-6 inline-block text-sm font-medium text-petrova-deep underline-offset-2 transition-colors hover:text-petrova-gold-hover hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/35 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-primary rounded-sm">
                            Към началото
                        </a>
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="mt-10">
                    {{ $posts->links('pagination::tailwind') }}
                </div>
            @endif

        </div>
    </section>

@endsection
