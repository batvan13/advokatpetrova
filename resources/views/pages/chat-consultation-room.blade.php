@extends('layouts.app')

@section('title', 'Чат консултация — ' . match ($state) {
    'early'     => 'Още не е достъпна',
    'waiting'   => 'Чакалня',
    'active'    => 'Активна',
    'completed' => 'Приключена',
    default     => 'Статус',
})

@section('content')

<div class="consultation-page">

    <section class="relative overflow-hidden min-h-screen">

        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/consultation/landing_background.webp') }}');"
            role="presentation"
        ></div>

        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/98 via-petrova-deep/95 to-petrova-main"
            aria-hidden="true"
        ></div>

        <div
            class="relative z-10 mx-auto max-w-2xl px-4 pt-20 pb-20 text-center"
            data-chat-room-state="{{ $state }}"
        >

            <div class="mb-8">
                <img src="{{ asset('images/logo-gold.svg') }}" alt="Адвокатска кантора Петрова" class="mx-auto h-16">
            </div>

            @if ($state === 'early')
                <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                    Чат консултацията още не е достъпна
                </h1>
                <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                    Достъпът до чакалнята ще бъде активен 10 минути преди началния час.
                </p>
            @elseif ($state === 'waiting')
                <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                    Вие сте в чакалнята
                </h1>
                <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                    Моля, изчакайте адвокатът да стартира консултацията.
                </p>
            @elseif ($state === 'active')
                <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                    Консултацията е активна
                </h1>
                <p class="text-petrova-secondary/80 text-sm sm:text-base mb-4 max-w-lg mx-auto">
                    Адвокатът стартира чат консултацията.
                </p>
                <p class="text-petrova-secondary/60 text-sm mb-8 max-w-lg mx-auto">
                    Моля, останете на тази страница.
                </p>
            @elseif ($state === 'completed')
                <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                    Чат консултацията приключи
                </h1>
                <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                    Сесията е приключена и вече не е достъпна за изпращане на съобщения.
                </p>
            @endif

            <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5 text-left text-sm mb-10">
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                        <span class="text-petrova-gold font-medium">Дата на консултацията</span>
                        <span class="text-petrova-primary">
                            {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y г.') }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                        <span class="text-petrova-gold font-medium">Начален час</span>
                        <span class="text-petrova-primary">
                            {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                        </span>
                    </div>

                    @if ($state === 'early')
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Достъп от</span>
                            <span class="text-petrova-primary">
                                {{ $opensAt->setTimezone('Europe/Sofia')->format('H:i') }}
                            </span>
                        </div>
                    @elseif ($state === 'waiting')
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Краен час</span>
                            <span class="text-petrova-primary">
                                {{ $booking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Статус</span>
                            <span class="text-amber-300 font-medium">Изчакване</span>
                        </div>
                    @elseif ($state === 'active')
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Краен час</span>
                            <span class="text-petrova-primary">
                                {{ $booking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded border border-petrova-gold/40
                      text-petrova-gold text-sm font-semibold hover:bg-petrova-gold/10 transition-colors">
                Към началната страница
            </a>

        </div>
    </section>

</div>

@endsection
