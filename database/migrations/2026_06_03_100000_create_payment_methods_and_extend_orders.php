<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('type', 32);
            $table->json('labels');
            $table->json('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('shipping_enabled')->default(true);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->boolean('free_shipping_enabled')->default(false);
            $table->decimal('free_shipping_from', 12, 2)->nullable();
            $table->string('redirect_url', 2000)->nullable();
            $table->string('bank_recipient')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('payment_note_template', 500)->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable()->after('phone');
            $table->text('delivery_address')->nullable()->after('street');

            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('postal_code', 16)->nullable()->change();
            $table->string('street')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn(['customer_name', 'delivery_address']);
        });

        Schema::dropIfExists('payment_methods');
    }
};
