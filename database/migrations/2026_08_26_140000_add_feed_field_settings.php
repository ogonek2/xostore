<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_settings', function (Blueprint $table) {
            $table->string('id_source', 32)->default('product_id')->after('product_condition');
            $table->json('enabled_fields')->nullable()->after('id_source');
        });
    }

    public function down(): void
    {
        Schema::table('feed_settings', function (Blueprint $table) {
            $table->dropColumn(['id_source', 'enabled_fields']);
        });
    }
};
