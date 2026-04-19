@php
    $description = $service->short_description;
    if (blank($description) && filled($service->full_description)) {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($service->full_description)));
        $description = \Illuminate\Support\Str::limit($plain, 200, '…');
    }
@endphp

<div class="overflow-hidden rounded-lg border border-white/10 bg-petrova-deep/30 transition-colors duration-200 hover:border-petrova-gold/20">

    @if(filled($service->image))
        <div class="aspect-[16/10] w-full overflow-hidden bg-petrova-deep/50">
            <img
                src="{{ upload_url($service->image) }}"
                alt="{{ $service->title }}"
                class="h-full w-full object-cover"
                loading="lazy"
            >
        </div>
    @endif

    <div class="p-6">
    @if($service->icon)
        <p class="mb-3 text-xs font-mono tracking-wide text-petrova-gold/75">{{ $service->icon }}</p>
    @endif

    <h3 class="text-base font-semibold text-petrova-primary">
        <a href="{{ route('services.show', $service->slug) }}"
           class="transition-colors hover:text-petrova-gold-hover focus:outline-none focus-visible:text-petrova-gold-hover focus-visible:ring-2 focus-visible:ring-petrova-gold/40 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-main rounded-sm">
            {{ $service->title }}
        </a>
    </h3>

    @if(filled($description))
        <p class="mt-2 text-sm leading-relaxed text-petrova-secondary">
            {{ $description }}
        </p>
    @endif

    </div>

</div>
