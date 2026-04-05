@extends('layouts.admin')

@section('title', 'Редакция — Екип — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Редакция: {{ $member->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Обнови данните или снимката.</p>
        </div>
        <a href="{{ route('admin.team-members.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към екип
        </a>
    </div>

    <form action="{{ route('admin.team-members.update', $member) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        @include('admin.team_members._form', [
            'submitLabel' => 'Запази промените',
            'cancelUrl'   => route('admin.team-members.index'),
            'member'      => $member,
        ])
    </form>

@endsection
