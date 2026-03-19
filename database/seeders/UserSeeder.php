<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'role' => 'admin',
                'email' => 'admin@example.com',
                'name' => 'admin.user',
                'first_name' => 'Admin',
                'last_name' => 'User',
            ],
            [
                'role' => 'teacher',
                'email' => 'teacher@example.com',
                'name' => 'teacher.user',
                'first_name' => 'Teacher',
                'last_name' => 'User',
            ],
            [
                'role' => 'student',
                'email' => 'student@example.com',
                'name' => 'student.user',
                'first_name' => 'Student',
                'last_name' => 'User',
            ],
            [
                'role' => 'annual_student',
                'email' => 'annualstudent@example.com',
                'name' => 'annual.student',
                'first_name' => 'Annual',
                'last_name' => 'Student',
            ],
        ];

        foreach ($users as $userData) {
            $profile = Profile::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'type' => 'person',
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'full_name' => trim($userData['first_name'].' '.$userData['last_name']),
                    'email' => $userData['email'],
                ]
            );

            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'profile_id' => $profile->id,
                    'name' => $userData['name'],
                    'password' => bcrypt('password123'),
                    'status' => 'active',
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
