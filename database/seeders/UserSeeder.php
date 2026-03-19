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
                'last_name' => 'One',
            ],
            [
                'role' => 'teacher',
                'email' => 'teacher2@example.com',
                'name' => 'teacher.two',
                'first_name' => 'Teacher',
                'last_name' => 'Two',
            ],
            [
                'role' => 'student',
                'email' => 'student01@example.com',
                'name' => 'student.one',
                'first_name' => 'Student',
                'last_name' => 'One',
            ],
            [
                'role' => 'annual_student',
                'email' => 'student02@example.com',
                'name' => 'student.two',
                'first_name' => 'Student',
                'last_name' => 'Two',
            ],
        ];

        for ($index = 3; $index <= 20; $index++) {
            $users[] = [
                'role' => $index % 2 === 0 ? 'annual_student' : 'student',
                'email' => sprintf('student%02d@example.com', $index),
                'name' => sprintf('student.%02d', $index),
                'first_name' => 'Student',
                'last_name' => (string) $index,
            ];
        }

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
