<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            LocalesSeeder::class,
            TranslationsSeeder::class,
            PlatformSettingsSeeder::class,
            FeatureFlagsSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
