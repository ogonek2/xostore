<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockTableWidget extends TableWidget
{
    protected static ?int $sort = 23;

    protected ?string $maxHeight = '320px';

    protected function getTableHeading(): ?string
    {
        return 'Низкий остаток (≤ 3)';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductVariant::query()
                    ->with('product')
                    ->where('is_active', true)
                    ->where('stock_qty', '<=', 3)
                    ->whereHas('product', fn ($q) => $q->where('track_inventory', true))
                    ->orderBy('stock_qty')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('product')
                    ->label('Товар')
                    ->formatStateUsing(fn ($record) => $record->product?->translate('name', 'pl') ?? '—')
                    ->url(fn ($record) => $record->product
                        ? ProductResource::getUrl('edit', ['record' => $record->product_id])
                        : null),
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('stock_qty')
                    ->label('Остаток')
                    ->alignEnd()
                    ->color('danger'),
            ])
            ->paginated(false);
    }
}
