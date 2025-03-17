<?php

namespace Devrabiul\AdvancedFileManager\Services;

use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class S3FileManagerService
{
    public static function getStorageDriver()
    {
        if (request()->has('driver') && !empty(request('driver')) && array_key_exists(request('driver'), config('advanced-file-manager.disks'))) {
            return request('driver', 'public');
        }
        return config('advanced-file-manager.filesystem.default_disk') ?? 'public';
    }

    public static function cacheKeyForS3ClientStatus(): string
    {
        $config = [
            'key' => Config::get('filesystems.disks.s3.key'),
            'secret' => Config::get('filesystems.disks.s3.secret'),
            'region' => Config::get('filesystems.disks.s3.region'),
            'bucket' => Config::get('filesystems.disks.s3.bucket'),
            'url' => Config::get('filesystems.disks.s3.url'),
            'endpoint' => Config::get('filesystems.disks.s3.endpoint'),
        ];
        return 's3_config_' . Str::slug(implode('_', $config));
    }

    public static function checkS3DriverCredential($key = null)
    {
        $result = Cache::remember(self::cacheKeyForS3ClientStatus(), 30 * 24 * 60 * 60, function () {
            try {
                $s3Config = config('advanced-file-manager.disks.s3');
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => $s3Config['region'],
                    'credentials' => [
                        'key' => $s3Config['key'],
                        'secret' => $s3Config['secret'],
                        'region' => $s3Config['region'],
                    ],
                    'endpoint' => $s3Config['endpoint'] ?? null,
                ]);
                $s3Client->listBuckets();
                Storage::disk('s3')->allDirectories('/');

                return [
                    'status' => true,
                    'message' => 'S3 connection established successfully.',
                ];
            } catch (\Exception $exception) {
                $errorMessage = $exception->getMessage();
                $suggestions = '';

                // Provide suggestions based on the error message
                if (str_contains($errorMessage, 'AuthorizationHeaderMalformed')) {
                    $suggestions = 'Please check your AWS region and ensure it matches the bucket\'s region.';
                } elseif (str_contains($errorMessage, 'IllegalLocationConstraintException')) {
                    $suggestions = 'Ensure that your bucket\'s region is configured correctly in the .env file.';
                } elseif (str_contains($errorMessage, 'Access Denied')) {
                    $suggestions = 'Check your IAM user permissions to ensure you have access to the bucket.';
                }

                return [
                    'status' => false,
                    'message' => 'Failed to connect to S3. Please check your credentials and configuration.',
                    'error' => $errorMessage,
                    'suggestions' => $suggestions,
                ];
            }
        });

        if ($key && $key != null) {
            return $result[$key] ?? 'Not Found';
        }
        return $result;
    }

    public static function getFileFullPath($disk, $path): array
    {
        try {
            if (Storage::disk($disk)->exists($path) && !empty(Storage::disk($disk)->exists($path))) {
                if ($disk == 'local' || $disk == 'public') {
                    $path = 'storage/app/public/' . $path;
                    if (config('advanced-file-manager.system_processing_directory') == 'public') {
                        $result = str_replace('storage/public', 'storage', str_replace('storage/app/public', 'storage', $path));
                    } else {
                        $result = $path;
                    }
                    return [
                        'key' => $path,
                        'path' => asset($result),
                        'status' => 200,
                    ];
                } else if ($disk == 's3') {
                    $fileUrl = null;

                    try {
                        // Try getting the URL using Laravel Storage facade
                        $fileUrl = Storage::disk($disk)->url($path);
                    } catch (\Exception $exception) {
                        \Log::warning("Storage::url() failed: " . $exception->getMessage());
                    }

                    // Check if the file URL is accessible
                    if ($disk === 's3' && (!self::isUrlAccessible($fileUrl))) {
                        // If the URL is not accessible, construct it manually
                        $s3Config = config('advanced-file-manager.disks.s3');
                        $s3Region = $s3Config['region'];
                        $s3Bucket = $s3Config['bucket'];
                        $fileUrl = "https://s3.{$s3Region}.amazonaws.com/{$s3Bucket}/{$path}";
                    }

                    return [
                        'key' => $path,
                        'path' => $fileUrl,
                        'status' => 200,
                    ];
                }
            }
        } catch (\Exception $exception) {
        }
        return [
            'key' => $path,
            'path' => null,
            'status' => 404,
        ];
    }

    private static function isUrlAccessible($url): bool
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $headers = @get_headers($url);
        if ($headers && strpos($headers[0], '200') !== false) {
            return true; // URL is accessible
        }
        return false; // URL is not accessible
    }

}
