@extends('layouts.app')

@section('title', 'Онлайн чат консултация')
@section('description', 'Изберете удобен ден и час за онлайн чат консултация с адвокат Петрова.')

@section('content')

<div class="consultation-page">

    {{-- ── Hero ──────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden">

        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/consultation/landing_background.webp') }}');"
            role="presentation"
        ></div>

        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/98 via-petrova-deep/95 to-petrova-main"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 pt-16 pb-16">

            {{-- Breadcrumb --}}
            <nav class="mb-8 flex items-center gap-2 text-sm text-petrova-secondary/70" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-petrova-primary transition-colors">
                    <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Начало
                </a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('consultation') }}" class="hover:text-petrova-primary transition-colors">Онлайн консултация</a>
                <span aria-hidden="true">/</span>
                <span class="text-petrova-secondary/50">Чат консултация</span>
            </nav>

            {{-- Page title --}}
            <h1 class="font-cormorant text-4xl font-bold italic tracking-tight text-petrova-primary sm:text-5xl">
                Онлайн чат консултация
            </h1>

            {{-- Instructions card --}}
            <div class="mt-10 rounded-xl border border-petrova-gold/20 bg-petrova-deep/70 backdrop-blur-sm px-6 py-6 space-y-4">
                <div>
                    <p class="text-base font-semibold italic text-petrova-gold mb-1">Инструкции</p>
                    <p class="text-sm text-petrova-secondary/80 leading-relaxed">
                        Изберете удобен ден и час за чат консултация. Консултацията се провежда в реално
                        време чрез писмен чат и продължава до 30 минути.
                        След потвърждение на заявката ще получите достъп до чат стаята.
                    </p>
                </div>
                <div>
                    <p class="text-base font-semibold italic text-petrova-gold mb-1">Важно</p>
                    <p class="text-sm text-petrova-secondary/80 leading-relaxed">
                        Моля, бъдете на разположение в записания час. Консултацията стартира при
                        присъединяване на адвоката. Всички разговори са конфиденциални.
                    </p>
                </div>
            </div>

            {{-- Validation error banner --}}
            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-red-400/40 bg-red-900/30 px-5 py-4">
                    <p class="text-sm font-semibold text-red-300 mb-1">Моля, поправи следните грешки:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-300">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Main form ────────────────────────────────────────── --}}
            <form
                id="chat-form"
                action="{{ route('chat-consultation.submit') }}"
                method="POST"
                novalidate
            >
                @csrf

                {{-- Hidden fields carrying state across steps --}}
                <input type="hidden" name="selected_date" id="f-selected-date" value="{{ old('selected_date') }}">
                <input type="hidden" name="selected_slot" id="f-selected-slot" value="{{ old('selected_slot') }}">

                {{-- ══════════════════════════════════════════════════
                     STEP 1 — Calendar + Slot picker
                ══════════════════════════════════════════════════ --}}
                <div id="step-1" class="mt-6">

                    <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/70 backdrop-blur-sm px-4 py-6 sm:px-6">

                        {{-- Calendar header --}}
                        <div class="flex items-center justify-between mb-4">
                            <button type="button" id="cal-prev" aria-label="Предишен месец"
                                class="w-9 h-9 flex items-center justify-center rounded border border-petrova-gold/30
                                       text-petrova-gold hover:bg-petrova-gold/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <p id="cal-title" class="text-base font-semibold text-petrova-primary tracking-wide"></p>
                            <button type="button" id="cal-next" aria-label="Следващ месец"
                                class="w-9 h-9 flex items-center justify-center rounded border border-petrova-gold/30
                                       text-petrova-gold hover:bg-petrova-gold/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>

                        {{-- Day-of-week headers --}}
                        <div class="grid grid-cols-7 mb-1">
                            @foreach (['П','В','С','Ч','П','С','Н'] as $label)
                                <div class="text-center text-xs font-semibold text-petrova-secondary/50 py-1.5">{{ $label }}</div>
                            @endforeach
                        </div>

                        {{-- Calendar grid --}}
                        <div id="cal-grid" class="grid grid-cols-7 gap-0.5"></div>

                        {{-- Slot section --}}
                        <div id="slots-section" class="mt-5 border-t border-petrova-gold/10 pt-5 hidden">
                            <p id="slots-date-label" class="text-xs font-semibold tracking-widest uppercase text-petrova-secondary/50 mb-3"></p>
                            <div id="slots-loading" class="hidden text-sm text-petrova-secondary/60 py-2">Зарежда се…</div>
                            <div id="slots-empty"   class="hidden text-sm text-petrova-secondary/60 py-2">Няма налични часове за този ден.</div>
                            <div id="slots-grid"    class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2"></div>
                        </div>

                    </div>

                    {{-- Step 1 footer --}}
                    <div class="mt-5 flex items-center justify-between">
                        <span class="text-sm text-petrova-secondary/50">1 / 3</span>
                        <button
                            type="button"
                            id="btn-to-step2"
                            disabled
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded bg-petrova-gold text-petrova-deep
                                   text-sm font-semibold transition hover:bg-petrova-gold-hover
                                   disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Напред
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-7.5-15-7.5v6l10 1.5-10 1.5v6z" />
                            </svg>
                        </button>
                    </div>

                </div>
                {{-- end step 1 --}}

                {{-- ══════════════════════════════════════════════════
                     STEP 2 — Personal data + payment method
                ══════════════════════════════════════════════════ --}}
                <div id="step-2" class="mt-6 hidden">

                    <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/70 backdrop-blur-sm px-6 py-6 space-y-5">

                        <p class="text-base font-semibold italic text-petrova-gold">Лични данни</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label for="first_name" class="block text-sm font-medium text-petrova-secondary/80 mb-1.5">
                                    Име <span class="text-petrova-gold">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    value="{{ old('first_name') }}"
                                    placeholder="Вашето име"
                                    class="w-full px-3 py-2.5 rounded border bg-petrova-deep/50 text-petrova-primary
                                           placeholder-petrova-secondary/30 text-sm
                                           focus:outline-none focus:border-petrova-gold/60 transition-colors
                                           {{ $errors->has('first_name') ? 'border-red-400/60' : 'border-petrova-gold/20' }}"
                                >
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-petrova-secondary/80 mb-1.5">
                                    Фамилия <span class="text-petrova-gold">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    value="{{ old('last_name') }}"
                                    placeholder="Вашата фамилия"
                                    class="w-full px-3 py-2.5 rounded border bg-petrova-deep/50 text-petrova-primary
                                           placeholder-petrova-secondary/30 text-sm
                                           focus:outline-none focus:border-petrova-gold/60 transition-colors
                                           {{ $errors->has('last_name') ? 'border-red-400/60' : 'border-petrova-gold/20' }}"
                                >
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-petrova-secondary/80 mb-1.5">
                                    Телефон <span class="text-petrova-gold">*</span>
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="+359 XXX XXX XXXX"
                                    class="w-full px-3 py-2.5 rounded border bg-petrova-deep/50 text-petrova-primary
                                           placeholder-petrova-secondary/30 text-sm
                                           focus:outline-none focus:border-petrova-gold/60 transition-colors
                                           {{ $errors->has('phone') ? 'border-red-400/60' : 'border-petrova-gold/20' }}"
                                >
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-petrova-secondary/80 mb-1.5">
                                    Имейл адрес <span class="text-petrova-gold">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Вашият имейл"
                                    class="w-full px-3 py-2.5 rounded border bg-petrova-deep/50 text-petrova-primary
                                           placeholder-petrova-secondary/30 text-sm
                                           focus:outline-none focus:border-petrova-gold/60 transition-colors
                                           {{ $errors->has('email') ? 'border-red-400/60' : 'border-petrova-gold/20' }}"
                                >
                            </div>

                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-petrova-secondary/80 mb-1.5">
                                Кратко описание <span class="text-petrova-secondary/40 font-normal">(незадължително)</span>
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                maxlength="2000"
                                placeholder="Опишете накратко темата на консултацията"
                                class="w-full px-3 py-2.5 rounded border bg-petrova-deep/50 text-petrova-primary
                                       placeholder-petrova-secondary/30 text-sm resize-y
                                       focus:outline-none focus:border-petrova-gold/60 transition-colors
                                       border-petrova-gold/20"
                            >{{ old('description') }}</textarea>
                        </div>

                        {{-- Payment method --}}
                        <div>
                            <p class="text-base font-semibold italic text-petrova-gold mb-3">Метод на плащане</p>
                            <div class="space-y-2">
                                @foreach (\App\Models\ChatConsultationBooking::PAYMENT_METHODS as $value => $label)
                                    <label class="flex items-center gap-3 cursor-pointer select-none group">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="{{ $value }}"
                                            {{ old('payment_method') === $value ? 'checked' : '' }}
                                            class="w-4 h-4 accent-petrova-gold cursor-pointer"
                                        >
                                        <span class="text-sm text-petrova-secondary/80 group-hover:text-petrova-primary transition-colors">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Consent --}}
                        <div class="pt-2 border-t border-petrova-gold/10">
                            <p class="text-xs text-petrova-secondary/50 mb-3">
                                Вашите лични данни ще бъдат използвани за обработка и предоставяне на заявената онлайн консултация.
                            </p>
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    name="consent"
                                    value="1"
                                    {{ old('consent') ? 'checked' : '' }}
                                    class="mt-0.5 w-4 h-4 accent-petrova-gold cursor-pointer flex-shrink-0"
                                >
                                <span class="text-xs text-petrova-secondary/70">
                                    Прочетох и съм съгласен с
                                    <a href="{{ route('privacy') }}" target="_blank" class="text-petrova-gold hover:underline">Политиката за поверителност</a>
                                </span>
                            </label>
                        </div>

                    </div>

                    {{-- Step 2 footer --}}
                    <div class="mt-5 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-petrova-secondary/50">2 / 3</span>
                            <button type="button" id="btn-back-to-step1"
                                class="text-sm text-petrova-secondary/60 hover:text-petrova-primary transition-colors">
                                Назад
                            </button>
                        </div>
                        <button
                            type="button"
                            id="btn-to-step3"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded bg-petrova-gold text-petrova-deep
                                   text-sm font-semibold transition hover:bg-petrova-gold-hover"
                        >
                            Напред
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-7.5-15-7.5v6l10 1.5-10 1.5v6z" />
                            </svg>
                        </button>
                    </div>

                </div>
                {{-- end step 2 --}}

                {{-- ══════════════════════════════════════════════════
                     STEP 3 — Review + final submit
                ══════════════════════════════════════════════════ --}}
                <div id="step-3" class="mt-6 hidden">

                    {{-- Step 3 instructions --}}
                    <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/70 backdrop-blur-sm px-6 py-6 space-y-4 mb-6">
                        <div>
                            <p class="text-base font-semibold italic text-petrova-gold mb-1">Инструкции</p>
                            <p class="text-sm text-petrova-secondary/80 leading-relaxed">
                                Прегледайте данните преди да потвърдите заявката. В избрания ден и час
                                адвокатът ще се присъедини към чат стаята и консултацията ще започне.
                            </p>
                        </div>
                        <div>
                            <p class="text-base font-semibold italic text-petrova-gold mb-1">Важно</p>
                            <p class="text-sm text-petrova-secondary/80 leading-relaxed">
                                Консултацията се провежда изцяло чрез писмен чат. Продължителност: до 30 минути.
                                Всички разговори са конфиденциални.
                            </p>
                        </div>
                    </div>

                    {{-- Review header --}}
                    <p class="text-base font-semibold italic text-petrova-gold mb-4">Преглед на данните</p>

                    {{-- Two-column summary --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Services column --}}
                        <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5">
                            <p class="text-sm font-semibold text-petrova-secondary/60 mb-4">Услуги</p>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-petrova-secondary/70">Онлайн чат консултация</span>
                                    <span class="text-petrova-primary font-medium">30 минути</span>
                                </div>
                                <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                                    <span class="text-petrova-gold font-medium">Дата</span>
                                    <span id="review-date" class="text-petrova-primary font-medium"></span>
                                </div>
                                <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                                    <span class="text-petrova-gold font-medium">Час</span>
                                    <span id="review-slot" class="text-petrova-primary font-medium"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Personal data column --}}
                        <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5">
                            <p class="text-sm font-semibold text-petrova-secondary/60 mb-4">Лични данни</p>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                                    <span class="text-petrova-gold font-medium">Име</span>
                                    <span id="review-first-name" class="text-petrova-primary font-medium"></span>
                                </div>
                                <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                                    <span class="text-petrova-gold font-medium">Фамилия</span>
                                    <span id="review-last-name" class="text-petrova-primary font-medium"></span>
                                </div>
                                <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                                    <span class="text-petrova-gold font-medium">Email</span>
                                    <span id="review-email" class="text-petrova-primary font-medium text-xs break-all"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-petrova-gold font-medium">Телефон</span>
                                    <span id="review-phone" class="text-petrova-primary font-medium"></span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Payment method + total --}}
                    <div class="mt-4 rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Метод на плащане</span>
                            <span id="review-payment" class="text-petrova-primary font-medium"></span>
                        </div>
                        <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                            <span class="text-petrova-gold font-medium">Обща сума</span>
                            <span class="text-petrova-primary font-semibold">
                                @if ($pricing)
                                    {{ number_format((float) $pricing->price_eur, 2, ',', '.') }}&nbsp;€
                                    @if ($pricing->show_bgn_price && $pricing->price_bgn)
                                        / {{ number_format((float) $pricing->price_bgn, 2, ',', '.') }}&nbsp;лв.
                                    @endif
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Step 3 footer --}}
                    <div class="mt-5 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-petrova-secondary/50">3 / 3</span>
                            <button type="button" id="btn-back-to-step2"
                                class="text-sm text-petrova-secondary/60 hover:text-petrova-primary transition-colors">
                                Назад
                            </button>
                        </div>
                        <button
                            type="submit"
                            id="btn-submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded bg-petrova-gold text-petrova-deep
                                   text-sm font-semibold transition hover:bg-petrova-gold-hover"
                        >
                            Потвърди записването
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-7.5-15-7.5v6l10 1.5-10 1.5v6z" />
                            </svg>
                        </button>
                    </div>

                </div>
                {{-- end step 3 --}}

            </form>

        </div>
    </section>

