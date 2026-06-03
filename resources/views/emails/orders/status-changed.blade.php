<!DOCTYPE html>
<html lang="{{ $order->locale ?: app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $order->number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <div style="white-space: pre-wrap;">{{ $body }}</div>

    <p style="margin-top: 24px; color: #6b7280; font-size: 14px;">
        {{ __('mail.order.common.contact_email') }}: {{ $contactEmail }}<br>
        {{ __('mail.order.common.contact_phone') }}: {{ $contactPhone }}
    </p>
</body>
</html>
