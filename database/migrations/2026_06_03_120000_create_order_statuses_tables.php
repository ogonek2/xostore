<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->json('labels');
            $table->string('color', 16)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('counts_towards_revenue')->default(true);
            $table->boolean('notify_email_on_enter')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_sms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_status_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('order_status_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('order_status_id')->nullable()->after('access_token')->constrained()->nullOnDelete();
        });

        $this->seedStatuses();
        $this->migrateOrders();

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'placed_at']);
            $table->dropColumn('status');
            $table->index(['order_status_id', 'placed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_status_id', 'placed_at']);
            $table->string('status', 32)->default('pending')->after('access_token');
            $table->index(['status', 'placed_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_status_id');
        });

        Schema::dropIfExists('order_status_sms_templates');
        Schema::dropIfExists('order_statuses');
    }

    protected function seedStatuses(): void
    {
        $now = now();
        $rows = [
            ['code' => 'pending', 'labels' => json_encode(['pl' => 'Oczekuje', 'en' => 'Pending']), 'is_default' => true, 'sort_order' => 0, 'counts_towards_revenue' => true],
            ['code' => 'confirmed', 'labels' => json_encode(['pl' => 'Potwierdzone', 'en' => 'Confirmed']), 'sort_order' => 10, 'counts_towards_revenue' => true],
            ['code' => 'processing', 'labels' => json_encode(['pl' => 'W realizacji', 'en' => 'Processing']), 'sort_order' => 20, 'counts_towards_revenue' => true],
            ['code' => 'shipped', 'labels' => json_encode(['pl' => 'Wysłane', 'en' => 'Shipped']), 'sort_order' => 30, 'counts_towards_revenue' => true, 'notify_email_on_enter' => true],
            ['code' => 'completed', 'labels' => json_encode(['pl' => 'Zakończone', 'en' => 'Completed']), 'sort_order' => 40, 'counts_towards_revenue' => true],
            ['code' => 'cancelled', 'labels' => json_encode(['pl' => 'Anulowane', 'en' => 'Cancelled']), 'sort_order' => 50, 'counts_towards_revenue' => false],
        ];

        foreach ($rows as $row) {
            DB::table('order_statuses')->insert(array_merge($row, [
                'is_active' => true,
                'notify_email_on_enter' => $row['notify_email_on_enter'] ?? false,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    protected function migrateOrders(): void
    {
        $map = DB::table('order_statuses')->pluck('id', 'code');

        foreach (DB::table('orders')->select('id', 'status')->get() as $order) {
            $code = $order->status ?: 'pending';
            DB::table('orders')->where('id', $order->id)->update([
                'order_status_id' => $map[$code] ?? $map['pending'],
            ]);
        }
    }
};