</div>

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Constants ────────────────────────────────────────────────────
    var SLOTS_URL   = '{{ route('chat-consultation.slots') }}';
    var MONTH_NAMES = ['Януари','Февруари','Март','Април','Май','Юни',
                       'Юли','Август','Септември','Октомври','Ноември','Декември'];
    var PAYMENT_LABELS = {
        card:    'Плащане с дебитна/кредитна карта',
        easypay: 'Плащане с Easy Pay',
        epay:    'Плащане с ePay',
    };

    // ── State ────────────────────────────────────────────────────────
    var today        = new Date();
    today.setHours(0, 0, 0, 0);

    var viewYear     = today.getFullYear();
    var viewMonth    = today.getMonth();
    var selectedDate = null;
    var selectedSlot = null;
    var currentSlots = [];

    var maxDate = new Date(today);
    maxDate.setMonth(maxDate.getMonth() + 3);

    // ── DOM refs ─────────────────────────────────────────────────────
    var calTitle     = document.getElementById('cal-title');
    var calGrid      = document.getElementById('cal-grid');
    var calPrev      = document.getElementById('cal-prev');
    var calNext      = document.getElementById('cal-next');
    var slotsSection = document.getElementById('slots-section');
    var slotsLabel   = document.getElementById('slots-date-label');
    var slotsLoading = document.getElementById('slots-loading');
    var slotsEmpty   = document.getElementById('slots-empty');
    var slotsGrid    = document.getElementById('slots-grid');

    var fDate  = document.getElementById('f-selected-date');
    var fSlot  = document.getElementById('f-selected-slot');

    var step1  = document.getElementById('step-1');
    var step2  = document.getElementById('step-2');
    var step3  = document.getElementById('step-3');

    var btnToStep2     = document.getElementById('btn-to-step2');
    var btnBackToStep1 = document.getElementById('btn-back-to-step1');
    var btnToStep3     = document.getElementById('btn-to-step3');
    var btnBackToStep2 = document.getElementById('btn-back-to-step2');

    // ── Helpers ──────────────────────────────────────────────────────
    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function toISO(y, m, d) {
        return y + '-' + pad(m + 1) + '-' + pad(d);
    }

    function isSameDay(a, b) {
        return a.getFullYear() === b.getFullYear() &&
               a.getMonth()   === b.getMonth()    &&
               a.getDate()    === b.getDate();
    }

    function formatBulgarianDate(isoStr) {
        var parts = isoStr.split('-');
        var d = parseInt(parts[2], 10);
        var m = parseInt(parts[1], 10) - 1;
        var y = parseInt(parts[0], 10);
        return d + ' ' + MONTH_NAMES[m] + ' ' + y + ' г.';
    }

    function showStep(n) {
        step1.classList.toggle('hidden', n !== 1);
        step2.classList.toggle('hidden', n !== 2);
        step3.classList.toggle('hidden', n !== 3);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Calendar ─────────────────────────────────────────────────────
    function renderCalendar() {
        calTitle.textContent = MONTH_NAMES[viewMonth] + ' ' + viewYear;

        var atMin = (viewYear === today.getFullYear() && viewMonth === today.getMonth());
        calPrev.disabled = atMin;
        calPrev.classList.toggle('opacity-30', atMin);
        calPrev.classList.toggle('cursor-not-allowed', atMin);

        var maxY = maxDate.getFullYear();
        var maxM = maxDate.getMonth();
        var atMax = (viewYear > maxY || (viewYear === maxY && viewMonth >= maxM));
        calNext.disabled = atMax;
        calNext.classList.toggle('opacity-30', atMax);
        calNext.classList.toggle('cursor-not-allowed', atMax);

        calGrid.innerHTML = '';

        var firstDay = new Date(viewYear, viewMonth, 1);
        var isoFirst = firstDay.getDay() === 0 ? 7 : firstDay.getDay();
        var offset   = isoFirst - 1;
        var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

        for (var i = 0; i < offset; i++) {
            calGrid.appendChild(document.createElement('div'));
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var cellDate   = new Date(viewYear, viewMonth, day);
            var iso        = toISO(viewYear, viewMonth, day);
            var isPast     = cellDate < today;
            var isFuture   = cellDate > maxDate;
            var isToday    = isSameDay(cellDate, today);
            var isSelected = (iso === selectedDate);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = day;
            btn.dataset.date = iso;
            btn.className = 'relative w-full aspect-square flex items-center justify-center text-sm rounded transition-colors';

            if (isPast || isFuture) {
                btn.disabled = true;
                btn.classList.add('text-petrova-secondary/20', 'cursor-not-allowed');
            } else if (isSelected) {
                btn.classList.add('bg-petrova-gold', 'text-petrova-deep', 'font-semibold');
            } else if (isToday) {
                btn.classList.add('border', 'border-petrova-gold/60', 'text-petrova-gold', 'font-semibold', 'hover:bg-petrova-gold/10');
            } else {
                btn.classList.add('text-petrova-secondary/80', 'hover:bg-petrova-gold/10', 'hover:text-petrova-primary');
            }

            if (!isPast && !isFuture) {
                btn.addEventListener('click', function (e) {
                    selectDate(e.currentTarget.dataset.date);
                });
            }

            calGrid.appendChild(btn);
        }
    }

    function selectDate(iso) {
        selectedDate = iso;
        selectedSlot = null;
        fDate.value  = iso;
        fSlot.value  = '';
        btnToStep2.disabled = true;
        renderCalendar();
        loadSlots(iso);
    }

    // ── Slots ────────────────────────────────────────────────────────
    function loadSlots(iso) {
        slotsSection.classList.remove('hidden');
        slotsLabel.textContent = formatBulgarianDate(iso);
        slotsLoading.classList.remove('hidden');
        slotsEmpty.classList.add('hidden');
        slotsGrid.innerHTML = '';
        currentSlots = [];

        fetch(SLOTS_URL + '?date=' + encodeURIComponent(iso), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            slotsLoading.classList.add('hidden');
            currentSlots = data.slots || [];
            renderSlots(currentSlots);
        })
        .catch(function () {
            slotsLoading.classList.add('hidden');
            slotsEmpty.classList.remove('hidden');
            slotsEmpty.textContent = 'Грешка при зареждане. Моля, опитайте отново.';
        });
    }

    function renderSlots(slots) {
        slotsGrid.innerHTML = '';

        if (!slots.length) {
            slotsEmpty.classList.remove('hidden');
            return;
        }

        slotsEmpty.classList.add('hidden');

        slots.forEach(function (time) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = time;
            btn.dataset.slot = time;

            var isActive = (time === selectedSlot);
            btn.className = 'px-3 py-2 rounded text-sm font-medium text-center transition-colors ' + (
                isActive
                    ? 'bg-petrova-gold text-petrova-deep'
                    : 'border border-petrova-gold/30 text-petrova-secondary/80 hover:border-petrova-gold hover:text-petrova-primary'
            );

            btn.addEventListener('click', function (e) {
                selectedSlot        = e.currentTarget.dataset.slot;
                fSlot.value         = selectedSlot;
                btnToStep2.disabled = false;
                renderSlots(currentSlots);
            });

            slotsGrid.appendChild(btn);
        });
    }

    // ── Navigation ───────────────────────────────────────────────────
    calPrev.addEventListener('click', function () {
        if (calPrev.disabled) return;
        viewMonth--;
        if (viewMonth < 0) { viewMonth = 11; viewYear--; }
        renderCalendar();
    });

    calNext.addEventListener('click', function () {
        if (calNext.disabled) return;
        viewMonth++;
        if (viewMonth > 11) { viewMonth = 0; viewYear++; }
        renderCalendar();
    });

    // ── Step transitions ─────────────────────────────────────────────
    btnToStep2.addEventListener('click', function () {
        if (!selectedDate || !selectedSlot) return;
        showStep(2);
    });

    btnBackToStep1.addEventListener('click', function () {
        showStep(1);
    });

    btnToStep3.addEventListener('click', function () {
        var firstName = document.getElementById('first_name').value.trim();
        var lastName  = document.getElementById('last_name').value.trim();
        var email     = document.getElementById('email').value.trim();
        var phone     = document.getElementById('phone').value.trim();
        var payment   = document.querySelector('input[name="payment_method"]:checked');
        var consent   = document.querySelector('input[name="consent"]');

        if (!firstName || !lastName || !email || !phone || !payment || !consent.checked) {
            document.getElementById('chat-form').submit();
            return;
        }

        document.getElementById('review-date').textContent       = formatBulgarianDate(selectedDate);
        document.getElementById('review-slot').textContent       = selectedSlot + ' ч.';
        document.getElementById('review-first-name').textContent = firstName;
        document.getElementById('review-last-name').textContent  = lastName;
        document.getElementById('review-email').textContent      = email;
        document.getElementById('review-phone').textContent      = phone;
        document.getElementById('review-payment').textContent    = PAYMENT_LABELS[payment.value] || payment.value;

        showStep(3);
    });

    btnBackToStep2.addEventListener('click', function () {
        showStep(2);
    });

    // ── Init ─────────────────────────────────────────────────────────
    var oldDate = fDate.value;
    var oldSlot = fSlot.value;

    if (oldDate) {
        selectedDate = oldDate;
        var parts = oldDate.split('-');
        viewYear  = parseInt(parts[0], 10);
        viewMonth = parseInt(parts[1], 10) - 1;
    }

    renderCalendar();

    if (oldDate) {
        loadSlots(oldDate);
        if (oldSlot) {
            selectedSlot = oldSlot;
            btnToStep2.disabled = false;
        }
        @if ($errors->any())
            if (oldSlot) { showStep(2); }
        @endif
    }

}());
</script>
@endpush

@endsection
