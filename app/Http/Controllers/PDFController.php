<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewRequest;
use App\Http\Requests\RequestAttendanceRequest;
use App\Models\ApproveAttendance;
use App\Models\AttendanceType;
use App\Models\RequestDTR;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Spatie\LaravelPdf\Facades\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use GuzzleHttp\Client;

class PDFController extends Controller
{
    /**
     * Generates and downloads a Daily Time Record (DTR) PDF for a specific employee and date range.
     * 
     * 
     * The function processes time entries, calculates total work hours, late hours, undertime, and overtime.
     * It creates a comprehensive DTR report with all necessary time entries and calculations.
     *
     * @return \Spatie\LaravelPdf\Pdf A PDF document containing the DTR information
     */
    public function downloadDTR(PreviewRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('id', $validated['user_id'])->first();
        $employeeNo = $user->id_number;
        $name = $user->full_name;
        
        // Generate signed URL for signature
        $signature = null;
        if ($user->signature_url) {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY')    
                ]
            ]);

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $user->signature_url
            ]);

            $request = $s3Client->createPresignedRequest($cmd, '+60 minutes');
            $signature = (string) $request->getUri();
        }
      

        $dates = [];
        $currentDate = Carbon::parse($validated['start_date']);

        // Create an array of all dates with default values
        while ($currentDate->format('Y-m-d') <= $validated['end_date']) {
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
                'status' => ($dayOfWeek === 'Sat' || $dayOfWeek === 'Sun') ? 'Day Off' : 'Absent',
                'holidays' => ($dayOfWeek === 'Sat' || $dayOfWeek === 'Sun') ? 'Rest Day' : '',
            ];
            $currentDate->addDay();
        }
    
        // Fetch existing records from the database
        $records = TimeEntry::where('user_id', $validated['user_id'])
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->get()
            ->mapWithKeys(function ($entry) use ($dayOfWeek) {

                $approve_attendance = ApproveAttendance::where('id', $entry->approved_attendance)
                ->first();
                
                $attendance_type = null;
                if ($approve_attendance) {
                    $attendance_type = AttendanceType::where('id', $approve_attendance->attendance_type)
                    ->first();
                }
                // $status = ($entry->am_time_in && $entry->am_time_out || $entry->pm_time_in && $entry->pm_time_out) ? ($attendance_type ? $attendance_type->type : '') : 'Absent';

                if($entry->am_time_in && $entry->am_time_out && !$entry->pm_time_in && !$entry->pm_time_out && !$attendance_type){
                    $status = 'Half Day';
                }elseif(!$entry->am_time_in && !$entry->am_time_out && $entry->pm_time_in && $entry->pm_time_out && !$attendance_type){
                    $status = 'Half Day';
                }elseif($attendance_type){
                    $status = $attendance_type ? $attendance_type->type : '';
                }elseif($entry->am_time_in && $entry->am_time_out && $entry->pm_time_in && $entry->pm_time_out && !$attendance_type){
                    $status = '';
                }
                else{
                    $status = 'Absent';
                }


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
                        'low' => $attendance_type ? $attendance_type->default_rendered_hours : $entry->rendered_hours,
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
    
        // Check for approved attendance records for the remaining temp dates
        foreach ($dates as $dateKey => $dateData) {
            // Find if there's an approved attendance record for this date
            $approvedAttendance = ApproveAttendance::where('user_id', $validated['user_id'])
                ->where('start_date', '<=', $dateKey)
                ->where('end_date', '>=', $dateKey)
                ->first();
            
            if ($approvedAttendance) {
                // Get the attendance type
                $attendanceType = AttendanceType::where('id', $approvedAttendance->attendance_type)->first();
                
                if ($attendanceType) {
                    // Update the temp date with attendance type information
                    $dates[$dateKey]['status'] = $attendanceType->type;
                    $dates[$dateKey]['low'] = $attendanceType->default_rendered_hours;
                }
            }
        }
    
        // Merge missing dates with actual records
        $finalRecords = collect($records)->merge($dates)->sortKeys()->values();

        $absence = $finalRecords->where('status', 'Absent')->count();
        $halfDay = $finalRecords->where('status', 'Half Day')->count();
        $absence = $absence * 480;
        $halfDay = $halfDay * 240;
        $totalAbsence = $absence + $halfDay;

        $dayOff = $finalRecords->where('status', 'Day Off')->count();
        $dayOff = $dayOff * 480;

        $leave = $finalRecords->where('status', 'Leave')->count();
        $leave = $leave * 480;

        $lenghtOfWork = $finalRecords->where('status', '!=', 'Absent')->pluck('low');
        $lenghtOfWork = $lenghtOfWork->map(function ($time) {
            // Convert rendered_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = isset($timeParts[0]) ? (int) $timeParts[0] : 0;
            $minutes = isset($timeParts[1]) ? (int) $timeParts[1] : 0;
            return ($hours * 60) + $minutes;
        })->sum();

        $late = TimeEntry::where('user_id', $validated['user_id'])
        ->where('late_hours', '!=', null)
        ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
        ->pluck('late_hours')
        ->map(function ($time) {
            // Convert late_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();
        $undertime = TimeEntry::where('user_id', $validated['user_id'])
        ->where('undertime_minutes', '!=', null)    
        ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
        ->pluck('undertime_minutes')
        ->map(function ($time) {
            // Convert late_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();

        $overtime = TimeEntry::where('user_id', $validated['user_id'])    
        ->where('excess_minutes', '!=', null)
        ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
        ->pluck('excess_minutes')
        ->map(function ($time) {
            // Convert late_hours (e.g., "HH:mm") to total minutes
            $timeParts = explode(':', $time);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            return ($hours * 60) + $minutes;
        })
        ->sum();
        

        $totalLateAndUndertimeandAbsence = $late + $undertime + $absence;

        $data = [
            'start' => $validated['start_date'],  
            'end' => $validated['end_date'],
            'employee_no' => $employeeNo,
            'name' => $name,
            'records' => $finalRecords,
            'lengthOfWork' => $lenghtOfWork,
            'late' => $late,
            'undertime' => $undertime,
            'overtime' => $overtime,
            'signature' => $signature,
            'totalLateAndUndertimeandAbsence' => $totalLateAndUndertimeandAbsence,
            'absence' => $totalAbsence,
            'dayOff' => $dayOff,
            'leave' => $leave,
        ];
    
        $pdf = Pdf::view('dtr', $data)
            ->format('a4')
            ->name('Daily_Time_Record.pdf');

         // Ensure the storage directory exists
         $storagePath = storage_path('app/public/dtr');
         if (!file_exists($storagePath)) {
             mkdir($storagePath, 0755, true);
         }

         // Generate a safe filename without special characters
         $safeEmployeeNo = preg_replace('/[^a-zA-Z0-9]/', '_', $employeeNo);
         $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d');
         $endDate = Carbon::parse($validated['end_date'])->format('Y-m-d');
         $currentDate = now()->format('Y-m-d');
         $filename = "DTR_{$safeEmployeeNo}_{$startDate}_{$endDate}_{$currentDate}.pdf";
         $filePath = $storagePath . '/' . $filename;
         
         if (file_exists($filePath)) {
             unlink($filePath);
         }
         
         try {
             $pdf->save($filePath);
             
             // Create a symbolic link to make the file publicly accessible
             $publicPath = public_path('storage/dtr');
             if (!file_exists($publicPath)) {
                 mkdir($publicPath, 0755, true);
             }
             
             // Copy the file to the public directory for preview
             copy($filePath, $publicPath . '/' . $filename);
             
             // Return the file path and public URL
             return response()->json([
                 'success' => true,
                 'file_path' => $filePath,
                 'filename' => $filename,
                 'preview_url' => '/storage/dtr/' . $filename
             ]);

         } catch (\Exception $e) {
             Log::error('PDF Save Failed: ' . $e->getMessage());
             return response()->json(['error' => 'Failed to save PDF file: ' . $e->getMessage()], 500);
         }
    }

    /**
     * Submit a DTR request
     * 
     * @param RequestAttendanceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitDTR(RequestAttendanceRequest $request)
    {
        $validated = $request->validated();

        // dd($validated);

        // Check if file path is provided
        if (isset($validated['attachment']['file_path']) && file_exists($validated['attachment']['file_path'])) {
            try {
                // Store the filename before modifying the attachment
                $filename = $validated['attachment']['filename'];
                
                // Upload to S3
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region'  => env('AWS_DEFAULT_REGION'),
                    'credentials' => [
                        'key'    => env('AWS_ACCESS_KEY_ID'),
                        'secret' => env('AWS_SECRET_ACCESS_KEY')
                    ]
                ]);

                // Upload the file to S3
                $s3Client->putObject([
                    'Bucket' => env('AWS_BUCKET'),
                    'Key'    => "dtr/{$filename}",
                    'Body'   => fopen($validated['attachment']['file_path'], 'rb'),
                    'ACL'    => 'private'
                ]);

                // Generate a signed URL for downloading
                $cmd = $s3Client->getCommand('GetObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key'    => "dtr/{$filename}"
                ]);

                $request = $s3Client->createPresignedRequest($cmd, '+120 minutes');
                $downloadUrl = (string) $request->getUri();

                // Create the S3 path
                $attachmentPath = "dtr/{$filename}";
                
                // Clean up the local file
                $publicPath = public_path('storage/dtr');
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }
                
                // Try to delete the file from public storage if it exists
                $publicFilePath = $publicPath . '/' . $filename;
                if (file_exists($publicFilePath)) {
                    unlink($publicFilePath);
                }

            } catch (\Exception $e) {
                Log::error('S3 Upload Failed: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to upload file to S3: ' . $e->getMessage()], 500);
            }
        } else {
            Log::error('File path not provided or file does not exist: ' . (isset($validated['attachment']['file_path']) ? $validated['attachment']['file_path'] : 'not set'));
            return response()->json(['error' => 'File path not provided or file does not exist'], 400);
        }

        
        $requestDTR = RequestDTR::create(
            [
                'user_id' => $validated['user_id'],
                'approver_id' => $validated['approver_id'],
                'attachment' => $attachmentPath,
                'status' => $validated['status'],
                'subject' => $validated['subject'],
            ]
        );
        return response()->json([
            'message' => 'DTR submitted successfully',
            'requestDTR' => $requestDTR
        ]);
    }


    /**
     * Get the approvers from the Empowerex API
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApprover(){
        $client = new \GuzzleHttp\Client();

        $token = Auth::user()->empowerex_token;
        
        try {
            $response = $client->request('GET', 'https://api.extest.link/api/v1/employees', [
                'query' => [
                    'role' => 'approver'
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch approvers',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
