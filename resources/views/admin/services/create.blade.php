@extends('layouts.admin')

@section('title', 'Нова услуга — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Нова услуга</h1>
            <p class="mt-1 text-sm text-gray-500">Попълни данните и запази новата услуга.</p>
        </div>
        <a href="{{ route('admin.services.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към услуги
        </a>
    </div>

    <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @include('admin.services._form', [
            'submitLabel' => 'Запази услугата',
            'cancelUrl'   => route('admin.services.index'),
        ])
    </form>

@endsection
