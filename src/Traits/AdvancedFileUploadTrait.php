<?php

namespace Devrabiul\AdvancedFileManager\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait AdvancedFileUploadTrait
{
    public static function uploadFileToStorage(string $directory, $file, $disk = 'public'): string
    {
        // Return empty string if the file is null
        if (is_null($file)) {
            return '';
        }

        // Get the storage disk instance
        $storageDisk = Storage::disk($disk);

        // If the disk is public, make sure the directory exists
        if ($disk === 'public') {
            if (!$storageDisk->exists($directory)) {
                $storageDisk->makeDirectory($directory);
            }
        }

        // Get the original file extension
        $format = $file->getClientOriginalExtension();

        // Generate a unique file name
        $fileName = ucwords(Str::slug(str_replace('/', '-', $directory))) . "-file-" . Carbon::now()->timestamp . "-" . uniqid() . "." . $format;

        if ($disk === 'public') {
            // For public disk, use move() to store the file
            $file->move(storage_path('app/public/' . $directory), $fileName);
            // Return the public URL path for the file
            return 'storage/' . $directory . '/' . $fileName;
        } elseif ($disk === 's3') {
            // For s3 disk, use storeAs() to store the file
            $path = $file->storeAs($directory, $fileName, 's3');
            // Return the S3 URL path for the file
            return Storage::disk('s3')->url($path);
        }

        return '';
    }
}