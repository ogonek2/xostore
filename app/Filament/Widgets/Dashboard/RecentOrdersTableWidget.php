<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrdersTableWidget extends TableWidget
{
    protected static ?int $sort = 24;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return 'Последние заказы';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with('orderStatus')
                    ->whereNotNull('placed_at')
                    ->latest('placed_at')
                    ->limit(12)
            )
            ->columns([
                TextColumn::make('number')
                    ->label('Номер')
                    ->url(fn (Order $record) => OrderResource::getUrl('edit', ['record' => $record]))
                    ->color('primary'),
                TextColumn::make('placed_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->placeholder('—'),
                TextColumn::make('email')->label('E-mail'),
                TextColumn::make('orderStatus.labels.pl')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (Order $record) => 'gray'),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', ' ').' '.config('shop.currency_symbol', 'zł')),
            ])
            ->paginated(false);
    }
}
