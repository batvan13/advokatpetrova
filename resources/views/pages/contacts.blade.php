@extends('layouts.app')

@section('title', 'Контакти')
@section('description', 'Свържете се с нас за въпроси, запитвания или среща.')

@section('content')

@php
    $heroTitle = trim((string) ($hero?->title ?? ''));

    $oldFirstName = old('first_name', '');
    $oldLastName = old('last_name', '');
    if ($oldFirstName === '' && $oldLastName === '' && old('name')) {
        $parts = preg_split('/\s+/u', (string) old('name'), 2, PREG_SPLIT_NO_EMPTY);
        $oldFirstName = $parts[0] ?? '';
        $oldLastName = $parts[1] ?? '';
    }

    $oldSubject = old('subject', '');
    $oldBody = old('message_body', '');
    if ($oldSubject === '' && $oldBody === '' && old('message')) {
        $full = (string) old('message');
        $subjLabel = '[Относно]: ';
        if (str_starts_with($full, $subjLabel) && str_contains($full, "\n\n")) {
            $chunks = explode("\n\n", $full, 2);
            $head = $chunks[0] ?? '';
            $oldBody = $chunks[1] ?? '';
            $oldSubject = str_starts_with($head, $subjLabel)
                ? mb_substr($head, mb_strlen($subjLabel))
                : $head;
        } elseif (str_contains($full, "\n\n")) {
            $chunks = explode("\n\n", $full, 2);
            $oldSubject = $chunks[0] ?? '';
            $oldBody = $chunks[1] ?? '';
        } else {
            $oldBody = $full;
        }
    }

    $mapsRaw = setting('google_maps_url');
    $mapsIframe = maps_iframe_src(is_string($mapsRaw) ? $mapsRaw : null);
