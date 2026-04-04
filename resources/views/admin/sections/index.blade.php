@extends('layouts.admin')

@section('title', 'Секции — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Секции</h1>
            <p class="mt-1 text-sm text-gray-500">Управлявай съдържанието на страниците.</p>
        </div>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Empty state --}}
    @if ($sections->isEmpty())

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма секции в базата</p>
            <p class="text-sm text-gray-400">Изпълни seeders или добави записи ръчно.</p>
        </div>

    @else

        {{-- Count summary --}}
        <p class="mb-3 text-xs text-gray-400">
            Общо: {{ $sections->count() }} {{ $sections->count() === 1 ? 'секция' : 'секции' }}
        </p>

        {{-- Table card --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Страница</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Секция</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Заглавие</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    @php $currentPage = null; @endphp

                    @foreach ($sections as $section)

                        {{-- Page group header --}}
                        @if ($section->page !== $currentPage)
                            @php $currentPage = $section->page; @endphp
                            <tr class="bg-gray-50">
                                <td colspan="4"
                                    class="px-6 py-2 text-sm font-semibold text-gray-700">
                                    {{ admin_page_section_page_label($section->page) }}
                                </td>
                            </tr>
                        @endif

                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-900 text-sm">{{ admin_page_section_page_label($section->page) }}</td>
                            <td class="px-6 py-4 text-gray-600 text-sm">{{ admin_page_section_section_label($section->section, $section->page) }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $section->title ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.sections.edit', $section) }}"
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
