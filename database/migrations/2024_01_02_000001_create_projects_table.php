<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('cover_color', 7)->nullable();
            $table->enum('status', ['active', 'archived', 'draft'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
