@extends('layouts.admin')

@section('title', 'Редакция на секция — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Редакция на секция</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ admin_page_section_caption($pageSection->page, $pageSection->section) }}
            </p>
        </div>
        <a href="{{ route('admin.sections.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към секции
        </a>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($pageSection->page === 'home' && $pageSection->section === 'faq')
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-10 text-sm text-gray-600">
            <p class="text-gray-900 font-medium mb-1">Секцията не се редактира от този панел</p>
            <p class="text-gray-500">Тази секция е скрита за този проект.</p>
        </div>
    @else
    <form action="{{ route('admin.sections.update', $pageSection) }}" method="POST" novalidate>
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

            {{-- Form card --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

                {{-- Section: Основно съдържание --}}
                <div class="px-8 py-6 space-y-5">

                    <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Основно съдържание</p>

                    {{-- Title --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Заглавие
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $pageSection->title) }}"
                            autofocus
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('title') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('title')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Subtitle --}}
                    <div>
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Подзаглавие
                        </label>
                        <input
                            type="text"
                            id="subtitle"
                            name="subtitle"
                            value="{{ old('subtitle', $pageSection->subtitle) }}"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('subtitle') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                        @error('subtitle')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Content --}}
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Съдържание
                        </label>
                        <textarea
                            id="content"
                            name="content"
                            rows="6"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                   focus:outline-none focus:border-gray-500 transition-colors resize-y
                                   {{ $errors->has('content') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >{{ old('content', $pageSection->content) }}</textarea>
                        @error('content')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Section: Бутон --}}
                <div class="px-8 py-6 space-y-5">

                    <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Бутон</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                        {{-- Button text --}}
                        <div>
                            <label for="meta_button_text" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Текст на бутона
                            </label>
                            <input
                                type="text"
                                id="meta_button_text"
                                name="meta[button_text]"
                                value="{{ old('meta.button_text', $pageSection->meta['button_text'] ?? '') }}"
                                placeholder="Напр. Научи повече"
                                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                       focus:outline-none focus:border-gray-500 transition-colors
                                       {{ $errors->has('meta.button_text') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                            >
                            @error('meta.button_text')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Button URL --}}
                        <div>
                            <label for="meta_button_url" class="block text-sm font-medium text-gray-700 mb-1.5">
                                URL на бутона
                            </label>
                            <input
                                type="text"
                                id="meta_button_url"
                                name="meta[button_url]"
                                value="{{ old('meta.button_url', $pageSection->meta['button_url'] ?? '') }}"
                                placeholder="/services"
                                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                                       font-mono focus:outline-none focus:border-gray-500 transition-colors
                                       {{ $errors->has('meta.button_url') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                            >
                            @error('meta.button_url')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

                {{-- Actions --}}
                <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
                    <button
                        type="submit"
                        class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                               hover:bg-gray-700 transition-colors">
                        Запази промените
                    </button>
                    <a href="{{ route('admin.sections.index') }}"
                       class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
                        Отказ
                    </a>
                </div>

            </div>

    </form>
    @endif

@endsection
