@extends('layouts.admin')

@section('title', 'Чат — #' . $chatBooking->id . ' — Admin')

@section('content')

@php
    $session = $chatBooking->session;
    $phase = $session->phase;
    $displayPhase = match ($phase) {
        'waiting'   => 'waiting',
        'active', 'ending' => 'active',
        'completed' => 'completed',
        default     => 'waiting',
    };
    $dateLabel = $chatBooking->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y');
    $startTime = $chatBooking->starts_at->setTimezone('Europe/Sofia')->format('H:i');
    $endTime = $chatBooking->ends_at->setTimezone('Europe/Sofia')->format('H:i');
    $canStart = ! $chatBooking->archived_at
        && $chatBooking->status === 'confirmed'
        && $phase === 'waiting';
    $canComplete = ! $chatBooking->archived_at
        && $chatBooking->status === 'confirmed'
        && in_array($phase, ['active', 'ending'], true);
    $pollEnabled = in_array($phase, ['waiting', 'active', 'ending'], true);
    $messagesIndexUrl = route('admin.chat-bookings.messages.index', $chatBooking);
    $messagesStoreUrl = route('admin.chat-bookings.messages.store', $chatBooking);
    $phaseBadgeClass = $phase === 'completed'
        ? 'inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700'
        : 'inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-medium text-yellow-700';
@endphp

<div class="mb-8 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-gray-900">Чат консултация #{{ $chatBooking->id }}</h1>
            <span id="admin-chat-phase-badge" class="{{ $phaseBadgeClass }}" data-admin-chat-phase-label>
                {{ $session->phaseLabel() }}
            </span>
        </div>
        <p class="mt-1 text-sm text-gray-500">
            {{ $chatBooking->fullName() }}
            &mdash;
            {{ $dateLabel }}
            {{ $startTime }}–{{ $endTime }}
        </p>
    </div>
    <a href="{{ route('admin.chat-bookings.show', $chatBooking) }}"
       class="text-sm text-gray-400 hover:text-gray-900 transition-colors">
        ← Към записването
    </a>
</div>

@if (session('success'))
    <div class="mb-6 px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-6 px-4 py-3 bg-white border border-red-200 rounded-lg text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

<div
    id="admin-chat-root"
    class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden"
    data-admin-chat-phase="{{ $displayPhase }}"
    @if ($pollEnabled)
        data-admin-messages-url="{{ $messagesIndexUrl }}"
        data-admin-poll-enabled="true"
    @endif
>

    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs font-bold tracking-widest uppercase text-gray-400">Чат стая</p>

        <div class="flex flex-wrap gap-2" data-admin-chat-actions>
            @if ($canStart)
                <form action="{{ route('admin.chat-bookings.start', $chatBooking) }}" method="POST" data-admin-start-form>
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-gray-900 border border-gray-900 rounded hover:bg-gray-800 transition-colors"
                            onclick="return confirm('Стартирай чат консултацията?')">
                        Стартирай чат консултацията
                    </button>
                </form>
            @endif

            @if ($canComplete)
                <form action="{{ route('admin.chat-bookings.complete', $chatBooking) }}" method="POST" data-admin-complete-form>
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:border-gray-500 hover:text-gray-900 transition-colors"
                            onclick="return confirm('Маркирай консултацията като проведена?')">
                        Маркирай като проведена
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div data-admin-panel="waiting" class="px-6 py-4 border-b border-gray-100 text-sm text-gray-600 {{ $displayPhase === 'waiting' ? '' : 'hidden' }}">
        Клиентът е в чакалнята. Стартирайте консултацията, за да започнете чата.
    </div>

    <div data-admin-panel="completed" class="px-6 py-4 border-b border-gray-100 text-sm text-green-700 {{ $displayPhase === 'completed' ? '' : 'hidden' }}">
        Консултацията е приключена. По-долу е пълният препис на разговора.
    </div>

    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Съобщения</h2>
    </div>

    <div
        id="admin-chat-messages"
        class="px-6 py-4 space-y-3 max-h-96 overflow-y-auto text-sm min-h-[12rem]"
        aria-live="polite"
        aria-relevant="additions"
    ></div>

    @if ($pollEnabled)
        <form
            id="admin-chat-send-form"
            action="{{ $messagesStoreUrl }}"
            method="POST"
            class="px-6 py-4 border-t border-gray-100 {{ in_array($displayPhase, ['active'], true) ? '' : 'hidden' }}"
            data-admin-send-form
        >
            @csrf
            <label for="admin-chat-message-input" class="block text-sm font-medium text-gray-700 mb-2">Съобщение до клиента</label>
            <textarea
                id="admin-chat-message-input"
                name="message"
                rows="3"
                maxlength="2000"
                placeholder="Напишете съобщение…"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-400 resize-y min-h-[4.5rem]"
                disabled
            ></textarea>
            <div class="mt-2 flex items-center justify-between gap-3">
                <span id="admin-chat-char-count" class="text-xs text-gray-400">0 / 2000</span>
                <button
                    type="submit"
                    id="admin-chat-send-button"
                    class="inline-flex items-center px-5 py-2 rounded text-sm font-medium text-white bg-gray-900 border border-gray-900 hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                >
                    Изпрати
                </button>
            </div>
        </form>
    @endif

    <div id="admin-chat-error" class="hidden px-6 pb-4 text-sm text-red-600" role="alert"></div>
