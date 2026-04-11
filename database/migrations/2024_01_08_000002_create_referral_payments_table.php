<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('created_at')->useCurrent();

            $table->index('referral_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_payments');
    }
};
