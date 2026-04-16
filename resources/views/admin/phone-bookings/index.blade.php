@extends('layouts.admin')

@section('title', 'Телефонни консултации — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Телефонни консултации</h1>
            <p class="mt-1 text-sm text-gray-500">Записани телефонни консултации.</p>
        </div>
        <a href="{{ route('admin.phone-bookings.archived') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            Архив →
        </a>
    </div>

    @if ($bookings->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма записвания</p>
            <p class="text-sm text-gray-400">Все още няма направени телефонни консултации.</p>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Статус</th>
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
                            <td class="px-5 py-4">
                                @if ($booking->status === 'pending_payment')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Очаква плащане</span>
                                @elseif ($booking->status === 'confirmed')
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">Потвърдена</span>
                                @elseif ($booking->status === 'completed')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Проведена</span>
                                @elseif ($booking->status === 'expired')
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-400">Изтекла</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">{{ $booking->status }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.phone-bookings.show', $booking) }}"
                                   class="text-xs px-3 py-1.5 rounded border border-gray-200
                                          text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                    Детайли
                                </a>
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
