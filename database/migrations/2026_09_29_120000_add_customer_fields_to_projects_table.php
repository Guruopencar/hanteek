<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('customer_first_name', 100)->nullable()->after('description');
            $table->string('customer_last_name', 100)->nullable()->after('customer_first_name');
            $table->string('customer_email', 200)->nullable()->after('customer_last_name');
            $table->string('customer_phone', 40)->nullable()->after('customer_email');
            $table->foreignId('card_id')->nullable()
                ->constrained('wallet_accounts')->nullOnDelete()
                ->after('customer_phone');
            $table->foreignId('contract_id')->nullable()
                ->constrained('contracts')->nullOnDelete()
                ->after('card_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['card_id']);
            $table->dropForeign(['contract_id']);
            $table->dropColumn([
                'customer_first_name',
                'customer_last_name',
                'customer_email',
                'customer_phone',
                'card_id',
                'contract_id',
            ]);
        });
    }
};
