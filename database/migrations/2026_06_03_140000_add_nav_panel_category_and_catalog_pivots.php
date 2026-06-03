<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_panel_category', function (Blueprint $table) {
            $table->foreignId('nav_panel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['nav_panel_id', 'category_id']);
        });

        Schema::create('nav_panel_catalog', function (Blueprint $table) {
            $table->foreignId('nav_panel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['nav_panel_id', 'catalog_id']);
        });

        DB::table('nav_panels')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->each(function (object $panel): void {
                DB::table('nav_panel_category')->insertOrIgnore([
                    'nav_panel_id' => $panel->id,
                    'category_id' => $panel->category_id,
                    'sort_order' => 0,
                ]);
            });

        DB::table('nav_panels')
            ->whereNotNull('catalog_id')
            ->orderBy('id')
            ->each(function (object $panel): void {
                DB::table('nav_panel_catalog')->insertOrIgnore([
                    'nav_panel_id' => $panel->id,
                    'catalog_id' => $panel->catalog_id,
                    'sort_order' => 0,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_panel_catalog');
        Schema::dropIfExists('nav_panel_category');
    }
};
