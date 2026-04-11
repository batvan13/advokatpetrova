{{-- Shared form fields for create + edit --}}

<div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

    <div class="px-8 py-6 space-y-5">

        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Период</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Начало <span class="text-red-400">*</span>
                </label>
                <input
                    type="datetime-local"
                    id="starts_at"
                    name="starts_at"
                    value="{{ old('starts_at', isset($closure) ? $closure->starts_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full px-3 py-2 border rounded text-sm text-gray-900
                           focus:outline-none focus:border-gray-500 transition-colors
                           {{ $errors->has('starts_at') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                >
                @error('starts_at')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Край <span class="text-red-400">*</span>
                </label>
                <input
                    type="datetime-local"
                    id="ends_at"
                    name="ends_at"
                    value="{{ old('ends_at', isset($closure) ? $closure->ends_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full px-3 py-2 border rounded text-sm text-gray-900
                           focus:outline-none focus:border-gray-500 transition-colors
                           {{ $errors->has('ends_at') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                >
                @error('ends_at')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div>
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1.5">
                Причина <span class="text-gray-400 font-normal">(незадължително)</span>
            </label>
            <input
                type="text"
                id="reason"
                name="reason"
                value="{{ old('reason', $closure->reason ?? '') }}"
                placeholder="Напр. Годишен отпуск"
                maxlength="500"
                class="w-full px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:border-gray-500 transition-colors
                       {{ $errors->has('reason') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
            >
            @error('reason')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
        <button
            type="submit"
            class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                   hover:bg-gray-700 transition-colors">
            Запази
        </button>
        <a href="{{ route('admin.closures.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            Отказ
        </a>
    </div>

</div>
