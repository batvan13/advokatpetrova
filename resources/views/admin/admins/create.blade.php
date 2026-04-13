@extends('layouts.admin')

@section('title', 'Нов администратор — Admin')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Нов администратор</h1>
            <p class="mt-1 text-sm text-gray-500">Попълни данните и запази.</p>
        </div>
        <a href="{{ route('admin.admins.index') }}"
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

    <form action="{{ route('admin.admins.store') }}" method="POST" novalidate>
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm divide-y divide-gray-100">

            <div class="px-8 py-6 space-y-5">
                <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Данни за акаунта</p>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Име <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        autofocus
                        class="w-full max-w-md px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Имейл <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full max-w-md px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('email') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Парола <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="w-full max-w-md px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors
                               {{ $errors->has('password') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @else
                        <p class="mt-1.5 text-xs text-gray-400">Минимум 8 символа.</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Потвърди паролата <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="w-full max-w-md px-3 py-2 border rounded text-sm text-gray-900 placeholder-gray-400
                               focus:outline-none focus:border-gray-500 transition-colors border-gray-200"
                    >
                </div>
            </div>

            <div class="px-8 py-5 bg-gray-50 rounded-b-xl flex items-center gap-4">
                <button
                    type="submit"
                    class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded
                           hover:bg-gray-700 transition-colors">
                    Създай администратор
                </button>
                <a href="{{ route('admin.admins.index') }}"
                   class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
                    Отказ
                </a>
            </div>

        </div>
    </form>

@endsection