</div>

@if ($pollEnabled)
@push('scripts')
<script>
(function () {
    var root = document.getElementById('admin-chat-root');
    if (!root || root.getAttribute('data-admin-poll-enabled') !== 'true') {
        return;
    }

    var messagesUrl = root.getAttribute('data-admin-messages-url');
    var messagesContainer = document.getElementById('admin-chat-messages');
    var sendForm = document.getElementById('admin-chat-send-form');
    var messageInput = document.getElementById('admin-chat-message-input');
    var sendButton = document.getElementById('admin-chat-send-button');
    var errorBox = document.getElementById('admin-chat-error');
    var charCount = document.getElementById('admin-chat-char-count');
    var actionsContainer = root.querySelector('[data-admin-chat-actions]');
    var phaseBadge = document.getElementById('admin-chat-phase-badge');

    var pollTimer = null;
    var POLL_MS = 3000;
    var lastMessageId = 0;
    var seenIds = {};
    var sendPending = false;
    var MAX_LENGTH = 2000;
    var finalPollDone = false;
    var lastCanSend = false;

    function panel(name) {
        return root.querySelector('[data-admin-panel="' + name + '"]');
    }

    function showPanel(name) {
        ['waiting', 'completed'].forEach(function (state) {
            var el = panel(state);
            if (el) {
                el.classList.toggle('hidden', state !== name);
            }
        });
        root.setAttribute('data-admin-chat-phase', name);
    }

    function hideStatusPanels() {
        ['waiting', 'completed'].forEach(function (state) {
            var el = panel(state);
            if (el) {
                el.classList.add('hidden');
            }
        });
        root.setAttribute('data-admin-chat-phase', 'active');
    }

    function stopPolling() {
        if (pollTimer !== null) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
        root.removeAttribute('data-admin-poll-enabled');
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

    function phaseBadgeConfig(sessionPhase) {
        var yellowBadge = 'inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-medium text-yellow-700';
        var greenBadge = 'inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700';
        var configs = {
            waiting: { text: 'Изчакване', className: yellowBadge },
            active: { text: 'Активна', className: yellowBadge },
            ending: { text: 'Приключва', className: yellowBadge },
            completed: { text: 'Приключена', className: greenBadge },
        };

        return configs[sessionPhase] || configs.waiting;
    }

    function updatePhaseBadge(sessionPhase) {
        if (!phaseBadge) {
            return;
        }

        var config = phaseBadgeConfig(sessionPhase);
        phaseBadge.textContent = config.text;
        phaseBadge.className = config.className;
    }

    function clearError() {
        if (!errorBox) {
            return;
        }
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }

    function showError(message) {
        if (!errorBox) {
            return;
        }
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function setSendEnabled(enabled) {
        if (!sendForm) {
            return;
        }
        if (enabled) {
            sendForm.classList.remove('hidden');
        } else {
            sendForm.classList.add('hidden');
        }
        if (messageInput) {
            messageInput.disabled = !enabled || sendPending;
        }
        if (sendButton) {
            sendButton.disabled = !enabled || sendPending;
        }
    }

    function updateActionButtons(sessionPhase, bookingStatus) {
        if (!actionsContainer) {
            return;
        }

        var startForm = actionsContainer.querySelector('[data-admin-start-form]');
        var completeForm = actionsContainer.querySelector('[data-admin-complete-form]');

        if (startForm) {
            startForm.classList.toggle('hidden', sessionPhase !== 'waiting' || bookingStatus !== 'confirmed');
        }
        if (completeForm) {
            completeForm.classList.toggle('hidden', !((sessionPhase === 'active' || sessionPhase === 'ending') && bookingStatus === 'confirmed'));
        }
    }

    function scrollMessagesToBottom() {
        if (!messagesContainer) {
            return;
        }
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function createMessageElement(msg) {
        var wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col gap-1';
        wrapper.setAttribute('data-message-id', String(msg.id));

        var isLawyer = msg.sender_type === 'lawyer';
        var label = document.createElement('span');
        label.className = 'text-xs font-medium ' + (isLawyer ? 'text-gray-900' : 'text-gray-500');
        label.textContent = isLawyer ? 'Адвокат' : 'Клиент';

        var bubble = document.createElement('div');
        bubble.className = 'rounded-lg px-3 py-2 whitespace-pre-wrap break-words ' +
            (isLawyer
                ? 'bg-gray-900 text-white max-w-[85%] ml-auto self-end'
                : 'bg-gray-100 text-gray-900 border border-gray-200 max-w-[85%]');

        var body = document.createElement('p');
        body.className = 'text-sm leading-relaxed m-0';
        body.textContent = msg.message;

        bubble.appendChild(body);
        wrapper.appendChild(label);
        wrapper.appendChild(bubble);

        return wrapper;
    }

    function appendMessage(msg) {
        if (!messagesContainer || seenIds[msg.id]) {
            return;
        }

        seenIds[msg.id] = true;
        messagesContainer.appendChild(createMessageElement(msg));

        if (msg.id > lastMessageId) {
            lastMessageId = msg.id;
        }

        scrollMessagesToBottom();
    }

    function handlePollData(data) {
        var display = resolveDisplayPhase(data.session_phase);

        updatePhaseBadge(data.session_phase);

        if (display === 'active') {
            hideStatusPanels();
        } else {
            showPanel(display);
        }

        updateActionButtons(data.session_phase, data.booking_status);

        if (display === 'completed') {
            setSendEnabled(false);
            if (Array.isArray(data.messages)) {
                data.messages.forEach(function (msg) {
                    appendMessage(msg);
                });
            }
            if (!finalPollDone) {
                finalPollDone = true;
                stopPolling();
            }
            return;
        }

        if (display === 'waiting') {
            setSendEnabled(false);
            return;
        }

        lastCanSend = !!data.can_send;
        setSendEnabled(lastCanSend);

        if (Array.isArray(data.messages)) {
            data.messages.forEach(function (msg) {
                appendMessage(msg);
            });
        }
    }

    function pollMessages() {
        fetch(messagesUrl + '?after_id=' + lastMessageId, {
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
                handlePollData(data);
            }
        })
        .catch(function () {
            /* retry on next interval */
        });
    }

    function updateCharCount() {
        if (!charCount || !messageInput) {
            return;
        }
        charCount.textContent = messageInput.value.length + ' / ' + MAX_LENGTH;
    }

    if (messageInput) {
        messageInput.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    if (sendForm) {
        sendForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (sendPending || !messageInput || messageInput.disabled) {
                return;
            }

            var text = messageInput.value.trim();
            if (!text) {
                return;
            }

            var formData = new FormData(sendForm);
            formData.set('message', text);

            sendPending = true;
            setSendEnabled(false);
            clearError();

            fetch(sendForm.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin',
            })
            .then(function (res) {
                return res.json().then(function (body) {
                    return { ok: res.ok, status: res.status, body: body };
                }).catch(function () {
                    return { ok: res.ok, status: res.status, body: null };
                });
            })
            .then(function (result) {
                if (result.ok && result.body) {
                    messageInput.value = '';
                    updateCharCount();
                    appendMessage(result.body);
                    messageInput.focus();
                    return;
                }

                if (result.status === 422 && result.body && result.body.message) {
                    showError(result.body.message);
                    return;
                }

                if (result.status === 422 && result.body && result.body.errors && result.body.errors.message) {
                    var errors = result.body.errors.message;
                    showError(Array.isArray(errors) ? errors[0] : String(errors));
                    return;
                }

                showError('Съобщението не можа да бъде изпратено. Опитайте отново.');
            })
            .catch(function () {
                showError('Временна мрежова грешка. Опитайте отново.');
            })
            .finally(function () {
                sendPending = false;
                setSendEnabled(lastCanSend);
            });
        });
    }

    pollMessages();
    pollTimer = setInterval(pollMessages, POLL_MS);
})();
</script>
@endpush
@elseif ($displayPhase === 'completed')
@push('scripts')
<script>
(function () {
    var messagesUrl = @json($messagesIndexUrl);
    var messagesContainer = document.getElementById('admin-chat-messages');
    var lastMessageId = 0;
    var seenIds = {};

    function createMessageElement(msg) {
        var wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col gap-1';
        wrapper.setAttribute('data-message-id', String(msg.id));

        var isLawyer = msg.sender_type === 'lawyer';
        var label = document.createElement('span');
        label.className = 'text-xs font-medium ' + (isLawyer ? 'text-gray-900' : 'text-gray-500');
        label.textContent = isLawyer ? 'Адвокат' : 'Клиент';

        var bubble = document.createElement('div');
        bubble.className = 'rounded-lg px-3 py-2 whitespace-pre-wrap break-words ' +
            (isLawyer
                ? 'bg-gray-900 text-white max-w-[85%] ml-auto self-end'
                : 'bg-gray-100 text-gray-900 border border-gray-200 max-w-[85%]');

        var body = document.createElement('p');
        body.className = 'text-sm leading-relaxed m-0';
        body.textContent = msg.message;

        bubble.appendChild(body);
        wrapper.appendChild(label);
        wrapper.appendChild(bubble);

        return wrapper;
    }

    function appendMessage(msg) {
        if (!messagesContainer || seenIds[msg.id]) {
            return;
        }
        seenIds[msg.id] = true;
        messagesContainer.appendChild(createMessageElement(msg));
        if (msg.id > lastMessageId) {
            lastMessageId = msg.id;
        }
    }

    fetch(messagesUrl + '?after_id=0', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
    .then(function (res) {
        if (!res.ok) {
            return null;
        }
        return res.json();
    })
    .then(function (data) {
        if (data && Array.isArray(data.messages)) {
            data.messages.forEach(appendMessage);
        }
    })
    .catch(function () {
        /* transcript load failed silently */
    });
})();
</script>
@endpush
@endif

@endsection
