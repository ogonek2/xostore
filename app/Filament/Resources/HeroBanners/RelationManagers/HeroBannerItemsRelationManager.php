<?php

namespace App\Filament\Resources\HeroBanners\RelationManagers;

use App\Filament\Forms\TranslationTabs;
use App\Filament\Resources\Products\RelationManagers\Concerns\ManagesTranslationsOnActions;
use App\Filament\Support\FilamentMedia;
use App\Support\Media\Media;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HeroBannerItemsRelationManager extends RelationManager
{
    use ManagesTranslationsOnActions;

    protected static string $relationship = 'items';

    protected static ?string $title = 'Сетка баннеров';

    protected static function translationConfigKey(): string
    {
        return 'hero_banner_item';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TranslationTabs::make(static::translationConfigKey(), 'Тексты баннера'),
            Section::make('Изображение и ссылки')
                ->schema([
                    FilamentMedia::image('image_path', 'hero-banners')
                        ->label('Изображение')
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('link_url')
                        ->label('Ссылка всей карточки')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull(),
                    TextInput::make('button_url')
                        ->label('Ссылка кнопки')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make('Оформление')
                ->schema([
                    Select::make('text_position')
                        ->label('Позиция текста')
                        ->options([
                            'top_left' => 'Сверху слева',
                            'top_center' => 'Сверху по центру',
                            'top_right' => 'Сверху справа',
                            'center_left' => 'По центру слева',
                            'center' => 'По центру',
                            'center_right' => 'По центру справа',
                            'bottom_left' => 'Снизу слева',
                            'bottom_center' => 'Снизу по центру',
                            'bottom_right' => 'Снизу справа',
                        ])
                        ->default('bottom_left')
                        ->required(),
                    Select::make('text_color')
                        ->label('Цвет текста')
                        ->options([
                            'light' => 'Светлый',
                            'dark' => 'Тёмный',
                        ])
                        ->default('light')
                        ->required(),
                    TextInput::make('overlay_opacity')
                        ->label('Затемнение (0–90)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(90)
                        ->default(30),
                    TextInput::make('sort_order')
                        ->label('Позиция в сетке')
                        ->numeric()
                        ->default(0)
                        ->helperText('Меньшее число — раньше в порядке «слева направо, сверху вниз».'),
                    Toggle::make('is_active')
                        ->label('Активна')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        $defaultLocale = (string) config('shop.default_language', 'pl');

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('translates'))
            ->paginated(false)
            ->striped(false)
            ->contentGrid([
                'default' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->extraAttributes([
                'class' => 'hero-banner-items-grid',
            ])
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk(Media::disk())
                    ->height('9rem')
                    ->extraImgAttributes([
                        'class' => 'w-full rounded-lg object-cover',
                    ]),
                TextColumn::make('title_preview')
                    ->label('Заголовок')
                    ->state(fn ($record) => $record->translate('title', $defaultLocale) ?: '—')
                    ->wrap(),
                TextColumn::make('sort_order')
                    ->label('#')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label('Вкл.')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                $this->makeTranslationCreateAction()
                    ->label('Добавить карточку'),
            ])
            ->recordActions([
                $this->makeTranslationEditAction()
                    ->label('Изменить'),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет карточек')
            ->emptyStateDescription('Добавьте изображения баннеров. Перетаскиванием задайте порядок в сетке.');
    }
}
