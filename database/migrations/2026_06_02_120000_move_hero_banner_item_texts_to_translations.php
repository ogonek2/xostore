<?php

use App\Models\HeroBannerItem;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $language = Language::query()
            ->where('code', config('shop.default_language', 'pl'))
            ->first();

        if ($language) {
            HeroBannerItem::query()->each(function (HeroBannerItem $item) use ($language): void {
                foreach (['title', 'subtitle', 'button_label'] as $field) {
                    $value = $item->getAttribute($field);

                    if (! is_string($value) || trim($value) === '') {
                        continue;
                    }

                    $item->setTranslation($field, $value, $language);
                }
            });
        }

        Schema::table('hero_banner_items', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'button_label']);
        });
    }

    public function down(): void
    {
        Schema::table('hero_banner_items', function (Blueprint $table) {
            $table->string('title', 180)->nullable()->after('hero_banner_section_id');
            $table->string('subtitle', 180)->nullable()->after('title');
            $table->string('button_label', 80)->nullable()->after('link_url');
        });
    }
};
