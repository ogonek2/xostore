<?php

namespace App\Models;

use App\Support\Feeds\FeedField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FeedSettings extends Model
{
    public const ID_SOURCE_PRODUCT = 'product_id';

    public const ID_SOURCE_VARIANT_SKU = 'variant_sku';

    public const ID_SOURCE_VARIANT_ID = 'variant_id';

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
        'id_source',
        'enabled_fields',
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
            'enabled_fields' => 'array',
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
                'id_source' => self::ID_SOURCE_PRODUCT,
                'enabled_fields' => FeedField::defaultEnabled(),
            ],
        );
    }

    public function resolveIdSource(): string
    {
        $source = (string) ($this->id_source ?: self::ID_SOURCE_PRODUCT);

        return in_array($source, [
            self::ID_SOURCE_PRODUCT,
            self::ID_SOURCE_VARIANT_SKU,
            self::ID_SOURCE_VARIANT_ID,
        ], true) ? $source : self::ID_SOURCE_PRODUCT;
    }

    /**
     * @return list<string>
     */
    public function resolvedEnabledFields(): array
    {
        $stored = $this->enabled_fields;

        if (! is_array($stored) || $stored === []) {
            return FeedField::defaultEnabled();
        }

        $enabled = array_values(array_unique(array_filter(
            $stored,
            fn ($field) => is_string($field) && $field !== '',
        )));

        foreach (FeedField::REQUIRED as $required) {
            if (! in_array($required, $enabled, true)) {
                $enabled[] = $required;
            }
        }

        return $enabled;
    }

    public function isFieldEnabled(string $field): bool
    {
        return in_array($field, $this->resolvedEnabledFields(), true);
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
