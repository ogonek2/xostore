<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('shop.name') }}</title>
</head>
<body style="margin:0;padding:24px;font-family:Georgia,serif;background:#f7f6f3;color:#1a1a1a;">
    <div style="max-width:560px;margin:0 auto;background:#fff;padding:32px;border:1px solid #e8e6e1;">
        {!! $body !!}
        <p style="margin-top:32px;font-size:12px;color:#6b6b6b;line-height:1.5;">
            <a href="{{ $unsubscribeUrl }}" style="color:#6b6b6b;">{{ __('mail.newsletter.unsubscribe') }}</a>
        </p>
    </div>
</body>
</html>
