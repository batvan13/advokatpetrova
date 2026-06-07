@if ($publishedReviews->isNotEmpty())
<section class="bg-white pb-16 pt-0 lg:pb-20">
    <div class="mx-auto w-full max-w-[1400px] px-6 lg:px-12">

        {{-- Title + CTA --}}
        <div class="mb-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-cormorant text-3xl font-normal italic leading-tight text-[#0D1A30] lg:text-4xl xl:text-[2.5rem]">
                Какво казват за нас
            </h2>

            {{-- CTA: same visual tokens as header "Онлайн консултация" --}}
            <a
                href="{{ route('reviews.create') }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-petrova-gold px-4 py-2.5 text-sm font-semibold text-petrova-deep shadow-sm transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 focus-visible:ring-offset-white"
            >
                <span class="whitespace-nowrap">Добавете отзив</span>
                <svg class="h-3 w-3 shrink-0" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M1 11L11 1M11 1H3M11 1V9" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </a>
        </div>

        {{-- Carousel viewport: overflow-hidden clips the scrollable track --}}
        <div class="overflow-hidden">
            <div
                id="reviews-track"
                class="flex snap-x snap-mandatory gap-5 overflow-x-auto pb-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
                tabindex="0"
                role="region"
                aria-label="Отзиви от клиенти"
            >
                @foreach ($publishedReviews as $review)
                    {{--
                        No isolate. No negative z-index.
                        blockquote + footer carry relative z-10 (stacking tier 7).
                        The decorative quote span carries no z-index (z:auto = tier 6).
                        Tier 6 paints before tier 7 → quote is always behind text, regardless of DOM order.
                        sm:w-[calc(50%_-_10px)] → underscores become spaces in Tailwind JIT → valid CSS.
                    --}}
                    <article class="relative flex min-h-[220px] w-full shrink-0 snap-start flex-col justify-between overflow-hidden rounded bg-[#1E2A3B] p-8 sm:w-[calc(50%_-_10px)] lg:min-h-[240px]">

                        <blockquote class="relative z-10 font-sans text-[0.9375rem] leading-[1.7] text-petrova-primary/95">
                            {{ $review->body }}
                        </blockquote>

                        <footer class="relative z-10 mt-8 font-cormorant text-base italic text-petrova-gold">
                            {{ $review->fullName() }}
                        </footer>

                        {{--
                            Typographic closing double-quote &#8221; (U+201D " RIGHT DOUBLE QUOTATION MARK).
                            Rendered in Cormorant Infant at 5 rem — two comma-like shapes matching the design.
                            z:auto (no explicit z-index) → stacking tier 6, painted before z-10 elements (tier 7).
                            No negative z-index. No isolate wrapper. Clean CSS stacking.
                        --}}
                        <span
                            aria-hidden="true"
                            class="pointer-events-none select-none absolute bottom-2 right-4 font-cormorant text-[5rem] leading-none text-petrova-gold/55"
                        >&#8221;</span>

                    </article>
                @endforeach
            </div>
        </div>

        {{--
            Single navigation row — rendered when count > 1.
            JS hides it entirely when all cards already fit (nothing to scroll).
            JS manages: prev/next disabled state + active dot colour.
        --}}
        @if ($publishedReviews->count() > 1)
        <div class="reviews-nav mt-6 flex items-center justify-between">

            <button
                type="button"
                class="reviews-prev inline-flex h-10 w-10 items-center justify-center bg-petrova-gold text-petrova-deep transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40"
                aria-label="Предишни отзиви"
            >
                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            {{--
                Dots: initial background set via inline style so they are visible before JS loads.
                JS overrides background-color on every state update.
                transition-colors provides a smooth 150 ms fade between active/inactive states.
            --}}
            <div class="reviews-dots flex items-center gap-2" aria-hidden="true">
                @for ($i = 0; $i < $publishedReviews->count(); $i++)
                    <span
                        class="block h-2 w-2 rounded-full transition-colors"
                        style="background-color: {{ $i === 0 ? 'rgb(13,26,48)' : 'rgba(13,26,48,0.2)' }}"
                    ></span>
                @endfor
            </div>

            <button
                type="button"
                class="reviews-next inline-flex h-10 w-10 items-center justify-center bg-petrova-gold text-petrova-deep transition hover:bg-petrova-gold-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40"
                aria-label="Следващи отзиви"
            >
                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

        </div>
        @endif

    </div>
</section>

<script>
(function () {
    function initReviewsCarousel() {
        var track   = document.getElementById('reviews-track');
        if (!track) return;

        var navRow  = document.querySelector('.reviews-nav');
        var prevs   = document.querySelectorAll('.reviews-prev');
        var nexts   = document.querySelectorAll('.reviews-next');
        var dotWrap = document.querySelector('.reviews-dots');

        // Must match gap-5 (20 px) declared on the track
        var GAP = 20;

        function cardW() {
            var c = track.querySelector('article');
            return c ? c.offsetWidth : 0;
        }

        // One scroll step = one card width + the gap
        function step() { return cardW() + GAP; }

        // Index of the card currently snapped to the left edge
        function curIdx() {
            var s = step();
            return s ? Math.round(track.scrollLeft / s) : 0;
        }

        // Maximum index reachable by scrolling (0 when all cards fit)
        function maxIdx() {
            var s = step();
            if (!s) return 0;
            var vis   = Math.round(track.clientWidth / s);
            var total = track.querySelectorAll('article').length;
            return Math.max(0, total - vis);
        }

        var ACTIVE  = 'rgb(13,26,48)';       // #0D1A30 — full opacity
        var PASSIVE = 'rgba(13,26,48,0.20)'; // #0D1A30 — 20 % opacity

        function update() {
            var c   = curIdx();
            var max = maxIdx();

            // Hide entire nav row when nothing to scroll (e.g. desktop with exactly 2 reviews)
            if (navRow) navRow.style.display = (max === 0) ? 'none' : '';

            // Prev arrow: disabled at the first card
            prevs.forEach(function (b) { b.disabled = (c <= 0); });

            // Next arrow: disabled at the last reachable position
            nexts.forEach(function (b) { b.disabled = (c >= max); });

            // Dots: highlight the dot matching the current index
            if (dotWrap) {
                Array.from(dotWrap.children).forEach(function (dot, i) {
                    dot.style.backgroundColor = (i === c) ? ACTIVE : PASSIVE;
                });
            }
        }

        function goTo(n) {
            track.scrollTo({ left: n * step(), behavior: 'smooth' });
        }

        prevs.forEach(function (b) {
            b.addEventListener('click', function () { goTo(Math.max(0, curIdx() - 1)); });
        });
        nexts.forEach(function (b) {
            b.addEventListener('click', function () { goTo(Math.min(maxIdx(), curIdx() + 1)); });
        });

        // Debounced scroll listener — updates dots + arrow states after scroll settles
        var t1;
        track.addEventListener('scroll', function () {
            clearTimeout(t1);
            t1 = setTimeout(update, 50);
        });

        // Debounced resize listener — recalculates visible count + hides nav if no longer needed
        var t2;
        window.addEventListener('resize', function () {
            clearTimeout(t2);
            t2 = setTimeout(update, 100);
        });

        // Set correct initial state on load
        update();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReviewsCarousel);
    } else {
        initReviewsCarousel();
    }
}());
</script>
@endif
