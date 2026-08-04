<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->string('ip', 45)->nullable()->after('admin_notes');
            $table->string('user_agent', 512)->nullable()->after('ip');
            $table->string('referer', 512)->nullable()->after('user_agent');
            $table->string('submission_source', 32)->nullable()->after('referer');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropColumn(['ip', 'user_agent', 'referer', 'submission_source']);
        });
    }
};
