<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_banner_sections', function (Blueprint $table) {
            $table->string('height_preset', 16)->default('auto')->after('layout');
            $table->string('width_preset', 16)->default('full')->after('height_preset');
            $table->string('image_fit', 16)->default('contain')->after('width_preset');
        });
    }

    public function down(): void
    {
        Schema::table('hero_banner_sections', function (Blueprint $table) {
            $table->dropColumn(['height_preset', 'width_preset', 'image_fit']);
        });
    }
};
