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
    <form action="{{ route('admin.sections.update', $pageSection) }}" method="POST" enctype="multipart/form-data" novalidate>
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

            @php
                $isAboutHero = $pageSection->page === 'about' && $pageSection->section === 'hero';
                $isAboutTeam = $pageSection->page === 'about' && $pageSection->section === 'team';
            @endphp

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

                    @if ($isAboutHero)
                        {{-- Preserve DB values: default update() always writes subtitle/content/meta --}}
                        <input type="hidden" name="subtitle" value="{{ e(old('subtitle', (string) ($pageSection->subtitle ?? ''))) }}">
                        <input type="hidden" name="content" value="{{ e(old('content', (string) ($pageSection->content ?? ''))) }}">
                        <input type="hidden" name="meta[button_text]" value="{{ e(old('meta.button_text', (string) ($pageSection->meta['button_text'] ?? ''))) }}">
                        <input type="hidden" name="meta[button_url]" value="{{ e(old('meta.button_url', (string) ($pageSection->meta['button_url'] ?? ''))) }}">
                    @else
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
                    @endif

                </div>

                @unless ($isAboutHero || $isAboutTeam)
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
                @endunless

                @if ($isAboutTeam)
                    <input type="hidden" name="meta[button_text]" value="{{ e(old('meta.button_text', (string) (($pageSection->meta ?? [])['button_text'] ?? ''))) }}">
                    <input type="hidden" name="meta[button_url]" value="{{ e(old('meta.button_url', (string) (($pageSection->meta ?? [])['button_url'] ?? ''))) }}">
                @endif

                {{-- Section: Hero image + pills (home.hero only) --}}
                @if ($pageSection->page === 'home' && $pageSection->section === 'hero')

                    {{-- Hero image --}}
                    <div class="px-8 py-6 space-y-5">

                        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Снимка (герой)</p>

                        @if ($pageSection->image_path)
                            <div>
                                <img
                                    src="{{ upload_url($pageSection->image_path) }}"
                                    alt="Текуща снимка"
                                    class="h-28 w-auto rounded border border-gray-200 object-cover"
                                >
                                <p class="mt-1.5 text-xs text-gray-400">Текуща снимка. Качи нова, за да я замениш.</p>
                            </div>
                        @endif

                        <div>
                            <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ $pageSection->image_path ? 'Нова снимка' : 'Снимка' }}
                            </label>
                            <input
                                type="file"
                                id="hero_image"
                                name="image"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full text-sm text-gray-700 file:mr-4 file:px-4 file:py-2
                                       file:rounded file:border-0 file:text-sm file:font-medium
                                       file:bg-gray-900 file:text-white file:cursor-pointer
                                       hover:file:bg-gray-700 transition-colors
                                       {{ $errors->has('image') ? 'border border-red-300 bg-red-50 rounded' : '' }}"
                            >
                            <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, WEBP — макс. 4 MB.</p>
                            @error('image')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($pageSection->image_path)
                            <div>
                                <input type="hidden" name="remove_image" value="0">
                                <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        name="remove_image"
                                        value="1"
                                        {{ old('remove_image') ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-gray-900
                                               focus:ring-0 focus:ring-offset-0 cursor-pointer"
                                    >
                                    <span class="text-sm text-gray-700">Изтрий текущата снимка</span>
                                </label>
                            </div>
                        @endif

                    </div>

                    {{-- Pills --}}
                    <div class="px-8 py-6 space-y-5">

                        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Пилюли / Бадж бутони</p>
                        <p class="text-xs text-gray-400 -mt-2">Всеки ред е отделен бадж под заглавието. URL е незадължителен.</p>

                        @php
                            $existingPills = old('meta.pills', $pageSection->pills ?? []);
                        @endphp

                        <div id="pills-container" class="space-y-3">
                            @foreach ($existingPills as $i => $pill)
                                <div class="pill-row flex items-center gap-3">
                                    <input
                                        type="text"
                                        name="meta[pills][{{ $i }}][text]"
                                        value="{{ $pill['text'] ?? '' }}"
                                        placeholder="Текст на бадж"
                                        class="flex-1 px-3 py-2 border border-gray-200 rounded text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-gray-500 transition-colors"
                                    >
                                    <input
                                        type="text"
                                        name="meta[pills][{{ $i }}][url]"
                                        value="{{ $pill['url'] ?? '' }}"
                                        placeholder="URL (незадължително)"
                                        class="flex-1 px-3 py-2 border border-gray-200 rounded text-sm text-gray-900 placeholder-gray-400 font-mono focus:outline-none focus:border-gray-500 transition-colors"
                                    >
                                    <button
                                        type="button"
                                        class="pill-remove-btn flex-shrink-0 px-2 py-2 text-gray-400 hover:text-red-500 transition-colors text-sm leading-none"
                                        aria-label="Премахни ред"
                                    >✕</button>
                                </div>
                            @endforeach
                        </div>

                        <button
                            type="button"
                            id="pill-add-btn"
                            class="mt-1 text-sm text-gray-500 hover:text-gray-900 transition-colors border border-dashed border-gray-300 hover:border-gray-500 rounded px-4 py-2"
                        >
                            + Добави бадж
                        </button>

                        @error('meta.pills')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                    </div>

                    <template id="pill-row-template">
                        <div class="pill-row flex items-center gap-3">
                            <input
                                type="text"
                                name="meta[pills][__IDX__][text]"
                                placeholder="Текст на бадж"
                                class="flex-1 px-3 py-2 border border-gray-200 rounded text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-gray-500 transition-colors"
                            >
                            <input
                                type="text"
                                name="meta[pills][__IDX__][url]"
                                placeholder="URL (незадължително)"
                                class="flex-1 px-3 py-2 border border-gray-200 rounded text-sm text-gray-900 placeholder-gray-400 font-mono focus:outline-none focus:border-gray-500 transition-colors"
                            >
                            <button
                                type="button"
                                class="pill-remove-btn flex-shrink-0 px-2 py-2 text-gray-400 hover:text-red-500 transition-colors text-sm leading-none"
                                aria-label="Премахни ред"
                            >✕</button>
                        </div>
                    </template>

                    <script>
                    (function () {
                        var container = document.getElementById('pills-container');
                        var addBtn    = document.getElementById('pill-add-btn');
                        var template  = document.getElementById('pill-row-template');
                        var index     = container.querySelectorAll('.pill-row').length;

                        addBtn.addEventListener('click', function () {
                            var clone = template.content.cloneNode(true);
                            clone.querySelectorAll('[name]').forEach(function (el) {
                                el.name = el.name.replace('__IDX__', index);
                            });
                            container.appendChild(clone);
                            index++;
                        });

                        container.addEventListener('click', function (e) {
                            var btn = e.target.closest('.pill-remove-btn');
                            if (btn) {
                                btn.closest('.pill-row').remove();
                            }
                        });
                    }());
                    </script>

                @endif

                {{-- Section: About preview image (home.about_preview only) --}}
                @if ($pageSection->page === 'home' && $pageSection->section === 'about_preview')

                    <div class="px-8 py-6 space-y-5">

                        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Снимка</p>

                        @if ($pageSection->image_path)
                            <div>
                                <img
                                    src="{{ upload_url($pageSection->image_path) }}"
                                    alt="Текуща снимка"
                                    class="h-28 w-auto rounded border border-gray-200 object-cover"
                                >
                                <p class="mt-1.5 text-xs text-gray-400">Текуща снимка. Качи нова, за да я замениш.</p>
                            </div>
                        @endif

                        <div>
                            <label for="about_image" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ $pageSection->image_path ? 'Нова снимка' : 'Снимка' }}
                            </label>
                            <input
                                type="file"
                                id="about_image"
                                name="image"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full text-sm text-gray-700 file:mr-4 file:px-4 file:py-2
                                       file:rounded file:border-0 file:text-sm file:font-medium
                                       file:bg-gray-900 file:text-white file:cursor-pointer
                                       hover:file:bg-gray-700 transition-colors
                                       {{ $errors->has('image') ? 'border border-red-300 bg-red-50 rounded' : '' }}"
                            >
                            <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, WEBP — макс. 4 MB.</p>
                            @error('image')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($pageSection->image_path)
                            <div>
                                <input type="hidden" name="remove_image" value="0">
                                <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        name="remove_image"
                                        value="1"
                                        {{ old('remove_image') ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 text-gray-900
                                               focus:ring-0 focus:ring-offset-0 cursor-pointer"
                                    >
                                    <span class="text-sm text-gray-700">Изтрий текущата снимка</span>
                                </label>
                            </div>
                        @endif

                    </div>

                @endif

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
