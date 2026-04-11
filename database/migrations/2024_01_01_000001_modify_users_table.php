<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('role', ['programmer', 'project_owner', 'super_admin'])->after('password');
            $table->enum('active_profile', ['programmer', 'recruiter'])->nullable()->after('role');
            $table->string('locale', 5)->default('uk')->after('active_profile');
            $table->boolean('is_verified')->default(false)->after('locale');
            $table->boolean('is_active')->default(true)->after('is_verified');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('referral_code', 20)->unique()->after('last_login_at');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'role', 'active_profile', 'locale',
                'is_verified', 'is_active', 'last_login_at',
                'referral_code', 'referred_by', 'deleted_at',
            ]);
        });
    }
};
