@extends('layouts.admin')

@section('title', 'Работно време — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Работно време</h1>
            <p class="mt-1 text-sm text-gray-500">Работни часове за телефонни консултации по дни от седмицата.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($hours->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма записи</p>
            <p class="text-sm text-gray-400">Изпълни seeders, за да се инициализират 7-те дни.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ден</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Начало</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Край</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($hours as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $row->dayLabel() }}</td>
                            <td class="px-6 py-4">
                                @if ($row->is_open)
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Отворено</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">Затворено</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $row->start_time ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $row->end_time ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.working-hours.edit', $row) }}"
                                   class="text-xs px-3 py-1.5 rounded border border-gray-200
                                          text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                    Редактирай
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection
