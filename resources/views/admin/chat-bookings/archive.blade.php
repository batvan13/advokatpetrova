@extends('layouts.admin')

@section('title', 'Архив — Чат консултации — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Архив — Чат консултации</h1>
            <p class="mt-1 text-sm text-gray-500">Архивирани онлайн чат консултации.</p>
        </div>
        <a href="{{ route('admin.chat-bookings.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Активни записвания
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

    @if ($bookings->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма архивирани записвания</p>
            <p class="text-sm text-gray-400">Все още няма архивирани чат консултации.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Дата / Час</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Клиент</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Имейл</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Плащане</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Архивирано</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($bookings as $booking)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-medium text-gray-900">
                                    {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $booking->starts_at->setTimezone('Europe/Sofia')->format('H:i') }}
                                    –
                                    {{ $booking->ends_at->setTimezone('Europe/Sofia')->format('H:i') }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-gray-900">{{ $booking->fullName() }}</td>
                            <td class="px-5 py-4 text-gray-600 text-xs">{{ $booking->email }}</td>
                            <td class="px-5 py-4 text-gray-600 text-xs">{{ $booking->paymentMethodLabel() }}</td>
                            <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                                {{ $booking->archived_at->setTimezone('Europe/Sofia')->format('d.m.Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.chat-bookings.show', $booking) }}"
                                       class="text-xs px-3 py-1.5 rounded border border-gray-200
                                              text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                        Преглед
                                    </a>
                                    <form action="{{ route('admin.chat-bookings.destroy', $booking) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border border-gray-200
                                                       text-red-500 hover:border-red-300 hover:bg-red-50 transition-colors"
                                                onclick="return confirm('Изтрий записването на {{ e($booking->fullName()) }} от {{ $booking->starts_at->setTimezone("Europe/Sofia")->format("d.m.Y") }}? Действието е необратимо.')">
                                            Изтрий
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @endif
    @endif

@endsection
