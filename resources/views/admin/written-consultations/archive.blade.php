@extends('layouts.admin')

@section('title', 'Архив — Писмени консултации — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Архив — Писмени консултации</h1>
            <p class="mt-1 text-sm text-gray-500">Архивирани заявки за писмена консултация.</p>
        </div>
        <a href="{{ route('admin.written-consultations.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Активни заявки
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

    @if ($requests->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма архивирани заявки</p>
            <p class="text-sm text-gray-400">Все още няма архивирани писмени консултации.</p>
        </div>
    @else

        <p class="mb-3 text-xs text-gray-400">
            Общо: {{ $requests->total() }} {{ $requests->total() === 1 ? 'заявка' : 'заявки' }}
        </p>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Клиент</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Имейл</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Плащане</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Архивирано</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($requests as $req)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $req->id }}</td>
                            <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">
                                {{ ($req->submitted_at ?? $req->created_at)->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $req->fullName() }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $req->email }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ $req->paymentMethodLabel() }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                {{ $req->archived_at->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.written-consultations.show', $req) }}"
                                       class="text-xs px-3 py-1.5 rounded border border-gray-200
                                              text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                        Преглед
                                    </a>
                                    <form action="{{ route('admin.written-consultations.destroy', $req) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border border-gray-200
                                                       text-red-500 hover:border-red-300 hover:bg-red-50 transition-colors"
                                                onclick="return confirm('Изтрий заявката на {{ e($req->fullName()) }}? Прикачените файлове ще бъдат изтрити. Действието е необратимо.')">
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

        @if ($requests->hasPages())
            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        @endif

    @endif

@endsection
