{{--
    Post create / edit form partial.
    Variables: optional $post (edit), $submitLabel, $cancelUrl
--}}

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

    <div class="px-8 py-6 space-y-5">
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Съдържание</p>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
                Заглавие <span class="text-red-400">*</span>
            </label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $post->title ?? '') }}"
                autofocus
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('title') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('title')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">Slug</label>
            <input
                type="text"
                id="slug"
                name="slug"
                value="{{ old('slug', $post->slug ?? '') }}"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       font-mono focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('slug') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('slug')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-gray-400">
                    Остави празно — генерира се автоматично от заглавието.
                </p>
            @enderror
        </div>

        <div>
            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1.5">Кратко описание</label>
            <textarea
                id="excerpt"
                name="excerpt"
                rows="3"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors resize-y
                       {{ $errors->has('excerpt') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
            @error('excerpt')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-1.5">
                Текст <span class="text-red-400">*</span>
            </label>
            <textarea
                id="content"
                name="content"
                rows="12"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors resize-y
                       {{ $errors->has('content') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >{{ old('content', $post->content ?? '') }}</textarea>
            @error('content')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="px-8 py-6 space-y-5">
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Изображение</p>

        <div>
            <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1.5">
                Основно изображение
            </label>
            <input
                type="file"
                id="featured_image"
                name="featured_image"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0
                       file:text-sm file:font-medium file:bg-gray-900 file:text-white hover:file:bg-gray-700"
            >
            @error('featured_image')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-gray-400">JPG, PNG или WebP, до 8 MB.</p>
        </div>

        @isset($post)
            @if($post->featured_image)
                <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <p class="text-xs text-gray-500">Текущо изображение</p>
                    <img src="{{ upload_url($post->featured_image) }}"
                         alt=""
                         class="max-h-40 rounded border border-gray-100 object-contain">
                    <input type="hidden" name="featured_image_remove" value="0">
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="featured_image_remove"
                            value="1"
                            @checked(old('featured_image_remove', false))
                            class="w-4 h-4 rounded border-gray-300 text-gray-900
                                   focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        >
                        <span class="text-sm text-gray-700">Премахни текущото изображение</span>
                    </label>
                </div>
            @endif
        @endisset
    </div>

    <div class="px-8 py-6 space-y-5">
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Публикуване</p>

        <input type="hidden" name="is_published" value="0">
        <label class="inline-flex items-center gap-3 cursor-pointer select-none">
            <input
                type="checkbox"
                name="is_published"
                value="1"
                @checked(old('is_published', $post->is_published ?? false))
                class="w-4 h-4 rounded border-gray-300 text-gray-900
                       focus:ring-0 focus:ring-offset-0 cursor-pointer"
            >
            <span class="text-sm text-gray-700">Публикувана</span>
        </label>
        @error('is_published')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror

        <div>
            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1.5">
                Дата и час на публикуване
            </label>
            <input
                type="datetime-local"
                id="published_at"
                name="published_at"
                value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                class="w-full max-w-xs px-3 py-2 border rounded text-sm text-gray-900
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('published_at') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('published_at')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-gray-400">
                    Ако е публикувана и полето е празно, се използва текущият момент.
                </p>
            @enderror
        </div>
    </div>

    <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
        <button
            type="submit"
            class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                   hover:bg-gray-700 transition-colors">
            {{ $submitLabel ?? 'Запази' }}
        </button>
        <a href="{{ $cancelUrl ?? route('admin.posts.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            Отказ
        </a>
    </div>

</div>
