<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('size_grid_id')->nullable()->after('primary_category_id')->constrained('size_grids')->nullOnDelete();
        });

        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 32);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id', 'type']);
            $table->index(['product_id', 'type']);
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->string('product_target_type', 32)->nullable()->after('category_id');
            $table->foreignId('catalog_id')->nullable()->after('product_target_type')->constrained()->nullOnDelete();
        });

        Schema::create('promotion_product', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['promotion_id', 'product_id']);
        });

        Schema::table('languages', function (Blueprint $table) {
            $table->boolean('auto_translate_on_create')->default(true)->after('is_active');
        });

        Schema::table('translates', function (Blueprint $table) {
            $table->boolean('is_machine_translated')->default(false)->after('value');
        });

        Schema::create('shop_visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('landing_path', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index('last_activity_at');
        });

        Schema::create('shop_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_visitor_session_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 64);
            $table->string('path', 500)->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'created_at']);
            $table->index(['product_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_events');
        Schema::dropIfExists('shop_visitor_sessions');
        Schema::table('translates', function (Blueprint $table) {
            $table->dropColumn('is_machine_translated');
        });
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('auto_translate_on_create');
        });
        Schema::dropIfExists('promotion_product');
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_id');
            $table->dropColumn('product_target_type');
        });
        Schema::dropIfExists('product_relations');
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_grid_id');
        });
    }
};
