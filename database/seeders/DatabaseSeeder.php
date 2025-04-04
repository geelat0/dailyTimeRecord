<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            ShiftSeeder::class,
            // ShiftScheduleSeeder::class,
            AttendanceTypeSeeder::class,
            HolidaySeeder::class,
        ]);
    }
}
