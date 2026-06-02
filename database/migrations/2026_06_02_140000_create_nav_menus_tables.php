<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_menus', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nav_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('nav_items')->cascadeOnDelete();
            $table->json('labels');
            $table->string('url', 500)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['nav_menu_id', 'parent_id', 'is_active', 'sort_order'], 'nav_items_menu_parent_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
        Schema::dropIfExists('nav_menus');
    }
};
