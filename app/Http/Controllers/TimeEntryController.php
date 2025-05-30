<?php

namespace App\Http\Controllers;

use App\DataTables\TimeSheetDataTable;
use App\Models\ShiftSchedule;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Controller for handling time entry operations
 */
class TimeEntryController extends Controller
{
    /**
     * Updates the morning time-in entry for the current user
     * Creates a new time entry if none exists for the current date
     * 
     * @param Request $request The HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTimeIn(Request $request)
    {
        $timeEntry = TimeEntry::getTimeEntries();
        if ($timeEntry) {
            $timeEntry->am_time_in = now();
            $timeEntry->date = now();
        }else{
            $timeEntry = New TimeEntry();
            $timeEntry->user_id = Auth::user()->id;
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

    /**
     * Updates the morning time-out entry for a specific time entry
     * 
     * @param Request $request The HTTP request containing the time entry ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTimeOut(Request $request)
    {
        $timeEntry = TimeEntry::getTimeEntries();
        if ($timeEntry) {
            $timeEntry->am_time_out = now();
            $timeEntry->date = now();
        } else {
            $timeEntry = new TimeEntry();
            $timeEntry->user_id = Auth::user()->id;
            $timeEntry->am_time_out = now();
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

    /**
     * Updates the afternoon time-in entry for the current user
     * Creates a new time entry if none exists for the current date
     * 
     * @param Request $request The HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTimeInPM(Request $request)
    {
        $timeEntry = TimeEntry::getTimeEntries();
        if ($timeEntry) {
            $timeEntry->pm_time_in = now();
            $timeEntry->date = now();
        } else {
            $timeEntry = new TimeEntry();
            $timeEntry->user_id = Auth::user()->id;
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

    /**
     * Updates the afternoon time-out entry for a specific time entry
     * 
     * @param Request $request The HTTP request containing the time entry ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTimeOutPM(Request $request)
    {
        $timeEntry = TimeEntry::getTimeEntries();
        if ($timeEntry) {
            $timeEntry->pm_time_out = now();
            $timeEntry->date = now();
        } else {
            $timeEntry = new TimeEntry();
            $timeEntry->user_id = Auth::user()->id;
            $timeEntry->pm_time_out = now();
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

    /**
     * Retrieves today's time entries for the current user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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
