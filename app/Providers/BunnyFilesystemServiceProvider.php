<?php

namespace App\Providers;

use App\Filesystem\BunnyStorageClient;
use Bangnokia\LaravelBunnyStorage\BunnyStorageAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;

class BunnyFilesystemServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('bunny', function ($app, array $config) {
            $root = $config['root'] ?? '';
            $pullZoneUrl = $config['pull_zone'] ?? '';
            $tokenAuthKey = $config['token_auth_key'] ?? '';

            if ($pullZoneUrl && $root) {
                $pullZoneUrl = rtrim($pullZoneUrl, '/').'/'.ltrim($root, '/');
            }

            $adapter = new BunnyStorageAdapter(
                new BunnyStorageClient(
                    $config['storage_zone'],
                    $config['api_key'],
                    $config['region'],
                ),
                $pullZoneUrl
            );

            $adapter->setTokenAuthKey($tokenAuthKey);

            $filesystem = $root !== ''
                ? new Filesystem(new PathPrefixedAdapter($adapter, $root), $config)
                : new Filesystem($adapter, $config);

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}
