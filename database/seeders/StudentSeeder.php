<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emails = [
            'student@example.com',
            'annualstudent@example.com',
        ];

        foreach ($emails as $email) {
            $user = User::query()->where('email', $email)->first();

            if (!$user?->profile_id) {
                continue;
            }

            Student::query()->updateOrCreate(
                ['profile_id' => $user->profile_id],
                ['status' => 'active']
            );
        }
    }
}
