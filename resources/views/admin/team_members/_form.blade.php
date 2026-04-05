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
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Данни</p>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                Име <span class="text-red-400">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $member->name ?? '') }}"
                required
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('name')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700 mb-1.5">
                Длъжност
            </label>
            <input
                type="text"
                id="position"
                name="position"
                value="{{ old('position', $member->position ?? '') }}"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('position') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('position')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Телефон
                </label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $member->phone ?? '') }}"
                    class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:border-gray-500 transition-colors
                           {{ $errors->has('phone') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                >
                @error('phone')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Имейл
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $member->email ?? '') }}"
                    class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:border-gray-500 transition-colors
                           {{ $errors->has('email') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                >
                @error('email')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="px-8 py-6 space-y-5">
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Снимка</p>

        @if (isset($member) && $member->image_path)
            <div>
                <img
                    src="{{ asset('storage/' . $member->image_path) }}"
                    alt=""
                    class="h-32 w-auto max-w-full rounded border border-gray-200 object-cover"
                >
                <p class="mt-1.5 text-xs text-gray-400">Текуща снимка. Качи нова, за да я замениш.</p>
            </div>
        @endif

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ isset($member) && $member->image_path ? 'Нова снимка' : 'Снимка' }}
            </label>
            <input
                type="file"
                id="image"
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
    </div>

    <div class="px-8 py-6 space-y-5">
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Настройки</p>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">
                Пореден номер
            </label>
            <input
                type="number"
                id="sort_order"
                name="sort_order"
                value="{{ old('sort_order', $member->sort_order ?? 0) }}"
                min="0"
                class="w-full max-w-xs px-3 py-2 border rounded text-sm text-gray-900
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('sort_order') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('sort_order')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $member->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-gray-900
                           focus:ring-0 focus:ring-offset-0 cursor-pointer"
                >
                <span class="text-sm text-gray-700">Активен в сайта</span>
            </label>
            @error('is_active')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
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
        <a href="{{ $cancelUrl ?? route('admin.team-members.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            Отказ
        </a>
    </div>
</div>
