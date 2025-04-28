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
            ['type' => 'Regular Attendance', 'code' => 'RA', 'default_rendered_hours' => '08:00:00'],
            ['type' => 'OB (Official Business)', 'code' => 'OB', 'default_rendered_hours' => '08:00:00'],
            ['type' => 'OB-AM (Official Business - AM)', 'code' => 'OBAM', 'default_rendered_hours' => '04:00:00'],
            ['type' => 'OB-PM (Official Business - PM)', 'code' => 'OBPM', 'default_rendered_hours' => '04:00:00'],
            ['type' => 'Holiday', 'code' => 'HLDY', 'default_rendered_hours' => '08:00:00'],
            ['type' => 'Leave', 'code' => 'L', 'default_rendered_hours' => '08:00:00'],
            
            // ['type' => 'Leave AM (Leave - AM)', 'code' => 'LAM'],
            // ['type' => 'Leave PM (Leave - PM)', 'code' => 'LPM'],
            ['type' => 'CDO (CDO)', 'code' => 'CDO', 'default_rendered_hours' => '08:00:00'],
            // ['type' => 'CDO-AM (Compensatory Day Off - AM)', 'code' => 'CDOAM'],
            // ['type' => 'CDO-PM (Compensatory Day Off - PM)', 'code' => 'CDOPM'],
            ['type' => 'Work From Home', 'code' => 'WFH', 'default_rendered_hours' => '08:00:00'],
            ['type' => 'Work Suspension', 'code' => 'WS', 'default_rendered_hours' => '00:00:00'],
            ['type' => 'Weekend/Day off', 'code' => 'WDO', 'default_rendered_hours' => '00:00:00'],
            ['type' => 'Wellness Day', 'code' => 'WD', 'default_rendered_hours' => '08:00:00'],


        ];

        foreach ($attendanceTypes as $type) {
            AttendanceType::updateOrCreate(
                ['type' => $type['type']],
                [
                    'code' => $type['code'],
                    'default_rendered_hours' => $type['default_rendered_hours']
                ]
            );
        }
    }
}
