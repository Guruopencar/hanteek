<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('developer_work_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained('developer_resumes')->cascadeOnDelete();
            $table->string('company', 200);
            $table->string('position', 200);
            $table->text('description')->nullable();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('resume_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_work_experience');
    }
};
