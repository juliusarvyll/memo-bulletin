<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdmin = Role::create(['name' => 'super_admin']);
        $editor = Role::create(['name' => 'editor']);
        $author = Role::create(['name' => 'author']);
        $viewer = Role::create(['name' => 'viewer']);
        $subscriber = Role::create(['name' => 'subscriber']);

        // Create permissions for memos
        $memoPermissions = [
            'view_any_memo',
            'view_memo',
            'create_memo',
            'update_memo',
            'delete_memo',
            'delete_any_memo',
        ];

        // Create permissions for categories
        $categoryPermissions = [
            'view_any_category',
            'view_category',
            'create_category',
            'update_category',
            'delete_category',
        ];

        // Create permissions for authors
        $authorPermissions = [
            'view_any_author',
            'view_author',
            'create_author',
            'update_author',
            'delete_author',
        ];

        // Create permissions for roles
        $rolePermissions = [
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
        ];

        // Create permissions for subscriber emails
        $subscriberPermissions = [
            'view_any_subscriber_email',
            'view_subscriber_email',
            'create_subscriber_email',
            'update_subscriber_email',
            'delete_subscriber_email',
        ];

        // Create all permissions
        foreach ([
            ...$memoPermissions,
            ...$categoryPermissions,
            ...$authorPermissions,
            ...$rolePermissions,
            ...$subscriberPermissions
        ] as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Filament panel access permissions
        Permission::create(['name' => 'access_filament']);

        // Assign permissions to roles
        $editor->givePermissionTo([
            ...$memoPermissions,
            ...$categoryPermissions,
            'view_any_author',
            'view_author',
            ...$subscriberPermissions, // Editors can manage subscribers
            'access_filament',
        ]);

        $author->givePermissionTo([
            'view_any_memo',
            'view_memo',
            'create_memo',
            'update_memo',
            'delete_memo',
            'view_any_category',
            'view_category',
            'view_any_subscriber_email', // Authors can view subscribers
            'view_subscriber_email',
            'access_filament',
        ]);

        // Viewer only gets public viewing permissions (no admin panel access)
        $viewer->givePermissionTo([
            'view_any_memo',
            'view_memo',
            'view_any_category',
            'view_category',
        ]);

        // Super admin gets everything
        $superAdmin->givePermissionTo(Permission::all());

        // Create a default super admin user
        $defaultAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $defaultAdmin->assignRole('super_admin');

        // Create a test viewer user
        $viewerUser = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
        ]);

        $viewerUser->assignRole('viewer');
    }
}
