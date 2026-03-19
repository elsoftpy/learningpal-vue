<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            LanguageSeeder::class,
            LanguageLevelSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            StudyProgramSeeder::class,
            StudyProgramWeekSeeder::class,
            StudyProgramWeekActivitySeeder::class,
            CourseSeeder::class,
            ClassScheduleSeeder::class,
            ClassScheduleDetailSeeder::class,
            ClassRecordSeeder::class,
        ]);
    }
}
