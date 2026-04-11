<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('developer_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('position', 200);
            $table->tinyInteger('experience_years')->default(0);
            $table->enum('work_type', ['remote', 'office', 'hybrid'])->default('remote');
            $table->enum('employment_type', ['full_time', 'part_time', 'freelance'])->default('full_time');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('fixed_rate', 10, 2)->nullable();
            $table->string('rate_currency', 3)->default('USD');
            $table->json('skills')->nullable();
            $table->text('description')->nullable();
            $table->string('portfolio_url', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'work_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_resumes');
    }
};
