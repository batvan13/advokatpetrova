@php
    $phone   = setting('contact_phone');
    $email   = setting('contact_email');

    // Strip everything except digits and leading + for a valid tel: URI.
    $tel = $phone ? ('tel:' . preg_replace('/[^\d+]/', '', $phone)) : null;

    $variant = $variant ?? 'light';
    $btnClass = $variant === 'dark'
        ? 'inline-flex items-center rounded-lg border border-white/20 px-5 py-2.5 text-sm font-medium text-petrova-primary transition hover:border-white/35 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-petrova-gold/45 focus-visible:ring-offset-2 focus-visible:ring-offset-petrova-deep'
        : 'inline-flex items-center rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white';
@endphp

@if($phone || $email)
    <div class="flex flex-wrap gap-3">

        @if($phone)
            <a href="{{ $tel }}"
               class="{{ $btnClass }}">
                Обади се
            </a>
        @endif

        @if($email)
            <a href="mailto:{{ $email }}"
               class="{{ $btnClass }}">
                Изпрати имейл
            </a>
        @endif

    </div>
@endif
