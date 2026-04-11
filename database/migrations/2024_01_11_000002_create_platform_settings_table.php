<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 200)->unique();
            $table->text('value');
            $table->enum('type', ['string', 'integer', 'decimal', 'boolean', 'json'])->default('string');
            $table->string('group', 100)->default('general');
            $table->string('description', 500)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
