@php
    use App\Models\ChatConsultationBooking;
    use App\Models\PhoneConsultationBooking;
    use App\Models\ViberConsultationBooking;
    use App\Models\WrittenConsultationRequest;

    $payable = $payment->payable;

    $typeLabel = match(true) {
        $payable instanceof PhoneConsultationBooking   => 'Телефонна консултация',
        $payable instanceof ViberConsultationBooking   => 'Viber видео консултация',
        $payable instanceof ChatConsultationBooking    => 'Чат консултация',
        $payable instanceof WrittenConsultationRequest => 'Писмена консултация',
        default                                        => 'Консултация',
    };

    $isSlotBased = ! ($payable instanceof WrittenConsultationRequest);
@endphp
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Симулация на плащане — {{ $payment->invoice_number }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            max-width: 480px;
            width: 100%;
            padding: 32px;
        }
        .badge {
            display: inline-block;
            font-size: 10px;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 999px;
            font-weight: 600;
            margin-bottom: 20px;
            background: #854d0e;
            color: #fef9c3;
        }
        .bank-logo { font-size: 13px; color: #94a3b8; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 6px; }
        h1 { font-size: 20px; font-weight: 700; color: #f1f5f9; margin-bottom: 24px; }
        hr { border: none; border-top: 1px solid #334155; margin: 20px 0; }
        .row { display: flex; justify-content: space-between; align-items: baseline; padding: 6px 0; font-size: 14px; }
        .row .label { color: #94a3b8; }
        .row .value { color: #f1f5f9; font-weight: 500; text-align: right; max-width: 60%; word-break: break-all; }
        .amount { font-size: 28px; font-weight: 800; color: #f8fafc; text-align: center; padding: 16px 0; }
        .currency { font-size: 16px; color: #94a3b8; }
        .status-pending { color: #f59e0b; }
        .status-paid    { color: #22c55e; }
        .status-failed  { color: #ef4444; }
        .status-expired { color: #94a3b8; }
        .actions { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
        .btn {
            display: block; width: 100%; padding: 12px;
            border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; transition: opacity .15s;
        }
        .btn:hover { opacity: .85; }
        .btn-paid    { background: #16a34a; color: #fff; }
        .btn-denied  { background: #dc2626; color: #fff; }
        .btn-expired { background: #475569; color: #cbd5e1; }
        .finalized-notice {
            background: #0f172a; border: 1px solid #334155;
            border-radius: 8px; padding: 14px;
            font-size: 13px; color: #94a3b8;
            text-align: center; margin-top: 16px;
        }
        .back-link { display: block; margin-top: 20px; text-align: center; font-size: 13px; color: #64748b; text-decoration: none; }
        .back-link:hover { color: #94a3b8; }
    </style>
</head>
<body>

<div class="card">

    <span class="badge">Тестова среда — Симулация на плащане</span>

    <div class="bank-logo">FakeEpay Terminal</div>
    <h1>Плащане {{ $payment->invoice_number }}</h1>

    <div class="amount">
        {{ number_format((float) $payment->amount, 2, ',', '.') }}
        <span class="currency">{{ $payment->currency }}</span>
    </div>

    <hr>

    <div class="row"><span class="label">Фактура №</span><span class="value">{{ $payment->invoice_number }}</span></div>
    <div class="row"><span class="label">Метод на плащане</span><span class="value">{{ $payable?->paymentMethodLabel() ?? $payment->payment_method }}</span></div>
    <div class="row"><span class="label">Тип услуга</span><span class="value">{{ $typeLabel }}</span></div>

    @if ($payable)
        <div class="row"><span class="label">Клиент</span><span class="value">{{ $payable->first_name }} {{ $payable->last_name }}</span></div>

        @if ($isSlotBased)
            <div class="row">
                <span class="label">Дата и час</span>
                <span class="value">{{ $payable->starts_at->setTimezone('Europe/Sofia')->format('d.m.Y, H:i') }}</span>
            </div>
        @else
            <div class="row"><span class="label">Заглавие</span><span class="value">{{ $payable->title }}</span></div>
        @endif
    @endif

    <div class="row">
        <span class="label">Изтича</span>
        <span class="value">{{ $payment->expires_at?->setTimezone('Europe/Sofia')->format('H:i, d.m.Y') ?? '—' }}</span>
    </div>
    <div class="row">
        <span class="label">Статус</span>
        <span class="value status-{{ $payment->status }}">{{ $payment->statusLabel() }}</span>
    </div>

    <hr>

    @if (! $payment->is_finalized)
        <div class="actions">
            <form method="POST" action="{{ route('payment.notify') }}">
                @csrf
                <input type="hidden" name="invoice_number"  value="{{ $payment->invoice_number }}">
                <input type="hidden" name="provider_status" value="PAID">
                <button type="submit" class="btn btn-paid">✓ Симулирай PAID — плащането е успешно</button>
            </form>
            <form method="POST" action="{{ route('payment.notify') }}">
                @csrf
                <input type="hidden" name="invoice_number"  value="{{ $payment->invoice_number }}">
                <input type="hidden" name="provider_status" value="DENIED">
                <button type="submit" class="btn btn-denied">✕ Симулирай DENIED — плащането е отказано</button>
            </form>
            <form method="POST" action="{{ route('payment.notify') }}">
                @csrf
                <input type="hidden" name="invoice_number"  value="{{ $payment->invoice_number }}">
                <input type="hidden" name="provider_status" value="EXPIRED">
                <button type="submit" class="btn btn-expired">⌛ Симулирай EXPIRED — времето е изтекло</button>
            </form>
        </div>
    @else
        <div class="finalized-notice">
            Плащането е финализирано: <strong style="color:#f1f5f9">{{ $payment->statusLabel() }}</strong>.<br>
            Повторна симулация не е възможна.
        </div>
    @endif

    <a href="{{ url()->previous(route('home')) }}" class="back-link">← Назад</a>

</div>

</body>
</html>
