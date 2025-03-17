<?php

namespace Devrabiul\AdvancedFileManager\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Devrabiul\AdvancedFileManager\Services\FileManagerHelperService;
use Devrabiul\AdvancedFileManager\Traits\AdvancedFileUploadTrait;

class FileUploadController extends Controller
{
    use AdvancedFileUploadTrait;

    // Handle file uploads
    public function uploadFiles(Request $request)
    {
        FileManagerHelperService::forgotCacheKeys();
        $uploadedFiles = $request->file('files');
        if ($uploadedFiles) {
            $paths = [];
            $directory = $request['targetFolder'] ?? 'uploads';
            $disk = $request['driver'] ?? 'public';
            $filePath = self::uploadFileToStorage($directory, $uploadedFiles, $disk);
            if ($filePath) {
                // Create the file metadata response
                $paths[] = [
                    'source' => Storage::url($filePath),  // Get the URL of the uploaded file
                    'poster' => Storage::url($filePath),  // Use the same file for preview
                ];
            }
            // Return the file data response
            return response()->json(['files' => $paths]);
        }
        return response()->json(['error' => 'No files uploaded'], 400);
    }


    // Handle file revert (delete file)
    public function revertUpload(Request $request)
    {
        $fileUrl = $request->input('file');
        $path = str_replace(Storage::url(''), '', $fileUrl);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    // Retrieve file metadata
    public function getFileMetadata(Request $request)
    {
        $filePath = $request->input('filePath');

        // You can customize the metadata to return for the file
        $metadata = [
            'size' => Storage::disk('public')->size('uploads/' . $filePath),
            'lastModified' => Storage::disk('public')->lastModified('uploads/' . $filePath),
        ];

        return response()->json($metadata);
    }

    // Rename a file
    public function renameFile(Request $request)
    {
        $oldName = $request->input('oldName');
        $newName = $request->input('newName');

        $oldPath = 'uploads/' . $oldName;
        $newPath = 'uploads/' . $newName;

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->move($oldPath, $newPath);
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    // Process an image (resize, crop, etc.)
    public function processImage(Request $request)
    {
        $filePath = $request->input('filePath');
        $action = $request->input('action'); // e.g., 'resize'
        $options = $request->input('options'); // Additional options (e.g., new dimensions)

        $path = 'uploads/' . $filePath;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Process the image (example: resize)
        if ($action == 'resize' && isset($options['width'], $options['height'])) {
            $image = Image::make(Storage::disk('public')->path($path));
            $image->resize($options['width'], $options['height']);
            $image->save(Storage::disk('public')->path($path));

            return response()->json(['success' => true, 'file' => Storage::url($path)]);
        }

        return response()->json(['error' => 'Invalid action'], 400);
    }

} 