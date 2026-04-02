@extends('layouts.app')

@section('title', 'Контакти')
@section('description', 'Свържете се с нас за въпроси, запитвания или среща.')

@section('content')

    {{-- Hero --}}
    <section class="bg-white py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="max-w-2xl">

                <h1 class="font-playfair text-3xl font-bold tracking-tight text-petrova-deep">
                    {{ $hero?->title ?? 'Контакти' }}
                </h1>

                @if($hero?->subtitle)
                    <p class="mt-6 text-lg leading-relaxed text-petrova-mid">
                        {{ $hero->subtitle }}
                    </p>
                @endif

                @if($hero?->content)
                    <p class="mt-4 text-base leading-relaxed text-petrova-deep/85">
                        {{ $hero->content }}
                    </p>
                @endif

                @if($hero?->button_text && $hero?->button_url)
                    <div class="mt-8">
                        <a href="{{ section_url($hero->button_url) }}"
                           class="inline-flex rounded-lg bg-petrova-gold px-6 py-3 text-sm font-semibold text-petrova-deep shadow-none transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-white">
                            {{ $hero->button_text }}
                        </a>
                    </div>
                @endif

                <div class="mt-8">
                    @include('partials.action-buttons')
                </div>

            </div>
        </div>
    </section>

    {{-- Contact info + form --}}
    <section class="bg-gray-50 py-16">
        <div class="mx-auto max-w-6xl px-4">
            <div class="grid gap-12 md:grid-cols-2 items-start">

                {{-- Left: contact details --}}
                <div>
                    <h2 class="font-playfair text-lg font-semibold text-petrova-deep">Информация</h2>

                    @if($hasContact)
                        <ul class="mt-6 space-y-4 text-sm text-petrova-mid">
                            @if($phone)
                                <li>
                                    <span class="text-xs font-semibold uppercase tracking-wider text-petrova-secondary">Телефон</span><br>
                                    <a href="{{ $tel }}" class="text-petrova-deep transition-colors hover:text-petrova-gold-hover">{{ $phone }}</a>
                                </li>
                            @endif
                            @if($email)
                                <li>
                                    <span class="text-xs font-semibold uppercase tracking-wider text-petrova-secondary">Имейл</span><br>
                                    <a href="mailto:{{ $email }}" class="text-petrova-deep transition-colors hover:text-petrova-gold-hover">{{ $email }}</a>
                                </li>
                            @endif
                            @if($address)
                                <li>
                                    <span class="text-xs font-semibold uppercase tracking-wider text-petrova-secondary">Адрес</span><br>
                                    <span class="whitespace-pre-line text-petrova-deep">{{ $address }}</span>
                                </li>
                            @endif
                        </ul>
                    @else
                        <p class="mt-6 text-sm text-petrova-secondary">Контактната информация ще бъде добавена скоро.</p>
                    @endif

                    @if($hasSocial)
                        <div class="mt-6 flex gap-4 text-sm text-petrova-secondary">
                            @if($facebook)
                                <a href="{{ $facebook }}" target="_blank" rel="noopener"
                                   class="transition-colors hover:text-petrova-deep">Facebook</a>
                            @endif
                            @if($instagram)
                                <a href="{{ $instagram }}" target="_blank" rel="noopener"
                                   class="transition-colors hover:text-petrova-deep">Instagram</a>
                            @endif
                            @if($linkedin)
                                <a href="{{ $linkedin }}" target="_blank" rel="noopener"
                                   class="transition-colors hover:text-petrova-deep">LinkedIn</a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Right: inquiry form --}}
                <div>
                    <h2 class="font-playfair text-lg font-semibold text-petrova-deep">Изпратете запитване</h2>

                    @if(! $email)

                        <p class="mt-6 text-sm text-petrova-secondary">
                            Формулярът за контакт не е конфигуриран.
                            Моля, свържете се с нас директно по телефон или имейл.
                        </p>

                    @elseif(is_string(session('inquiry_success')) && session('inquiry_success') !== '')

                        <div class="mt-6 rounded-lg border border-petrova-deep/10 bg-white px-6 py-8 text-center">
                            <p class="text-sm font-semibold text-petrova-deep">{{ session('inquiry_success') }}</p>
                            <p class="mt-1 text-sm text-petrova-mid">Благодарим ви. Ще се свържем с вас скоро.</p>
                            <a href="{{ url()->current() }}"
                               class="mt-4 inline-block text-sm text-petrova-secondary underline underline-offset-2 transition-colors hover:text-petrova-deep">
                                Изпрати ново запитване
                            </a>
                        </div>

                    @else

                        @if($errors->has('throttle'))
                            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                                <p class="text-sm text-red-600">{{ $errors->first('throttle') }}</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('inquiry.submit') }}" class="relative mt-6 space-y-4" novalidate>
                            @csrf

                            @if($errors->has('inquiry'))
                                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                                    <p class="text-sm text-red-600">{{ $errors->first('inquiry') }}</p>
                                </div>
                            @endif

                            <input type="hidden" name="opened_at" value="{{ time() }}">

                            <div class="absolute left-[-9999px] top-auto w-px h-px overflow-hidden" aria-hidden="true">
                                <label for="company">Фирма</label>
                                <input type="text" name="company" id="company" value="" tabindex="-1" autocomplete="off">
                            </div>

                            <div>
                                <label for="name" class="mb-1 block text-sm font-medium text-petrova-deep">
                                    Име <span class="text-red-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Вашето Име"
                                    class="w-full rounded-lg border bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-secondary/50 focus:border-petrova-gold/35 focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('name') ? 'border-red-400' : 'border-petrova-deep/15' }}"
                                >
                                @error('name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="mb-1 block text-sm font-medium text-petrova-deep">
                                    Имейл <span class="text-red-400">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="example@email.com"
                                    class="w-full rounded-lg border bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-secondary/50 focus:border-petrova-gold/35 focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('email') ? 'border-red-400' : 'border-petrova-deep/15' }}"
                                >
                                @error('email')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="mb-1 block text-sm font-medium text-petrova-deep">
                                    Телефон <span class="text-xs font-normal text-petrova-secondary">(по желание)</span>
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="+359 88 123 4567"
                                    class="w-full rounded-lg border bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-secondary/50 focus:border-petrova-gold/35 focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('phone') ? 'border-red-400' : 'border-petrova-deep/15' }}"
                                >
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="message" class="mb-1 block text-sm font-medium text-petrova-deep">
                                    Съобщение <span class="text-red-400">*</span>
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="4"
                                    placeholder="Опишете вашето запитване..."
                                    class="w-full rounded-lg border bg-white px-4 py-3 text-sm text-petrova-deep placeholder:text-petrova-secondary/50 focus:border-petrova-gold/35 focus:outline-none focus:ring-2 focus:ring-petrova-gold/35 {{ $errors->has('message') ? 'border-red-400' : 'border-petrova-deep/15' }}"
                                >{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex rounded-lg bg-petrova-gold px-6 py-3 text-sm font-semibold text-petrova-deep shadow-none transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-gray-50"
                            >
                                Изпрати
                            </button>

                        </form>

                    @endif

                </div>

            </div>
        </div>
    </section>

@endsection
