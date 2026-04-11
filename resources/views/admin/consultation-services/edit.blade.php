@extends('layouts.admin')

@section('title', 'Редакция на цена — Admin')

@section('content')

    @php
        $typeLabels = [
            'phone'   => 'Телефонна консултация',
            'chat'    => 'Чат консултация',
            'written' => 'Писмена консултация',
            'video'   => 'Видео консултация',
        ];
    @endphp

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Редакция на цена</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $typeLabels[$service->type] ?? $service->type }}
            </p>
        </div>
        <a href="{{ route('admin.consultation-services.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към цени
        </a>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

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

    <form action="{{ route('admin.consultation-services.update', $service) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            {{-- Основни цени --}}
            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">
                    @if ($service->isVideo())
                        Цени — 30 минути
                    @else
                        Цени
                    @endif
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- price_eur --}}
                    <div>
                        <label for="price_eur" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Цена EUR
                        </label>
                        <input
                            type="number"
                            id="price_eur"
                            name="price_eur"
                            value="{{ old('price_eur', $service->price_eur) }}"
                            min="0"
                            step="0.01"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('price_eur') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('price_eur')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- price_bgn --}}
                    <div>
                        <label for="price_bgn" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Цена BGN
                        </label>
                        <input
                            type="number"
                            id="price_bgn"
                            name="price_bgn"
                            value="{{ old('price_bgn', $service->price_bgn) }}"
                            min="0"
                            step="0.01"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('price_bgn') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('price_bgn')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- 60-минутни цени (само за video) --}}
            @if ($service->isVideo())
            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Цени — 60 минути</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- price_eur_60 --}}
                    <div>
                        <label for="price_eur_60" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Цена EUR (60 мин)
                        </label>
                        <input
                            type="number"
                            id="price_eur_60"
                            name="price_eur_60"
                            value="{{ old('price_eur_60', $service->price_eur_60) }}"
                            min="0"
                            step="0.01"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('price_eur_60') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('price_eur_60')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- price_bgn_60 --}}
                    <div>
                        <label for="price_bgn_60" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Цена BGN (60 мин)
                        </label>
                        <input
                            type="number"
                            id="price_bgn_60"
                            name="price_bgn_60"
                            value="{{ old('price_bgn_60', $service->price_bgn_60) }}"
                            min="0"
                            step="0.01"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('price_bgn_60') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('price_bgn_60')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>
            @endif

            {{-- BGN visibility toggle --}}
            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Видимост на BGN цена</p>

                <input type="hidden" name="show_bgn_price" value="0">
                <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        name="show_bgn_price"
                        value="1"
                        {{ old('show_bgn_price', $service->show_bgn_price) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-gray-900
                               focus:ring-0 focus:ring-offset-0 cursor-pointer"
                    >
                    <span class="text-sm text-gray-700">Показвай BGN цената на страницата</span>
                </label>
                <p class="text-xs text-gray-400 -mt-2">Когато е изключено, BGN цената не се показва на публичната страница.</p>

            </div>

            {{-- Actions --}}
            <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
                <button
                    type="submit"
                    class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                           hover:bg-gray-700 transition-colors">
                    Запази промените
                </button>
                <a href="{{ route('admin.consultation-services.index') }}"
                   class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
                    Отказ
                </a>
            </div>

        </div>

    </form>

@endsection
