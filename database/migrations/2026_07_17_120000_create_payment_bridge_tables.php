<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('public_token', 64)->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('broker_payment_id')->nullable()->index();
            $table->string('provider_order_id')->nullable()->index();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 8);
            $table->string('status', 32)->index();
            $table->string('idempotency_key', 64)->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'provider']);
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 128)->unique();
            $table->foreignUuid('payment_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32);
            $table->json('payload');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('bridge_nonces', function (Blueprint $table) {
            $table->id();
            $table->string('nonce', 128);
            $table->string('direction', 16);
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->unique(['nonce', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_nonces');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
