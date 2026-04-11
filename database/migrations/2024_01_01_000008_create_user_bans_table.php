<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('banned_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('banned_at')->useCurrent();
            $table->timestamp('unbanned_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['banner_id', 'banned_id']);
            $table->index('banned_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bans');
    }
};
