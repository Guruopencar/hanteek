<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id', 100)->nullable()->index()->after('payment_method');
            $table->foreignId('payment_method_id')->nullable()
                ->constrained('payment_methods')->nullOnDelete()
                ->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['stripe_payment_intent_id', 'payment_method_id']);
        });
    }
};
