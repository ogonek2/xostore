<?php

namespace App\Models;

use App\Enums\HomepageBlockType;
use Illuminate\Database\Eloquent\Model;

class HomepageSettings extends Model
{
    protected $fillable = [
        'blocks',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['blocks' => static::defaultBlocks()],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resolvedBlocks(): array
    {
        $blocks = $this->blocks;

        if (is_array($blocks) && $blocks !== []) {
            return $blocks;
        }

        return static::defaultBlocks();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function defaultBlocks(): array
    {
        return array_map(
            fn (HomepageBlockType $type) => match ($type) {
                HomepageBlockType::CategoryShowcase => [
                    'type' => $type->value,
                    'is_active' => true,
                    'settings' => ['items' => []],
                ],
                default => [
                    'type' => $type->value,
                    'is_active' => true,
                ],
            },
            HomepageBlockType::defaults(),
        );
    }
}
