@extends('layouts.admin')

@section('title', 'Нов затворен период — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Нов затворен период</h1>
            <p class="mt-1 text-sm text-gray-500">Добави период без консултации.</p>
        </div>
        <a href="{{ route('admin.closures.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-medium text-red-700 mb-1">Моля, поправи следните грешки:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li class="text-sm text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.closures.store') }}" method="POST" novalidate>
        @csrf
        @include('admin.closures._form')
    </form>

@endsection
