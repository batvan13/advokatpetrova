@extends('layouts.admin')

@section('title', 'Чат консултация #' . $chatBooking->id . ' — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-gray-900">Чат консултация #{{ $chatBooking->id }}</h1>
                @if ($chatBooking->archived_at)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">Архивиран</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $chatBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                &mdash;
                {{ $chatBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                –
                {{ $chatBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                (30 мин.)
            </p>
        </div>
        <a href="{{ route('admin.chat-bookings.index') }}"
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
                        @if ($chatBooking->status === 'pending_payment')
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Очаква плащане</span>
                        @elseif ($chatBooking->status === 'confirmed')
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">Потвърдена</span>
                        @elseif ($chatBooking->status === 'completed')
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Проведена</span>
                        @elseif ($chatBooking->status === 'expired')
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-400">Изтекла</span>
                        @else
                            <span class="text-gray-700">{{ $chatBooking->status }}</span>
                        @endif
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Дата</span>
                    <span class="font-medium text-gray-900">
                        {{ $chatBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Час</span>
                    <span class="font-medium text-gray-900">
                        {{ $chatBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                        –
                        {{ $chatBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Продължителност</span>
                    <span class="text-gray-700">30 минути</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Метод на плащане</span>
                    <span class="text-gray-700">{{ $chatBooking->paymentMethodLabel() }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Цена (EUR)</span>
                    <span class="font-medium text-gray-900">
                        {{ number_format((float) $chatBooking->price_eur, 2, ',', '.') }} €
                    </span>
                </div>

                @if ($chatBooking->show_bgn_price && $chatBooking->price_bgn)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Цена (BGN)</span>
                        <span class="font-medium text-gray-900">
                            {{ number_format((float) $chatBooking->price_bgn, 2, ',', '.') }} лв.
                        </span>
                    </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-gray-500">Записано на</span>
                    <span class="text-gray-700">{{ $chatBooking->created_at->setTimezone('Europe/Sofia')->format('d.m.Y H:i') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Google Calendar</span>
                    <span>
                        @if ($chatBooking->google_event_id)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Синхронизиран</span>
                        @elseif ($chatBooking->google_sync_status === 'failed')
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Неуспешна синхронизация</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </span>
                </div>

            </div>

        </div>

        {{-- Client + Session details --}}
        <div class="space-y-6">

            {{-- Client --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

                <div class="px-6 py-4">
                    <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Клиент</p>
                </div>

                <div class="px-6 py-4 space-y-3 text-sm">

                    <div class="flex justify-between">
                        <span class="text-gray-500">Три имена</span>
                        <span class="font-medium text-gray-900">{{ $chatBooking->fullName() }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Имейл</span>
                        <a href="mailto:{{ $chatBooking->email }}"
                           class="text-gray-700 hover:text-gray-900 underline underline-offset-2">
                            {{ $chatBooking->email }}
                        </a>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Телефон</span>
                        <span class="text-gray-700">{{ $chatBooking->phone }}</span>
                    </div>

                </div>

                @if ($chatBooking->description)
                    <div class="px-6 py-4">
                        <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-2">Описание</p>
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $chatBooking->description }}</p>
                    </div>
                @endif

            </div>

            {{-- Session bootstrap status --}}
            @if ($chatBooking->session)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

                    <div class="px-6 py-4">
                        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Чат сесия</p>
                    </div>

                    <div class="px-6 py-4 space-y-3 text-sm">

                        <div class="flex justify-between">
                            <span class="text-gray-500">Фаза</span>
                            <span class="inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-medium text-yellow-700">
                                {{ $chatBooking->session->phaseLabel() }}
                            </span>
                        </div>

                        @if ($chatBooking->session->started_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Начало</span>
                                <span class="text-gray-700">
                                    {{ $chatBooking->session->started_at->setTimezone('Europe/Sofia')->format('d.m.Y H:i') }}
                                </span>
                            </div>
                        @endif

                        @if ($chatBooking->session->ended_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Край</span>
                                <span class="text-gray-700">
                                    {{ $chatBooking->session->ended_at->setTimezone('Europe/Sofia')->format('d.m.Y H:i') }}
                                </span>
                            </div>
                        @endif

                        @if (!$chatBooking->session->started_at)
                            <p class="text-xs text-gray-400 italic">
                                Сесията ще стартира при присъединяване на адвоката в Phase 3B.
                            </p>
                        @endif

                    </div>

                </div>
            @endif

        </div>

    </div>

    @if (! $chatBooking->archived_at)
        <div class="mt-6 flex justify-end gap-3">
            @if ($chatBooking->status === 'confirmed')
                @if ($chatBooking->session && $chatBooking->session->phase === 'waiting')
                    <form action="{{ route('admin.chat-bookings.start', $chatBooking) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-gray-900 border border-gray-900 rounded hover:bg-gray-800 transition-colors"
                                onclick="return confirm('Стартирай чат консултацията?')">
                            Стартирай чат консултацията
                        </button>
                    </form>
                @endif
                <form action="{{ route('admin.chat-bookings.complete', $chatBooking) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:border-gray-500 hover:text-gray-900 transition-colors"
                            onclick="return confirm('Маркирай консултацията като проведена?')">
                        Маркирай като проведена
                    </button>
                </form>
            @elseif ($chatBooking->status === 'completed')
                <form action="{{ route('admin.chat-bookings.archive', $chatBooking) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded hover:border-gray-400 hover:text-gray-700 transition-colors"
                            onclick="return confirm('Архивирай това записване? То ще изчезне от активния списък.')">
                        Архивирай
                    </button>
                </form>
            @elseif ($chatBooking->status === 'pending_payment' || $chatBooking->status === 'expired')
                <form action="{{ route('admin.chat-bookings.archive', $chatBooking) }}" method="POST">
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
