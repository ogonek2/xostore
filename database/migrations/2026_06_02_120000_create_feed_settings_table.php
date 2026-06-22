<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('google_enabled')->default(true);
            $table->boolean('facebook_enabled')->default(true);
            $table->boolean('auto_regenerate')->default(true);
            $table->boolean('include_out_of_stock')->default(true);
            $table->string('locale', 8)->default('pl');
            $table->string('google_slug')->default('google-merchant.xml');
            $table->string('facebook_slug')->default('facebook-catalog.csv');
            $table->string('google_product_category')->nullable();
            $table->string('product_condition', 32)->default('new');
            $table->timestamp('google_last_generated_at')->nullable();
            $table->unsignedInteger('google_item_count')->default(0);
            $table->unsignedBigInteger('google_file_size')->default(0);
            $table->timestamp('facebook_last_generated_at')->nullable();
            $table->unsignedInteger('facebook_item_count')->default(0);
            $table->unsignedBigInteger('facebook_file_size')->default(0);
            $table->unsignedInteger('last_duration_ms')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_settings');
    }
};
