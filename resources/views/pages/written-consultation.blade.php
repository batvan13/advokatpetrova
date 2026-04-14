@extends('layouts.app')

@section('title', 'Писмена консултация')
@section('description', 'Изпратете Вашия казус и получете подробен правен анализ по имейл в рамките на 48 часа.')

@section('content')

<div>

    {{-- ── Hero / Background ──────────────────────────────────────── --}}
    <section class="relative min-h-screen overflow-hidden">

        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/consultation/landing_background.webp') }}');"
            role="presentation"
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/90 via-petrova-deep/80 to-petrova-main/95"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto max-w-3xl px-4 pt-16 pb-20">

            {{-- Breadcrumb --}}
            <nav class="mb-6 flex items-center gap-2 text-sm text-petrova-secondary/70" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-petrova-primary transition-colors">
                    <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Начало
                </a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('consultation') }}" class="hover:text-petrova-primary transition-colors">Онлайн консултация</a>
            </nav>

            {{-- Page title --}}
            <h1 class="font-cormorant mb-8 text-4xl font-bold italic tracking-tight text-petrova-primary sm:text-5xl">
                Писмена консултация
            </h1>

            {{-- ── Validation errors (server-side) ──────────────────── --}}
            @if ($errors->any())
                <div class="mb-6 rounded border border-red-400/40 bg-red-900/30 px-5 py-4">
                    <p class="mb-2 text-sm font-semibold text-red-300">Моля, поправете следните грешки:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-300">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Main form panel ───────────────────────────────────── --}}
            <form
                id="wcf"
                action="{{ route('written-consultation.submit') }}"
                method="POST"
                enctype="multipart/form-data"
                novalidate
            >
                @csrf

                {{-- ── Instructions block (visible on all steps) ──────── --}}
                <div class="mb-5 rounded border border-petrova-gold/20 bg-petrova-deep/60 px-6 py-5">
                    <h2 class="font-cormorant text-base font-bold italic text-petrova-gold">Инструкции</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-petrova-secondary">
                        След изпращане на заявката и успешно плащане ще получите имейл с потвърждение. Вашият казус ще бъде разгледан от адвокат в рамките на посочения срок. Отговорът ще бъде изпратен директно на имейл.
                    </p>
                    <h2 class="font-cormorant mt-4 text-base font-bold italic text-petrova-gold">Важно</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-petrova-secondary">
                        Моля, опишете казуса максимално подробно. Можете да прикачите документи за по-точен правен анализ. Максимален срок за отговор: до 48 часа (освен ако не е указано друго).
                    </p>
                </div>

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- STEP 1                                               --}}
                {{-- ════════════════════════════════════════════════════ --}}
                <div id="wcf-step-1" class="wcf-step">

                    <div class="rounded border border-petrova-gold/20 bg-petrova-deep/60 px-6 py-6 space-y-5">

                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-petrova-secondary mb-1.5">
                                Заглавие на казуса <span class="text-petrova-gold">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="Накратко опишете темата"
                                class="w-full rounded border px-3 py-2.5 text-sm bg-petrova-main/80 text-petrova-primary placeholder-petrova-secondary/50 border-petrova-gold/20 focus:outline-none focus:border-petrova-gold/60 transition-colors
                                       {{ $errors->has('title') ? 'border-red-400/60' : '' }}"
                            >
                            @error('title')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-petrova-secondary mb-1.5">
                                Моля, предоставете възможно най-много детайли, за да получите точен и полезен отговор. <span class="text-petrova-gold">*</span>
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="7"
                                maxlength="5000"
                                placeholder="Опишете Вашия случай"
                                class="w-full rounded border px-3 py-2.5 text-sm bg-petrova-main/80 text-petrova-primary placeholder-petrova-secondary/50 border-petrova-gold/20 focus:outline-none focus:border-petrova-gold/60 transition-colors resize-y
                                       {{ $errors->has('description') ? 'border-red-400/60' : '' }}"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- File upload --}}
                        <div>
                            <div
                                id="wcf-drop-zone"
                                class="rounded border-2 border-dashed border-petrova-gold/30 bg-petrova-main/40 px-6 py-8 text-center cursor-pointer hover:border-petrova-gold/60 transition-colors"
                            >
                                <svg class="mx-auto h-9 w-9 text-petrova-gold/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                <p class="mt-3 text-sm italic font-medium text-petrova-secondary">Изберете файлове или ги пуснете тук</p>
                                <p class="mt-1 text-xs text-petrova-secondary/60">JPEG, PNG, PDF, DOC, DOCX формати, до 5 файла / 10MB всеки</p>
                                <button
                                    type="button"
                                    id="wcf-pick-btn"
                                    class="mt-4 inline-flex items-center rounded bg-petrova-gold px-5 py-2 text-sm font-semibold text-petrova-deep hover:bg-petrova-gold-hover transition-colors"
                                >
                                    Изберете файлове
                                </button>
                                <input
                                    type="file"
                                    id="wcf-file-input"
                                    name="files[]"
                                    multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    class="hidden"
                                >
                            </div>

                            {{-- File list preview --}}
                            <ul id="wcf-file-list" class="mt-3 space-y-1.5 text-sm text-petrova-secondary hidden"></ul>

                            @error('files')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                            @error('files.*')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- STEP 2                                               --}}
                {{-- ════════════════════════════════════════════════════ --}}
                <div id="wcf-step-2" class="wcf-step hidden">

                    <div class="rounded border border-petrova-gold/20 bg-petrova-deep/60 px-6 py-6 space-y-6">

                        {{-- Personal data --}}
                        <div>
                            <h2 class="font-cormorant text-base font-bold italic text-petrova-gold mb-4">Лични данни</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-petrova-secondary mb-1.5">
                                        Име <span class="text-petrova-gold">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="first_name"
                                        name="first_name"
                                        value="{{ old('first_name') }}"
                                        placeholder="Вашето име"
                                        class="w-full rounded border px-3 py-2.5 text-sm bg-petrova-main/80 text-petrova-primary placeholder-petrova-secondary/50 border-petrova-gold/20 focus:outline-none focus:border-petrova-gold/60 transition-colors
                                               {{ $errors->has('first_name') ? 'border-red-400/60' : '' }}"
                                    >
                                    @error('first_name')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-petrova-secondary mb-1.5">
                                        Фамилия <span class="text-petrova-gold">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        value="{{ old('last_name') }}"
                                        placeholder="Вашата фамилия"
                                        class="w-full rounded border px-3 py-2.5 text-sm bg-petrova-main/80 text-petrova-primary placeholder-petrova-secondary/50 border-petrova-gold/20 focus:outline-none focus:border-petrova-gold/60 transition-colors
                                               {{ $errors->has('last_name') ? 'border-red-400/60' : '' }}"
                                    >
                                    @error('last_name')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-petrova-secondary mb-1.5">
                                        Телефон <span class="text-petrova-gold">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        placeholder="+359 XXX XXX XXXX"
                                        class="w-full rounded border px-3 py-2.5 text-sm bg-petrova-main/80 text-petrova-primary placeholder-petrova-secondary/50 border-petrova-gold/20 focus:outline-none focus:border-petrova-gold/60 transition-colors
                                               {{ $errors->has('phone') ? 'border-red-400/60' : '' }}"
                                    >
                                    @error('phone')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-petrova-secondary mb-1.5">
                                        Имейл адрес <span class="text-petrova-gold">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="Вашият имейл"
                                        class="w-full rounded border px-3 py-2.5 text-sm bg-petrova-main/80 text-petrova-primary placeholder-petrova-secondary/50 border-petrova-gold/20 focus:outline-none focus:border-petrova-gold/60 transition-colors
                                               {{ $errors->has('email') ? 'border-red-400/60' : '' }}"
                                    >
                                    @error('email')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- Payment method --}}
                        <div>
                            <h2 class="font-cormorant text-base font-bold italic text-petrova-gold mb-4">Метод на плащане</h2>

                            <div class="space-y-3">

                                @foreach (['card' => 'Плащане с дебитна/кредитна карта', 'easypay' => 'Плащане с Easy Pay', 'epay' => 'Плащане с ePay'] as $value => $label)
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="{{ $value }}"
                                            {{ old('payment_method') === $value ? 'checked' : '' }}
                                            class="w-4 h-4 border-petrova-gold/40 text-petrova-gold focus:ring-petrova-gold/30 focus:ring-offset-0 bg-petrova-main cursor-pointer"
                                        >
                                        <span class="text-sm text-petrova-secondary">{{ $label }}</span>
                                    </label>
                                @endforeach

                                @error('payment_method')
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror

                            </div>
                        </div>

                        {{-- Consent --}}
                        <div class="border-t border-petrova-gold/10 pt-5">
                            <p class="text-xs text-petrova-secondary/70 mb-3">
                                Вашите лични данни ще бъдат използвани за обработка и предоставяне на заявената онлайн консултация.
                            </p>
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    name="consent"
                                    value="1"
                                    {{ old('consent') ? 'checked' : '' }}
                                    class="mt-0.5 w-4 h-4 rounded border-petrova-gold/40 text-petrova-gold focus:ring-petrova-gold/30 focus:ring-offset-0 bg-petrova-main cursor-pointer flex-shrink-0"
                                >
                                <span class="text-sm text-petrova-secondary">
                                    Прочетох и съм съгласен с
                                    <a href="{{ route('privacy') }}" target="_blank" class="text-petrova-gold hover:underline">Политиката за поверителност</a>
                                </span>
                            </label>
                            @error('consent')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- STEP 3                                               --}}
                {{-- ════════════════════════════════════════════════════ --}}
                <div id="wcf-step-3" class="wcf-step hidden">

                    <div class="rounded border border-petrova-gold/20 bg-petrova-deep/60 px-6 py-6">

                        <h2 class="font-cormorant text-base font-bold italic text-petrova-gold mb-5">Преглед на данните</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Services summary --}}
                            <div class="rounded bg-petrova-main/70 border border-petrova-gold/15 px-5 py-4">
                                <h3 class="font-cormorant text-sm font-bold italic text-petrova-primary mb-3">Услуги</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                                        <span class="text-petrova-gold font-medium">Писмена консултация</span>
                                        <span class="text-petrova-secondary">до 48 часа</span>
                                    </div>
                                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                                        <span class="text-petrova-secondary/70">Заглавие</span>
                                        <span id="review-title" class="text-petrova-secondary text-right max-w-[55%] truncate"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Personal data summary --}}
                            <div class="rounded bg-petrova-main/70 border border-petrova-gold/15 px-5 py-4">
                                <h3 class="font-cormorant text-sm font-bold italic text-petrova-primary mb-3">Лични данни</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                                        <span class="text-petrova-gold font-medium">Име</span>
                                        <span id="review-first-name" class="text-petrova-secondary"></span>
                                    </div>
                                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                                        <span class="text-petrova-gold font-medium">Фамилия</span>
                                        <span id="review-last-name" class="text-petrova-secondary"></span>
                                    </div>
                                    <div class="flex justify-between border-b border-petrova-gold/10 pb-2">
                                        <span class="text-petrova-gold font-medium">Email</span>
                                        <span id="review-email" class="text-petrova-secondary text-right max-w-[55%] truncate"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-petrova-gold font-medium">Телефон</span>
                                        <span id="review-phone" class="text-petrova-secondary"></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Payment method + total --}}
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between border-t border-petrova-gold/10 pt-3">
                                <span class="text-petrova-gold font-medium italic">Метод на плащане</span>
                                <span id="review-payment" class="text-petrova-secondary"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-petrova-gold font-medium italic">Обща сума</span>
                                <span class="text-petrova-secondary">
                                    @if ($pricing)
                                        {{ number_format((float) $pricing->price_eur, 2, ',', '.') }}&nbsp;€
                                        @if ($pricing->show_bgn_price)
                                            / {{ number_format((float) $pricing->price_bgn, 2, ',', '.') }}&nbsp;лв.
                                        @endif
                                    @endif
                                </span>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- ── Step footer: counter + navigation ──────────────── --}}
                <div class="mt-6 flex items-center justify-between">
                    <span id="wcf-counter" class="text-sm text-petrova-secondary/70">1 / 3</span>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            id="wcf-back"
                            class="rounded border border-petrova-gold/40 px-5 py-2.5 text-sm font-semibold text-petrova-secondary hover:border-petrova-gold hover:text-petrova-primary transition-colors"
                        >
                            Назад
                        </button>

                        <button
                            type="button"
                            id="wcf-next"
                            class="inline-flex items-center gap-2 rounded bg-petrova-gold px-5 py-2.5 text-sm font-semibold text-petrova-deep hover:bg-petrova-gold-hover transition-colors"
                        >
                            Запазете
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                            </svg>
                        </button>

                        <button
                            type="submit"
                            id="wcf-submit"
                            class="hidden inline-flex items-center gap-2 rounded bg-petrova-gold px-5 py-2.5 text-sm font-semibold text-petrova-deep hover:bg-petrova-gold-hover transition-colors"
                        >
                            Плащане
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                            </svg>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </section>

