<?php

namespace App\Console\Commands;

use App\Services\Feeds\ProductFeedGenerator;
use Illuminate\Console\Command;

class RegenerateProductFeedsCommand extends Command
{
    protected $signature = 'shop:regenerate-feeds';

    protected $description = 'Regenerate Google Merchant and Facebook product feeds';

    public function handle(ProductFeedGenerator $generator): int
    {
        $settings = $generator->regenerateAll();

        $this->info('Product feeds regenerated.');
        $this->line('Google items: '.$settings->google_item_count);
        $this->line('Facebook items: '.$settings->facebook_item_count);
        $this->line('Duration: '.($settings->last_duration_ms ?? 0).' ms');

        if ($settings->last_error) {
            $this->warn('Last error: '.$settings->last_error);
        }

        return self::SUCCESS;
    }
}
