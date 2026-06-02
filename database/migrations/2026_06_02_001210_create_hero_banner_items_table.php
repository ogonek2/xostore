<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_banner_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_banner_section_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180)->nullable();
            $table->string('subtitle', 180)->nullable();
            $table->string('image_path');
            $table->string('link_url')->nullable();
            $table->string('button_label', 80)->nullable();
            $table->string('button_url')->nullable();
            $table->string('text_position', 32)->default('bottom_left');
            $table->string('text_color', 16)->default('light');
            $table->unsignedTinyInteger('overlay_opacity')->default(30);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hero_banner_section_id', 'is_active', 'sort_order'], 'hero_banner_items_section_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_banner_items');
    }
};
