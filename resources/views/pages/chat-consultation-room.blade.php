@extends('layouts.app')

@section('title', 'Чат консултация — ' . match ($state) {
    'early'     => 'Още не е достъпна',
    'waiting'   => 'Чакалня',
    'active'    => 'Активна',
    'completed' => 'Приключена',
    default     => 'Статус',
})

@section('content')

@php
    $dateLabel = $booking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y г.');
    $startTime = $booking->starts_at->setTimezone('Europe/Sofia')->format('H:i');
    $endTime   = $booking->ends_at->setTimezone('Europe/Sofia')->format('H:i');
    $opensTime = $opensAt->setTimezone('Europe/Sofia')->format('H:i');
@endphp

<div class="consultation-page">

    <section class="relative overflow-hidden min-h-screen">

        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ asset('images/consultation/landing_background.webp') }}');"
            role="presentation"
        ></div>

        <div
            class="absolute inset-0 bg-gradient-to-b from-petrova-deep/98 via-petrova-deep/95 to-petrova-main"
            aria-hidden="true"
        ></div>

        <div
            id="chat-room-root"
            class="relative z-10 mx-auto max-w-2xl px-4 pt-20 pb-20 text-center"
            data-chat-room-state="{{ $state }}"
            @if ($statusUrl)
                data-chat-status-url="{{ $statusUrl }}"
                data-chat-poll-enabled="true"
            @endif
        >

            <div class="mb-8">
                <img src="{{ asset('images/logo-gold.svg') }}" alt="Адвокатска кантора Петрова" class="mx-auto h-16">
            </div>

            @if ($state === 'early')
                <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                    Чат консултацията още не е достъпна
                </h1>
                <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                    Достъпът до чакалнята ще бъде активен 10 минути преди началния час.
                </p>

                <div class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5 text-left text-sm mb-10">
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Дата на консултацията</span>
                            <span class="text-petrova-primary">{{ $dateLabel }}</span>
                        </div>
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Начален час</span>
                            <span class="text-petrova-primary">{{ $startTime }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-petrova-gold font-medium">Достъп от</span>
                            <span class="text-petrova-primary">{{ $opensTime }}</span>
                        </div>
                    </div>
                </div>
            @else
                {{-- Pollable states: waiting, active, completed (initial render) --}}
                <div data-chat-panel="waiting" class="{{ $state === 'waiting' ? '' : 'hidden' }}">
                    <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                        Вие сте в чакалнята
                    </h1>
                    <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                        Моля, изчакайте адвокатът да стартира консултацията.
                    </p>
                </div>

                <div data-chat-panel="active" class="{{ $state === 'active' ? '' : 'hidden' }}">
                    <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                        Консултацията е активна
                    </h1>
                    <p class="text-petrova-secondary/80 text-sm sm:text-base mb-4 max-w-lg mx-auto">
                        Адвокатът стартира чат консултацията.
                    </p>
                    <p class="text-petrova-secondary/60 text-sm mb-8 max-w-lg mx-auto">
                        Моля, останете на тази страница.
                    </p>
                </div>

                <div data-chat-panel="completed" class="{{ $state === 'completed' ? '' : 'hidden' }}">
                    <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                        Чат консултацията приключи
                    </h1>
                    <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                        Сесията е приключена и вече не е достъпна за изпращане на съобщения.
                    </p>
                </div>

                <div id="chat-room-details" class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5 text-left text-sm mb-10">
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Дата на консултацията</span>
                            <span class="text-petrova-primary">{{ $dateLabel }}</span>
                        </div>
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Начален час</span>
                            <span class="text-petrova-primary">{{ $startTime }}</span>
                        </div>
                        <div data-chat-detail="end-time" class="flex justify-between border-b border-petrova-gold/10 pb-3 {{ $state === 'early' ? 'hidden' : '' }}">
                            <span class="text-petrova-gold font-medium">Краен час</span>
                            <span class="text-petrova-primary">{{ $endTime }}</span>
                        </div>
                        <div data-chat-detail="waiting-status" class="flex justify-between {{ $state === 'waiting' ? '' : 'hidden' }}">
                            <span class="text-petrova-gold font-medium">Статус</span>
                            <span class="text-amber-300 font-medium">Изчакване</span>
                        </div>
                    </div>
                </div>
            @endif

            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded border border-petrova-gold/40
                      text-petrova-gold text-sm font-semibold hover:bg-petrova-gold/10 transition-colors">
                Към началната страница
            </a>

        </div>
    </section>

</div>

@if ($statusUrl)
@push('scripts')
<script>
(function () {
    var root = document.getElementById('chat-room-root');
    if (!root || root.getAttribute('data-chat-poll-enabled') !== 'true') {
        return;
    }

    var statusUrl = root.getAttribute('data-chat-status-url');
    var pollTimer = null;
    var POLL_MS = 3000;

    function panel(name) {
        return root.querySelector('[data-chat-panel="' + name + '"]');
    }

    function detail(name) {
        return root.querySelector('[data-chat-detail="' + name + '"]');
    }

    function showPanel(name) {
        ['waiting', 'active', 'completed'].forEach(function (state) {
            var el = panel(state);
            if (el) {
                el.classList.toggle('hidden', state !== name);
            }
        });

        var waitingStatus = detail('waiting-status');
        var endTime = detail('end-time');
        if (waitingStatus) {
            waitingStatus.classList.toggle('hidden', name !== 'waiting');
        }
        if (endTime) {
            endTime.classList.remove('hidden');
        }

        root.setAttribute('data-chat-room-state', name);
    }

    function stopPolling() {
        if (pollTimer !== null) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
        root.removeAttribute('data-chat-poll-enabled');
    }

    function resolveDisplayPhase(sessionPhase) {
        if (sessionPhase === 'active' || sessionPhase === 'ending') {
            return 'active';
        }
        if (sessionPhase === 'completed') {
            return 'completed';
        }
        return 'waiting';
    }

    function handleStatus(data) {
        var display = resolveDisplayPhase(data.session_phase);
        showPanel(display);

        if (display === 'completed') {
            stopPolling();
        }
    }

    function pollStatus() {
        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(function (res) {
            if (res.status === 404) {
                stopPolling();
                return null;
            }
            if (!res.ok) {
                return null;
            }
            return res.json();
        })
        .then(function (data) {
            if (data) {
                handleStatus(data);
            }
        })
        .catch(function () {
            /* retry on next interval */
        });
    }

    pollStatus();
    pollTimer = setInterval(pollStatus, POLL_MS);
})();
</script>
@endpush
@endif

@endsection
