<article class="flex h-full flex-col overflow-hidden rounded-lg border border-petrova-deep/10 bg-white transition-colors duration-200 hover:border-petrova-gold/20">

    @if (filled($post->featured_image))
        <img
            src="{{ asset('storage/'.$post->featured_image) }}"
            alt="{{ $post->title }}"
            class="h-56 w-full object-cover"
            loading="lazy"
        >
    @else
        <div class="h-56 w-full bg-petrova-secondary/10" aria-hidden="true"></div>
    @endif

    <div class="flex flex-1 flex-col p-6">
        @if ($post->published_at)
            <div class="mb-2 text-xs font-medium uppercase tracking-wide text-petrova-secondary/70">
                {{ $post->published_at->format('d.m.Y') }}
            </div>
        @endif

        <h2 class="text-base font-semibold tracking-tight text-petrova-deep">
            <a
                href="{{ route('blog.show', $post->slug) }}"
                class="transition-colors hover:text-petrova-gold-hover focus:outline-none focus-visible:text-petrova-gold-hover focus-visible:ring-2 focus-visible:ring-petrova-gold/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white rounded-sm"
            >
                {{ $post->title }}
            </a>
        </h2>

        @if ($post->excerpt)
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-petrova-secondary">
                {{ $post->excerpt }}
            </p>
        @endif

        <div class="mt-auto pt-4">
            <a
                href="{{ route('blog.show', $post->slug) }}"
                class="inline-flex w-full items-center justify-between rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
            >
                <span>Прочетете повече</span>
                <span class="text-base font-light leading-none" aria-hidden="true">+</span>
            </a>
        </div>
    </div>
</article>
