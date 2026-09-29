<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Маппінг старих значень programmer -> developer
        DB::table('users')
            ->where('active_profile', 'programmer')
            ->update(['active_profile' => 'developer']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('active_profile', 'developer')
            ->update(['active_profile' => 'programmer']);
    }
};
