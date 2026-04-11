<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('developer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['vacancy', 'resume', 'agreement'])->default('agreement');
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->enum('contract_type', ['fixed_price', 'time_material'])->default('fixed_price');
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('status', [
                'draft', 'pending_signature', 'active',
                'completed', 'cancelled', 'archived'
            ])->default('draft');
            $table->timestamp('owner_signed_at')->nullable();
            $table->timestamp('developer_signed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('document_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['developer_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
