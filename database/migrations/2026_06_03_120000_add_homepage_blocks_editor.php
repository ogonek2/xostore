<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->json('blocks')->nullable()->after('id');
        });

        $row = DB::table('homepage_settings')->where('id', 1)->first();

        if ($row) {
            $blocks = $this->buildBlocksFromLegacyRow($row);

            DB::table('homepage_settings')->where('id', 1)->update([
                'blocks' => json_encode($blocks),
            ]);
        }

        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_category_showcase',
                'show_trending_section',
                'show_promotions_section',
                'show_new_arrivals_section',
                'show_banners_section',
                'category_showcase',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->boolean('show_category_showcase')->default(true);
            $table->boolean('show_trending_section')->default(true);
            $table->boolean('show_promotions_section')->default(true);
            $table->boolean('show_new_arrivals_section')->default(true);
            $table->boolean('show_banners_section')->default(true);
            $table->json('category_showcase')->nullable();
        });

        $row = DB::table('homepage_settings')->where('id', 1)->first();
        $blocks = json_decode($row->blocks ?? '[]', true) ?: [];

        $legacy = $this->legacyFromBlocks($blocks);

        DB::table('homepage_settings')->where('id', 1)->update($legacy);

        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn('blocks');
        });
    }

  /**
     * @return list<array<string, mixed>>
     */
    protected function buildBlocksFromLegacyRow(object $row): array
    {
        $categoryItems = json_decode($row->category_showcase ?? '[]', true) ?: [];

        $blocks = [
            ['type' => 'hero', 'is_active' => true],
            [
                'type' => 'banners',
                'is_active' => (bool) ($row->show_banners_section ?? true),
            ],
            [
                'type' => 'category_showcase',
                'is_active' => (bool) ($row->show_category_showcase ?? true),
                'settings' => ['items' => $categoryItems],
            ],
            [
                'type' => 'trending',
                'is_active' => (bool) ($row->show_trending_section ?? true),
            ],
            [
                'type' => 'promotions',
                'is_active' => (bool) ($row->show_promotions_section ?? true),
            ],
            [
                'type' => 'new_arrivals',
                'is_active' => (bool) ($row->show_new_arrivals_section ?? true),
            ],
        ];

        return $blocks;
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return array<string, mixed>
     */
    protected function legacyFromBlocks(array $blocks): array
    {
        $legacy = [
            'show_category_showcase' => true,
            'show_trending_section' => true,
            'show_promotions_section' => true,
            'show_new_arrivals_section' => true,
            'show_banners_section' => true,
            'category_showcase' => json_encode([]),
        ];

        foreach ($blocks as $block) {
            $active = (bool) ($block['is_active'] ?? true);
            $type = $block['type'] ?? null;

            if ($type === 'banners') {
                $legacy['show_banners_section'] = $active;
            }

            if ($type === 'category_showcase') {
                $legacy['show_category_showcase'] = $active;
                $legacy['category_showcase'] = json_encode($block['settings']['items'] ?? []);
            }

            if ($type === 'trending') {
                $legacy['show_trending_section'] = $active;
            }

            if ($type === 'promotions') {
                $legacy['show_promotions_section'] = $active;
            }

            if ($type === 'new_arrivals') {
                $legacy['show_new_arrivals_section'] = $active;
            }
        }

        return $legacy;
    }
};
