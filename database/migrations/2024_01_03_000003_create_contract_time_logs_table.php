<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('contract_tasks')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours', 6, 2);
            $table->text('description')->nullable();
            $table->date('logged_date');
            $table->timestamps();

            $table->index(['contract_id', 'logged_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_time_logs');
    }
};
