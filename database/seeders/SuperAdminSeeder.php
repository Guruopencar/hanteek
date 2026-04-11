<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@hunteek.com'],
            [
                'name'          => 'Super Admin',
                'email'         => 'admin@hunteek.com',
                'password'      => Hash::make('Admin@Hunteek2024!'),
                'role'          => 'super_admin',
                'locale'        => 'uk',
                'is_verified'   => true,
                'is_active'     => true,
                'referral_code' => Str::random(8),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        // Створити профіль адміна
        \App\Models\UserProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'country'    => 'Ukraine',
                'city'       => 'Lviv',
            ]
        );

        // Створити гаманець адміна
        \App\Models\Wallet::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'balance'  => 0.00,
                'frozen'   => 0.00,
                'currency' => 'USD',
            ]
        );

        $this->command->info('✓ Super Admin створено');
        $this->command->info('  Email: admin@hunteek.com');
        $this->command->info('  Pass:  Admin@Hunteek2024!');
        $this->command->warn('  ⚠ Змініть пароль після першого входу!');
    }
}
