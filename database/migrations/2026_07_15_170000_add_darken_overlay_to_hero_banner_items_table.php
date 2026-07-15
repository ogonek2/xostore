<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_banner_items', function (Blueprint $table) {
            $table->boolean('darken_overlay')
                ->default(true)
                ->after('text_color');
        });
    }

    public function down(): void
    {
        Schema::table('hero_banner_items', function (Blueprint $table) {
            $table->dropColumn('darken_overlay');
        });
    }
};
