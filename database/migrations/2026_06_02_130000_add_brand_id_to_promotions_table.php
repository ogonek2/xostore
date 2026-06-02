<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('catalog_id')
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('promotions')
            ->whereNull('product_target_type')
            ->whereNotNull('category_id')
            ->update(['product_target_type' => 'category']);

        DB::table('promotions')
            ->whereNull('product_target_type')
            ->whereNotNull('catalog_id')
            ->update(['product_target_type' => 'catalog']);
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};
