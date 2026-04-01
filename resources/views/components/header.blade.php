<header class="sticky top-0 z-50 border-b border-gray-200 bg-white">

    {{-- ── Top bar ─────────────────────────────────────────────── --}}
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">

        <a href="{{ route('home') }}" class="group flex flex-col leading-tight">
            <span class="text-xl font-bold tracking-tight text-gray-900">
                {{ setting('site_name', 'Website') }}
            </span>
            @if(setting('site_tagline'))
                <span class="text-xs text-gray-400 font-normal">
                    {{ setting('site_tagline') }}
                </span>
            @endif
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }} transition-colors">
                Начало
            </a>
            <a href="{{ route('about') }}"
               class="{{ request()->routeIs('about') ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }} transition-colors">
                За нас
            </a>
            <a href="{{ route('services') }}"
               class="{{ request()->routeIs('services', 'services.show') ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }} transition-colors">
                Услуги
            </a>
            <a href="{{ route('consultation') }}"
               class="{{ request()->routeIs('consultation') ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }} transition-colors">
                Консултация
            </a>
            <a href="{{ route('blog') }}"
               class="{{ request()->routeIs('blog', 'blog.show') ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }} transition-colors">
                Блог
            </a>
            <a href="{{ route('contacts') }}"
               class="{{ request()->routeIs('contacts') ? 'border-gray-900 text-gray-900' : 'border-gray-300 text-gray-500 hover:border-gray-600 hover:text-gray-900' }} border rounded-full px-3 py-1 transition-colors">
                Контакти
            </a>
        </nav>

        {{-- Hamburger button (mobile only) --}}
        <button id="mobile-menu-toggle"
                class="md:hidden flex items-center justify-center w-9 h-9 rounded text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                aria-label="Меню"
                aria-expanded="false"
                aria-controls="mobile-menu">
            <svg id="icon-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
            <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

    </div>

    {{-- ── Mobile menu panel ───────────────────────────────────── --}}
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white">
        <nav class="mx-auto max-w-6xl px-4 py-3 flex flex-col gap-0.5 text-sm font-medium">
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'text-gray-900 font-semibold bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }} rounded px-3 py-2.5 transition-colors">
                Начало
            </a>
            <a href="{{ route('about') }}"
               class="{{ request()->routeIs('about') ? 'text-gray-900 font-semibold bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }} rounded px-3 py-2.5 transition-colors">
                За нас
            </a>
                <a href="{{ route('services') }}"
                   class="{{ request()->routeIs('services', 'services.show') ? 'text-gray-900 font-semibold bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }} rounded px-3 py-2.5 transition-colors">
                Услуги
            </a>
            <a href="{{ route('consultation') }}"
               class="{{ request()->routeIs('consultation') ? 'text-gray-900 font-semibold bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }} rounded px-3 py-2.5 transition-colors">
                Консултация
            </a>
            <a href="{{ route('blog') }}"
               class="{{ request()->routeIs('blog', 'blog.show') ? 'text-gray-900 font-semibold bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }} rounded px-3 py-2.5 transition-colors">
                Блог
            </a>
            <div class="px-3 py-2">
                <a href="{{ route('contacts') }}"
                   class="{{ request()->routeIs('contacts') ? 'border-gray-900 text-gray-900' : 'border-gray-300 text-gray-500 hover:border-gray-600 hover:text-gray-900' }} inline-block border rounded-full px-3 py-1 transition-colors">
                    Контакти
                </a>
            </div>
        </nav>
    </div>

    <script>
        (function () {
            var btn       = document.getElementById('mobile-menu-toggle');
            var menu      = document.getElementById('mobile-menu');
            var iconOpen  = document.getElementById('icon-open');
            var iconClose = document.getElementById('icon-close');

            btn.addEventListener('click', function () {
                var opening = menu.classList.toggle('hidden') === false;
                iconOpen.classList.toggle('hidden', opening);
                iconClose.classList.toggle('hidden', !opening);
                btn.setAttribute('aria-expanded', String(opening));
            });
        })();
    </script>

</header>
