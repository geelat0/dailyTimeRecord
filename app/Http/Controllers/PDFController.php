<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Spatie\LaravelPdf\Facades\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFController extends Controller
{


    public function downloadDTR()
    {
        $startDate = '2025-04-01';
        $endDate = '2025-04-15';
        $employeeNo = '1';
        $name = 'John Doe';

    
        $dates = [];
        $currentDate = Carbon::parse($startDate);
    
        // Create an array of all dates with default values
        while ($currentDate->format('Y-m-d') <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d'); // Store full date format for matching
            $dayOfWeek = $currentDate->format('D'); // Get the day of the week (e.g., Sat, Sun)
            $dates[$dateKey] = [
                'date' => $currentDate->format('j'),
                'day' => $currentDate->format('D'),
                'in' => '',
                'break_out' => '',
                'break_in' => '',
                'out' => '',
                'ot_in' => '',
                'ot_out' => '',
                'low' => '',
                'ot' => '',
                'ut' => '',
                'lt' => '',
                'short_break' => '',
                'nd_reg' => '',
                'nd_ot' => '',
                'others' => '',
                'status' =>  '',
                'holidays' => ($dayOfWeek === 'Sat' || $dayOfWeek === 'Sun') ? 'Rest Day' : '',
            ];
            $currentDate->addDay();
        }
    
        // Fetch existing records from the database
        $records = TimeEntry::where('user_id', $employeeNo)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($entry)  use(&$totalRenderedMinutes) {
 
                $status = ($entry->am_time_in && $entry->am_time_out || $entry->pm_time_in && $entry->pm_time_out) ? '' : 'Absent';

                return [
                    Carbon::parse($entry->date)->format('Y-m-d') => [
                        'date' => Carbon::parse($entry->date)->format('j'),
                        'day' => Carbon::parse($entry->date)->format('D'),
                        'in' => $entry->am_time_in ? date('h:i A', strtotime($entry->am_time_in)) : '',
                        'break_out' => $entry->am_time_out ? date('h:i A', strtotime($entry->am_time_out)) : '',
                        'break_in' => $entry->pm_time_in ? date('h:i A', strtotime($entry->pm_time_in)) : '',
                        'out' => $entry->pm_time_out ? date('h:i A', strtotime($entry->pm_time_out)) : '',
                        'ot_in' => $entry->ot_in ?? '',
                        'ot_out' => $entry->ot_out ?? '',
                        'low' => $entry->rendered_hours ? date('h:i', strtotime($entry->rendered_hours)) : '',
                        'ot' => $entry->excess_minutes ? date('H:i', strtotime($entry->excess_minutes)) : '',
                        'ut' => $entry->undertime_minutes ? date('H:i', strtotime($entry->undertime_minutes)) : '',
                        'lt' => $entry->late_hours ? date('H:i', strtotime($entry->late_hours)) : '',
                        'short_break' => $entry->short_break ?? '',
                        'nd_reg' => $entry->nd_reg ?? '',
                        'nd_ot' => $entry->nd_ot ?? '',
                        'others' => $entry->others ?? '',
                        'status' => $status,
                        'holidays' => $entry->holidays ?? '',
                    ],
                ];
            });
    
        // Remove temp dates that already exist in $records
        foreach ($records as $recordDate => $record) {
            unset($dates[$recordDate]); // Removes the date if it has actual records
        }
    
        // Merge missing dates with actual records
        $finalRecords = collect($records)->merge($dates)->sortKeys()->values();

        $lenghtOfWork = TimeEntry::where('user_id', $employeeNo)
        ->where('rendered_hours', '!=', null)
        ->whereBetween('date', [$startDate, $endDate])
        ->pluck('rendered_hours')
        ->map(function ($time) {
            // Convert rendered_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();

        $late = TimeEntry::where('user_id', $employeeNo)
        ->where('late_hours', '!=', null)
        ->whereBetween('date', [$startDate, $endDate])
        ->pluck('late_hours')
        ->map(function ($time) {
            // Convert late_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();
        $undertime = TimeEntry::where('user_id', $employeeNo)
        ->where('undertime_minutes', '!=', null)    
        ->whereBetween('date', [$startDate, $endDate])
        ->pluck('undertime_minutes')
        ->map(function ($time) {
            // Convert late_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();

        $overtime = TimeEntry::where('user_id', $employeeNo)    
        ->where('excess_minutes', '!=', null)
        ->whereBetween('date', [$startDate, $endDate])
        ->pluck('excess_minutes')
        ->map(function ($time) {
            // Convert late_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();

        $data = [
            'start' => $startDate,
            'end' => $endDate,
            'employee_no' => $employeeNo,
            'name' => $name,
            'records' => $finalRecords,
            'lengthOfWork' => $lenghtOfWork,
            'late' => $late,
            'undertime' => $undertime,
            'overtime' => $overtime,
        ];
    
        return Pdf::view('dtr', $data)
            ->format('a4')
            ->name('Daily_Time_Record.pdf');
    }


}
