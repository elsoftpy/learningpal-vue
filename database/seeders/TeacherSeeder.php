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
        $user = User::query()->where('email', 'teacher@example.com')->first();

        if (!$user?->profile_id) {
            return;
        }

        Teacher::query()->updateOrCreate(
            ['profile_id' => $user->profile_id],
            ['status' => 'active']
        );
    }
}
