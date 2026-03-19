<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emails = [
            'teacher@example.com',
            'teacher2@example.com',
        ];

        foreach ($emails as $email) {
            $user = User::query()->where('email', $email)->first();

            if (!$user?->profile_id) {
                continue;
            }

            Teacher::query()->updateOrCreate(
                ['profile_id' => $user->profile_id],
                ['status' => 'active']
            );
        }
    }
}
