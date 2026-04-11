<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.ban', 'users.verify',
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'vacancies.view', 'vacancies.create', 'vacancies.edit', 'vacancies.delete', 'vacancies.apply',
            'contracts.view', 'contracts.create', 'contracts.edit', 'contracts.delete', 'contracts.sign', 'contracts.archive',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.complete',
            'wallet.view', 'wallet.deposit', 'wallet.withdraw', 'wallet.transfer',
            'reviews.create', 'reviews.view', 'reviews.moderate',
            'messages.send', 'messages.view',
            'news.view', 'news.create', 'news.edit', 'news.delete', 'news.publish',
            'support.create', 'support.view', 'support.reply', 'support.manage',
            'admin.access', 'admin.analytics', 'admin.settings', 'admin.translations',
            'admin.feature_flags', 'admin.finance',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Programmer
        $programmer = Role::firstOrCreate(['name' => 'programmer', 'guard_name' => 'web']);
        $programmer->syncPermissions([
            'projects.view',
            'vacancies.view', 'vacancies.apply',
            'contracts.view', 'contracts.sign',
            'tasks.view', 'tasks.complete',
            'wallet.view', 'wallet.withdraw', 'wallet.transfer',
            'reviews.create', 'reviews.view',
            'messages.send', 'messages.view',
            'news.view',
            'support.create', 'support.view',
        ]);

        // Project Owner
        $projectOwner = Role::firstOrCreate(['name' => 'project_owner', 'guard_name' => 'web']);
        $projectOwner->syncPermissions([
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'vacancies.view', 'vacancies.create', 'vacancies.edit', 'vacancies.delete',
            'contracts.view', 'contracts.create', 'contracts.edit', 'contracts.sign', 'contracts.archive',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'wallet.view', 'wallet.deposit', 'wallet.withdraw', 'wallet.transfer',
            'reviews.create', 'reviews.view',
            'messages.send', 'messages.view',
            'news.view',
            'support.create', 'support.view',
        ]);

        // Super Admin — всі права
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $this->command->info('✓ Roles & Permissions створено');
    }
}
