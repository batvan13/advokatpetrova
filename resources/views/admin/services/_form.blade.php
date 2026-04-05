{{--
    Reusable form partial for Service create / edit.

    Expected variables (all optional — defaults handle the create case):
      $service     — Service model instance (for edit); absent on create
      $submitLabel — string, button text; default: 'Запази услугата'
      $cancelUrl   — string, cancel href;  default: admin.services.index
--}}

{{-- ── Обобщение на грешки ─────────────────────────────── --}}
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

{{-- ── Form card ──────────────────────────────────────────── --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

    {{-- ── Секция: Основна информация ──────────────────────── --}}
    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Основна информация</p>

        {{-- Заглавие --}}
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
                Заглавие <span class="text-red-400">*</span>
            </label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $service->title ?? '') }}"
                placeholder="Напр. Ремонт на покриви"
                autofocus
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('title') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('title')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">
                Slug
            </label>
            <input
                type="text"
                id="slug"
                name="slug"
                value="{{ old('slug', $service->slug ?? '') }}"
                placeholder="remont-na-pokrivi"
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

    </div>

    {{-- ── Секция: Описания ─────────────────────────────────── --}}
    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Описания</p>

        {{-- Кратко описание --}}
        <div>
            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1.5">
                Кратко описание
            </label>
            <textarea
                id="short_description"
                name="short_description"
                rows="3"
                placeholder="Едно-две изречения, показвани в списъка с услуги."
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors resize-y
                       {{ $errors->has('short_description') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >{{ old('short_description', $service->short_description ?? '') }}</textarea>
            @error('short_description')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Пълно описание --}}
        <div>
            <label for="full_description" class="block text-sm font-medium text-gray-700 mb-1.5">
                Пълно описание
            </label>
            <textarea
                id="full_description"
                name="full_description"
                rows="6"
                placeholder="Детайлно описание на услугата."
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors resize-y
                       {{ $errors->has('full_description') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >{{ old('full_description', $service->full_description ?? '') }}</textarea>
            @error('full_description')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ── Секция: Изображение ───────────────────────────────── --}}
    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Изображение</p>

        <div>
            <label for="service_image" class="block text-sm font-medium text-gray-700 mb-1.5">
                Основно изображение
            </label>
            <input
                type="file"
                id="service_image"
                name="image"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0
                       file:text-sm file:font-medium file:bg-gray-900 file:text-white hover:file:bg-gray-700"
            >
            @error('image')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-gray-400">JPG, PNG или WebP, до 4 MB. Запазва се като WebP.</p>
        </div>

        @isset($service)
            @if($service->image)
                <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <p class="text-xs text-gray-500">Текущо изображение</p>
                    <img
                        src="{{ asset('storage/' . $service->image) }}"
                        alt=""
                        class="max-h-40 rounded border border-gray-100 object-contain"
                    >
                    <input type="hidden" name="remove_image" value="0">
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remove_image"
                            value="1"
                            @checked(old('remove_image', false))
                            class="w-4 h-4 rounded border-gray-300 text-gray-900
                                   focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        >
                        <span class="text-sm text-gray-700">Премахни текущото изображение</span>
                    </label>
                </div>
            @endif
        @endisset

    </div>

    {{-- ── Секция: Настройки ────────────────────────────────── --}}
    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Настройки</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Икона --}}
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Икона
                </label>
                <input
                    type="text"
                    id="icon"
                    name="icon"
                    value="{{ old('icon', $service->icon ?? '') }}"
                    placeholder="Напр. heroicon-wrench"
                    class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:border-gray-500 transition-colors
                           {{ $errors->has('icon') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                >
                @error('icon')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Пореден номер --}}
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Пореден номер
                </label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    value="{{ old('sort_order', $service->sort_order ?? 0) }}"
                    min="0"
                    class="w-full px-3 py-2 border rounded text-sm text-gray-900
                           focus:outline-none focus:border-gray-500 transition-colors
                           {{ $errors->has('sort_order') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                >
                @error('sort_order')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Активна услуга --}}
        <div>
            {{-- Hidden ensures value=0 is submitted when checkbox is unchecked --}}
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-gray-900
                           focus:ring-0 focus:ring-offset-0 cursor-pointer"
                >
                <span class="text-sm text-gray-700">Активна услуга</span>
            </label>
            @error('is_active')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ── Действия ─────────────────────────────────────────── --}}
    <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
        <button
            type="submit"
            class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                   hover:bg-gray-700 transition-colors">
            {{ $submitLabel ?? 'Запази услугата' }}
        </button>
        <a href="{{ $cancelUrl ?? route('admin.services.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            Отказ
        </a>
    </div>

</div>
