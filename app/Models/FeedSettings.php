<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FeedSettings extends Model
{
    protected $fillable = [
        'google_enabled',
        'facebook_enabled',
        'auto_regenerate',
        'include_out_of_stock',
        'locale',
        'google_slug',
        'facebook_slug',
        'google_product_category',
        'product_condition',
        'google_last_generated_at',
        'google_item_count',
        'google_file_size',
        'facebook_last_generated_at',
        'facebook_item_count',
        'facebook_file_size',
        'last_duration_ms',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'google_enabled' => 'boolean',
            'facebook_enabled' => 'boolean',
            'auto_regenerate' => 'boolean',
            'include_out_of_stock' => 'boolean',
            'google_last_generated_at' => 'datetime',
            'facebook_last_generated_at' => 'datetime',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'google_enabled' => true,
                'facebook_enabled' => true,
                'auto_regenerate' => true,
                'include_out_of_stock' => true,
                'locale' => (string) config('shop.default_language', 'pl'),
                'google_slug' => (string) config('shop.feeds.google.slug', 'google-merchant.xml'),
                'facebook_slug' => (string) config('shop.feeds.facebook.slug', 'facebook-catalog.csv'),
                'google_product_category' => (string) config('shop.feeds.google.default_category', ''),
                'product_condition' => (string) config('shop.feeds.product_condition', 'new'),
            ],
        );
    }

    public function googlePublicUrl(): string
    {
        return url('/feeds/'.$this->google_slug);
    }

    public function facebookPublicUrl(): string
    {
        return url('/feeds/'.$this->facebook_slug);
    }

    public function googleStoragePath(): string
    {
        return $this->storageDirectory().'/'.$this->google_slug;
    }

    public function facebookStoragePath(): string
    {
        return $this->storageDirectory().'/'.$this->facebook_slug;
    }

    public function storageDisk(): string
    {
        return (string) config('shop.feeds.disk', 'public');
    }

    public function storageDirectory(): string
    {
        return trim((string) config('shop.feeds.directory', 'feeds'), '/');
    }

    public function googleFileExists(): bool
    {
        return Storage::disk($this->storageDisk())->exists($this->googleStoragePath());
    }

    public function facebookFileExists(): bool
    {
        return Storage::disk($this->storageDisk())->exists($this->facebookStoragePath());
    }

    public function formattedFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1_048_576) {
            return number_format($bytes / 1024, 1, '.', ' ').' KB';
        }

        return number_format($bytes / 1_048_576, 2, '.', ' ').' MB';
    }
}
