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
            @if ($messagesIndexUrl)
                data-chat-messages-url="{{ $messagesIndexUrl }}"
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
                </div>

                <div data-chat-panel="completed" class="{{ $state === 'completed' ? '' : 'hidden' }}">
                    <h1 class="font-cormorant text-3xl sm:text-4xl font-bold italic tracking-tight text-petrova-primary mb-4">
                        Чат консултацията приключи
                    </h1>
                    <p class="text-petrova-secondary/80 text-sm sm:text-base mb-8 max-w-lg mx-auto">
                        Сесията е приключена и вече не е достъпна за изпращане на съобщения.
                    </p>
                </div>

                <div id="chat-room-details" class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 px-5 py-5 text-left text-sm mb-6">
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Дата на консултацията</span>
                            <span class="text-petrova-primary">{{ $dateLabel }}</span>
                        </div>
                        <div class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Начален час</span>
                            <span class="text-petrova-primary">{{ $startTime }}</span>
                        </div>
                        <div data-chat-detail="end-time" class="flex justify-between border-b border-petrova-gold/10 pb-3">
                            <span class="text-petrova-gold font-medium">Краен час</span>
                            <span class="text-petrova-primary">{{ $endTime }}</span>
                        </div>
                        <div data-chat-detail="waiting-status" class="flex justify-between {{ $state === 'waiting' ? '' : 'hidden' }}">
                            <span class="text-petrova-gold font-medium">Статус</span>
                            <span class="text-amber-300 font-medium">Изчакване</span>
                        </div>
                    </div>
                </div>

                {{-- Chat interface (hidden in waiting until session becomes active) --}}
                <div
                    id="chat-interface"
                    class="rounded-xl border border-petrova-gold/20 bg-petrova-deep/60 text-left mb-8 {{ $state === 'active' ? '' : 'hidden' }}"
                    data-chat-interface
                >
                    <div class="px-5 py-4 border-b border-petrova-gold/10">
                        <h2 class="text-petrova-gold font-medium text-sm">Съобщения</h2>
                    </div>

                    <div
                        id="chat-messages"
                        class="px-5 py-4 space-y-3 max-h-80 overflow-y-auto text-sm"
                        aria-live="polite"
                        aria-relevant="additions"
                    ></div>

                    @if ($messagesStoreUrl)
                        <form
                            id="chat-send-form"
                            action="{{ $messagesStoreUrl }}"
                            method="POST"
                            class="px-5 py-4 border-t border-petrova-gold/10 {{ $state === 'active' ? '' : 'hidden' }}"
                            data-chat-send-form
                        >
                            @csrf
                            <label for="chat-message-input" class="sr-only">Съобщение</label>
                            <textarea
                                id="chat-message-input"
                                name="message"
                                rows="3"
                                maxlength="2000"
                                placeholder="Напишете съобщение…"
                                class="w-full rounded-lg border border-petrova-gold/20 bg-petrova-deep/80 px-3 py-2 text-sm text-white placeholder:text-gray-400 focus:border-petrova-gold focus:ring-2 focus:ring-petrova-gold/40 focus:outline-none resize-y min-h-[4.5rem]"
                                {{ $state === 'active' ? '' : 'disabled' }}
                            ></textarea>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <span id="chat-char-count" class="text-xs text-petrova-secondary/60">0 / 2000</span>
                                <button
                                    type="submit"
                                    id="chat-send-button"
                                    class="inline-flex items-center px-5 py-2 rounded border border-petrova-gold/40 text-petrova-gold text-sm font-semibold hover:bg-petrova-gold/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ $state === 'active' ? '' : 'disabled' }}
                                >
                                    Изпрати
                                </button>
                            </div>
                        </form>
                    @endif

                    <div id="chat-error" class="hidden px-5 pb-4 text-sm text-red-300" role="alert"></div>
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

@if ($messagesIndexUrl && $messagesStoreUrl)
@push('scripts')
<script>
(function () {
    var root = document.getElementById('chat-room-root');
    if (!root || root.getAttribute('data-chat-poll-enabled') !== 'true') {
        return;
    }

    var messagesUrl = root.getAttribute('data-chat-messages-url');
    var chatInterface = document.getElementById('chat-interface');
    var messagesContainer = document.getElementById('chat-messages');
    var sendForm = document.getElementById('chat-send-form');
    var messageInput = document.getElementById('chat-message-input');
    var sendButton = document.getElementById('chat-send-button');
    var errorBox = document.getElementById('chat-error');
    var charCount = document.getElementById('chat-char-count');

    var pollTimer = null;
    var POLL_MS = 3000;
    var lastMessageId = 0;
    var seenIds = {};
    var sendPending = false;
    var MAX_LENGTH = 2000;
    var lastCanSend = {{ $state === 'active' ? 'true' : 'false' }};

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
        if (waitingStatus) {
            waitingStatus.classList.toggle('hidden', name !== 'waiting');
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

    function clearMessages() {
        if (messagesContainer) {
            messagesContainer.textContent = '';
        }
        lastMessageId = 0;
        seenIds = {};
    }

    function hideChatInterface() {
        if (chatInterface) {
            chatInterface.classList.add('hidden');
        }
        if (sendForm) {
            sendForm.classList.add('hidden');
        }
        setSendEnabled(false);
    }

    function showChatInterface() {
        if (chatInterface) {
            chatInterface.classList.remove('hidden');
        }
        if (sendForm) {
            sendForm.classList.remove('hidden');
        }
    }

    function setSendEnabled(enabled) {
        if (messageInput) {
            messageInput.disabled = !enabled || sendPending;
        }
        if (sendButton) {
            sendButton.disabled = !enabled || sendPending;
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

        var isClient = msg.sender_type === 'client';
        var label = document.createElement('span');
        label.className = 'text-xs font-medium ' + (isClient ? 'text-petrova-gold' : 'text-petrova-secondary/70');
        label.textContent = isClient ? 'Вие' : 'Адвокат';

        var bubble = document.createElement('div');
        bubble.className = 'rounded-lg px-3 py-2 whitespace-pre-wrap break-words ' +
            (isClient
                ? 'bg-petrova-gold/15 text-petrova-primary self-end max-w-[85%] ml-auto'
                : 'bg-petrova-deep/80 border border-petrova-gold/10 text-petrova-primary max-w-[85%]');

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
        showPanel(display);

        if (display === 'completed') {
            clearMessages();
            hideChatInterface();
            stopPolling();
            return;
        }

        if (display === 'waiting') {
            hideChatInterface();
            clearMessages();
            return;
        }

        showChatInterface();
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
@endif

@endsection
