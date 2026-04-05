@extends('layouts.admin')

@section('title', 'Нов член на екипа — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Нов член на екипа</h1>
            <p class="mt-1 text-sm text-gray-500">Попълни данните и качи снимка (по желание).</p>
        </div>
        <a href="{{ route('admin.team-members.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към екип
        </a>
    </div>

    <form action="{{ route('admin.team-members.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @include('admin.team_members._form', [
            'submitLabel' => 'Запази',
            'cancelUrl'   => route('admin.team-members.index'),
            'member'      => null,
        ])
    </form>

@endsection
