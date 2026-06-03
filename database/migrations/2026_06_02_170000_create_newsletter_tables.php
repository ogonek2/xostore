<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('status', 32)->default('subscribed');
            $table->string('source', 64)->default('website');
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('newsletter_group_subscriber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['newsletter_group_id', 'newsletter_subscriber_id'], 'newsletter_group_subscriber_unique');
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->string('status', 32)->default('draft');
            $table->foreignId('newsletter_group_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('newsletter_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['newsletter_campaign_id', 'newsletter_subscriber_id'], 'newsletter_sends_campaign_subscriber_unique');
            $table->index(['newsletter_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_sends');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_group_subscriber');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('newsletter_groups');
    }
};
