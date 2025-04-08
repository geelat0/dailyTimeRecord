<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;


abstract class Controller
{
    public function show()
    {
        $path = 'api.json'; // Path to the generated OpenAPI file
        if (!Storage::exists($path)) {
            return response()->json(['error' => 'API documentation not found.'], 404);
        }

        return response()->file(storage_path('app/' . $path), [
            'Content-Type' => 'application/json',
        ]);
    }
}
