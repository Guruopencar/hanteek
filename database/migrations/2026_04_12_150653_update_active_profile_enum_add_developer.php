<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    DB::statement("ALTER TABLE users MODIFY COLUMN active_profile ENUM('programmer','recruiter','developer') NULL DEFAULT NULL");
    
    // Data migration: programmer -> developer
    DB::table('users')->where('active_profile', 'programmer')->update(['active_profile' => 'developer']);
}

public function down(): void
{
    DB::table('users')->where('active_profile', 'developer')->update(['active_profile' => 'programmer']);
    DB::statement("ALTER TABLE users MODIFY COLUMN active_profile ENUM('programmer','recruiter') NULL DEFAULT NULL");
}
};
