<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recruiter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name', 200)->nullable();
            $table->string('company_website', 255)->nullable();
            $table->enum('company_size', ['1-10', '11-50', '51-200', '201-500', '500+'])->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('position', 200)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_profiles');
    }
};
