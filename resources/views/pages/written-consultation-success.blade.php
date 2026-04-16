@extends('layouts.app')

@section('title', 'Статус на заявка — Писмена консултация')
@section('description', 'Статус на Вашата заявка за писмена консултация.')

@section('content')

@php
    $payment   = $consultationRequest?->payment ?? null;
    $isPending = ! $payment || $payment->isPending();
    $isPaid    = $payment?->isPaid();
    $isFailed  = $payment?->isFailed();
    $isExpired = $payment?->isExpired();
@endphp

<div class="min-h-screen bg-petrova-deep flex flex-col items-center justify-center px-4 py-20">

    {{-- Logo --}}
    <div class="mb-8">
        <a href="{{ route('home') }}">
            <img src="{{ asset('images/logo-gold.svg') }}" alt="RP Law" class="h-16 w-auto mx-auto">
        </a>
    </div>

    {{-- Heading --}}
    <p class="text-sm text-petrova-secondary/70 mb-2 tracking-wide">Благодарим Ви за доверието!</p>
    <h1 class="font-cormorant text-3xl font-bold italic text-petrova-primary text-center sm:text-4xl mb-4">
        @if ($isPaid) Плащането е потвърдено!
        @elseif ($isFailed) Плащането е неуспешно.
        @elseif ($isExpired) Времето за плащане изтече.
        @else Вашето запитване е получено!
        @endif
    </h1>

    {{-- Payment status banner --}}
    @if ($isPending)
        <div class="w-full max-w-3xl mb-6 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-300 text-center">
            Заявката е регистрирана. Очаква се потвърждение на плащането.
            @if ($payment)
                <a href="{{ route('payment.simulate', ['invoice' => $payment->invoice_number]) }}" class="ml-2 underline font-semibold">Към плащане →</a>
            @endif
        </div>
    @elseif ($isPaid)
        <div class="w-full max-w-3xl mb-6 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-300 text-center">
            Плащането е потвърдено. Заявката е приета за обработка. Ще получите отговор в рамките на <strong>48 часа</strong>.
        </div>
    @elseif ($isFailed)
        <div class="w-full max-w-3xl mb-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300 text-center">
            Плащането е отказано или неуспешно. Моля, свържете се с нас за съдействие.
        </div>
    @elseif ($isExpired)
        <div class="w-full max-w-3xl mb-6 rounded-lg border border-petrova-gold/20 bg-petrova-deep/60 px-4 py-3 text-sm text-petrova-secondary/70 text-center">
            Времето за плащане е изтекло. Заявката е отменена.
        </div>
    @endif

    @if ($consultationRequest)

        {{-- Summary strip --}}
        <div class="w-full max-w-3xl mb-8 rounded border border-petrova-gold/20 bg-petrova-main/60 overflow-hidden">
            <div class="grid grid-cols-2 divide-x divide-petrova-gold/10 sm:grid-cols-3 lg:grid-cols-6">

                <div class="px-4 py-4 border-b border-petrova-gold/10 sm:border-b-0">
                    <p class="text-xs text-petrova-secondary/60 mb-1 italic">№ Запитване</p>
                    <p class="text-sm font-semibold text-petrova-primary">{{ $consultationRequest->id }}</p>
                </div>

                <div class="px-4 py-4 border-b border-petrova-gold/10 sm:border-b-0">
                    <p class="text-xs text-petrova-secondary/60 mb-1 italic">Дата</p>
                    <p class="text-sm font-semibold text-petrova-primary">
                        {{ $consultationRequest->submitted_at?->format('d.m.Y г.') ?? $consultationRequest->created_at->format('d.m.Y г.') }}
                    </p>
                </div>

                <div class="px-4 py-4 border-b border-petrova-gold/10 sm:border-b-0">
                    <p class="text-xs text-petrova-secondary/60 mb-1 italic">Имейл</p>
                    <p class="text-sm font-semibold text-petrova-primary truncate">{{ $consultationRequest->email }}</p>
                </div>

                <div class="px-4 py-4 border-b border-petrova-gold/10 sm:border-b-0">
                    <p class="text-xs text-petrova-secondary/60 mb-1 italic">Обща сума</p>
                    <p class="text-sm font-semibold text-petrova-primary">
                        {{ number_format((float) $consultationRequest->price_eur, 2, ',', '.') }}&nbsp;€
                        @if ($consultationRequest->show_bgn_price && $consultationRequest->price_bgn)
                            / {{ number_format((float) $consultationRequest->price_bgn, 2, ',', '.') }}&nbsp;лв.
                        @endif
                    </p>
                </div>

                <div class="px-4 py-4 border-b border-petrova-gold/10 sm:border-b-0">
                    <p class="text-xs text-petrova-secondary/60 mb-1 italic">Метод на плащане</p>
                    <p class="text-sm font-semibold text-petrova-primary">{{ $consultationRequest->paymentMethodLabel() }}</p>
                </div>

                <div class="px-4 py-4">
                    <p class="text-xs text-petrova-secondary/60 mb-1 italic">Статус плащане</p>
                    @if ($isPaid) <p class="text-sm font-semibold text-green-400">Платено</p>
                    @elseif ($isFailed) <p class="text-sm font-semibold text-red-400">Неуспешно</p>
                    @elseif ($isExpired) <p class="text-sm font-semibold text-petrova-secondary/60">Изтекло</p>
                    @else <p class="text-sm font-semibold text-amber-400">Очаква плащане</p>
                    @endif
                </div>

            </div>
        </div>

        {{-- Detail cards --}}
        <div class="w-full max-w-3xl grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">

            {{-- Services --}}
            <div class="rounded border border-petrova-gold/20 bg-petrova-main/60 px-5 py-5">
                <h2 class="font-cormorant text-sm font-bold italic text-petrova-primary mb-4">Услуги</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                        <span class="text-petrova-gold font-medium">Писмена консултация</span>
                        <span class="text-petrova-secondary">до 48 часа</span>
                    </div>
                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                        <span class="text-petrova-secondary/70">Заглавие</span>
                        <span class="text-petrova-secondary text-right max-w-[55%]">{{ $consultationRequest->title }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-petrova-gold font-medium">Обща сума:</span>
                        <span class="text-petrova-secondary">
                            {{ number_format((float) $consultationRequest->price_eur, 2, ',', '.') }}&nbsp;€
                            @if ($consultationRequest->show_bgn_price && $consultationRequest->price_bgn)
                                / {{ number_format((float) $consultationRequest->price_bgn, 2, ',', '.') }}&nbsp;лв.
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Personal data --}}
            <div class="rounded border border-petrova-gold/20 bg-petrova-main/60 px-5 py-5">
                <h2 class="font-cormorant text-sm font-bold italic text-petrova-primary mb-4">Лични данни</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                        <span class="text-petrova-gold font-medium">Име</span>
                        <span class="text-petrova-secondary">{{ $consultationRequest->first_name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                        <span class="text-petrova-gold font-medium">Фамилия</span>
                        <span class="text-petrova-secondary">{{ $consultationRequest->last_name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                        <span class="text-petrova-gold font-medium">Email</span>
                        <span class="text-petrova-secondary truncate max-w-[55%]">{{ $consultationRequest->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-petrova-gold font-medium">Телефон</span>
                        <span class="text-petrova-secondary">{{ $consultationRequest->phone }}</span>
                    </div>
                </div>
            </div>

        </div>

    @endif

    {{-- CTA --}}
    <a
        href="{{ route('home') }}"
        class="inline-flex items-center gap-2 rounded bg-petrova-gold px-6 py-3 text-sm font-semibold text-petrova-deep hover:bg-petrova-gold-hover transition-colors"
    >
        Към началната страница
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
    </a>

</div>

@endsection
