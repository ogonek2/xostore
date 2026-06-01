<?php

namespace App\Console\Commands;

use App\Support\Media\Media;
use App\Support\Media\MediaUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestBunnyStorageCommand extends Command
{
    protected $signature = 'bunny:test';

    protected $description = 'Проверка подключения к Bunny Storage и CDN';

    public function handle(): int
    {
        if (Media::disk() !== 'bunny') {
            $this->warn('MEDIA_DISK не равен bunny. Установите MEDIA_DISK=bunny в .env');
        }

        $disk = Storage::disk('bunny');
        $path = 'healthcheck/'.now()->format('Y-m-d-His').'.txt';
        $payload = 'xostore-ok-'.now()->toIso8601String();

        try {
            $disk->put($path, $payload);
        } catch (\Throwable $e) {
            $this->error('Загрузка не удалась: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $disk->exists($path)) {
            $this->error('Файл не найден в хранилище после загрузки.');

            return self::FAILURE;
        }

        $read = $disk->get($path);
        $url = MediaUrl::fromPath($path, 'bunny');

        $this->info('Загрузка: OK');
        $this->line("Путь: {$path}");
        $this->line("CDN URL: {$url}");
        $this->line('Содержимое: '.($read === $payload ? 'совпадает' : 'не совпадает'));

        try {
            $disk->delete($path);
            $this->info('Тестовый файл удалён.');
        } catch (\Throwable) {
            $this->warn('Не удалось удалить тестовый файл: '.$path);
        }

        return self::SUCCESS;
    }
}
