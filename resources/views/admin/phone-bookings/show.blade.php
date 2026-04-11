@extends('layouts.admin')

@section('title', 'Телефонна консултация #' . $phoneBooking->id . ' — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Телефонна консултация #{{ $phoneBooking->id }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $phoneBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                &mdash;
                {{ $phoneBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                –
                {{ $phoneBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
            </p>
        </div>
        <a href="{{ route('admin.phone-bookings.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад
        </a>
    </div>

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
                        @if ($phoneBooking->status === \App\Models\PhoneConsultationBooking::STATUS_BOOKED)
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">Записан</span>
                        @elseif ($phoneBooking->status === \App\Models\PhoneConsultationBooking::STATUS_COMPLETED)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Проведена</span>
                        @else
                            <span class="text-gray-700">{{ $phoneBooking->status }}</span>
                        @endif
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Дата</span>
                    <span class="font-medium text-gray-900">
                        {{ $phoneBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Час</span>
                    <span class="font-medium text-gray-900">
                        {{ $phoneBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                        –
                        {{ $phoneBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Метод на плащане</span>
                    <span class="text-gray-700">{{ $phoneBooking->paymentMethodLabel() }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Цена (EUR)</span>
                    <span class="font-medium text-gray-900">
                        {{ number_format((float) $phoneBooking->price_eur, 2, ',', '.') }} €
                    </span>
                </div>

                @if ($phoneBooking->show_bgn_price && $phoneBooking->price_bgn)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Цена (BGN)</span>
                        <span class="font-medium text-gray-900">
                            {{ number_format((float) $phoneBooking->price_bgn, 2, ',', '.') }} лв.
                        </span>
                    </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-gray-500">Записано на</span>
                    <span class="text-gray-700">{{ $phoneBooking->created_at->setTimezone('Europe/Sofia')->format('d.m.Y H:i') }}</span>
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
                    <span class="font-medium text-gray-900">{{ $phoneBooking->fullName() }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Имейл</span>
                    <a href="mailto:{{ $phoneBooking->email }}"
                       class="text-gray-700 hover:text-gray-900 underline underline-offset-2">
                        {{ $phoneBooking->email }}
                    </a>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Телефон</span>
                    <span class="text-gray-700">{{ $phoneBooking->phone }}</span>
                </div>

            </div>

            @if ($phoneBooking->description)
                <div class="px-6 py-4">
                    <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-2">Описание</p>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $phoneBooking->description }}</p>
                </div>
            @endif

        </div>

    </div>

@endsection
