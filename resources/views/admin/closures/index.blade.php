@extends('layouts.admin')

@section('title', 'Затворени периоди — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Затворени периоди</h1>
            <p class="mt-1 text-sm text-gray-500">Отпуска, отсъствия и други периоди без консултации.</p>
        </div>
        <a href="{{ route('admin.closures.create') }}"
           class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors">
            + Добави период
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($closures->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма затворени периоди</p>
            <p class="text-sm text-gray-400">Добави период, ако предстои отпуска или отсъствие.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">От</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">До</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Причина</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($closures as $closure)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-900 whitespace-nowrap">
                                {{ $closure->starts_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-gray-900 whitespace-nowrap">
                                {{ $closure->ends_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $closure->reason ?: '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.closures.edit', $closure) }}"
                                       class="text-xs px-3 py-1.5 rounded border border-gray-200
                                              text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                        Редактирай
                                    </a>
                                    <form action="{{ route('admin.closures.destroy', $closure) }}" method="POST"
                                          onsubmit="return confirm('Изтрий този период?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border border-gray-200
                                                       text-red-500 hover:border-red-400 hover:text-red-700 transition-colors">
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
    @endif

@endsection
