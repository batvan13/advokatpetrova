<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #111827; background: #ffffff; max-width: 580px; margin: 0 auto; padding: 32px 24px;">

    @php
        $req          = $payable;
        $priceDisplay = number_format($req->price_eur, 2) . ' EUR';
        if ($req->show_bgn_price && $req->price_bgn) {
            $priceDisplay .= ' / ' . number_format($req->price_bgn, 2) . ' лв.';
        }
        $attachmentCount = $req->relationLoaded('attachments') ? $req->attachments->count() : 0;
    @endphp

    {{-- Payment confirmed banner --}}
    <div style="margin-bottom: 24px; padding: 14px 16px; background: #f0fdf4; border-left: 3px solid #16a34a; border-radius: 4px; font-size: 14px; color: #166534;">
        <strong>Плащането е потвърдено.</strong> Вашата заявка за писмена консултация е приета за обработка.
    </div>

    <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 8px;">Писмена консултация</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 0 0 28px;">Заявката Ви е приета и ще бъде разгледана от адвокат.</p>

    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="padding: 10px 0; color: #6b7280; width: 130px; vertical-align: top;">Заглавие</td>
            <td style="padding: 10px 0; color: #111827;">{{ $req->title }}</td>
        </tr>
        @if ($attachmentCount > 0)
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Приложени файлове</td>
            <td style="padding: 10px 0; color: #111827;">{{ $attachmentCount }}</td>
        </tr>
        @endif
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Цена</td>
            <td style="padding: 10px 0; color: #111827;">{{ $priceDisplay }}</td>
        </tr>
        <tr style="border-top: 1px solid #f3f4f6;">
            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;">Плащане</td>
            <td style="padding: 10px 0; color: #111827;">{{ $req->paymentMethodLabel() }}</td>
        </tr>
    </table>

    <div style="margin-top: 24px; padding: 16px; background: #f9fafb; border-radius: 8px; font-size: 14px; line-height: 1.7; color: #374151;">
        <p style="margin: 0 0 8px; font-weight: 600;">Следващи стъпки</p>
        <p style="margin: 0;">
            Ще получите отговор в рамките на <strong>48 часа</strong>.
            Отговорът ще бъде изпратен на <strong>{{ $req->email }}</strong>.
        </p>
    </div>

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
