<?php

namespace Database\Seeders;

use App\Enums\ProfileTypeEnum;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profile = Profile::factory()->create();

        $userName = strtolower(str_replace(' ', '', trim($profile->full_name)));

        $student = User::factory()->create([
            'profile_id' => $profile->id,
            'email' => $profile->email,
            'name' => $userName,
            'password' => bcrypt('password123'),
        ]);

        $student->assignRole('student');
    }
}
