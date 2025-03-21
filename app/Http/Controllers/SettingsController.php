<?php

namespace App\Http\Controllers;

use App\DataTables\SettingDataTable;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\ShiftScheduleRequest;
use App\Models\TimeEntry;
use Carbon\Carbon;

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

        // dd($startDate, $endDate, $user_id);

        $shiftSchedules = ShiftSchedule::whereBetween('start_date', [$startDate, $endDate])
            ->whereBetween('end_date', [$startDate, $endDate])
            ->where('user_id', $user_id)
            ->get();

        if($shiftSchedules->count() > 0){
            foreach ($shiftSchedules as $shiftSchedule) {
                $timeEntries = TimeEntry::whereBetween('date', [$shiftSchedule->start_date, $shiftSchedule->end_date])
                    ->where('user_id', $user_id)
                    ->get();

                foreach ($timeEntries as $entry) {
                    if($entry->am_time_in != null && $entry->am_time_out != null || $entry->pm_time_in != null && $entry->pm_time_out != null){
                        $controller = new TimeSheetController();
                        $controller->computeTimes($entry);
                    }
                }
            }
        }else{
            return;
        }
    }

    public function store(ShiftScheduleRequest $request)
    {
        try {
            $validated = $request->validated();

            // If custom schedule (shift_id = 0), create a new shift first
            if ($validated['shift_id'] == 0) {
                    $shift = Shift::create([
                        'shift_name' => 'Custom Schedule',
                        'am_time_in' => $validated['am_time_in'],
                        'am_time_out' => $validated['am_time_out'],
                        'pm_time_in' => $validated['pm_time_in'],
                        'pm_time_out' => $validated['pm_time_out'],
                        'is_flexi_schedule' => false,
                        'am_late_threshold' => $validated['am_time_in'],
                        'pm_late_threshold' => $validated['pm_time_in']
                    ]);
                    $validated['shift_id'] = $shift->id;
                }

            $existingShiftSchedule = ShiftSchedule::where('start_date', $validated['start_date'])
                ->orWhere('end_date', $validated['end_date'])
                ->where('user_id', 1)
                ->first();
            

            if($existingShiftSchedule){
                return response()->json([
                    'message' => 'Schedule already exists',
                    'data' => $existingShiftSchedule
                ], 400);
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


    public function update(Request $request){

        $shiftSchedule = ShiftSchedule::find($request->id);

        if(!$shiftSchedule){
            return response()->json([
                'message' => 'Schedule not found',
            ], 404);
        }

        $checkShift = Shift::where('shift_name', 'Custom Schedule')
        ->where('id', $request->shift_id)
        ->first();

        if($checkShift){
           $checkShift->update([
            'am_time_in' => $request->am_time_in,
            'am_time_out' => $request->am_time_out,
            'pm_time_in' => $request->pm_time_in,
            'pm_time_out' => $request->pm_time_out,
            'is_flexi_schedule' => false,
            'am_late_threshold' => $request->am_time_in,
            'pm_late_threshold' => $request->pm_time_in
           ]);
        }

        $shiftSchedule->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift_id' => $request->shift_id ? $request->shift_id : $checkShift->id,
            'remarks' => $request->remarks
        ]);

        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
        $this->reComputeShiftSchedule($startDate, $endDate, 1);

        return response()->json([
            'message' => 'Schedule updated successfully',
            'data' => $shiftSchedule
        ], 200);
    }
}
