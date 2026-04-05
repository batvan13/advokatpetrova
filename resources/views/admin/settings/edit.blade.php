@extends('layouts.admin')

@section('title', 'Настройки — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8">
        <h1 class="text-xl font-semibold text-gray-900">Настройки</h1>
        <p class="mt-1 text-sm text-gray-500">Глобални настройки на сайта.</p>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        {{-- Global error summary --}}
        @if ($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm font-medium text-red-700 mb-1">Моля, поправи следните грешки:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm text-red-600">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            {{-- ── Section: Сайт ──────────────────────────────── --}}
            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Сайт</p>

                {{-- site_name --}}
                <div>
                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Име на сайта
                    </label>
                    <input
                        type="text"
                        id="site_name"
                        name="site_name"
                        value="{{ old('site_name', $settings->get('site_name')) }}"
                        placeholder="Моята фирма"
                        autofocus
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('site_name') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('site_name')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- site_tagline --}}
                <div>
                    <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Слоган
                    </label>
                    <input
                        type="text"
                        id="site_tagline"
                        name="site_tagline"
                        value="{{ old('site_tagline', $settings->get('site_tagline')) }}"
                        placeholder="Кратко описание или мото"
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('site_tagline') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('site_tagline')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- ── Section: Контакти ───────────────────────────── --}}
            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Контакти</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- contact_email --}}
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Имейл
                        </label>
                        <input
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            value="{{ old('contact_email', $settings->get('contact_email')) }}"
                            placeholder="office@example.com"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('contact_email') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('contact_email')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-400">На този адрес ще получаваш всички запитвания от контактната форма на сайта.</p>
                    </div>

                    {{-- contact_phone --}}
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Телефон
                        </label>
                        <input
                            type="tel"
                            id="contact_phone"
                            name="contact_phone"
                            value="{{ old('contact_phone', $settings->get('contact_phone')) }}"
                            placeholder="+359 88 888 8888"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('contact_phone') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('contact_phone')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- address --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Адрес
                    </label>
                    <textarea
                        id="address"
                        name="address"
                        rows="2"
                        placeholder="ул. Примерна 1, гр. София"
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors resize-y
                               {{ $errors->has('address') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >{{ old('address', $settings->get('address')) }}</textarea>
                    @error('address')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- google_maps_url --}}
                <div>
                    <label for="google_maps_url" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Google Maps embed линк
                    </label>
                    <p class="mb-1.5 text-xs text-gray-500">
                        Поставете embed линк от Google Maps (Share → Embed a map). Стандартен Google Maps линк няма да покаже вградена карта.
                    </p>
                    <input
                        type="url"
                        id="google_maps_url"
                        name="google_maps_url"
                        value="{{ old('google_maps_url', $settings->get('google_maps_url')) }}"
                        placeholder="https://www.google.com/maps/embed?pb=..."
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               font-mono text-xs focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('google_maps_url') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('google_maps_url')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- ── Section: Социални мрежи ─────────────────────── --}}
            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Социални мрежи</p>

                {{-- facebook_url --}}
                <div>
                    <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Facebook
                    </label>
                    <input
                        type="url"
                        id="facebook_url"
                        name="facebook_url"
                        value="{{ old('facebook_url', $settings->get('facebook_url')) }}"
                        placeholder="https://facebook.com/your-page"
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               font-mono text-xs focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('facebook_url') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('facebook_url')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- instagram_url --}}
                <div>
                    <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Instagram
                    </label>
                    <input
                        type="url"
                        id="instagram_url"
                        name="instagram_url"
                        value="{{ old('instagram_url', $settings->get('instagram_url')) }}"
                        placeholder="https://instagram.com/your-handle"
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               font-mono text-xs focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('instagram_url') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('instagram_url')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- linkedin_url --}}
                <div>
                    <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-1.5">
                        LinkedIn
                    </label>
                    <input
                        type="url"
                        id="linkedin_url"
                        name="linkedin_url"
                        value="{{ old('linkedin_url', $settings->get('linkedin_url')) }}"
                        placeholder="https://www.linkedin.com/in/your-profile"
                        class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               font-mono text-xs focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('linkedin_url') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    <p class="mt-1 text-xs text-gray-500">Профил или фирмена страница в LinkedIn.</p>
                    @error('linkedin_url')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- ── Actions ─────────────────────────────────────── --}}
            <div class="px-8 py-5 bg-gray-50 rounded-b-xl">
                <button
                    type="submit"
                    class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                           hover:bg-gray-700 transition-colors">
                    Запази настройките
                </button>
            </div>

        </div>

    </form>

@endsection
