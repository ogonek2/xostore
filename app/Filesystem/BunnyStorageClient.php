<?php

namespace App\Filesystem;

use Bangnokia\LaravelBunnyStorage\BunnyStorageClient as BaseBunnyStorageClient;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

class BunnyStorageClient extends BaseBunnyStorageClient
{
    public function __construct(
        string $storage_zone_name,
        string $api_key,
        string $region = 'de',
    ) {
        parent::__construct($storage_zone_name, $api_key, $region);

        if (static::shouldVerifySsl()) {
            return;
        }

        $handler = HandlerStack::create(new CurlHandler());

        $this->guzzleClient = new Guzzle([
            'handler' => $handler,
            'verify' => false,
        ]);
    }

    protected static function shouldVerifySsl(): bool
    {
        $configured = config('filesystems.disks.bunny.verify_ssl');

        if ($configured !== null) {
            return filter_var($configured, FILTER_VALIDATE_BOOL);
        }

        return app()->environment('production');
    }
}
