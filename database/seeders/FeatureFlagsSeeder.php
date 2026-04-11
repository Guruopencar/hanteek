<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureFlagsSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            [
                'key'         => 'chat_enabled',
                'is_enabled'  => true,
                'description' => 'Real-time чат між користувачами (Pusher)',
            ],
            [
                'key'         => 'news_module',
                'is_enabled'  => false,
                'description' => 'Модуль новин (після MVP)',
            ],
            [
                'key'         => 'support_module',
                'is_enabled'  => false,
                'description' => 'Модуль підтримки (після MVP)',
            ],
            [
                'key'         => 'time_material_module',
                'is_enabled'  => false,
                'description' => 'Трекер часу для T&M контрактів (після MVP)',
            ],
            [
                'key'         => 'wallet_real_payments',
                'is_enabled'  => false,
                'description' => 'Реальні платежі (Stripe/LiqPay). false = внутрішній баланс',
            ],
            [
                'key'         => 'push_notifications',
                'is_enabled'  => true,
                'description' => 'Push нотифікації (Pusher Beams для Flutter)',
            ],
            [
                'key'         => 'referral_system',
                'is_enabled'  => true,
                'description' => 'Реферальна система',
            ],
            [
                'key'         => 'email_notifications',
                'is_enabled'  => true,
                'description' => 'Email нотифікації',
            ],
            [
                'key'         => 'registration_open',
                'is_enabled'  => true,
                'description' => 'Відкрита реєстрація на платформі',
            ],
            [
                'key'         => 'maintenance_mode',
                'is_enabled'  => false,
                'description' => 'Режим технічного обслуговування',
            ],
        ];

        foreach ($flags as $flag) {
            DB::table('feature_flags')->updateOrInsert(
                ['key' => $flag['key']],
                array_merge($flag, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('✓ Feature Flags створено (' . count($flags) . ' прапорців)');
    }
}
