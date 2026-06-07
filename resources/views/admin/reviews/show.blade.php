@extends('layouts.admin')

@section('title', 'Отзив #' . $review->id . ' — Admin')

@section('content')

    @php
        use App\Models\Review;
    @endphp

    <div class="mb-8 flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.reviews.index') }}"
               class="text-xs text-gray-400 hover:text-gray-700 transition-colors mb-2 inline-block">
                ← Към списъка
            </a>
            <h1 class="text-xl font-semibold text-gray-900">Отзив #{{ $review->id }}</h1>
        </div>
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

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <dl class="divide-y divide-gray-100 text-sm">
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Статус</dt>
                <dd class="sm:col-span-2">
                    @if ($review->status === Review::STATUS_PENDING)
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">Чакащ</span>
                    @elseif ($review->status === Review::STATUS_PUBLISHED)
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-900 text-white">Публикуван</span>
                    @endif
                </dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Дата</dt>
                <dd class="sm:col-span-2 text-gray-900">{{ $review->created_at->format('d.m.Y H:i') }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Име</dt>
                <dd class="sm:col-span-2 text-gray-900">{{ $review->first_name }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Фамилия</dt>
                <dd class="sm:col-span-2 text-gray-900">{{ $review->last_name }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Имейл</dt>
                <dd class="sm:col-span-2 text-gray-900">
                    <a href="mailto:{{ $review->email }}" class="text-gray-900 hover:underline">{{ $review->email }}</a>
                </dd>
            </div>
            <div class="px-6 py-4">
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Отзив</dt>
                <dd class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $review->body }}</dd>
            </div>
        </dl>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-wrap items-center gap-3">
            @if ($review->status === Review::STATUS_PENDING)
                <form action="{{ route('admin.reviews.publish', $review) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="text-sm font-medium px-4 py-2 bg-gray-900 text-white rounded hover:bg-gray-700 transition-colors">
                        Публикувай
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline"
                  onsubmit="return confirm('Изтрий отзива на {{ addslashes($review->fullName()) }}?\nТова действие е необратимо.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-sm font-medium px-4 py-2 bg-white border border-red-200 text-red-600 rounded hover:border-red-400 hover:text-red-700 transition-colors">
                    Изтрий
                </button>
            </form>
        </div>
    </div>

@endsection
