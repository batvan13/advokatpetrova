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

        $booking     = $payable;
        $startsAt    = \Carbon\Carbon::parse($booking->starts_at)->setTimezone('Europe/Sofia');
        $durationMin = isset($booking->duration_minutes)
            ? $booking->duration_minutes
            : (defined(get_class($booking) . '::DURATION_MINUTES') ? $booking::DURATION_MINUTES : 30);

        $priceDisplay = number_format($booking->price_eur, 2) . ' EUR';
        if ($booking->show_bgn_price && $booking->price_bgn) {
            $priceDisplay .= ' / ' . number_format($booking->price_bgn, 2) . ' лв.';
        }
    @endphp

    {{-- Payment confirmed banner --}}
    <div style="margin-bottom: 24px; padding: 14px 16px; background: #f0fdf4; border-left: 3px solid #16a34a; border-radius: 4px; font-size: 14px; color: #166534;">
        <strong>Плащането е потвърдено.</strong> Вашата консултация е резервирана успешно.
    </div>

    <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 8px;">{{ $typeLabel }}</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 0 0 28px;">Детайли на потвърдената консултация.</p>

    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; width: 130px; vertical-align: top;">Дата</td>
            <td style="padding: 10px 0; color: #111827;">{{ $startsAt->format('d.m.Y') }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Час</td>
            <td style="padding: 10px 0; color: #111827;">{{ $startsAt->format('H:i') }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Продължителност</td>
            <td style="padding: 10px 0; color: #111827;">{{ $durationMin }} минути</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Цена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $priceDisplay }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Плащане</td>
            <td style="padding: 10px 0; color: #111827;">{{ $booking->paymentMethodLabel() }}</td>
        </tr>
    </table>

    {{-- Type-specific instructions --}}
    <div style="margin-top: 24px; padding: 16px; background: #f9fafb; border-radius: 8px; font-size: 14px; line-height: 1.7; color: #374151;">
        @if ($type === 'phone')
            <p style="margin: 0 0 8px; font-weight: 600;">Как протича консултацията?</p>
            <p style="margin: 0;">
                Вие се обаждате в избрания час на нашия телефонен номер:
                <strong>{{ $contactNumber ?? '—' }}</strong>.
            </p>
        @elseif ($type === 'viber')
            <p style="margin: 0 0 8px; font-weight: 600;">Как протича консултацията?</p>
            <p style="margin: 0;">
                Свържете се с нас чрез <strong>Viber</strong> в избрания час на номер:
                <strong>{{ $contactNumber ?? '—' }}</strong>.
            </p>
        @elseif ($type === 'chat')
            <p style="margin: 0 0 8px; font-weight: 600;">Важна информация</p>
            <p style="margin: 0;">
                Достъпът до чат стаята ще бъде предоставен преди началото на консултацията.
                Ще получите допълнителни инструкции по имейл.
            </p>
        @endif
    </div>

    {{-- Success page link --}}
    @if ($successUrl)
        <p style="margin-top: 24px; font-size: 14px; color: #374151;">
            Можете да проследите статуса на заявката:
        </p>
        <p style="margin: 8px 0 0;">
            <a href="{{ $successUrl }}" style="color: #1d4ed8; text-decoration: underline; font-size: 14px; word-break: break-all;">{{ $successUrl }}</a>
        </p>
    @endif

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #f3f4f6;">

    <p style="font-size: 12px; color: #9ca3af; margin: 0;">
        Това съобщение е изпратено автоматично от системата на {{ config('app.name') }}.
        @if($contactEmail)
            За въпроси се свържете с нас на
            <a href="mailto:{{ $contactEmail }}" style="color: #9ca3af;">{{ $contactEmail }}</a>.
        @endif
    </p>

</body>
</html>
