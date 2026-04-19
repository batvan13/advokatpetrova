{{--
    Reusable form partial for GalleryItem create / edit.

    Expected variables (all optional — defaults handle the create case):
      $item        — GalleryItem model instance (for edit); absent on create
      $submitLabel — string, button text; default: 'Запази'
      $cancelUrl   — string, cancel href;  default: admin.gallery.index
--}}

@php
    $currentType = old('type', $item->type ?? \App\Models\GalleryItem::TYPE_IMAGE);
@endphp

{{-- ── Error summary ────────────────────────────────────── --}}
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

{{-- ── Form card ────────────────────────────────────────── --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

    {{-- ── Секция: Основна информация ──────────────────── --}}
    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Основна информация</p>

        {{-- Заглавие --}}
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
                Заглавие
            </label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $item->title ?? '') }}"
                placeholder="Незадължително"
                autofocus
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('title') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('title')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Тип --}}
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">
                Тип <span class="text-red-400">*</span>
            </label>
            <select
                id="type"
                name="type"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('type') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
                @foreach (\App\Models\GalleryItem::TYPES as $value => $label)
                    <option value="{{ $value }}" {{ $currentType === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('type')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ── Секция: Изображение ──────────────────────────── --}}
    <div id="section-image" class="px-8 py-6 space-y-5"
         style="{{ $currentType !== \App\Models\GalleryItem::TYPE_IMAGE ? 'display:none' : '' }}">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Изображение</p>

        {{-- Current image thumbnail (edit only) --}}
        @if(isset($item) && $item->image_path)
            <div>
                <img
                    src="{{ upload_url($item->image_path) }}"
                    alt="{{ $item->title ?? 'Текущо изображение' }}"
                    class="h-28 w-auto rounded border border-gray-200 object-cover"
                >
                <p class="mt-1.5 text-xs text-gray-400">Текущо изображение. Качи ново, за да го замениш.</p>
            </div>
        @endif

        {{-- File upload --}}
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ isset($item) && $item->image_path ? 'Ново изображение' : 'Изображение' }}
                @if(! (isset($item) && $item->image_path))
                    <span class="text-red-400">*</span>
                @endif
            </label>
            <input
                type="file"
                id="image"
                name="image"
                accept="image/*"
                class="w-full text-sm text-gray-700 file:mr-4 file:px-4 file:py-2
                       file:rounded file:border-0 file:text-sm file:font-medium
                       file:bg-gray-900 file:text-white file:cursor-pointer
                       hover:file:bg-gray-700 transition-colors
                       {{ $errors->has('image') ? 'border border-red-300 bg-red-50 rounded' : '' }}"
            >
            <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, GIF, WEBP — макс. 4 MB.</p>
            @error('image')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ── Секция: Видео ────────────────────────────────── --}}
    <div id="section-video" class="px-8 py-6 space-y-5"
         style="{{ $currentType !== \App\Models\GalleryItem::TYPE_VIDEO ? 'display:none' : '' }}">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Видео</p>

        <div>
            <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1.5">
                URL на видеото <span class="text-red-400">*</span>
            </label>
            <input
                type="url"
                id="video_url"
                name="video_url"
                value="{{ old('video_url', $item->video_url ?? '') }}"
                placeholder="https://www.youtube.com/watch?v=..."
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       font-mono text-xs focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('video_url') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            <p class="mt-1.5 text-xs text-gray-500 leading-relaxed">
                <span class="font-medium text-gray-800">Поддържат се само YouTube видео линкове</span>
                (напр. <span class="font-mono text-gray-600">youtube.com/watch?v=…</span>,
                <span class="font-mono text-gray-600">youtu.be/…</span>,
                <span class="font-mono text-gray-600">youtube.com/shorts/…</span>,
                <span class="font-mono text-gray-600">youtube.com/embed/…</span>).
                Други платформи не се вграждат в публичната галерия.
            </p>
            @if(isset($item) && $item->isVideo())
                @php
                    $videoUrlForHint = old('video_url', $item->video_url ?? '');
                    $parsedYoutube = \App\Support\GalleryVideoEmbedNormalizer::parse($videoUrlForHint);
                @endphp
                @if($parsedYoutube)
                    <p class="mt-2 text-xs text-gray-600">
                        Открит формат:
                        <span class="font-semibold text-gray-900">YouTube</span>
                    </p>
                @elseif($videoUrlForHint !== '')
                    <p class="mt-2 text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                        Текущият линк не е приемлив YouTube адрес. В публичната галерия няма да се покаже вграден плеър — подменете го с валиден YouTube линк и запазете.
                    </p>
                @endif
            @endif
            @error('video_url')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ── Секция: Настройки ────────────────────────────── --}}
    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Настройки</p>

        {{-- Пореден номер --}}
        <div class="max-w-xs">
            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">
                Пореден номер
            </label>
            <input
                type="number"
                id="sort_order"
                name="sort_order"
                value="{{ old('sort_order', $item->sort_order ?? 0) }}"
                min="0"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('sort_order') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('sort_order')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Активен --}}
        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-gray-900
                           focus:ring-0 focus:ring-offset-0 cursor-pointer"
                >
                <span class="text-sm text-gray-700">Активен запис</span>
            </label>
        </div>

    </div>

    {{-- ── Действия ─────────────────────────────────────── --}}
    <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
        <button
            type="submit"
            class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                   hover:bg-gray-700 transition-colors">
            {{ $submitLabel ?? 'Запази' }}
        </button>
        <a href="{{ $cancelUrl ?? route('admin.gallery.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            Отказ
        </a>
    </div>

</div>

{{-- ── Type toggle script ───────────────────────────────── --}}
<script>
(function () {
    var select   = document.getElementById('type');
    var secImage = document.getElementById('section-image');
    var secVideo = document.getElementById('section-video');

    function update() {
        var isImage = select.value === '{{ \App\Models\GalleryItem::TYPE_IMAGE }}';
        secImage.style.display = isImage ? '' : 'none';
        secVideo.style.display = isImage ? 'none' : '';
    }

    select.addEventListener('change', update);
    update();
}());
</script>
