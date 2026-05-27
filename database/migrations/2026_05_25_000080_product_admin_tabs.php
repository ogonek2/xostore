<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('model_slug', 128)->nullable()->after('sku');
            $table->string('color_label', 64)->nullable()->after('model_slug');
            $table->string('color_slug', 64)->nullable()->after('color_label');
            $table->string('color_hex', 7)->nullable()->after('color_slug');
            $table->unsignedInteger('sort_order')->default(0)->after('is_new');
            $table->boolean('custom_tailoring_enabled')->default(false)->after('track_inventory');
        });

        Schema::create('product_detail_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_size_chart_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size', 32)->nullable();
            $table->string('chest', 32)->nullable();
            $table->string('waist', 32)->nullable();
            $table->string('hips', 32)->nullable();
            $table->string('inseam', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_size_chart_rows');
        Schema::dropIfExists('product_detail_items');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'model_slug',
                'color_label',
                'color_slug',
                'color_hex',
                'sort_order',
                'custom_tailoring_enabled',
            ]);
        });
    }
};
