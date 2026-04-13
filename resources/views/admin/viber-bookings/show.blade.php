@extends('layouts.admin')

@section('title', 'Viber консултация #' . $viberBooking->id . ' — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-gray-900">Viber консултация #{{ $viberBooking->id }}</h1>
                @if ($viberBooking->archived_at)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">Архивиран</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $viberBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                &mdash;
                {{ $viberBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                –
                {{ $viberBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                ({{ $viberBooking->duration_minutes }} мин.)
            </p>
        </div>
        <a href="{{ route('admin.viber-bookings.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 px-4 py-3 bg-white border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif
    @if (session('info'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-500">
            {{ session('info') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Booking details --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            <div class="px-6 py-4">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Записване</p>
            </div>

            <div class="px-6 py-4 space-y-3 text-sm">

                <div class="flex justify-between">
                    <span class="text-gray-500">Статус</span>
                    <span>
                        @if ($viberBooking->status === \App\Models\ViberConsultationBooking::STATUS_BOOKED)
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">Записан</span>
                        @elseif ($viberBooking->status === \App\Models\ViberConsultationBooking::STATUS_COMPLETED)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Проведена</span>
                        @else
                            <span class="text-gray-700">{{ $viberBooking->status }}</span>
                        @endif
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Продължителност</span>
                    <span class="font-medium text-gray-900">{{ $viberBooking->duration_minutes }} минути</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Дата</span>
                    <span class="font-medium text-gray-900">
                        {{ $viberBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Час</span>
                    <span class="font-medium text-gray-900">
                        {{ $viberBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                        –
                        {{ $viberBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Метод на плащане</span>
                    <span class="text-gray-700">{{ $viberBooking->paymentMethodLabel() }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Цена (EUR)</span>
                    <span class="font-medium text-gray-900">
                        {{ number_format((float) $viberBooking->price_eur, 2, ',', '.') }} €
                    </span>
                </div>

                @if ($viberBooking->show_bgn_price && $viberBooking->price_bgn)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Цена (BGN)</span>
                        <span class="font-medium text-gray-900">
                            {{ number_format((float) $viberBooking->price_bgn, 2, ',', '.') }} лв.
                        </span>
                    </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-gray-500">Записано на</span>
                    <span class="text-gray-700">{{ $viberBooking->created_at->setTimezone('Europe/Sofia')->format('d.m.Y H:i') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Google Calendar</span>
                    <span>
                        @if ($viberBooking->google_event_id)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Синхронизиран</span>
                        @elseif ($viberBooking->google_sync_status === 'failed')
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Неуспешна синхронизация</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </span>
                </div>

            </div>

        </div>

        {{-- Client details --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            <div class="px-6 py-4">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Клиент</p>
            </div>

            <div class="px-6 py-4 space-y-3 text-sm">

                <div class="flex justify-between">
                    <span class="text-gray-500">Три имена</span>
                    <span class="font-medium text-gray-900">{{ $viberBooking->fullName() }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Имейл</span>
                    <a href="mailto:{{ $viberBooking->email }}"
                       class="text-gray-700 hover:text-gray-900 underline underline-offset-2">
                        {{ $viberBooking->email }}
                    </a>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Телефон</span>
                    <span class="text-gray-700">{{ $viberBooking->phone }}</span>
                </div>

            </div>

            @if ($viberBooking->description)
                <div class="px-6 py-4">
                    <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-2">Описание</p>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $viberBooking->description }}</p>
                </div>
            @endif

        </div>

    </div>

    @if (! $viberBooking->archived_at)
        <div class="mt-6 flex justify-end">
            @if ($viberBooking->status === \App\Models\ViberConsultationBooking::STATUS_BOOKED)
                <form action="{{ route('admin.viber-bookings.complete', $viberBooking) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:border-gray-500 hover:text-gray-900 transition-colors"
                            onclick="return confirm('Маркирай консултацията като проведена?')">
                        Маркирай като проведена
                    </button>
                </form>
            @elseif ($viberBooking->status === \App\Models\ViberConsultationBooking::STATUS_COMPLETED)
                <form action="{{ route('admin.viber-bookings.archive', $viberBooking) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded hover:border-gray-400 hover:text-gray-700 transition-colors"
                            onclick="return confirm('Архивирай това записване? То ще изчезне от активния списък.')">
                        Архивирай
                    </button>
                </form>
            @endif
        </div>
    @endif

@endsection
