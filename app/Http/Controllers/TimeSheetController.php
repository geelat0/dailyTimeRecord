<?php

namespace App\Http\Controllers;

use App\DataTables\TimeSheetDataTable;
use App\Http\Requests\TimeEntryRequest;
use App\Models\AttendanceType;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\TimeEntry;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;

class TimeSheetController extends Controller
{
    public function index(Request $request, TimeSheetDataTable $dataTable)
    {
        if ($request->wantsJson()) {
            return $dataTable->ajax(); // Return JSON data for Vue
        }
        return Inertia::render('DTR/TimeSheet', [
            'dataTable' => $dataTable->html() // Render the dataTable to DTR/TimeSheet
        ]);
    }

    public function computeTimes($timeEntry){

        $shift = null;
        if ($timeEntry->shift_schedule_id) {
            $shiftSchedule = ShiftSchedule::find($timeEntry->shift_schedule_id);
            $shift = Shift::find($shiftSchedule->shift_id);
        }

        if (!$shift) {
            $entryDate = Carbon::parse($timeEntry->date ?? $timeEntry->temp_date);
            $dayOfWeek = $entryDate->dayOfWeek;

            if ($dayOfWeek == Carbon::MONDAY) {
                $shift = (object) [
                    'am_time_in' => '07:00:00',
                    'am_time_out' => '12:00:00',
                    'pm_time_in' => '13:00:00',
                    'pm_time_out' => '17:00:00',
                    'am_late_threshold' => '08:00:00',
                    'pm_late_threshold' => '13:00:00',
                    'is_flexi_schedule' => false,
                ];
            } else {
                $shift = (object) [
                    'am_time_in' => '07:00:00',
                    'am_time_out' => '12:00:00',
                    'pm_time_in' => '13:00:00',
                    'pm_time_out' => '17:00:00',
                    'am_late_threshold' => '08:30:00',
                    'pm_late_threshold' => '13:00:00',
                    'is_flexi_schedule' => true,
                ];
            }
        }

        if($shift){
            $actual1stHalfTimeIn = $timeEntry->am_time_in; 
            $actual1stHalfTimeOut = $timeEntry->am_time_out; 
            $actual2ndHalfTimeIn = $timeEntry->pm_time_in;
            $actual2ndHalfTimeOut = $timeEntry->pm_time_out ;

            // Expected times
            $expected1stHalfTimeIn = $shift->am_time_in ? Carbon::parse($shift->am_time_in) : null;
            $expected1stHalfTimeOut = $shift->am_time_out ? Carbon::parse($shift->am_time_out) : null;
            $expected2ndHalfTimeIn = $shift->pm_time_in ? Carbon::parse($shift->pm_time_in) : null;
            $expected2ndHalfTimeOut = $shift->pm_time_out ? Carbon::parse($shift->pm_time_out) : null;
            $lateThreshold = $shift->am_late_threshold ? Carbon::parse($shift->am_late_threshold) : null;
            $PMlateThreshold = $shift->pm_late_threshold ? Carbon::parse($shift->pm_late_threshold) : null;
            $flexibleSchedule = $shift->is_flexi_schedule;

        }


        // Use the date from the actual first half time in
        if($actual1stHalfTimeIn){
            $specificDate = new DateTime(explode(' ', $actual1stHalfTimeIn)[0]);
        }else{
            $specificDate = new DateTime(explode(' ', $actual2ndHalfTimeIn)[0]);
        }

        // Set the expected times to the same specific date
        $expected1stHalfTimeIn->setDate((int)$specificDate->format('Y'), (int)$specificDate->format('m'), (int)$specificDate->format('d'));
        $expected1stHalfTimeOut->setDate((int)$specificDate->format('Y'), (int)$specificDate->format('m'), (int)$specificDate->format('d'));
        $expected2ndHalfTimeIn->setDate((int)$specificDate->format('Y'), (int)$specificDate->format('m'), (int)$specificDate->format('d'));
        $expected2ndHalfTimeOut->setDate((int)$specificDate->format('Y'), (int)$specificDate->format('m'), (int)$specificDate->format('d'));
        $lateThreshold->setDate((int)$specificDate->format('Y'), (int)$specificDate->format('m'), (int)$specificDate->format('d'));


        // Convert actual times to DateTime objects if provided
        if ($actual1stHalfTimeIn && $actual1stHalfTimeOut) {
            $actual1stHalfTimeIn = new DateTime($actual1stHalfTimeIn);
            $actual1stHalfTimeOut = new DateTime($actual1stHalfTimeOut);
            
        }
        $firstHalfDateTimeIn = $actual1stHalfTimeIn ? $actual1stHalfTimeIn->format('Y-m-d') : null;
       
        if ($actual2ndHalfTimeIn && $actual2ndHalfTimeOut) {
            $actual2ndHalfTimeIn = new DateTime($actual2ndHalfTimeIn);
            $actual2ndHalfTimeOut = new DateTime($actual2ndHalfTimeOut);
        }
        $firstHalfDateTimeOut = $actual1stHalfTimeOut ? $actual1stHalfTimeOut->format('Y-m-d') : null;
        
        // Determine if the timelog is overnight
        if ($firstHalfDateTimeOut > $firstHalfDateTimeIn) {
            $expected1stHalfTimeOut->modify('+1 day');
            $expected2ndHalfTimeIn->modify('+1 day');
            $expected2ndHalfTimeOut->modify('+1 day');
        }

        // Calculate total break minutes between first and second half
        $intervalBreak = $expected1stHalfTimeOut->diff($expected2ndHalfTimeIn);
        $breakMinutes = max(0, ($intervalBreak->days * 24 * 60) + ($intervalBreak->h * 60) + $intervalBreak->i);
        $totalRenderingMinutes = 480 + $breakMinutes; // 8 hours work + break time
        // Initialize variables
        $firstHalfLateTime = 0;
        $firstHalfUnderTime = 0;
        $secondHalfLateTime = 0;
        $secondHalfUnderTime = 0;
        $excessTimeBeyondEndTime = 0;
        $lengthOfWork = 0;

        // 1st half: Late time computation
        if ($actual1stHalfTimeIn > $lateThreshold) {
            $intervalFirstHalfLate = $actual1stHalfTimeIn->diff($lateThreshold);
            $firstHalfLateTime = max(0, ($intervalFirstHalfLate->days * 24 * 60) + ($intervalFirstHalfLate->h * 60) + $intervalFirstHalfLate->i);
        }


        if($actual1stHalfTimeOut){
            $actual1stHalfTimeOut = Carbon::instance($actual1stHalfTimeOut); // Convert DateTime to Carbon
            $expected1stHalfTimeOut = Carbon::instance($expected1stHalfTimeOut); // Ensure it's Carbon
        }
       
        // 2nd half: Late time computation
        if ($actual2ndHalfTimeIn > $expected2ndHalfTimeIn) {
            $intervalSecondHalfLate = $expected2ndHalfTimeIn->diff($actual2ndHalfTimeIn);
            $secondHalfLateTime = max(0, ($intervalSecondHalfLate->days * 24 * 60) + ($intervalSecondHalfLate->h * 60) + $intervalSecondHalfLate->i);
        }

        // 1st half: Under time computation
        if ($actual1stHalfTimeOut && $actual1stHalfTimeOut < $expected1stHalfTimeOut) {
            $intervalFirstHalfUnder = $actual1stHalfTimeOut->diff($expected1stHalfTimeOut);
            $firstHalfUnderTime = max(0, ($intervalFirstHalfUnder->days * 24 * 60) + ($intervalFirstHalfUnder->h * 60) + $intervalFirstHalfUnder->i);
        }

        // 2nd half: Under time computation based on schedule type
        if($actual2ndHalfTimeOut){
            $actual2ndHalfTimeOut = Carbon::instance($actual2ndHalfTimeOut); // Convert DateTime to Carbon
            $expected2ndHalfTimeOut = Carbon::instance($expected2ndHalfTimeOut); // Ensure it's Carbon
        }
        if ($flexibleSchedule) {
            $expectedTimeout = $actual1stHalfTimeIn ? (clone $actual1stHalfTimeIn)->add(new DateInterval("PT" . ($totalRenderingMinutes) . "M")) : (clone $expected2ndHalfTimeIn)->add(new DateInterval("PT" . (($totalRenderingMinutes / 2) - 30) . "M"));
            // $expectedTimeout = (clone $actual1stHalfTimeIn)->add(new DateInterval("PT" . ($totalRenderingMinutes) . "M"));
            // dd($expectedTimeout);
            if ($actual2ndHalfTimeOut && $actual2ndHalfTimeOut < $expectedTimeout) {
                $interval2ndHalfUnder = $actual2ndHalfTimeOut->diff($expectedTimeout);
                $secondHalfUnderTime = max(0, ($interval2ndHalfUnder->days * 24 * 60) + ($interval2ndHalfUnder->h * 60) + $interval2ndHalfUnder->i);
            }

        } else {
            if ($actual2ndHalfTimeOut && $actual2ndHalfTimeOut < $expected2ndHalfTimeOut) {
                $interval2ndHalfUnder = $actual2ndHalfTimeOut->diff($expected2ndHalfTimeOut);
                $secondHalfUnderTime = max(0, ($interval2ndHalfUnder->days * 24 * 60) + ($interval2ndHalfUnder->h * 60) + $interval2ndHalfUnder->i);
            }
        }

        $totalLateMinutes = $firstHalfLateTime + $secondHalfLateTime;
        $lateHours = intdiv($totalLateMinutes, 60);
        $lateMinutes = $totalLateMinutes % 60;
        $timeEntry->late_hours = sprintf('%02d:%02d', $lateHours, $lateMinutes);

        $totalUnderMinutes = $firstHalfUnderTime +  $secondHalfUnderTime;
        $underTimeHours = intdiv($totalUnderMinutes, 60);
        $underTimeMinutes = $totalUnderMinutes % 60;
        $timeEntry->undertime_minutes = sprintf('%02d:%02d', $underTimeHours, $underTimeMinutes);
       
        // Calculate excess time beyond end time based on schedule type
        if ($flexibleSchedule) {
            $expectedTimeout = $actual1stHalfTimeIn ? (clone $actual1stHalfTimeIn)->add(new DateInterval("PT" . ($totalRenderingMinutes) . "M")) : (clone $expected2ndHalfTimeIn)->add(new DateInterval("PT" . (($totalRenderingMinutes / 2) - 30) . "M"));
            // $expectedTimeout = (clone $actual1stHalfTimeIn)->add(new DateInterval("PT" . ($totalRenderingMinutes) . "M"));

            if ($actual2ndHalfTimeOut > $expectedTimeout) {
                $intervalExcessTimeBeyondEndTime = $expectedTimeout->diff($actual2ndHalfTimeOut);
                $excessTimeBeyondEndTime = max(0, ($intervalExcessTimeBeyondEndTime->days * 24 * 60) + ($intervalExcessTimeBeyondEndTime->h * 60) + $intervalExcessTimeBeyondEndTime->i);
            }
        } else {
            if ($actual2ndHalfTimeOut > $expected2ndHalfTimeOut) {
                $intervalExcessTimeBeyondEndTime = $expected2ndHalfTimeOut->diff($actual2ndHalfTimeOut);
                $excessTimeBeyondEndTime = max(0, ($intervalExcessTimeBeyondEndTime->days * 24 * 60) + ($intervalExcessTimeBeyondEndTime->h * 60) + $intervalExcessTimeBeyondEndTime->i);
            }
        }

        $excessTimeHours = intdiv($excessTimeBeyondEndTime, 60);
        $excessTimeMinutes = $excessTimeBeyondEndTime % 60;
        $timeEntry->excess_minutes = sprintf('%02d:%02d', $excessTimeHours, $excessTimeMinutes);

        // Calculate length of work in minutes
        if ($flexibleSchedule) {
            $adjusted1stHalfTimeIn = $actual1stHalfTimeIn;
            if ($actual1stHalfTimeIn < $expected1stHalfTimeIn) {
                $adjusted1stHalfTimeIn = $expected1stHalfTimeIn;
            }
            $expectedTimeout = $actual1stHalfTimeIn ? (clone $actual1stHalfTimeIn)->add(new DateInterval("PT" . ($totalRenderingMinutes) . "M")) : (clone $expected2ndHalfTimeIn)->add(new DateInterval("PT" . (($totalRenderingMinutes / 2) - 30) . "M"));
            // $expectedTimeout = (clone $actual1stHalfTimeIn)->add(new DateInterval("PT" . ($totalRenderingMinutes) . "M"));
            // $expectedTimeout = new DateTime($expectedTimeout);
            $firstHalfLength = $actual1stHalfTimeOut ? max(0, min($actual1stHalfTimeOut->getTimestamp(), $expected1stHalfTimeOut->getTimestamp()) - max($adjusted1stHalfTimeIn->getTimestamp(), $expected1stHalfTimeIn->getTimestamp())) / 60 : 0;
            $secondHalfLength = $actual2ndHalfTimeOut ? max(0, min($actual2ndHalfTimeOut->getTimestamp(), $expectedTimeout->getTimestamp()) - max($actual2ndHalfTimeIn->getTimestamp(), $expected2ndHalfTimeIn->getTimestamp())) / 60 : 0;
            $lengthOfWork = $firstHalfLength + $secondHalfLength;
        } else {
            $firstHalfLength = $actual1stHalfTimeOut ? max(0, min($actual1stHalfTimeOut->getTimestamp(), $expected1stHalfTimeOut->getTimestamp()) - max($actual1stHalfTimeIn->getTimestamp(), $expected1stHalfTimeIn->getTimestamp())) / 60 : 0;
            $secondHalfLength = $actual2ndHalfTimeOut ? max(0, min($actual2ndHalfTimeOut->getTimestamp(), $expected2ndHalfTimeOut->getTimestamp()) - max($actual2ndHalfTimeIn->getTimestamp(), $expected2ndHalfTimeIn->getTimestamp())) / 60 : 0;
            $lengthOfWork = $firstHalfLength + $secondHalfLength;
        }

        $renderedTimeHours = intdiv($lengthOfWork, 60);
        $renderedTimeMinutes = $lengthOfWork % 60;
        $timeEntry->rendered_hours = sprintf('%02d:%02d', $renderedTimeHours, $renderedTimeMinutes);
        $timeEntry->save();
        
        return [
            'first_half_late_time' => $firstHalfLateTime,
            'first_half_under_time' => $firstHalfUnderTime,
            'second_half_late_time' => $secondHalfLateTime,
            'second_half_under_time' => $secondHalfUnderTime,
            'rendered_minutes' => $lengthOfWork,
            'late_minutes' => $firstHalfLateTime + $secondHalfLateTime,
            'undertime_minutes' => $firstHalfUnderTime + $secondHalfUnderTime,
            'excess_minutes' => $excessTimeBeyondEndTime,
        ];
    }
 
