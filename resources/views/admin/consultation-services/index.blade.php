@extends('layouts.admin')

@section('title', 'Консултации — Цени — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Консултации — Цени</h1>
            <p class="mt-1 text-sm text-gray-500">Редактирай цените на четирите типа консултации.</p>
        </div>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    @php
        $typeLabels = [
            'phone'   => 'Телефонна',
            'chat'    => 'Чат',
            'written' => 'Писмена',
            'video'   => 'Видео',
        ];
    @endphp

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Тип</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Цена EUR</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Цена BGN</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">BGN видима</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">

                @foreach (['phone', 'chat', 'written', 'video'] as $type)
                    @php $s = $services[$type] ?? null; @endphp
                    @if ($s)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $typeLabels[$type] }}
                                <span class="ml-1.5 text-xs text-gray-400 font-normal font-mono">{{ $type }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ number_format($s->price_eur, 2) }} €
                                @if ($s->isVideo() && $s->price_eur_60 !== null)
                                    <span class="text-gray-400 text-xs ml-1">/ {{ number_format($s->price_eur_60, 2) }} € (60 мин)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ number_format($s->price_bgn, 2) }} лв.
                                @if ($s->isVideo() && $s->price_bgn_60 !== null)
                                    <span class="text-gray-400 text-xs ml-1">/ {{ number_format($s->price_bgn_60, 2) }} лв. (60 мин)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($s->show_bgn_price)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Да</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600">Скрита</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.consultation-services.edit', $s) }}"
                                   class="text-xs px-3 py-1.5 rounded border border-gray-200
                                          text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                    Редактирай
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach

            </tbody>
        </table>
    </div>

@endsection
