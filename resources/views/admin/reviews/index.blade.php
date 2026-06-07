@extends('layouts.admin')

@section('title', 'Отзиви — Admin')

@section('content')

    <div class="mb-8">
        <h1 class="text-xl font-semibold text-gray-900">Отзиви</h1>
        <p class="mt-1 text-sm text-gray-500">Отзиви, изпратени от посетители на сайта.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
            {{ session('success') }}
        </div>
    @endif

    @php
        use App\Models\Review;
        $tabs = [
            'all' => 'Всички',
            Review::STATUS_PENDING => 'Чакащи',
            Review::STATUS_PUBLISHED => 'Публикувани',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach($tabs as $value => $label)
            <a href="{{ route('admin.reviews.index', $value !== 'all' ? ['status' => $value] : []) }}"
               class="text-sm px-4 py-1.5 rounded transition-colors
                      {{ $filter === $value
                          ? 'bg-gray-900 text-white'
                          : 'border border-gray-200 text-gray-600 hover:border-gray-400 hover:text-gray-900' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($reviews->isEmpty())

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-8 py-16 text-center">
            <p class="text-sm font-medium text-gray-900 mb-1">Няма отзиви</p>
            <p class="text-sm text-gray-400">Когато посетители изпратят отзив, той ще се появи тук.</p>
        </div>

    @else

        <p class="mb-3 text-xs text-gray-400">
            Общо: {{ $reviews->total() }} {{ $reviews->total() === 1 ? 'отзив' : 'отзива' }}
        </p>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Име</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Отзив</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($reviews as $review)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                {{ $review->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $review->fullName() }}</td>
                            <td class="px-6 py-4">
                                @if ($review->status === Review::STATUS_PENDING)
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">Чакащ</span>
                                @elseif ($review->status === Review::STATUS_PUBLISHED)
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-900 text-white">Публикуван</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate" title="{{ $review->body }}">
                                {{ \Illuminate\Support\Str::limit($review->body, 80) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.reviews.show', $review) }}"
                                       class="text-xs px-3 py-1.5 rounded border border-gray-200
                                              text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors">
                                        Преглед
                                    </a>
                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                          onsubmit="return confirm('Изтрий отзива на {{ addslashes($review->fullName()) }}?\nТова действие е необратимо.')">
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
            {{ $reviews->links() }}
        </div>

    @endif

@endsection
