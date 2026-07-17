<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->uuid('broker_payment_id')->unique();
            $table->uuid('source_payment_id')->unique();
            $table->string('source_order_number');
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('locale', 10);
            $table->text('return_url');
            $table->string('return_token', 64)->unique();
            $table->string('customer_ip', 45);
            $table->longText('buyer');
            $table->json('products');
            $table->string('payu_order_id')->nullable()->unique();
            $table->text('redirect_uri')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->string('idempotency_key')->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->unsignedInteger('callback_delivery_attempts')->default(0);
            $table->text('callback_delivery_error')->nullable();
            $table->timestamp('callback_delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('broker_payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_payment_intent_id')->constrained()->cascadeOnDelete();
            $table->uuid('event_id')->unique();
            $table->char('fingerprint', 64)->unique();
            $table->string('status', 40);
            $table->json('payload');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('bridge_nonces', function (Blueprint $table) {
            $table->id();
            $table->string('nonce', 128)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_nonces');
        Schema::dropIfExists('broker_payment_events');
        Schema::dropIfExists('broker_payment_intents');
    }
};
