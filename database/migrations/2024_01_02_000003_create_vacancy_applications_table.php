<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vacancy_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->text('cover_letter')->nullable();
            $table->decimal('proposed_rate', 10, 2)->nullable();
            $table->enum('status', ['pending', 'viewed', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'applicant_id']);
            $table->index(['applicant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_applications');
    }
};
