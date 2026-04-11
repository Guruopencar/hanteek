<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->enum('type', ['main', 'savings', 'bonus', 'escrow'])->default('main');
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('wallet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_accounts');
    }
};
