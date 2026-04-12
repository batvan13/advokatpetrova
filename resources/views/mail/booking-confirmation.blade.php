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

    <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 8px;">{{ $typeLabel }}</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 0 0 28px;">Вашата заявка е получена успешно.</p>

    {{-- Booking details --}}
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
    </table>

    {{-- Type-specific instructions --}}
    <div style="margin-top: 24px; padding: 16px; background: #f9fafb; border-radius: 8px; font-size: 14px; line-height: 1.7; color: #374151;">
        @if ($type === 'phone')
            <p style="margin: 0 0 8px; font-weight: 600;">Как протича консултацията?</p>
            <p style="margin: 0;">
                Вие се обаждате в избрания час на нашия телефонен номер:
                <strong>{{ $contactNumber ?? '—' }}</strong>.
            </p>
            <p style="margin: 8px 0 0;">
                Моля, уверете се, че сте на достъпно място и разполагате с достатъчно време.
            </p>
        @elseif ($type === 'viber')
            <p style="margin: 0 0 8px; font-weight: 600;">Как протича консултацията?</p>
            <p style="margin: 0;">
                Свържете се с нас чрез <strong>Viber</strong> в избрания час на номер:
                <strong>{{ $contactNumber ?? '—' }}</strong>.
            </p>
            <p style="margin: 8px 0 0;">
                Уверете се, че имате инсталирано приложението Viber и стабилна интернет връзка.
            </p>
        @elseif ($type === 'chat')
            <p style="margin: 0 0 8px; font-weight: 600;">Важна информация</p>
            <p style="margin: 0;">
                Достъпът до чат стаята ще бъде предоставен след потвърждение на плащането.
                Ще получите допълнително съобщение с инструкции.
            </p>
        @endif
    </div>

    {{-- Payment notice --}}
    <div style="margin-top: 16px; padding: 14px 16px; background: #fffbeb; border-left: 3px solid #f59e0b; border-radius: 4px; font-size: 13px; color: #92400e; line-height: 1.6;">
        Заявката Ви е регистрирана. Ще бъдете свързани за потвърждение и инструкции за плащане.
    </div>

    {{-- Success page link --}}
    <p style="margin-top: 24px; font-size: 14px; color: #374151;">
        Можете да прегледате детайлите на заявката си по всяко време:
    </p>
    <p style="margin: 8px 0 0;">
        <a href="{{ $successUrl }}" style="color: #1d4ed8; text-decoration: underline; font-size: 14px; word-break: break-all;">{{ $successUrl }}</a>
    </p>

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
