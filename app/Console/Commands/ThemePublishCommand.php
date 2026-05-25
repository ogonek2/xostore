<?php

namespace App\Console\Commands;

use App\Support\Theme\ThemePublisher;
use Illuminate\Console\Command;

class ThemePublishCommand extends Command
{
    protected $signature = 'theme:publish';

    protected $description = 'Publish theme color and font tokens to Tailwind CSS';

    public function handle(ThemePublisher $publisher): int
    {
        $path = $publisher->publish();

        $this->components->info("Theme tokens published to [{$path}]");

        return self::SUCCESS;
    }
}
