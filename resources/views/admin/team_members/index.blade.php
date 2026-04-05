@extends('layouts.admin')

@section('title', 'Екип — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Екип (За нас)</h1>
            <p class="mt-1 text-sm text-gray-500">Управлявай членовете, показвани в секцията „Екип“ на страницата За нас.</p>
        </div>
        <a href="{{ route('admin.team-members.create') }}"
           class="text-sm font-medium px-4 py-2 bg-gray-900 text-white rounded hover:bg-gray-700 transition-colors">
            + Добави член
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($members->isEmpty())

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма добавени членове</p>
            <p class="text-sm text-gray-400 mb-6">Създай първия запис или изпълни seeders.</p>
            <a href="{{ route('admin.team-members.create') }}"
               class="inline-block text-sm font-medium px-4 py-2 bg-gray-900 text-white rounded
                      hover:bg-gray-700 transition-colors">
                + Добави член
            </a>
        </div>

    @else

        <p class="mb-3 text-xs text-gray-400">
            Общо: {{ $members->total() }} {{ $members->total() === 1 ? 'запис' : 'записа' }}
        </p>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Име</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Длъжност</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Активен</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ред</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($members as $member)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-400">{{ $member->id }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $member->name }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $member->position ?: '—' }}</td>
                            <td class="px-6 py-4">
                                @if ($member->is_active)
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-900 text-white">Да</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Не</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $member->sort_order }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    <a href="{{ route('admin.team-members.edit', $member) }}"
                                       class="text-xs px-3 py-1.5 rounded border border-gray-200
                                              text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                        Редактирай
                                    </a>
                                    <form action="{{ route('admin.team-members.toggle', $member) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border transition-colors
                                                       {{ $member->is_active
                                                           ? 'border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-700'
                                                           : 'border-gray-200 text-gray-400 hover:border-gray-600 hover:text-gray-600' }}">
                                            {{ $member->is_active ? 'Деактивирай' : 'Активирай' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.team-members.destroy', $member) }}" method="POST"
                                          onsubmit="return confirm('Изтрий „{{ addslashes($member->name) }}"?\nТова действие е необратимо.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border border-red-200 text-red-600
                                                       hover:border-red-400 hover:text-red-700 transition-colors">
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

        <div class="mt-6">
            {{ $members->links() }}
        </div>

    @endif

@endsection
