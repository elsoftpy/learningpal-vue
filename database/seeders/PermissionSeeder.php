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
            'show reports menu',
        ];

        foreach ($menuPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $usersPermissions = [
            'view users',
            'create users',
            'edit users',
            'edit profiles',
            'change user status',
            'change roles',
            'delete users',
            'edit own profile',
            'view profile data',
            'view id columns',
        ];

        foreach ($usersPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $languagesPermissions = [
            'view languages',
            'create languages',
            'edit languages',
            'delete languages',
        ];

        foreach ($languagesPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $academicSettingsPermissions = [
            'view language levels',
            'create language levels',
            'edit language levels',
            'delete language levels',
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
            'view teachers',
            'create teachers',
            'edit teachers',
            'delete teachers',
            'view students',
            'view all students',
            'create students',
            'edit students',
            'delete students',
            'reschedule class',
            'view level contents',
            'create level contents',
            'edit level contents',
            'delete level contents',
            'view study programs',
            'create study programs',
            'edit study programs',
            'edit study program week',
            'delete study program week',
            'edit study program week activity',
            'delete study program week activity',
            'delete study programs',
        ];

        foreach ($academicSettingsPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $academicLessonsPermissions = [
            'view class schedules',
            'create class schedules',
            'edit class schedules',
            'delete class schedules',
            'view schedule feedback',
            'create schedule feedback',
            'edit schedule feedback',
            'delete schedule feedback', 
            'view classes',
            'create classes',
            'edit classes',
            'delete classes',
            'view class schedule details',
            'edit class schedule details',
            'change schedule detail status',
            'delete class schedule details',
            'confirm class reprogramming',
            'view class records',
            'create class records',
            'edit class records',
            'delete class records',
            'view monthly classes report',
            'view teacher hours report',
            'list other teachers',
            'view assigned distance activities',
            'view own distance activities',
            'view all distance activities',
            'view distance activity teacher and course columns',
            'complete own distance activity tasks',
            'upload own distance activity production',
            'upload own class record production',
            'reset distance activity completion',
            'delete distance activity submissions',
        ];

        foreach ($academicLessonsPermissions as $permission) {
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
            'view class records',
            'upload own class record production',
            'view assigned distance activities',
            'complete own distance activity tasks',
            'upload own distance activity production',
        ]);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->revokePermissionTo(Permission::all());
        $teacher->givePermissionTo([
            'edit own profile',
            // academic
            'show academic menu',
            // academic settings
            'show academic settings menu',
            'view language levels',
            'view class schedules',
            'view schedule feedback',
            /* 'create schedule feedback',
            'edit schedule feedback',
            'delete schedule feedback', */
            'view students',
            // academic classes
            'show academic classes menu',
            'create class schedules',
            'edit class schedules',
            'view class schedule details',
            'edit class schedule details',
            /*'delete class schedule details',
            'confirm class reprogramming', */
            'view class records',
            'create class records',
            'view own distance activities',
            'view distance activity teacher and course columns',
/*             'create class records',
            'edit class records',
            'delete class records', */

        ]);

        $annualStudent = Role::firstOrCreate(['name' => 'annual_student']);
        $annualStudent->revokePermissionTo(Permission::all());
        $annualStudent->givePermissionTo([
            // system
            'edit own profile',
            // academic
            'show academic menu',
            // academic classes
            'show academic classes menu',
            'reschedule class',
            'view class records',
            'view assigned distance activities',
            'complete own distance activity tasks',
            'upload own distance activity production',
            'upload own class record production',
            'view own distance activities',
        ]);
    }
}
