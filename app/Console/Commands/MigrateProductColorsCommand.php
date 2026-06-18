<?php

namespace App\Console\Commands;

use App\Support\Shop\ProductColorMigrator;
use Illuminate\Console\Command;

class MigrateProductColorsCommand extends Command
{
    protected $signature = 'shop:migrate-product-colors {--dry-run : Показать статистику без изменений в БД}';

    protected $description = 'Перенести уникальные цвета с товаров в справочник colors и связать color_id';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Режим dry-run: изменения в БД не применяются.');
        }

        $stats = ProductColorMigrator::migrate($dryRun);

        $this->table(
            ['Метрика', 'Количество'],
            [
                ['Новых цветов в справочнике', $stats['created']],
                ['Товаров привязано', $stats['linked']],
                ['Уже были привязаны', $stats['already_linked']],
                ['Пропущено (нет данных о цвете)', $stats['skipped']],
            ],
        );

        if ($dryRun) {
            $this->info('Запустите без --dry-run для применения.');
        } else {
            $this->info('Миграция цветов завершена.');
        }

        return self::SUCCESS;
    }
}