    public function computeTimeEntries()
    {
        $timeEntries = TimeEntry::TimeEntries();

        foreach ($timeEntries as $timeEntry) {
            $this->computeTimes($timeEntry);
        }

        return response()->json(
            $timeEntries,
            200
        );
    }

    public function updateTimeEntry(TimeEntryRequest $request)
    {

        try {
            $amTimeIn = $request->am_time_in ? Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->am_time_in) : null;
            $amTimeOut = $request->am_time_out ? Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->am_time_out) : null;
            $pmTimeIn = $request->pm_time_in ?  Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->pm_time_in) : null;
            $pmTimeOut = $request->pm_time_out ? Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->pm_time_out) : null;

        // Check if am_time_out is greater than am_time_in and add one day to am_time_out for night shift
            if ($amTimeIn && $amTimeOut && $amTimeOut->lessThan($amTimeIn)) {
                $amTimeOut->addDay();
                if($pmTimeIn && $pmTimeOut ){
                    $pmTimeIn->addDay();
                    $pmTimeOut->addDay();
                }
            }
          
            $timeEntry = TimeEntry::updateOrCreate(
                ['id' => $request->id],
                [
                    'date' => $request->date,
                    'user_id' => 1,
                    'am_time_in' => $amTimeIn ? $amTimeIn->format('Y-m-d H:i:s') : null,
                    'am_time_out' => $amTimeOut ? $amTimeOut->format('Y-m-d H:i:s') : null,
                    'pm_time_in' => $pmTimeIn ? $pmTimeIn->format('Y-m-d H:i:s') : null,
                    'pm_time_out' => $pmTimeOut ? $pmTimeOut->format('Y-m-d H:i:s') : null,
                ]
            );

        $timeEntry->save();
        if($amTimeIn && $amTimeOut || $pmTimeIn && $pmTimeOut){
            $shiftSchedule = ShiftSchedule::getShiftSchedule($timeEntry->date, 1);
            if($shiftSchedule){
                $timeEntry->shift_schedule_id = $shiftSchedule->id;
                $timeEntry->save();
            }
            
            $this->computeTimes($timeEntry);
        }
        return response()->json(
            [
                'message' => 'Time entry updated successfully',
                'data' => $timeEntry
            ],
            200
        );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'An error occurred while updating the time entry.',
                    'error' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function getAttendanceType()
    {
        $attendanceType = AttendanceType::getAttendanceType();
        return $attendanceType;
    }

    public function getTimeEntryByID(Request $request){
        $timeEntry = TimeEntry::TimeEntriesByID($request->id);
        return response()->json(
            $timeEntry,
            200
        );
    }

    public function computeRenderedHoursPerCutOff(Request $request){
        $cutOffOne = 15;
        $cutOffTwo = 31;
        $renderedHours = 0;
        $totalRenderedHours = 0;
        $timeEntries = TimeEntry::TimeEntries();
        $timeEntries = $timeEntries->sortBy('date');
        dd($timeEntries);
    

    }
}
