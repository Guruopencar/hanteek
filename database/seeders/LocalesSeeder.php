<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalesSeeder extends Seeder
{
    public function run(): void
    {
        $locales = [
            [
                'code'        => 'uk',
                'name'        => 'Українська',
                'native_name' => 'Українська',
                'flag_emoji'  => '🇺🇦',
                'is_active'   => true,
                'is_default'  => true,
                'sort_order'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'en',
                'name'        => 'English',
                'native_name' => 'English',
                'flag_emoji'  => '🇬🇧',
                'is_active'   => true,
                'is_default'  => false,
                'sort_order'  => 2,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($locales as $locale) {
            DB::table('locales')->updateOrInsert(['code' => $locale['code']], $locale);
        }

        $this->command->info('✓ Locales створено (uk, en)');
    }
}
