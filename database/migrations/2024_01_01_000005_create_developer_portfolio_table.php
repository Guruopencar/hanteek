<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('developer_portfolio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained('developer_resumes')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('url', 255)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->json('technologies')->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('resume_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_portfolio');
    }
};
