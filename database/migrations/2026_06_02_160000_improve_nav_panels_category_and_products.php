<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nav_panels', function (Blueprint $table) {
            $table->boolean('show_subcategories')->default(true)->after('category_id');
            $table->boolean('show_products')->default(false)->after('show_subcategories');
        });

        DB::table('nav_panels')
            ->where('type', 'category_children')
            ->update([
                'type' => 'category',
                'show_subcategories' => true,
                'show_products' => false,
            ]);

        Schema::create('nav_panel_product', function (Blueprint $table) {
            $table->foreignId('nav_panel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['nav_panel_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_panel_product');

        Schema::table('nav_panels', function (Blueprint $table) {
            $table->dropColumn(['show_subcategories', 'show_products']);
        });
    }
};
