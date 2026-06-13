@extends('layouts.admin')

@section('title', 'Работно време — ' . $workingHour->dayLabel() . ' — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Работно време</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $workingHour->dayLabel() }}</p>
        </div>
        <a href="{{ route('admin.working-hours.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

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

    <form action="{{ route('admin.working-hours.update', $workingHour) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            <div class="px-8 py-6 space-y-5">

                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">{{ $workingHour->dayLabel() }}</p>

                {{-- is_open toggle --}}
                <div>
                    <input type="hidden" name="is_open" value="0">
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="is_open"
                            value="1"
                            id="is_open"
                            {{ old('is_open', $workingHour->is_open) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-gray-900
                                   focus:ring-0 focus:ring-offset-0 cursor-pointer"
                        >
                        <span class="text-sm text-gray-700">Отворено в този ден</span>
                    </label>
                </div>

                {{-- Times --}}
                @php
                    $halfHourOptions = [];
                    for ($hour = 0; $hour < 24; $hour++) {
                        foreach ([0, 30] as $minute) {
                            $halfHourOptions[] = sprintf('%02d:%02d', $hour, $minute);
                        }
                    }

                    $normalizeTimeHi = function ($value) {
                        if ($value === null || $value === '') {
                            return null;
                        }

                        return substr((string) $value, 0, 5);
                    };

                    $startTimeValue = $normalizeTimeHi(old('start_time', $workingHour->start_time));
                    $endTimeValue = $normalizeTimeHi(old('end_time', $workingHour->end_time));
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" id="time-fields">

                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Начало на работния ден
                        </label>
                        <select
                            id="start_time"
                            name="start_time"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('start_time') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                            <option value="" @selected($startTimeValue === null)>—</option>
                            @foreach ($halfHourOptions as $time)
                                <option value="{{ $time }}" @selected($startTimeValue === $time)>{{ $time }}</option>
                            @endforeach
                        </select>
                        @error('start_time')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Край на работния ден
                        </label>
                        <select
                            id="end_time"
                            name="end_time"
                            class="w-full px-3 py-2 border rounded text-sm text-gray-900
                                   focus:outline-none focus:border-gray-500 transition-colors
                                   {{ $errors->has('end_time') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                        >
                            <option value="" @selected($endTimeValue === null)>—</option>
                            @foreach ($halfHourOptions as $time)
                                <option value="{{ $time }}" @selected($endTimeValue === $time)>{{ $time }}</option>
                            @endforeach
                        </select>
                        @error('end_time')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
                <button
                    type="submit"
                    class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                           hover:bg-gray-700 transition-colors">
                    Запази промените
                </button>
                <a href="{{ route('admin.working-hours.index') }}"
                   class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
                    Отказ
                </a>
            </div>

        </div>

    </form>

@push('scripts')
<script>
(function () {
    var checkbox   = document.getElementById('is_open');
    var timeFields = document.getElementById('time-fields');

    function toggle() {
        timeFields.style.opacity = checkbox.checked ? '1' : '0.4';
    }

    checkbox.addEventListener('change', toggle);
    toggle();
}());
</script>
@endpush

@endsection
