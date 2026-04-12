<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #111827; background: #ffffff; max-width: 580px; margin: 0 auto; padding: 32px 24px;">

    @php
        $typeLabels = [
            'phone' => 'Телефонна консултация',
            'viber' => 'Viber видео консултация',
            'chat'  => 'Чат консултация',
        ];
        $typeLabel = $typeLabels[$type] ?? 'Консултация';

        $startsAt = \Carbon\Carbon::parse($booking->starts_at)->setTimezone('Europe/Sofia');
        $dateFormatted = $startsAt->translatedFormat('d.m.Y');
        $timeFormatted = $startsAt->format('H:i');

        $durationMinutes = isset($booking->duration_minutes)
            ? $booking->duration_minutes
            : (defined(get_class($booking) . '::DURATION_MINUTES') ? $booking::DURATION_MINUTES : 30);

        $priceDisplay = number_format($booking->price_eur, 2) . ' EUR';
        if ($booking->show_bgn_price && $booking->price_bgn) {
            $priceDisplay .= ' / ' . number_format($booking->price_bgn, 2) . ' лв.';
        }
    @endphp

    <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 8px;">Нова заявка — {{ $typeLabel }}</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 0 0 28px;">Получена е нова заявка за консултация.</p>

    {{-- Client data --}}
    <h3 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Клиент</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; width: 130px; vertical-align: top;">Имена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $booking->fullName() }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Имейл</td>
            <td style="padding: 10px 0;">
                <a href="mailto:{{ $booking->email }}" style="color: #111827;">{{ $booking->email }}</a>
            </td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Телефон</td>
            <td style="padding: 10px 0; color: #111827;">{{ $booking->phone }}</td>
        </tr>
    </table>

    {{-- Booking details --}}
    <h3 style="font-size: 14px; font-weight: 600; color: #374151; margin: 24px 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Детайли</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; width: 130px; vertical-align: top;">Дата</td>
            <td style="padding: 10px 0; color: #111827;">{{ $dateFormatted }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Час</td>
            <td style="padding: 10px 0; color: #111827;">{{ $timeFormatted }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Продължителност</td>
            <td style="padding: 10px 0; color: #111827;">{{ $durationMinutes }} минути</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Цена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $priceDisplay }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Плащане</td>
            <td style="padding: 10px 0; color: #111827;">{{ $booking->paymentMethodLabel() }}</td>
        </tr>
        @if (!empty($booking->description))
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Бележка</td>
            <td style="padding: 10px 0; color: #374151; white-space: pre-wrap;">{{ $booking->description }}</td>
        </tr>
        @endif
    </table>

    {{-- Admin link --}}
    <div style="margin-top: 28px;">
        <a href="{{ $adminUrl }}"
           style="display: inline-block; padding: 10px 20px; background: #111827; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500;">
            Отвори в администрацията
        </a>
    </div>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #f3f4f6;">

    <p style="font-size: 12px; color: #9ca3af; margin: 0;">
        Това съобщение е изпратено автоматично от системата на {{ config('app.name') }}.
    </p>

</body>
</html>
