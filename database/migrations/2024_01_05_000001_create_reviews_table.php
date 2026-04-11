<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment');
            $table->enum('status', ['pending', 'published', 'hidden', 'deleted'])->default('pending');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'reviewer_id']);
            $table->index(['reviewee_id', 'is_published']);
            $table->index(['status', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
