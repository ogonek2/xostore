<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_chart_presets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('unit', 16)->default('cm');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('size_chart_preset_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_chart_preset_id')->constrained()->cascadeOnDelete();
            $table->string('size', 32);
            $table->decimal('chest_cm', 6, 1)->nullable();
            $table->decimal('waist_cm', 6, 1)->nullable();
            $table->decimal('hips_cm', 6, 1)->nullable();
            $table->decimal('inseam_cm', 6, 1)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['size_chart_preset_id', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('size_chart_preset_id')
                ->nullable()
                ->after('size_grid_id')
                ->constrained('size_chart_presets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_chart_preset_id');
        });

        Schema::dropIfExists('size_chart_preset_rows');
        Schema::dropIfExists('size_chart_presets');
    }
};
