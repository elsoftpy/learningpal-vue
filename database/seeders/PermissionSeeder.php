<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuPermissions = [
            'show system menu',
            'show system settings menu',
            'show academic menu',
            'show academic settings menu',
            'show academic classes menu',
        ];

        foreach ($menuPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $usersPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'edit own profile',
            'view profile data',
        ];

        foreach ($usersPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $academicSettingsPermissions = [
            'view language levels',
            'create language levels',
            'edit language levels',
            'delete language levels',
            'view class schedules',
            'create class schedules',
            'edit class schedules',
            'delete class schedules',
        ];

        foreach ($academicSettingsPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $student = Role::firstOrCreate(['name' => 'student']);
        $student->revokePermissionTo(Permission::all());
        $student->givePermissionTo([
            // system
            'edit own profile',
            // academic
            'show academic menu',
            // academic classes
            'show academic classes menu',
        ]);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->revokePermissionTo(Permission::all());
        $teacher->givePermissionTo([
            // system
            'show system menu',
            'show system settings menu',
            // users
            'view users', 
            'edit own profile',
            // academic
            'show academic menu', 
            // academic settings
            'show academic settings menu',
            'view language levels',
            'view class schedules',
            // academic classes
            'show academic classes menu',
        ]);
    }
}
