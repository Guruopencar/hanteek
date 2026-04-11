<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 300);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->enum('work_type', ['remote', 'office', 'hybrid'])->default('remote');
            $table->string('location', 200)->nullable();
            $table->enum('contract_type', ['fixed_price', 'time_material'])->default('fixed_price');
            $table->decimal('budget', 12, 2)->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('skills_required')->nullable();
            $table->enum('status', ['draft', 'published', 'in_progress', 'completed', 'archived'])->default('draft');
            $table->unsignedInteger('applications_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'contract_type', 'work_type']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
