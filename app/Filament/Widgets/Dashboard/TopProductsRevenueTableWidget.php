<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithShopAnalytics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
class TopProductsRevenueTableWidget extends TableWidget
{
    use InteractsWithShopAnalytics;

    protected static ?int $sort = 14;

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 2];

    protected function getTableHeading(): ?string
    {
        return 'Топ товаров по выручке';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => $this->analytics()->topProductsByRevenue(12)
                ->map(fn ($row) => [
                    'product_name' => (string) $row->product_name,
                    'quantity' => (int) $row->quantity,
                    'revenue' => (float) $row->revenue,
                ])
                ->values()
                ->all())
            ->columns([
                TextColumn::make('product_name')->label('Товар')->wrap(),
                TextColumn::make('quantity')
                    ->label('Шт.')
                    ->alignEnd(),
                TextColumn::make('revenue')
                    ->label('Выручка')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => $this->analytics()->formatMoney($state)),
            ])
            ->paginated(false);
    }
}
