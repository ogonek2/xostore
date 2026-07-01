<?php

namespace App\Support\Telegram;

use App\Filament\Resources\Consultations\ConsultationRequestResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\ConsultationRequest;
use App\Models\Order;

final class TelegramNotifier
{
    public function __construct(
        protected TelegramBotClient $client,
    ) {}

    public function isEnabled(): bool
    {
        if (! (bool) config('shop.telegram.enabled', false)) {
            return false;
        }

        return filled(config('shop.telegram.bot_token'))
            && filled(config('shop.telegram.chat_id'));
    }

    public function notifyOrder(Order $order): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $order->loadMissing(['items', 'paymentMethod', 'orderStatus']);

        $locale = $order->locale ?: (string) config('shop.default_language', 'pl');
        $lines = [
            '🛒 <b>Новый заказ '.TelegramHtml::escape($order->number).'</b>',
            '',
            '👤 <b>Клиент</b>',
            TelegramHtml::line('Имя', $order->displayName()),
            TelegramHtml::line('Email', $order->email),
            TelegramHtml::line('Телефон', $order->phone),
            TelegramHtml::line('Адрес', $order->displayAddress()),
            TelegramHtml::line('Город', $order->city),
            TelegramHtml::line('Страна', $order->country),
        ];

        if (filled($order->notes)) {
            $lines[] = '';
            $lines[] = '📝 <b>Комментарий</b>';
            $lines[] = TelegramHtml::escape($order->notes);
        }

        $lines[] = '';
        $lines[] = '📦 <b>Товары</b>';

        foreach ($order->items as $item) {
            $label = $item->product_name;

            if (filled($item->variant_label)) {
                $label .= ' ('.$item->variant_label.')';
            }

            $lines[] = '• '.TelegramHtml::escape($label)
                .' × '.(int) $item->quantity
                .' — '.$this->formatMoney($item->total_price);
        }

        $lines[] = '';
        $lines[] = '💳 <b>Оплата</b>';
        $lines[] = TelegramHtml::line('Способ', $order->paymentMethod?->label($locale) ?? $order->paymentMethod?->code);
        $lines[] = TelegramHtml::line('Статус', $order->statusLabel('ru'));

        $lines[] = '';
        $lines[] = '📊 <b>Сумма</b>';
        $lines[] = TelegramHtml::line('Подытог', $this->formatMoneyPlain($order->subtotal));
        $lines[] = TelegramHtml::line(
            'Доставка',
            (float) $order->shipping > 0 ? $this->formatMoneyPlain($order->shipping) : 'бесплатно',
        );
        $lines[] = '<b>Итого: '.TelegramHtml::escape($this->formatMoneyPlain($order->total)).'</b>';

        if ($order->placed_at) {
            $lines[] = '';
            $lines[] = '🕐 '.TelegramHtml::escape($order->placed_at->format('d.m.Y H:i'));
        }

        $adminPath = OrderResource::getUrl('edit', ['record' => $order]);
        $adminUrl = TelegramAdminLinks::resolve($adminPath);

        if ($adminUrl === null) {
            $lines[] = '';
            $lines[] = '🔗 <b>Админка:</b> '.TelegramHtml::escape($adminPath);
        }

        $this->client->sendMessage(
            (string) config('shop.telegram.chat_id'),
            implode("\n", $lines),
            $adminUrl
                ? $this->client->inlineUrlButton('Открыть заказ в админке', $adminUrl)
                : null,
        );
    }

    public function notifyConsultation(ConsultationRequest $request): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $request->loadMissing(['product.translates']);

        $productName = $request->product
            ? ($request->product->translate('name', $request->locale) ?? $request->product->sku)
            : null;

        $lines = [
            '💬 <b>Заявка на консультацию</b>',
            '',
            TelegramHtml::line('Имя', $request->name),
            TelegramHtml::line('Email', $request->email),
            TelegramHtml::line('Телефон', $request->phone),
            TelegramHtml::line('Язык', strtoupper((string) $request->locale)),
        ];

        if ($productName) {
            $lines[] = TelegramHtml::line('Товар', $productName);
        }

        if ($request->preferred_at) {
            $lines[] = TelegramHtml::line('Удобное время', $request->preferred_at->format('d.m.Y H:i'));
        }

        $lines[] = '';
        $lines[] = '💬 <b>Сообщение</b>';
        $lines[] = TelegramHtml::escape($request->message);
        $lines[] = '';
        $lines[] = '🕐 '.TelegramHtml::escape($request->created_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i'));

        $adminPath = ConsultationRequestResource::getUrl('edit', ['record' => $request]);
        $adminUrl = TelegramAdminLinks::resolve($adminPath);

        if ($adminUrl === null) {
            $lines[] = '';
            $lines[] = '🔗 <b>Админка:</b> '.TelegramHtml::escape($adminPath);
        }

        $this->client->sendMessage(
            (string) config('shop.telegram.chat_id'),
            implode("\n", $lines),
            $adminUrl
                ? $this->client->inlineUrlButton('Открыть в админке', $adminUrl)
                : null,
        );
    }

    protected function formatMoney(mixed $amount): string
    {
        return TelegramHtml::escape($this->formatMoneyPlain($amount));
    }

    protected function formatMoneyPlain(mixed $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' '.config('shop.currency_symbol', 'zł');
    }
}
