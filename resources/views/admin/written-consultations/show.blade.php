@extends('layouts.admin')

@section('title', 'Писмена консултация #' . $request->id . ' — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Заявка #{{ $request->id }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $request->fullName() }} —
                {{ ($request->submitted_at ?? $request->created_at)->format('d.m.Y, H:i') }}
            </p>
        </div>
        <a href="{{ route('admin.written-consultations.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към списъка
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">

        {{-- Status + action --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700">Статус:</span>
                @if ($request->status === 'answered')
                    <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">Отговорена</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">Нова / Чака отговор</span>
                @endif
            </div>

            @if ($request->status !== 'answered')
                <form action="{{ route('admin.written-consultations.mark-answered', $request) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button
                        type="submit"
                        class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors"
                    >
                        Маркирай като отговорена
                    </button>
                </form>
            @endif
        </div>

        {{-- Main data --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            {{-- Personal data --}}
            <div class="px-8 py-6">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-4">Лични данни</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Ime</p>
                        <p class="font-medium text-gray-900">{{ $request->first_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Фамилия</p>
                        <p class="font-medium text-gray-900">{{ $request->last_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Имейл</p>
                        <p class="font-medium text-gray-900">
                            <a href="mailto:{{ $request->email }}" class="hover:underline">{{ $request->email }}</a>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Телефон</p>
                        <p class="font-medium text-gray-900">{{ $request->phone }}</p>
                    </div>
                </div>
            </div>

            {{-- Consultation data --}}
            <div class="px-8 py-6">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-4">Казус</p>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Заглавие</p>
                        <p class="font-medium text-gray-900">{{ $request->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Описание</p>
                        <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $request->description }}</p>
                    </div>
                </div>
            </div>

            {{-- Payment + pricing --}}
            <div class="px-8 py-6">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-4">Плащане</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Метод</p>
                        <p class="font-medium text-gray-900">{{ $request->paymentMethodLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Цена EUR</p>
                        <p class="font-medium text-gray-900">{{ number_format((float) $request->price_eur, 2, ',', '.') }} €</p>
                    </div>
                    @if ($request->show_bgn_price && $request->price_bgn)
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Цена BGN</p>
                            <p class="font-medium text-gray-900">{{ number_format((float) $request->price_bgn, 2, ',', '.') }} лв.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Attachments --}}
            <div class="px-8 py-6">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400 mb-4">
                    Прикачени файлове
                    <span class="normal-case font-normal text-gray-400">({{ $request->attachments->count() }})</span>
                </p>

                @if ($request->attachments->isEmpty())
                    <p class="text-sm text-gray-400">Няма прикачени файлове.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($request->attachments as $attachment)
                            <li class="flex items-center justify-between rounded border border-gray-100 bg-gray-50 px-4 py-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                    </svg>
                                    <span class="text-sm text-gray-700 truncate">{{ $attachment->original_name }}</span>
                                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $attachment->humanSize() }}</span>
                                </div>
                                <a
                                    href="{{ route('admin.written-consultations.download', [$request, $attachment->id]) }}"
                                    class="ml-4 flex-shrink-0 text-xs px-3 py-1.5 rounded border border-gray-200 text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors"
                                >
                                    Изтегли
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>

    </div>

@endsection