@endphp

    {{-- Hero — aligned with /about pattern --}}
    <section
        id="contacts-top"
        class="scroll-mt-24 relative flex min-h-[min(52vh,520px)] items-center overflow-hidden md:scroll-mt-28"
    >
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/contacts/banner-kontakti.webp') }}');"
            role="presentation"
        ></div>
        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/84 via-petrova-deep/68 to-petrova-main/88"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 py-24 text-left sm:py-28">
            <h1 class="font-playfair text-4xl font-bold tracking-tight text-petrova-primary sm:text-5xl">
                {{ $heroTitle !== '' ? $heroTitle : 'Контакти' }}
            </h1>

            <p class="mt-6 max-w-2xl font-sans text-sm leading-relaxed text-petrova-secondary/80 sm:text-base">
                <a
                    href="{{ route('home') }}"
                    class="transition-colors hover:text-petrova-primary focus:outline-none focus-visible:text-petrova-primary focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep rounded"
                >Начало</a>
                <span aria-hidden="true"> / </span>
                <span class="text-petrova-primary/95">Контакти</span>
            </p>
        </div>
    </section>

    {{-- Main: strict split — left column = white panel, right column = beige panel (no shared section beige) --}}
    <section class="w-full border-t border-petrova-gold/25 bg-white">
        <div class="mx-auto max-w-6xl">
            <div class="grid grid-cols-1 items-stretch gap-y-12 lg:grid-cols-2 lg:gap-x-0 lg:gap-y-0">

                {{-- Left column wrapper: full-height continuous white --}}
                <div class="flex min-h-full flex-col space-y-8 bg-white px-4 pb-12 pt-20 sm:px-6 lg:px-10 lg:pb-28 lg:pt-24 lg:pr-8 xl:pl-12 xl:pr-10">
                    <div class="rounded-xl border border-petrova-deep/15 bg-white p-8 shadow-sm">
                        <h2 class="font-playfair text-2xl font-semibold leading-snug tracking-tight text-petrova-deep">
                            Екипът на Адвокатска кантора Петрова предоставя:
                        </h2>
                        <ul class="mt-5 list-none space-y-3 pl-0 font-sans text-base leading-relaxed text-petrova-mid">
                            <li class="flex gap-2.5">
                                <span class="shrink-0 font-medium text-petrova-deep/50" aria-hidden="true">–</span>
                                <span>Безплатна адвокатска помощ при случаи на домашно насилие</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="shrink-0 font-medium text-petrova-deep/50" aria-hidden="true">–</span>
                                <span>Безплатни консултации за материално затруднени лица всеки четвъртък, от 11:00 ч. до 12:00 ч.</span>
                            </li>
                        </ul>
                    </div>

                    @if($hasContact)
                        <div class="divide-y divide-petrova-deep/10 overflow-hidden rounded-2xl border border-petrova-deep/10 bg-white font-sans shadow-sm">
                            @if($phone)
                                <div class="flex gap-4 px-5 py-5 sm:px-6 sm:py-5">
                                    <img
                                        src="{{ asset('images/contacts/phone.svg') }}"
                                        alt=""
                                        class="h-14 w-14 shrink-0"
                                        width="56"
                                        height="56"
                                        decoding="async"
                                        loading="lazy"
                                    >
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-petrova-deep">Телефон</p>
                                        <a href="{{ $tel }}" class="mt-1 block text-base font-medium text-petrova-mid transition-colors hover:text-petrova-gold-hover">{{ $phone }}</a>
                                    </div>
                                </div>
                            @endif
                            @if($email)
                                <div class="flex gap-4 px-5 py-5 sm:px-6 sm:py-5">
                                    <img
                                        src="{{ asset('images/contacts/email.svg') }}"
                                        alt=""
                                        class="h-14 w-14 shrink-0"
                                        width="56"
                                        height="56"
                                        decoding="async"
                                        loading="lazy"
                                    >
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-petrova-deep">Имейл адрес</p>
                                        <a href="mailto:{{ $email }}" class="mt-1 block break-all text-base font-medium text-petrova-mid transition-colors hover:text-petrova-gold-hover">{{ $email }}</a>
                                    </div>
                                </div>
                            @endif
                            @if($address)
                                <div class="flex gap-4 px-5 py-5 sm:px-6 sm:py-5">
                                    <img
                                        src="{{ asset('images/contacts/location.svg') }}"
                                        alt=""
                                        class="h-14 w-14 shrink-0"
                                        width="56"
                                        height="56"
                                        decoding="async"
                                        loading="lazy"
                                    >
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-petrova-deep">Адрес</p>
                                        <p class="mt-1 whitespace-pre-line text-base leading-relaxed text-petrova-mid">{{ $address }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="rounded-2xl border border-dashed border-petrova-deep/15 bg-white px-6 py-8 text-sm text-petrova-secondary">
                            Контактната информация ще бъде добавена скоро.
                        </p>
                    @endif

                    @if($hasSocial)
                        <div class="space-y-4">
                            <h3 class="font-playfair text-xl font-semibold tracking-tight text-petrova-deep">
                                Последвайте ни
                            </h3>
                        <div class="flex flex-wrap items-center gap-4">
                            @if($linkedin)
                                <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex shrink-0 rounded-full transition-opacity hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                                    aria-label="LinkedIn">
                                    <img
                                        src="{{ asset('images/contacts/linkedin.svg') }}"
                                        alt=""
                                        class="h-12 w-12"
                                        width="48"
                                        height="48"
                                        decoding="async"
                                        loading="lazy"
                                    >
                                </a>
                            @endif
                            @if($facebook)
                                <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex shrink-0 rounded-full transition-opacity hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                                    aria-label="Facebook">
                                    <img
                                        src="{{ asset('images/contacts/facebook.svg') }}"
                                        alt=""
                                        class="h-12 w-12"
                                        width="48"
                                        height="48"
                                        decoding="async"
                                        loading="lazy"
                                    >
                                </a>
                            @endif
                            @if($instagram)
                                <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex shrink-0 rounded-full transition-opacity hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                                    aria-label="Instagram">
                                    <img
                                        src="{{ asset('images/contacts/instagram.svg') }}"
                                        alt=""
                                        class="h-12 w-12"
                                        width="48"
                                        height="48"
                                        decoding="async"
                                        loading="lazy"
                                    >
                                </a>
                            @endif
                        </div>
                        </div>
                    @endif
                </div>

                {{-- Right column wrapper: full-height continuous beige --}}
                <div class="flex min-h-full w-full justify-center bg-[#DFD6C2] px-4 pb-24 pt-0 sm:px-6 lg:justify-end lg:px-10 lg:pb-28 lg:pt-24 lg:pl-10 xl:px-12">
                    <div class="w-full max-w-xl space-y-12 lg:ml-auto">
                        <header class="space-y-5">
                            <h2 class="font-playfair text-4xl font-bold leading-[1.15] tracking-tight text-petrova-deep sm:text-[2.25rem]">
                                Свържете се с нас
                            </h2>
                            <p class="max-w-2xl text-[1.0625rem] font-medium leading-relaxed text-petrova-deep sm:text-lg">
                                Ако имате някакъв въпрос, моля не се колебайте да ни изпратите съобщение. Ние ще Ви отговорим в рамките на 24 часа!
                            </p>
                        </header>
                    <div class="w-full rounded-xl bg-petrova-deep px-8 py-10 text-white shadow-lg ring-1 ring-white/10">
                    <h2 class="font-playfair text-xl font-semibold tracking-tight text-petrova-primary">
                        Изпратете запитване
                    </h2>

                    @if(! $email)

                        <p class="mt-6 text-sm leading-relaxed text-petrova-secondary">
                            Формулярът за контакт не е конфигуриран.
                            Моля, свържете се с нас директно по телефон или имейл.
                        </p>

                    @elseif(is_string(session('inquiry_success')) && session('inquiry_success') !== '')

                        <div class="mt-8 rounded-lg border border-white/15 bg-white/5 px-6 py-8 text-center">
                            <p class="text-sm font-semibold text-petrova-primary">{{ session('inquiry_success') }}</p>
                            <p class="mt-2 text-sm text-petrova-secondary">Благодарим ви. Ще се свържем с вас скоро.</p>
                            <a href="{{ url()->current() }}"
                               class="mt-4 inline-block text-sm text-petrova-secondary underline underline-offset-2 transition-colors hover:text-petrova-primary">
                                Изпрати ново запитване
                            </a>
                        </div>

                    @else

                        @if($errors->has('throttle'))
                            <div class="mt-6 rounded-lg border border-red-400/40 bg-red-950/40 px-4 py-3">
                                <p class="text-sm text-red-200">{{ $errors->first('throttle') }}</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('inquiry.submit') }}" class="relative mt-8 space-y-5" novalidate>
                            @csrf

                            @if($errors->has('inquiry'))
                                <div class="rounded-lg border border-red-400/40 bg-red-950/40 px-4 py-3">
                                    <p class="text-sm text-red-200">{{ $errors->first('inquiry') }}</p>
                                </div>
                            @endif

                            <input type="hidden" name="opened_at" value="{{ time() }}">

                            <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                                <label for="company">Фирма</label>
                                <input type="text" name="company" id="company" value="" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="inquiry_first" class="mb-1 block text-sm font-medium text-petrova-secondary">
                                        Име <span class="text-red-300">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="inquiry_first"
                                        name="first_name"
                                        autocomplete="given-name"
                                        value="{{ $oldFirstName }}"
                                        required
                                        class="w-full rounded-lg border border-petrova-deep/15 bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35"
                                        placeholder="Име"
                                    >
                                </div>
                                <div>
                                    <label for="inquiry_last" class="mb-1 block text-sm font-medium text-petrova-secondary">
                                        Фамилия <span class="text-red-300">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="inquiry_last"
                                        name="last_name"
                                        autocomplete="family-name"
                                        value="{{ $oldLastName }}"
                                        required
                                        class="w-full rounded-lg border border-petrova-deep/15 bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35"
                                        placeholder="Фамилия"
                                    >
                                </div>
                            </div>
                            @error('name')
                                <p class="text-xs text-red-300">{{ $message }}</p>
                            @enderror

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="inquiry_email" class="mb-1 block text-sm font-medium text-petrova-secondary">
                                        Имейл адрес <span class="text-red-300">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="inquiry_email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        autocomplete="email"
                                        required
                                        placeholder="example@email.com"
                                        class="w-full rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('email') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                                    >
                                    @error('email')
                                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="inquiry_phone" class="mb-1 block text-sm font-medium text-petrova-secondary">
                                        Телефон <span class="text-xs font-normal text-petrova-secondary/70">(по желание)</span>
                                    </label>
                                    <input
                                        type="tel"
                                        id="inquiry_phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        autocomplete="tel"
                                        placeholder="+359 88 123 4567"
                                        class="w-full rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('phone') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                                    >
                                    @error('phone')
                                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="inquiry_subject" class="mb-1 block text-sm font-medium text-petrova-secondary">
                                    Относно <span class="text-red-300">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="inquiry_subject"
                                    name="subject"
                                    value="{{ $oldSubject }}"
                                    required
                                    class="w-full rounded-lg border border-petrova-deep/15 bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35"
                                    placeholder="Тема на запитването"
                                >
                            </div>

                            <div>
                                <label for="inquiry_body" class="mb-1 block text-sm font-medium text-petrova-secondary">
                                    Съобщение <span class="text-red-300">*</span>
                                </label>
                                <textarea
                                    id="inquiry_body"
                                    name="message_body"
                                    rows="5"
                                    required
                                    minlength="10"
                                    placeholder="Опишете вашето запитване..."
                                    class="w-full resize-y rounded-lg border px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-mid/50 focus:border-petrova-gold focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('message') ? 'border-red-500 bg-white' : 'border-petrova-deep/15 bg-white' }}"
                                >{{ $oldBody }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-start gap-3 pt-1">
                                <input
                                    type="checkbox"
                                    id="inquiry_gdpr"
                                    required
                                    class="mt-1 h-4 w-4 shrink-0 rounded border-white/35 bg-white text-petrova-gold focus:ring-2 focus:ring-petrova-gold/50 focus:ring-offset-2 focus:ring-offset-petrova-deep"
                                >
                                <label for="inquiry_gdpr" class="text-xs leading-relaxed text-petrova-secondary">
                                    Прочетох и съм съгласен с
                                    <a href="{{ route('privacy') }}" class="font-medium text-petrova-gold underline decoration-petrova-gold/50 underline-offset-2 transition-colors hover:text-petrova-primary hover:decoration-petrova-primary">Политиката за поверителност</a>.
                                    <span class="text-red-300">*</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-petrova-gold px-6 py-3.5 text-sm font-semibold text-petrova-deep shadow-none transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep"
                            >
                                <span>Изпратете</span>
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </form>

                    @endif
                    </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @if($mapsIframe)
        <section class="w-full border-t border-petrova-deep/10 bg-[#DFD6C2]" aria-label="Карта">
            <iframe
                src="{{ $mapsIframe }}"
                class="block h-[480px] w-full border-0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </section>
    @endif

@endsection
