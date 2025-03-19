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
        
        $validated = new ApproveAttendance($request->validated());

        $startDates = $request->start_date;
        $endDates = $request->end_date; 
        $files = $request->file('file');
        // Store the file on S3 and get the file path
   


        $attendances = [];

        foreach ($startDates as $index => $startDate) {
           

            $fileDetails = [];
            foreach ($files as $fileIndex => $file) {
                $path = $file->store('uploads', 's3');
                $fileName = basename($path);
                $fileDetails[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'file' => str_replace('.pdf', '', $fileName) . Str::random(10)

                ];
            }

            $attendance = new ApproveAttendance();
            $attendance->user_id = $validated['user_id'];
            $attendance->start_date = $startDate;
            $attendance->end_date = $endDates[$index];
            $attendance->attendance_type = $validated['attendance_type'];
            $attendance->files = json_encode($fileDetails); // Store files as JSON
            $attendance->remarks = $validated['remarks'];
            $attendance->save();

            // Update time entries with the approved attendance ID
            TimeEntry::whereBetween('date', [$startDate, $endDates[$index]])
            ->where('user_id', $validated['user_id'])
            ->update(['approved_attendance' => $attendance->id]);

            $attendances[] = $attendance;
        }

        return response()->json(
            $attendance,
            200
        );

    }

    public function download($filename)
    {
        $path = 'uploads/' . $filename . '.pdf';
        
        if (!Storage::disk('s3')->exists($path)) {
            abort(404);
        }

        return Storage::disk('s3')->download($path);
    }

    public function getTemporaryFileUrl($fileName)
    {
        // Generate a temporary URL that will expire in 1 hour (3600 seconds)
        $url = Storage::disk('s3')->temporaryUrl(
            $fileName, now()->addMinutes(60)
        );

        return response()->json(['url' => $url]);
    }

    public function getFile($fileName)
    {
        if (Storage::disk('s3')->exists($fileName)) {
            $url = Storage::disk('s3')->url($fileName);
            return response()->json(['url' => $url]);
        } else {
                return response()->json(['error' => 'File not found'], 404);
            }
    }
}
