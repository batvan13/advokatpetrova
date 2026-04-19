@extends('layouts.admin')

@section('title', 'Галерия — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Галерия</h1>
            <p class="mt-1 text-sm text-gray-500">Управлявай снимките и видеата в галерията.</p>
        </div>
        <a href="{{ route('admin.gallery.create') }}"
           class="text-sm font-medium px-4 py-2 bg-gray-900 text-white rounded hover:bg-gray-700 transition-colors">
            + Добави
        </a>
    </div>

    {{-- Filter tabs --}}
    <div class="mb-6 flex items-center gap-2">
        @php
            $tabs = [
                'all'   => 'Всички',
                'image' => 'Снимки',
                'video' => 'Видео',
            ];
        @endphp
        @foreach($tabs as $value => $label)
            <a href="{{ route('admin.gallery.index', $value !== 'all' ? ['type' => $value] : []) }}"
               class="text-sm px-4 py-1.5 rounded transition-colors
                      {{ $type === $value
                          ? 'bg-gray-900 text-white'
                          : 'border border-gray-200 text-gray-600 hover:border-gray-400 hover:text-gray-900' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Flash message --}}
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

    {{-- Empty state --}}
    @if ($items->isEmpty())

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            @if($type !== 'all')
                <p class="text-sm font-medium text-gray-900 mb-1">
                    Няма {{ $type === 'image' ? 'снимки' : 'видеа' }}
                </p>
                <p class="text-sm text-gray-400 mb-6">Промени филтъра или добави нов запис.</p>
            @else
                <p class="text-sm font-medium text-gray-900 mb-1">Галерията е празна</p>
                <p class="text-sm text-gray-400 mb-6">Добави първата снимка или видео.</p>
            @endif
            <a href="{{ route('admin.gallery.create') }}"
               class="inline-block text-sm font-medium px-4 py-2 bg-gray-900 text-white rounded
                      hover:bg-gray-700 transition-colors">
                + Добави
            </a>
        </div>

    @else

        {{-- Count summary --}}
        <p class="mb-3 text-xs text-gray-400">
            Общо: {{ $items->total() }} {{ $items->total() === 1 ? 'запис' : 'записа' }}
        </p>

        {{-- Table card --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Преглед</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Заглавие</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Активен</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ред</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-400">{{ $item->id }}</td>

                            {{-- Preview --}}
                            <td class="px-6 py-4">
                                @if($item->isImage() && $item->image_path)
                                    <img src="{{ upload_url($item->image_path) }}"
                                         alt="{{ $item->title ?? '' }}"
                                         class="h-10 w-10 rounded object-cover border border-gray-100">
                                @elseif($item->isVideo() && $item->video_url)
                                    <a href="{{ $item->video_url }}" target="_blank" rel="noopener"
                                       class="text-xs text-gray-400 hover:text-gray-700 transition-colors whitespace-nowrap">
                                        Видео ↗
                                    </a>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $item->title ?: '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                    {{ $item->isImage() ? 'bg-gray-100 text-gray-600' : 'bg-gray-900 text-white' }}">
                                    {{ \App\Models\GalleryItem::TYPES[$item->type] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->is_active)
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-900 text-white">Да</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Не</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $item->sort_order }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.gallery.edit', $item) }}"
                                       class="text-xs px-3 py-1.5 rounded border border-gray-200
                                              text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                        Редактирай
                                    </a>

                                    {{-- Toggle status --}}
                                    <form action="{{ route('admin.gallery.toggle', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border transition-colors
                                                       {{ $item->is_active
                                                           ? 'border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-700'
                                                           : 'border-gray-200 text-gray-400 hover:border-gray-600 hover:text-gray-600' }}">
                                            {{ $item->is_active ? 'Деактивирай' : 'Активирай' }}
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                                          onsubmit="return confirm('Изтрий този запис?\nТова действие е необратимо.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded border border-transparent
                                                       text-red-400 hover:border-red-300 hover:text-red-600 transition-colors">
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

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div class="mt-4">
                {{ $items->links('pagination::tailwind') }}
            </div>
        @endif

    @endif

@endsection
