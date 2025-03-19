<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = DB::table('shift')->get();
        $shift_ids = DB::table('shift')->pluck('id')->toArray();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        foreach ($shifts as $shift) {
            $current_date = $startOfMonth->copy();
            while ($current_date->lte($endOfMonth)) {
                $start_date = $current_date->copy()->addDay(); // Start the shift on the next day
                $end_date = $start_date->copy()->addWeek(); // Adjust end_date to be the same as start_date plus the duration of the shift

                // Check if a schedule already exists for the given date range
                $existingSchedule = DB::table('shift_schedule')
                    ->where('start_date', $start_date)
                    ->where('end_date', $end_date)
                    ->first();

                if (!$existingSchedule) {
                    DB::table('shift_schedule')->insert([
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'shift_id' => $shift_ids[array_rand($shift_ids)],
                        'user_id' => 1, // Use 1 as the user ID
                        'remarks' => '',
                        'status' => 'For Approval',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Set the current_date to the end_date for the next iteration
                $current_date = $end_date->copy();
            }
        }
    
    }
}
