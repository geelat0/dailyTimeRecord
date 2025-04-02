<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = now()->year; // Get the current year
        $country = 'PH'; // Country code for the Philippines

        // Fetch holidays from the Nager.Date API
        $response = Http::get("https://date.nager.at/api/v3/publicholidays/{$year}/{$country}");

        if ($response->successful()) {
            $holidays = collect($response->json())->map(function ($holiday) {
                return [
                    'date' => $holiday['date'],
                    'name' => $holiday['name'],
                    'created_by' => 1,
                ];
            })->toArray();

            // Insert holidays into the database
            DB::table('holidays')->insert($holidays);
        } else {
            $this->command->error('Failed to fetch holidays from the Nager.Date API.');
        }
    }
}
