<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\RequestStatusDataTable;
use Inertia\Inertia;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

class RequestStatusController extends Controller
{
    public function index(Request $request, RequestStatusDataTable $dataTable)
    {
        if ($request->wantsJson()) {
            return $dataTable->ajax(); // Return JSON data for Vue
        }
        return Inertia::render('DTR/RequestStatus', [
            'dataTable' => $dataTable->html() // Render the dataTable to DTR/TimeSheet
        ]);    }

    /**
     * Generate a presigned URL for an S3 attachment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function openAttachment(Request $request)
    {
        try {
            $attachmentPath = $request->input('attachment_path');
            
            if (!$attachmentPath) {
                return response()->json(['error' => 'Attachment path is required'], 400);
            }
            // Extract the key from the full URL if it's a full URL
            $key = $attachmentPath;
        
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => 'ap-southeast-1',
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY')
                ]
            ]);

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $key
            ]);

            $request = $s3Client->createPresignedRequest($cmd, '+60 minutes');
            $presignedUrl = (string) $request->getUri();

            return response()->json([
                'success' => true,
                'url' => $presignedUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating presigned URL: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate attachment URL',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
