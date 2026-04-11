<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5);
            $table->string('group', 100);
            $table->string('key', 300);
            $table->text('value');
            $table->enum('status', ['translated', 'untranslated', 'needs_review'])->default('untranslated');
            $table->timestamps();

            $table->unique(['locale', 'group', 'key']);
            $table->index(['locale', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
