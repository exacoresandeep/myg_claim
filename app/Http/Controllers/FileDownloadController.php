<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileDownloadController extends Controller
{
    public function download2($filename)
    {
        $path = storage_path(env('IMG_SERVER_PATH') . $filename); // Adjust the path if needed

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }
    
    public function show2($filename)
    {
        $path = storage_path(env('IMG_SERVER_PATH') . $filename);
        
        if (file_exists($path)) {
            return response()->file($path);
        }
        
        abort(404);
    }
    public function view1($filename)
    {
        $path = storage_path('app/uploads/' . $filename);

        if (file_exists($path)) {
            return response()->file($path);
        }

        abort(404);
    }
     public function download($filename)
{
    $primaryPath = storage_path(env('IMG_SERVER_PATH') . $filename);
    $backupPath = storage_path('app/uploads/' . $filename);

    if (file_exists($primaryPath)) {
        return response()->download($primaryPath);
    }

    if (file_exists($backupPath)) {
        return response()->download($backupPath);
    }

    abort(404, 'File not found');
        }
        public function show($filename)
{
    $primaryPath = storage_path(env('IMG_SERVER_PATH') . $filename);
    $backupPath = storage_path('app/uploads/' . $filename);

    if (file_exists($primaryPath)) {
        return response()->file($primaryPath);
    }

    if (file_exists($backupPath)) {
        return response()->file($backupPath);
    }

    abort(404, 'File not found');
}
public function view($filename)
{
    $primaryPath = storage_path('app/uploads/' . $filename);
    $backupPath = storage_path('app/public/uploads/' . $filename);

    if (file_exists($primaryPath)) {
        return response()->file($primaryPath);
    }

    if (file_exists($backupPath)) {
        return response()->file($backupPath);
       }

    abort(404, 'File not found');
}

}