<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->morphs('translatable');
            $table->string('field', 64);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(
                ['language_id', 'translatable_type', 'translatable_id', 'field'],
                'translates_unique'
            );
            $table->index(['translatable_type', 'translatable_id', 'field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
};
