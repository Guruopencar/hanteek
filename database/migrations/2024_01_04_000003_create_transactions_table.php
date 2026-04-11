<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('from_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('to_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('from_account_id')->nullable()->constrained('wallet_accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('wallet_accounts')->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', [
                'deposit', 'withdrawal', 'transfer',
                'payment', 'commission', 'refund', 'bonus'
            ]);
            $table->decimal('amount', 12, 2);
            $table->decimal('commission', 8, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', [
                'pending', 'processing', 'completed',
                'failed', 'cancelled', 'refunded'
            ])->default('pending');
            $table->string('description', 500)->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('external_id', 255)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['from_wallet_id', 'type', 'status']);
            $table->index(['to_wallet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
