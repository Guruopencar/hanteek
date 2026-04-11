<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('avatar', 500)->nullable();
            $table->text('bio')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('telegram', 100)->nullable();
            $table->string('linkedin', 255)->nullable();
            $table->string('github', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('contracts_count')->default(0);
            $table->timestamps();

            $table->index('average_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
