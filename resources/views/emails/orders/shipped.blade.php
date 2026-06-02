<!DOCTYPE html>
<html lang="{{ $order->locale ?: app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('mail.order_shipped.title') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="margin-bottom: 12px;">{{ __('mail.order_shipped.title') }}</h1>
    <p>{{ __('mail.order_shipped.intro') }}</p>

    <h2 style="margin-top: 24px;">{{ __('mail.order.common.order_number') }}: {{ $order->number }}</h2>

    <h3 style="margin-top: 20px;">{{ __('mail.order.common.products') }}</h3>
    <ul style="padding-left: 18px;">
        @foreach ($order->items as $item)
            <li>
                {{ $item->product_name }}
                @if ($item->variant_label)
                    ({{ $item->variant_label }})
                @endif
                — {{ (int) $item->quantity }} x {{ number_format((float) $item->unit_price, 2, ',', ' ') }}
            </li>
        @endforeach
    </ul>

    <p><strong>{{ __('mail.order.common.total') }}:</strong> {{ $formattedTotal }}</p>

    <h3 style="margin-top: 20px;">{{ __('mail.order.common.contact_info') }}</h3>
    <p style="margin: 0;">{{ __('mail.order.common.contact_email') }}: {{ $contactEmail }}</p>
    <p style="margin: 0;">{{ __('mail.order.common.contact_phone') }}: {{ $contactPhone }}</p>
</body>
</html>
