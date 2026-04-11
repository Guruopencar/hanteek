<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'cancelled'])->default('todo');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 6, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_tasks');
    }
};
