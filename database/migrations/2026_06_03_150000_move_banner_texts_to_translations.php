<?php

use App\Models\Banner;
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
            Banner::query()->each(function (Banner $banner) use ($language): void {
                foreach (['title', 'link_url'] as $field) {
                    $value = $banner->getAttribute($field);

                    if (! is_string($value) || trim($value) === '') {
                        continue;
                    }

                    $banner->setTranslation($field, $value, $language);
                }
            });
        }

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['title', 'link_url']);
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('title', 160)->nullable()->after('id');
            $table->string('link_url')->nullable()->after('image_path');
        });
    }
};
