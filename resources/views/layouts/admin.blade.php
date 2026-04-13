<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="/favicon.ico" sizes="any">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen flex antialiased bg-gray-50">

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="w-60 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col">

        {{-- Brand --}}
        <div class="h-14 flex items-center px-5 border-b border-gray-100">
            <span class="text-xs font-bold tracking-widest uppercase text-gray-900 select-none">
                Admin Panel
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.dashboard')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.services.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.services.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Услуги
            </a>

            <a href="{{ route('admin.team-members.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.team-members.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Екип
            </a>

            <a href="{{ route('admin.posts.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.posts.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Блог
            </a>

            <a href="{{ route('admin.sections.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.sections.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Секции
            </a>

            <a href="{{ route('admin.settings.edit') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.settings.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Настройки
            </a>

            <a href="{{ route('admin.consultation-services.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.consultation-services.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Консултации
            </a>

            <a href="{{ route('admin.written-consultations.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.written-consultations.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Писмени конс.
            </a>

            <a href="{{ route('admin.phone-bookings.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.phone-bookings.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Тел. консултации
            </a>

            <a href="{{ route('admin.viber-bookings.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.viber-bookings.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Viber консултации
            </a>

            <a href="{{ route('admin.chat-bookings.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.chat-bookings.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Чат консултации
            </a>

            <a href="{{ route('admin.working-hours.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.working-hours.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Работно време
            </a>

            <a href="{{ route('admin.closures.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.closures.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Затворени периоди
            </a>

            <a href="{{ route('admin.inquiries.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.inquiries.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Запитвания
            </a>

            <a href="{{ route('admin.admins.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.admins.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Администратори
            </a>

        </nav>

        {{-- Password --}}
        <div class="px-3 pt-2 pb-1">
            <a href="{{ route('admin.password.edit') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors
                      {{ request()->routeIs('admin.password.*')
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                Смяна на парола
            </a>
        </div>

        {{-- Logout --}}
        <div class="px-3 py-4 border-t border-gray-100">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-sm text-gray-400
                               hover:bg-gray-100 hover:text-gray-900 transition-colors text-left">
                    Изход
                </button>
            </form>
        </div>

    </aside>

    {{-- ── Content area ────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        <main class="flex-1 overflow-y-auto">
            <div class="max-w-5xl mx-auto px-8 py-10">
                @yield('content')
            </div>
        </main>

    </div>

</body>
</html>
