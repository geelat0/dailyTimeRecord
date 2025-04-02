<?php

namespace App\Http\Controllers;

use App\DataTables\AttachmentsDataTable;
use App\Http\Requests\ApprovedAttendanceRequest;
use App\Models\ApproveAttendance;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ApprovedAttendanceController extends Controller
{
    public function index(Request $request, AttachmentsDataTable $dataTable)
    {
        if ($request->wantsJson()) {
            return $dataTable->ajax(); // Return JSON data for Vue
        }
        return Inertia::render('DTR/AttachmentHistory', [
            'dataTable' => $dataTable->html() // Render the dataTable to DTR/TimeSheet
        ]);
    }

    /**
     * Store a newly created attendance in storage.
     */
    public function store(ApprovedAttendanceRequest $request)
    { 
        
        try{
            $validated = $request->validated();
            $startDates = $request->start_date;
            $endDates = $request->end_date; 
            $files = $request->file('file');
            $attendances = [];

    
            foreach ($startDates as $index => $startDate) {
                $endDate = $endDates[$index];

                // Process the dates
                $startDateTime = \Carbon\Carbon::parse($startDate);
                $endDateTime = \Carbon\Carbon::parse($endDate);
                
                // Get the date range
                $dateRange = [];
                while ($startDateTime <= $endDateTime) {
                    $dateRange[] = $startDateTime->format('Y-m-d');
                    $startDateTime->addDay();
                }

                $fileDetails = [];
                if ($files) {
                    foreach ($dateRange as $date) {
                        foreach ($files as $file) {
                            $path = $file->store('uploads', 's3');
                            $fileName = basename($path);
                            $fileDetails[] = [
                                'file_name' => $file->getClientOriginalName(),
                                'file' => str_replace('.pdf', '', $fileName) . Str::random(10),
                                'date' => $date
                            ];
                        }
                    }
                }

                // Group files by date
                $groupedFiles = [];
                foreach ($fileDetails as $fileDetail) {
                    $date = $fileDetail['date'];
                    if (!isset($groupedFiles[$date])) {
                        $groupedFiles[$date] = [];
                    }
                    $groupedFiles[$date][] = [
                        'file_name' => $fileDetail['file_name'],
                        'file' => $fileDetail['file']
                    ];
                }

                // Ensure each date in the range has an entry
                foreach ($dateRange as $date) {
                    if (!isset($groupedFiles[$date])) {
                        $groupedFiles[$date] = [];
                    }
                }

                // Convert to final format
                $finalFileDetails = [];
                foreach ($groupedFiles as $date => $files) {
                    $finalFileDetails[] = [
                        'date' => $date,
                        'files' => $files
                    ];
                }

                // Check if there is an existing attendance with overlapping dates
                $existingAttendance = ApproveAttendance::where('user_id', $validated['user_id'])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                        });
                })
                ->first();

                if ($existingAttendance) {
                    // Get existing files and merge with new files
                    $existingFiles = json_decode($existingAttendance->files, true) ?? [];
                    
                    // Convert existing files to grouped format if not already
                    $existingGroupedFiles = [];
                    foreach ($existingFiles as $fileGroup) {
                        $date = $fileGroup['date'];
                        if (!isset($existingGroupedFiles[$date])) {
                            $existingGroupedFiles[$date] = [];
                        }
                        $existingGroupedFiles[$date] = $fileGroup['files'];
                    }

                    // Merge existing files with new files
                    foreach ($groupedFiles as $date => $files) {
                        if (!isset($existingGroupedFiles[$date])) {
                            $existingGroupedFiles[$date] = [];
                        }
                        $existingGroupedFiles[$date] = array_merge($existingGroupedFiles[$date], $files);
                    }

                    // Convert back to final format
                    $finalFileDetails = [];
                    foreach ($existingGroupedFiles as $date => $files) {
                        $finalFileDetails[] = [
                            'date' => $date,
                            'files' => $files
                        ];
                    }
                    
                    $existingAttendance->files = json_encode($finalFileDetails);
                    $existingAttendance->remarks = $validated['remarks'];
                    $existingAttendance->save();
                    $attendance = $existingAttendance;
                    
                } else {
                    $attendance = new ApproveAttendance();
                    $attendance->user_id = $validated['user_id'];
                    $attendance->start_date = $startDate;
                    $attendance->end_date = $endDate;
                    $attendance->attendance_type = $validated['attendance_type'];
                    $attendance->files = json_encode($finalFileDetails); // Store files as JSON
                    $attendance->remarks = $validated['remarks'];

                }
                $attendance->save();

                // Update time entries with the approved attendance ID
                TimeEntry::whereBetween('date', [$startDate, $endDates[$index]])
                ->where('user_id', $validated['user_id'])
                ->update(['approved_attendance' => $attendance->id]);
    
                $attendances[] = $attendance;

            }
    
            return response()->json(
                [
                    'message' => 'Approved attendance or absence saved successfully',
                    'data' => $attendances
                ],
                200
            );

        }catch(\Exception $e){
            return response()->json(
                [
                    'message' => 'An error occurred while approving attendance.',
                    'error' => $e->getMessage()
                ],
                500
            );
        }
       

    }

    public function download($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), ['jpeg', 'jpg', 'png'])) {
            $path = 'uploads/' . $filename;
        } else {
            $path = 'uploads/' . $filename . '.pdf';
        }
                
        if (!Storage::disk('s3')->exists($path)) {
            abort(404);
        }

        return Storage::disk('s3')->download($path);
    }

}
