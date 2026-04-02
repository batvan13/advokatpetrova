<header class="sticky top-0 z-50 border-b border-white/10 bg-petrova-main font-playfair">

    {{-- ── Top bar (mobile: logo + hamburger | desktop: 3-zone grid) ── --}}
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 md:grid md:grid-cols-[1fr_auto_1fr] md:items-center">

        <a href="{{ route('home') }}" class="group flex min-w-0 flex-col leading-tight justify-self-start">
            <span class="text-xl font-bold tracking-tight text-petrova-primary">
                {{ setting('site_name', 'Website') }}
            </span>
            @if(setting('site_tagline'))
                <span class="text-xs font-normal text-petrova-secondary">
                    {{ setting('site_tagline') }}
                </span>
            @endif
        </a>

        {{-- Desktop: centered nav (no consultation here) --}}
        <nav class="hidden min-w-0 items-center justify-center gap-6 text-sm font-medium md:flex">
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'font-semibold text-petrova-primary' : 'text-petrova-secondary transition-colors hover:text-petrova-primary' }}">
                Начало
            </a>
            <a href="{{ route('about') }}"
               class="{{ request()->routeIs('about') ? 'font-semibold text-petrova-primary' : 'text-petrova-secondary transition-colors hover:text-petrova-primary' }}">
                За нас
            </a>
            <a href="{{ route('services') }}"
               class="{{ request()->routeIs('services', 'services.show') ? 'font-semibold text-petrova-primary' : 'text-petrova-secondary transition-colors hover:text-petrova-primary' }}">
                Услуги
            </a>
            <a href="{{ route('blog') }}"
               class="{{ request()->routeIs('blog', 'blog.show') ? 'font-semibold text-petrova-primary' : 'text-petrova-secondary transition-colors hover:text-petrova-primary' }}">
                Блог
            </a>
            <a href="{{ route('contacts') }}"
               class="{{ request()->routeIs('contacts') ? 'font-semibold text-petrova-primary' : 'text-petrova-secondary transition-colors hover:text-petrova-primary' }}">
                Контакти
            </a>
        </nav>

        <div class="flex shrink-0 items-center justify-end justify-self-end gap-3">
            <a href="{{ route('consultation') }}"
               class="{{ request()->routeIs('consultation')
                    ? 'bg-petrova-gold-hover ring-2 ring-petrova-primary/50 ring-offset-2 ring-offset-petrova-main'
                    : 'bg-petrova-gold hover:bg-petrova-gold-hover' }}
                    hidden rounded-lg px-4 py-2.5 text-sm font-semibold text-petrova-deep shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main md:inline-flex md:items-center md:justify-center">
                Онлайн консултация
            </a>

            <button id="mobile-menu-toggle"
                    class="flex h-10 w-10 items-center justify-center rounded-lg text-petrova-secondary transition-colors hover:bg-white/10 hover:text-petrova-primary md:hidden"
                    aria-label="Меню"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    type="button">
                <svg id="icon-open" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
                <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="hidden h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    </div>

    {{-- ── Mobile menu panel ───────────────────────────────────── --}}
    <div id="mobile-menu" class="hidden border-t border-white/10 bg-petrova-main md:hidden">
        <nav class="mx-auto flex max-w-6xl flex-col gap-0.5 px-4 py-3 text-sm font-medium">
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'bg-white/10 font-semibold text-petrova-primary' : 'text-petrova-secondary hover:bg-white/5 hover:text-petrova-primary' }} rounded-lg px-3 py-2.5 transition-colors">
                Начало
            </a>
            <a href="{{ route('about') }}"
               class="{{ request()->routeIs('about') ? 'bg-white/10 font-semibold text-petrova-primary' : 'text-petrova-secondary hover:bg-white/5 hover:text-petrova-primary' }} rounded-lg px-3 py-2.5 transition-colors">
                За нас
            </a>
            <a href="{{ route('services') }}"
               class="{{ request()->routeIs('services', 'services.show') ? 'bg-white/10 font-semibold text-petrova-primary' : 'text-petrova-secondary hover:bg-white/5 hover:text-petrova-primary' }} rounded-lg px-3 py-2.5 transition-colors">
                Услуги
            </a>
            <a href="{{ route('blog') }}"
               class="{{ request()->routeIs('blog', 'blog.show') ? 'bg-white/10 font-semibold text-petrova-primary' : 'text-petrova-secondary hover:bg-white/5 hover:text-petrova-primary' }} rounded-lg px-3 py-2.5 transition-colors">
                Блог
            </a>
            <a href="{{ route('contacts') }}"
               class="{{ request()->routeIs('contacts') ? 'bg-white/10 font-semibold text-petrova-primary' : 'text-petrova-secondary hover:bg-white/5 hover:text-petrova-primary' }} rounded-lg px-3 py-2.5 transition-colors">
                Контакти
            </a>
            <a href="{{ route('consultation') }}"
               class="{{ request()->routeIs('consultation')
                    ? 'bg-petrova-gold-hover text-petrova-deep ring-2 ring-petrova-primary/50 ring-offset-2 ring-offset-petrova-main'
                    : 'bg-petrova-gold text-petrova-deep hover:bg-petrova-gold-hover' }}
                    mx-0 mt-1 rounded-lg px-3 py-2.5 text-center font-semibold shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main">
                Онлайн консултация
            </a>
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
