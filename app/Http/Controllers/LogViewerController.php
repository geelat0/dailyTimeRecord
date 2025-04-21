<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LogViewerController extends Controller
{
    public function index()
    {
        return view('logs.viewer');
    }

    public function getLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        
        if (File::exists($logPath)) {
            $logs = collect(file($logPath))
                ->map(function ($line) {
                    return htmlspecialchars($line);
                })
                ->take(-1000) // Get last 1000 lines
                ->values()
                ->toArray();
        }

        return response()->json(['logs' => $logs]);
    }

    public function clearLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        return response()->json(['message' => 'Logs cleared successfully']);
    }
} 