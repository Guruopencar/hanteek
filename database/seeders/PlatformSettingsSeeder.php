<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Commission
            [
                'key'         => 'commission_rate',
                'value'       => '5',
                'type'        => 'decimal',
                'group'       => 'commission',
                'description' => 'Комісія платформи у відсотках (%)',
            ],
            [
                'key'         => 'min_withdrawal_amount',
                'value'       => '10',
                'type'        => 'decimal',
                'group'       => 'commission',
                'description' => 'Мінімальна сума виведення (USD)',
            ],
            [
                'key'         => 'min_deposit_amount',
                'value'       => '5',
                'type'        => 'decimal',
                'group'       => 'commission',
                'description' => 'Мінімальна сума поповнення (USD)',
            ],

            // Reviews
            [
                'key'         => 'review_deadline_days',
                'value'       => '14',
                'type'        => 'integer',
                'group'       => 'reviews',
                'description' => 'Кількість днів для залишення відгуку після завершення контракту',
            ],
            [
                'key'         => 'review_min_comment_length',
                'value'       => '20',
                'type'        => 'integer',
                'group'       => 'reviews',
                'description' => 'Мінімальна кількість символів у коментарі відгуку',
            ],

            // Referral
            [
                'key'         => 'referral_reward_amount',
                'value'       => '5',
                'type'        => 'decimal',
                'group'       => 'referral',
                'description' => 'Винагорода за реферала (USD)',
            ],
            [
                'key'         => 'referral_reward_on_first_contract',
                'value'       => '1',
                'type'        => 'boolean',
                'group'       => 'referral',
                'description' => 'Нараховувати бонус після першого контракту реферала',
            ],

            // Limits
            [
                'key'         => 'max_active_vacancies_per_project',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'limits',
                'description' => 'Максимальна кількість активних вакансій на один проект',
            ],
            [
                'key'         => 'max_team_members',
                'value'       => '50',
                'type'        => 'integer',
                'group'       => 'limits',
                'description' => 'Максимальна кількість членів команди',
            ],
            [
                'key'         => 'max_tasks_per_contract',
                'value'       => '100',
                'type'        => 'integer',
                'group'       => 'limits',
                'description' => 'Максимальна кількість задач в контракті',
            ],

            // General
            [
                'key'         => 'platform_name',
                'value'       => 'Hunteek',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Назва платформи',
            ],
            [
                'key'         => 'platform_currency',
                'value'       => 'USD',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Основна валюта платформи',
            ],
            [
                'key'         => 'support_email',
                'value'       => 'support@hunteek.com',
                'type'        => 'string',
                'group'       => 'general',
                'description' => 'Email служби підтримки',
            ],
            [
                'key'         => 'pagination_per_page',
                'value'       => '12',
                'type'        => 'integer',
                'group'       => 'general',
                'description' => 'Кількість елементів на сторінці',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('platform_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('✓ Platform Settings створено (' . count($settings) . ' налаштувань)');
    }
}
