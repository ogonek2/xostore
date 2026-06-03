<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('newsletter_enabled')->default(false);
            $table->json('newsletter')->nullable();
            $table->json('brand')->nullable();
            $table->boolean('social_enabled')->default(false);
            $table->json('social')->nullable();
            $table->boolean('contact_enabled')->default(false);
            $table->json('contact')->nullable();
            $table->boolean('payments_enabled')->default(false);
            $table->json('payments')->nullable();
            $table->json('bottom')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
    }
};
