@extends('layouts.admin')

@section('title', 'Редакция на услуга — Admin')

@section('content')

    {{-- Page heading --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Редакция на услуга</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $service->title }}</p>
        </div>
        <a href="{{ route('admin.services.index') }}"
           class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
            ← Назад към услуги
        </a>
    </div>

    <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        @include('admin.services._form', [
            'service'     => $service,
            'submitLabel' => 'Запази промените',
            'cancelUrl'   => route('admin.services.index'),
        ])
    </form>

@endsection
