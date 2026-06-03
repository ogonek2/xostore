<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('newsletter_campaigns')
            ->whereIn('status', ['scheduled', 'cancelled'])
            ->update(['status' => 'draft']);

        DB::table('newsletter_campaigns')
            ->where('status', 'sending')
            ->update(['status' => 'sent']);
    }

    public function down(): void
    {
        //
    }
};
