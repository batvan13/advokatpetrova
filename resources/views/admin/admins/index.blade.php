@extends('layouts.admin')

@section('title', 'Администратори — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Администратори</h1>
            <p class="mt-1 text-sm text-gray-500">Акаунти с достъп до административния панел.</p>
        </div>
        <a href="{{ route('admin.admins.create') }}"
           class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors">
            + Нов администратор
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Име</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Имейл</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Създаден</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($admins as $admin)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $admin->name }}
                            @if ($admin->id === Auth::id())
                                <span class="ml-2 inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Вие</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $admin->email }}</td>
                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            {{ $admin->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.admins.edit', $admin) }}"
                                   class="text-xs px-3 py-1.5 rounded border border-gray-200
                                          text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                    Редактирай
                                </a>
                                @if ($admin->id !== Auth::id() && $totalCount > 1)
                                    <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border border-gray-200
                                                       text-red-500 hover:border-red-300 hover:bg-red-50 transition-colors"
                                                onclick="return confirm('Изтрий администратор {{ addslashes($admin->name) }}?')">
                                            Изтрий
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
