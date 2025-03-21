<?php

namespace App\Http\Controllers;

use App\DataTables\TimeSheetDataTable;
use App\Models\ShiftSchedule;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeEntryController extends Controller
{
    public function updateTimeIn(Request $request)
    {
        $timeEntry = TimeEntry::getTimeEntries();
        if ($timeEntry) {
            $timeEntry->am_time_in = now();
            $timeEntry->date = now();
        }else{
            $timeEntry = New TimeEntry();
            $timeEntry->user_id = 1;
            $timeEntry->am_time_in = now();
            $timeEntry->date = now();
        }
        $timeEntry->save();
        $shiftSchedule = ShiftSchedule::getShiftSchedule($timeEntry->date, 1);
        if($shiftSchedule){
            $timeEntry->shift_schedule_id = $shiftSchedule->id;
            $timeEntry->save();
        }
        // $controller = new TimeSheetController();
        // $controller->computeTimes($timeEntry);
        return response()->json(
            $timeEntry,
            200
        );
    }

    public function updateTimeOut(Request $request)
    {
        $timeEntry = TimeEntry::find($request->id);
        $timeEntry->am_time_out = now();
        $timeEntry->date = now();
        $timeEntry->save();
        $shiftSchedule = ShiftSchedule::getShiftSchedule($timeEntry->date, 1);
        if($shiftSchedule){
            $timeEntry->shift_schedule_id = $shiftSchedule->id;
            $timeEntry->save();
        }
        $controller = new TimeSheetController();
        $controller->computeTimes($timeEntry);

        return response()->json(
            $timeEntry,
            200
        );
    }

    public function updateTimeInPM(Request $request)
    {
        $timeEntry = TimeEntry::getTimeEntries();
        if ($timeEntry) {
            $timeEntry->pm_time_in = now();
            $timeEntry->date = now();
        } else {
            $timeEntry = new TimeEntry();
            $timeEntry->user_id = 1;
            $timeEntry->pm_time_in = now();
            $timeEntry->date = now();
        }
        $timeEntry->save();
        $shiftSchedule = ShiftSchedule::getShiftSchedule($timeEntry->date, 1);
        if($shiftSchedule){
            $timeEntry->shift_schedule_id = $shiftSchedule->id;
            $timeEntry->save();
        }

        $controller = new TimeSheetController();
        $controller->computeTimes($timeEntry);
        
        return response()->json(
            $timeEntry,
            200
        );
    }

    public function updateTimeOutPM(Request $request)
    {
        $timeEntry = TimeEntry::find($request->id);
        $timeEntry->pm_time_out = now();
        $timeEntry->date = now();
        $timeEntry->save();

        $shiftSchedule = ShiftSchedule::getShiftSchedule($timeEntry->date, 1);
        if($shiftSchedule){
            $timeEntry->shift_schedule_id = $shiftSchedule->id;
            $timeEntry->save();
        }
        
        $controller = new TimeSheetController();
        $controller->computeTimes($timeEntry);

        return response()->json(
            $timeEntry,
            200
        );
    } 

    public function getUserTimeEntries()
    {
        $timeEntries = TimeEntry::getTimeEntries();

        return response()->json(
            $timeEntries,
            200
        );
    }

    // public function checkShiftSchedule()
    // {
    //     $schedules = ShiftSchedule::getCurrentMonthSchedules();

    //     if ($schedules->isNotEmpty()) {
    //         $dates = collect();

    //         foreach ($schedules as $schedule) {
    //             $scheduleDates = collect(range(
    //                 strtotime($schedule->start_date),
    //                 strtotime($schedule->end_date),
    //                 86400 // 24 hours in seconds
    //             ))->map(function ($timestamp) {
    //                 return date('Y-m-d', $timestamp);
    //             });

    //             $dates = $dates->merge($scheduleDates);

    //             foreach ($scheduleDates as $date) {
    //                 $entry = TimeEntry::where('user_id', 1)
    //                     ->whereDate('date', $date)
    //                     ->first();

    //                 if (!$entry) {
    //                     $entry = new TimeEntry();
    //                     $entry->user_id = 1;
    //                     $entry->date = $date;
    //                     $entry->shift_schedule_id = $schedule->id;
    //                     $entry->save();
    //                 } else {
    //                     $entry->date = $date;
    //                     $entry->shift_schedule_id = $schedule->id;
    //                     $entry->save();
    //                 }

    //                 $controller = new TimeSheetController();
    //                 $controller->computeTimes($entry);
    //             }
    //         }

    //         return response()->json([
    //             'message' => 'Time entries created successfully',
    //             'dates' => $dates->unique()->values()->all()
    //         ], 200);
    //     }

    //     return response()->json([
    //         'message' => 'No shift schedule found'
    //     ], 404);
    // }
}
