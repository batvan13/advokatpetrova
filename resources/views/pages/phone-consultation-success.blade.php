@extends('layouts.app')

@section('title', 'Заявката е получена — Телефонна консултация')

@section('content')

<div class="consultation-page font-playfair">

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

        <div class="relative z-10 mx-auto max-w-4xl px-4 pt-20 pb-20 text-center">

            {{-- Logo / brand mark --}}
            <div class="mb-8">
                <img src="{{ asset('images/logo-gold.svg') }}" alt="Адвокатска кантора Петрова" class="mx-auto h-16">
            </div>

            {{-- Thank you --}}
            <p class="text-sm tracking-widest uppercase text-petrova-secondary/60 mb-3">
                Благодарим Ви за доверието!
            </p>

            <h1 class="text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-6">
                Вашата заявка е получена успешно!
            </h1>

            <p class="text-sm text-petrova-secondary/70 leading-relaxed max-w-xl mx-auto mb-10">
                На посочения от Вас имейл адрес ще получите потвърждение за направената заявка, както и инструкции за плащане и следващите стъпки.
                Ще се свържем с Вас в най-кратък срок.
            </p>

            {{-- Summary strip --}}
            <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-4 py-4 mb-8 text-left">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 text-sm divide-y sm:divide-y-0 sm:divide-x divide-petrova-gold/10">

                    <div class="py-2 sm:py-0 sm:px-3 first:pl-0 last:pr-0">
                        <p class="text-xs text-petrova-secondary/50 mb-1 italic">№ Записване</p>
                        <p class="text-petrova-primary font-medium">{{ $booking->id }}</p>
                    </div>

                    <div class="py-2 sm:py-0 sm:px-3">
                        <p class="text-xs text-petrova-secondary/50 mb-1 italic">Дата</p>
                        <p class="text-petrova-primary font-medium">
                            {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y г.') }}
                        </p>
                    </div>

                    <div class="py-2 sm:py-0 sm:px-3">
                        <p class="text-xs text-petrova-secondary/50 mb-1 italic">Имейл</p>
                        <p class="text-petrova-primary font-medium text-xs break-all">{{ $booking->email }}</p>
                    </div>

                    <div class="py-2 sm:py-0 sm:px-3">
                        <p class="text-xs text-petrova-secondary/50 mb-1 italic">Обща сума</p>
                        <p class="text-petrova-primary font-medium">
                            {{ number_format((float) $booking->price_eur, 2, ',', '.') }}&nbsp;€
                            @if ($booking->show_bgn_price && $booking->price_bgn)
                                / {{ number_format((float) $booking->price_bgn, 2, ',', '.') }}&nbsp;лв.
                            @endif
                        </p>
                    </div>

                    <div class="py-2 sm:py-0 sm:px-3">
                        <p class="text-xs text-petrova-secondary/50 mb-1 italic">Метод на плащане</p>
                        <p class="text-petrova-primary font-medium">{{ $booking->paymentMethodLabel() }}</p>
                    </div>

                    <div class="py-2 sm:py-0 sm:px-3">
                        <p class="text-xs text-petrova-secondary/50 mb-1 italic">Статус</p>
                        <p class="text-petrova-gold font-semibold">Очаква потвърждение</p>
                    </div>

                </div>
            </div>

            {{-- Two-column detail --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left mb-10">

                {{-- Services --}}
                <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5">
                    <p class="text-sm font-semibold italic text-petrova-gold mb-4">Услуги</p>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Телефонна консултация</span>
                            <span class="text-petrova-primary">30 минути</span>
                        </div>
                        <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                            <span class="text-petrova-gold font-medium">Дата</span>
                            <span class="text-petrova-primary">
                                {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y г.') }}
                            </span>
                        </div>
                        <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                            <span class="text-petrova-gold font-medium">Часови диапазон</span>
                            <span class="text-petrova-primary">
                                {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                                –
                                {{ $booking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                            <span class="text-petrova-gold font-medium">Обща сума:</span>
                            <span class="text-petrova-primary font-semibold">
                                {{ number_format((float) $booking->price_eur, 2, ',', '.') }}&nbsp;€
                                @if ($booking->show_bgn_price && $booking->price_bgn)
                                    / {{ number_format((float) $booking->price_bgn, 2, ',', '.') }}&nbsp;лв.
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Personal data --}}
                <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5">
                    <p class="text-sm font-semibold italic text-petrova-gold mb-4">Лични данни</p>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Име</span>
                            <span class="text-petrova-primary">{{ $booking->first_name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Фамилия</span>
                            <span class="text-petrova-primary">{{ $booking->last_name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Email</span>
                            <span class="text-petrova-primary text-xs break-all">{{ $booking->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Телефон</span>
                            <span class="text-petrova-primary">{{ $booking->phone }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- CTA --}}
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded border border-petrova-gold/40
                      text-petrova-gold text-sm font-semibold hover:bg-petrova-gold/10 transition-colors">
                Към началната страница
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </a>

        </div>
    </section>

</div>

@endsection
