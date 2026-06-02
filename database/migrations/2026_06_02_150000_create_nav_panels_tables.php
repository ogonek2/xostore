<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nav_item_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->json('title_labels')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('catalog_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('columns')->default(1);
            $table->unsignedTinyInteger('item_limit')->default(12);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['nav_item_id', 'is_active', 'sort_order'], 'nav_panels_item_active_sort_idx');
        });

        Schema::create('nav_panel_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nav_panel_id')->constrained()->cascadeOnDelete();
            $table->json('labels');
            $table->string('url', 500)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['nav_panel_id', 'sort_order'], 'nav_panel_links_panel_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_panel_links');
        Schema::dropIfExists('nav_panels');
    }
};