</div>

@push('scripts')
<script>
(function () {
    var currentStep = 1;
    var totalSteps  = 3;

    var steps   = [
        document.getElementById('wcf-step-1'),
        document.getElementById('wcf-step-2'),
        document.getElementById('wcf-step-3'),
    ];
    var counter = document.getElementById('wcf-counter');
    var backBtn = document.getElementById('wcf-back');
    var nextBtn = document.getElementById('wcf-next');
    var submitBtn = document.getElementById('wcf-submit');

    // ── Payment method labels ────────────────────────────────────────
    var paymentLabels = {
        'card':    'Плащане с дебитна/кредитна карта',
        'easypay': 'Плащане с Easy Pay',
        'epay':    'Плащане с ePay',
    };

    function showStep(n) {
        steps.forEach(function (s, i) {
            s.classList.toggle('hidden', i !== n - 1);
        });
        counter.textContent = n + ' / ' + totalSteps;

        // Back button: hidden on step 1
        backBtn.classList.toggle('hidden', n === 1);

        // Next vs Submit
        if (n === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }

        // Update next button label
        if (n === 2) {
            nextBtn.childNodes[0].textContent = 'Напред';
        } else if (n === 1) {
            nextBtn.childNodes[0].textContent = 'Запазете';
        }

        currentStep = n;
    }

    function validateStep1() {
        var title = document.getElementById('title').value.trim();
        var desc  = document.getElementById('description').value.trim();
        var errors = [];

        if (!title) errors.push('Моля, въведете заглавие на казуса.');
        if (!desc)  errors.push('Моля, опишете Вашия казус.');
        if (desc.length > 5000) errors.push('Описанието не може да надвишава 5000 символа.');

        var files = document.getElementById('wcf-file-input').files;
        if (files.length > 5) errors.push('Може да прикачите максимум 5 файла.');
        for (var i = 0; i < files.length; i++) {
            if (files[i].size > 10 * 1024 * 1024) {
                errors.push('Файлът "' + files[i].name + '" надвишава 10 MB.');
            }
        }

        return errors;
    }

    function validateStep2() {
        var firstName = document.getElementById('first_name').value.trim();
        var lastName  = document.getElementById('last_name').value.trim();
        var email     = document.getElementById('email').value.trim();
        var phone     = document.getElementById('phone').value.trim();
        var payment   = document.querySelector('input[name="payment_method"]:checked');
        var consent   = document.querySelector('input[name="consent"]');
        var errors = [];

        if (!firstName) errors.push('Моля, въведете Вашето име.');
        if (!lastName)  errors.push('Моля, въведете Вашата фамилия.');
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Моля, въведете валиден имейл адрес.');
        if (!phone)     errors.push('Моля, въведете телефонен номер.');
        if (!payment)   errors.push('Моля, изберете метод на плащане.');
        if (!consent || !consent.checked) errors.push('Трябва да се съгласите с Политиката за поверителност.');

        return errors;
    }

    function showClientErrors(errors) {
        var existing = document.getElementById('wcf-client-errors');
        if (existing) existing.remove();

        if (!errors.length) return;

        var div = document.createElement('div');
        div.id = 'wcf-client-errors';
        div.className = 'mb-4 rounded border border-red-400/40 bg-red-900/30 px-5 py-4';

        var title = document.createElement('p');
        title.className = 'mb-2 text-sm font-semibold text-red-300';
        title.textContent = 'Моля, поправете следните грешки:';
        div.appendChild(title);

        var ul = document.createElement('ul');
        ul.className = 'list-disc list-inside space-y-0.5';
        errors.forEach(function (e) {
            var li = document.createElement('li');
            li.className = 'text-sm text-red-300';
            li.textContent = e;
            ul.appendChild(li);
        });
        div.appendChild(ul);

        var form = document.getElementById('wcf');
        form.insertBefore(div, steps[currentStep - 1]);
    }

    function populateReview() {
        var title     = document.getElementById('title').value.trim();
        var firstName = document.getElementById('first_name').value.trim();
        var lastName  = document.getElementById('last_name').value.trim();
        var email     = document.getElementById('email').value.trim();
        var phone     = document.getElementById('phone').value.trim();
        var payment   = document.querySelector('input[name="payment_method"]:checked');

        document.getElementById('review-title').textContent      = title;
        document.getElementById('review-first-name').textContent = firstName;
        document.getElementById('review-last-name').textContent  = lastName;
        document.getElementById('review-email').textContent      = email;
        document.getElementById('review-phone').textContent      = phone;
        document.getElementById('review-payment').textContent    = payment
            ? (paymentLabels[payment.value] || payment.value)
            : '—';
    }

    nextBtn.addEventListener('click', function () {
        var errors = [];

        if (currentStep === 1) {
            errors = validateStep1();
        } else if (currentStep === 2) {
            errors = validateStep2();
        }

        showClientErrors(errors);
        if (errors.length) return;

        if (currentStep === 2) {
            populateReview();
        }

        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    backBtn.addEventListener('click', function () {
        if (currentStep > 1) {
            var existing = document.getElementById('wcf-client-errors');
            if (existing) existing.remove();
            showStep(currentStep - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // ── File upload ──────────────────────────────────────────────────
    // Uses a DataTransfer accumulator so multiple pick/drop sessions
    // merge into one list (up to 5 files) instead of replacing it.
    var pickBtn   = document.getElementById('wcf-pick-btn');
    var fileInput = document.getElementById('wcf-file-input');
    var dropZone  = document.getElementById('wcf-drop-zone');
    var fileList  = document.getElementById('wcf-file-list');

    // Accumulated FileList maintained via DataTransfer.
    var accumulated = new DataTransfer();

    function mergeFiles(newFiles) {
        for (var i = 0; i < newFiles.length; i++) {
            // Skip duplicates by name + size (best-effort dedup).
            var duplicate = false;
            for (var j = 0; j < accumulated.files.length; j++) {
                if (accumulated.files[j].name === newFiles[i].name &&
                    accumulated.files[j].size === newFiles[i].size) {
                    duplicate = true;
                    break;
                }
            }
            if (!duplicate && accumulated.files.length < 5) {
                accumulated.items.add(newFiles[i]);
            }
        }
        // Push the merged set back into the real input so it submits correctly.
        fileInput.files = accumulated.files;
        renderFileList(accumulated.files);
    }

    function removeFile(index) {
        var fresh = new DataTransfer();
        for (var i = 0; i < accumulated.files.length; i++) {
            if (i !== index) fresh.items.add(accumulated.files[i]);
        }
        accumulated = fresh;
        fileInput.files = accumulated.files;
        renderFileList(accumulated.files);
    }

    pickBtn.addEventListener('click', function () {
        fileInput.click();
    });

    dropZone.addEventListener('click', function (e) {
        if (e.target !== pickBtn) fileInput.click();
    });

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('border-petrova-gold/70');
    });

    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('border-petrova-gold/70');
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('border-petrova-gold/70');
        if (e.dataTransfer && e.dataTransfer.files.length) {
            mergeFiles(e.dataTransfer.files);
        }
    });

    // On each native picker open, merge the new selection into the accumulator.
    // Reset the input value first so the same file can be re-added after removal.
    fileInput.addEventListener('change', function () {
        mergeFiles(fileInput.files);
    });

    function renderFileList(files) {
        fileList.innerHTML = '';
        if (!files.length) {
            fileList.classList.add('hidden');
            return;
        }
        fileList.classList.remove('hidden');
        for (var i = 0; i < files.length; i++) {
            (function (idx) {
                var li = document.createElement('li');
                li.className = 'flex items-center justify-between gap-2 text-petrova-secondary/80';

                var nameSpan = document.createElement('span');
                nameSpan.className = 'flex items-center gap-2 truncate';
                nameSpan.innerHTML = '<svg class="h-4 w-4 text-petrova-gold/60 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>'
                    + '<span class="truncate">' + files[idx].name + '</span>';

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'flex-shrink-0 text-petrova-secondary/40 hover:text-red-400 transition-colors';
                removeBtn.setAttribute('aria-label', 'Премахни файл');
                removeBtn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
                removeBtn.addEventListener('click', function () { removeFile(idx); });

                li.appendChild(nameSpan);
                li.appendChild(removeBtn);
                fileList.appendChild(li);
            }(i));
        }
    }

    // ── If server returned errors, show step that has errors ─────────
    @if ($errors->has('title') || $errors->has('description') || $errors->hasAny(['files', 'files.*']))
        showStep(1);
    @elseif ($errors->has('first_name') || $errors->has('last_name') || $errors->has('email') || $errors->has('phone') || $errors->has('payment_method') || $errors->has('consent'))
        showStep(2);
    @else
        showStep(1);
    @endif

}());
</script>
@endpush

@endsection
