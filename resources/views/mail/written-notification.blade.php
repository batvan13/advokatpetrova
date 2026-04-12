<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #111827; background: #ffffff; max-width: 580px; margin: 0 auto; padding: 32px 24px;">

    @php
        $priceDisplay = number_format($consultationRequest->price_eur, 2) . ' EUR';
        if ($consultationRequest->show_bgn_price && $consultationRequest->price_bgn) {
            $priceDisplay .= ' / ' . number_format($consultationRequest->price_bgn, 2) . ' лв.';
        }

        $attachmentCount = $consultationRequest->attachments->count();
        $submittedAt = \Carbon\Carbon::parse($consultationRequest->submitted_at)
            ->setTimezone('Europe/Sofia')
            ->format('d.m.Y H:i');
    @endphp

    <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 8px;">Нова заявка — Писмена консултация</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 0 0 28px;">Получена е нова заявка за писмена консултация.</p>

    {{-- Client data --}}
    <h3 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Клиент</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; width: 130px; vertical-align: top;">Имена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $consultationRequest->fullName() }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Имейл</td>
            <td style="padding: 10px 0;">
                <a href="mailto:{{ $consultationRequest->email }}" style="color: #111827;">{{ $consultationRequest->email }}</a>
            </td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Телефон</td>
            <td style="padding: 10px 0; color: #111827;">{{ $consultationRequest->phone }}</td>
        </tr>
    </table>

    {{-- Request details --}}
    <h3 style="font-size: 14px; font-weight: 600; color: #374151; margin: 24px 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Заявка</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; width: 130px; vertical-align: top;">Заглавие</td>
            <td style="padding: 10px 0; color: #111827;">{{ $consultationRequest->title }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Подадена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $submittedAt }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Приложени файлове</td>
            <td style="padding: 10px 0; color: #111827;">{{ $attachmentCount }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Цена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $priceDisplay }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Плащане</td>
            <td style="padding: 10px 0; color: #111827;">{{ $consultationRequest->paymentMethodLabel() }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Описание</td>
            <td style="padding: 10px 0; color: #374151; white-space: pre-wrap;">{{ $consultationRequest->description }}</td>
        </tr>
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
