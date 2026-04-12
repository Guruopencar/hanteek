<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('user_profiles', function (Blueprint $table) {
			
			$table->string('facebook', 255)->nullable()->after('github');
			$table->string('instagram', 255)->nullable()->after('facebook');
			$table->string('whatsapp', 100)->nullable()->after('instagram');
			$table->string('viber', 100)->nullable()->after('whatsapp');
		});
	}

	public function down(): void
	{
		Schema::table('user_profiles', function (Blueprint $table) {
			$table->dropColumn(['bio','facebook','instagram','whatsapp','viber']);
		});
	}
};
