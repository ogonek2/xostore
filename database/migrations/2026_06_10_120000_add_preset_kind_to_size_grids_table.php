<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('size_grids', function (Blueprint $table) {
            $table->string('preset_kind', 32)->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('size_grids', function (Blueprint $table) {
            $table->dropColumn('preset_kind');
        });
    }
};
