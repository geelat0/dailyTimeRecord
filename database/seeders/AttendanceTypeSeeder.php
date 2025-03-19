<?php

namespace Database\Seeders;

use App\Models\AttendanceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendanceTypes = [
            ['type' => 'Regular Attendance', 'code' => 'RA'],
            ['type' => 'OB (Official Business)', 'code' => 'OB'], //
            ['type' => 'OB-AM (Official Business - AM)', 'code' => 'OBAM'],
            ['type' => 'OB-PM (Official Business - PM)', 'code' => 'OBPM'],
            ['type' => 'Holiday', 'code' => 'HLDY'],
            ['type' => 'Leave', 'code' => 'L'],
            // ['type' => 'Leave AM (Leave - AM)', 'code' => 'LAM'],
            // ['type' => 'Leave PM (Leave - PM)', 'code' => 'LPM'],
            ['type' => 'CDO (CDO)', 'code' => 'CDO'], //
            // ['type' => 'CDO-AM (Compensatory Day Off - AM)', 'code' => 'CDOAM'],
            // ['type' => 'CDO-PM (Compensatory Day Off - PM)', 'code' => 'CDOPM'],
            ['type' => 'Work From Home', 'code' => 'WFH'],
            ['type' => 'Work Suspension', 'code' => 'WS'],
            ['type' => 'Weekend/Day off', 'code' => 'WDO'],

        ];

        foreach ($attendanceTypes as $type) {
            AttendanceType::updateOrCreate(
                ['type' => $type['type']],
                ['code' => $type['code']]
            );
        }
    }
}
