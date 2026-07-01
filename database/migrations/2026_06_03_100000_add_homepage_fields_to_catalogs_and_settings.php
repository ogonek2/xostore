<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->string('homepage_section', 32)->nullable()->after('show_on_homepage');
        });

        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_category_showcase')->default(true);
            $table->boolean('show_trending_section')->default(true);
            $table->boolean('show_promotions_section')->default(true);
            $table->boolean('show_new_arrivals_section')->default(true);
            $table->boolean('show_banners_section')->default(true);
            $table->json('category_showcase')->nullable();
            $table->timestamps();
        });

        DB::table('catalogs')->where('code', 'trendy')->update([
            'homepage_section' => 'trending',
            'show_on_homepage' => true,
        ]);

        DB::table('catalogs')->where('code', 'nowynki')->update([
            'homepage_section' => 'new_arrivals',
            'show_on_homepage' => true,
        ]);

        $showcase = collect(config('shop.homepage_showcase', []))
            ->map(function (array $item) {
                $categoryId = DB::table('categories')
                    ->where('code', $item['category_code'] ?? null)
                    ->value('id');

                if (! $categoryId) {
                    return null;
                }

                return [
                    'category_id' => $categoryId,
                    'sublabel_key' => $item['sublabel'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        DB::table('homepage_settings')->insert([
            'id' => 1,
            'show_category_showcase' => true,
            'show_trending_section' => true,
            'show_promotions_section' => true,
            'show_new_arrivals_section' => true,
            'show_banners_section' => true,
            'category_showcase' => json_encode($showcase),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');

        Schema::table('catalogs', function (Blueprint $table) {
            $table->dropColumn('homepage_section');
        });
    }
};
