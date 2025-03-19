<?php

namespace App\Http\Controllers;

use App\DataTables\SettingDataTable;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\ShiftScheduleRequest;
use App\Models\TimeEntry;

class SettingsController extends Controller
{
    public function index(Request $request, SettingDataTable $dataTable)
    {
        if ($request->wantsJson()) {
            return $dataTable->ajax();
        }
        return Inertia::render('DTR/Settings', [
            'dataTable' => $dataTable->html()
        ]);
    }

    public function getShift(){
        $shiftSchedules = Shift::where('shift_name', '!=', 'Custom Schedule')->get();
        return response()->json($shiftSchedules);
    }
    
    function reComputeShiftSchedule($startDate, $endDate, $user_id){
        $timeEntries = TimeEntry::whereBetween('date', [$startDate, $endDate])
            ->whereBetween('end_date', [$startDate, $endDate])
            ->where('user_id', $user_id)
            ->get();

        foreach ($timeEntries as $entry) {
            $controller = new TimeSheetController();
            $controller->computeTimes($entry);
        }
    }

    public function store(ShiftScheduleRequest $request)
    {
        try {
            $validated = $request->validated();
            // If custom schedule (shift_id = 4), create a new shift first
            if ($validated['shift_id'] == 4) {

                $shift = Shift::create([
                    'shift_name' => 'Custom Schedule',
                    'am_time_in' => $validated['am_time_in'],
                    'am_time_out' => $validated['am_time_out'],
                    'pm_time_in' => $validated['pm_time_in'],
                    'pm_time_out' => $validated['pm_time_out'],
                    'is_flexi_schedule' => true,
                    'am_late_threshold' => $validated['am_time_in'],
                    'pm_late_threshold' => $validated['pm_time_in']
                ]);
                $validated['shift_id'] = $shift->id;
            }

            // Create the shift schedule
            $schedule = ShiftSchedule::create([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'user_id' => 1,
                'shift_id' => $validated['shift_id'],
                'remarks' => $validated['remarks'],
                'status' => 'For Approval'
            ]);

            $this->reComputeShiftSchedule($validated['start_date'], $validated['end_date'], 1);

            return response()->json([
                'message' => 'Schedule change request submitted successfully',
                'data' => $schedule
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit schedule change request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
