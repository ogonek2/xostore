<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_status_sms_templates')) {
            Schema::rename('order_status_sms_templates', 'order_status_email_templates');
        }

        if (! Schema::hasColumn('order_status_email_templates', 'subject')) {
            Schema::table('order_status_email_templates', function (Blueprint $table) {
                $table->string('subject')->nullable()->after('name');
            });
        }

        DB::table('order_status_email_templates')
            ->whereNull('subject')
            ->update(['subject' => 'Zamówienie {{order_number}} — {{status}}']);

        if (Schema::hasColumn('order_statuses', 'notify_email_on_enter')) {
            Schema::table('order_statuses', function (Blueprint $table) {
                $table->dropColumn('notify_email_on_enter');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('order_statuses', 'notify_email_on_enter')) {
            Schema::table('order_statuses', function (Blueprint $table) {
                $table->boolean('notify_email_on_enter')->default(false)->after('counts_towards_revenue');
            });

            $shippedId = DB::table('order_statuses')->where('code', 'shipped')->value('id');
            if ($shippedId) {
                DB::table('order_statuses')->where('id', $shippedId)->update(['notify_email_on_enter' => true]);
            }
        }

        if (Schema::hasColumn('order_status_email_templates', 'subject')) {
            Schema::table('order_status_email_templates', function (Blueprint $table) {
                $table->dropColumn('subject');
            });
        }

        if (Schema::hasTable('order_status_email_templates')) {
            Schema::rename('order_status_email_templates', 'order_status_sms_templates');
        }
    }
};
