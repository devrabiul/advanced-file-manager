<?php

namespace Devrabiul\AdvancedFileManager\Services;

use Carbon\Carbon;
use Devrabiul\AdvancedFileManager\Services\AdvancedFileManagerService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Devrabiul\AdvancedFileManager\Services\S3FileManagerService;

class FileManagerHelperService
{
    public static function getAllFiles($targetFolder = null, object|array $request = []): array
    {
        $GenData = [];
        $request = !empty($request) ? $request : request()->all();
        $targetFolder = !empty($targetFolder) ? $targetFolder : request('targetFolder') ?? '/';
        $AllFilesInCurrentFolder = Storage::disk(S3FileManagerService::getStorageDriver())->files($targetFolder);
        $GenData['path'] = $AllFilesInCurrentFolder;

        $FilesWithInfo = [];
        $FilesWithInfo = AdvancedFileManagerService::getFilesWithInfo(filePaths: $AllFilesInCurrentFolder);

        $totalFileSize = 0;
        foreach ($AllFilesInCurrentFolder as $file) {
            $totalFileSize += Storage::disk(S3FileManagerService::getStorageDriver())->size($file);
        }

        $GenData['size'] = FileManagerHelperService::getAdvancedFileFormatSize($totalFileSize);
        $GenData['files'] = $FilesWithInfo;
        $GenData['totalFiles'] = count($AllFilesInCurrentFolder);

        $GenData['last_modified'] = Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified('')))->diffForHumans();
        if ($targetFolder && Storage::exists($targetFolder)) {
            $GenData['last_modified'] = Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($targetFolder)))->diffForHumans();
        }

        return $GenData;
    }

    public static function renderAdvancedFileManagerView(array|object $request = [], string $type = ''): array
    {
        $requestData = !empty($request) ? $request : request()->all();
        $targetFolder = urldecode($requestData['targetFolder'] ?? '/');
        $storageDriver = S3FileManagerService::getStorageDriver();

        $cacheKeyAllFiles = "files_in_{$storageDriver}";
        $cacheKeyFiles = "files_in_{$targetFolder}_{$storageDriver}";
        $cacheKeyFolders = "folders_in_{$targetFolder}_{$storageDriver}";
        $cacheKeyOverview = "overview_in_{$targetFolder}_{$storageDriver}";

        FileManagerHelperService::cacheKeys($cacheKeyFiles);
        FileManagerHelperService::cacheKeys($cacheKeyFolders);
        FileManagerHelperService::cacheKeys($cacheKeyOverview);

        // Check if the theme exists or fallback to a default if needed
        $theme = self::getFileManagerTheme() ?: 'default';

        if (S3FileManagerService::getStorageDriver() == 's3' && S3FileManagerService::checkS3DriverCredential('status') == false) {
            return [
                'quick_access' => view("advanced-file-manager::$theme.partials._quick-access-items")->render(),
                'html' => view("advanced-file-manager::$theme.partials._driver-error")->render(),
                'html_files' => '',
            ];
        }

        $AllFilesInStorage = Cache::remember($cacheKeyAllFiles, 3600, function () {
            return Storage::disk(S3FileManagerService::getStorageDriver())->allFiles();
        });

        $AllFilesInCurrentFolder = Cache::remember($cacheKeyFiles, 3600, function () use ($targetFolder, $requestData) {
            return AdvancedFileManagerService::getAllFiles(targetFolder: $targetFolder, request: $requestData);
        });

        $AllFilesInCurrentFolderFiles = AdvancedFileManagerService::getAllFilesInCurrentFolder($cacheKeyFiles, $targetFolder, $requestData);

        $folderArray = Cache::remember($cacheKeyFolders, 3600, function () use ($targetFolder) {
            return AdvancedFileManagerService::getAllFolders($targetFolder);
        });

        $AllFilesOverview = Cache::remember($cacheKeyOverview, 3600, function () use ($AllFilesInCurrentFolder) {
            return AdvancedFileManagerService::getAllFilesOverview(AllFilesWithInfo: $AllFilesInCurrentFolder);
        });

        $lastFolderArray = explode('/', $targetFolder);
        $lastFolder = count($lastFolderArray) > 1 ? str_replace('/' . end($lastFolderArray), '', $targetFolder) : '';

        $dataArray = [
            'folderArray' => $folderArray,
            'AllFilesInCurrentFolder' => $AllFilesInCurrentFolder,
            'AllFilesInCurrentFolderFiles' => $AllFilesInCurrentFolderFiles,
            'lastFolder' => $lastFolder,
            'AllFilesOverview' => $AllFilesOverview
        ];

        return [
            'quick_access' => view("advanced-file-manager::$theme.partials._quick-access-items")->render(),
            'html' => view("advanced-file-manager::$theme.partials._content", $dataArray)->render(),
            'html_files' => view("advanced-file-manager::$theme.partials._files-list-content", $dataArray)->render(),
        ];
    }


    public static function renderAdvancedFileManagerFilesView(array|object $request = [], string $type = ''): array
    {
        $requestData = !empty($request) ? $request : request()->all();
        $storageDriver = S3FileManagerService::getStorageDriver();
        $fileType = $requestData['fileType'] ?? '';
        
        $cacheKeyAllFiles = "files_in_{$storageDriver}_{$fileType}";
        FileManagerHelperService::cacheKeys($cacheKeyAllFiles);

        $filesByTypeInStorage = Cache::remember($cacheKeyAllFiles, 3600, function () use ($fileType) {
            $allFiles = Storage::disk(S3FileManagerService::getStorageDriver())->allFiles();
            
            // Filter files by type if specified
            if (!empty($fileType)) {
                $allFiles = array_filter($allFiles, function($file) use ($fileType) {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    
                    switch($fileType) {
                        case 'images':
                            return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'ico', 'tiff']);
                        case 'documents':
                            return in_array($extension, ['doc', 'docx', 'txt', 'rtf', 'odt', 'pages', 'tex']);
                        case 'videos':
                            return in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp', 'm4v']);
                        case 'music':
                            return in_array($extension, ['mp3', 'wav', 'ogg', 'wma', 'm4a', 'aac', 'flac', 'alac']);
                        case 'archives':
                            return in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso']);
                        case 'recent':
                            // Get files modified in the last 7 days
                            $lastModified = Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file);
                            return Carbon::createFromTimestamp($lastModified)->isAfter(now()->subDays(7));
                        case 'others':
                            // Get files that don't match any of the above categories
                            $commonExtensions = [
                                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'ico', 'tiff', // images
                                'doc', 'docx', 'txt', 'rtf', 'odt', 'pages', 'tex', // documents
                                'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp', 'm4v', // videos
                                'mp3', 'wav', 'ogg', 'wma', 'm4a', 'aac', 'flac', 'alac', // music
                                'zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso', // archives
                            ];
                            return !in_array($extension, $commonExtensions);
                        default:
                            return $extension === strtolower($fileType);
                    }
                });
            }
            
            return array_values($allFiles);
        });

        $AllFilesInCurrentFolderFiles = AdvancedFileManagerService::getFilesWithInfo(filePaths: $filesByTypeInStorage);

        $AllFilesInCurrentFolderFiles = collect($AllFilesInCurrentFolderFiles);
        if (request()->has('search') && !empty(request('search'))) {
            $AllFilesInCurrentFolderFiles = $AllFilesInCurrentFolderFiles->filter(function ($file) use ($requestData) {
                return str_contains(strtolower($file['name']), strtolower($requestData['search']));
            });
        }

        $perPage = 20;
        $page = request()->get('page', 1);
        $items = $AllFilesInCurrentFolderFiles->slice(($page - 1) * $perPage, $perPage)->values();

        $AllFilesInCurrentFolderFiles = new LengthAwarePaginator($items, count($AllFilesInCurrentFolderFiles), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        $dataArray = [
            'AllFilesInCurrentFolderFiles' => $AllFilesInCurrentFolderFiles,
        ];

        // Check if the theme exists or fallback to a default if needed
        $theme = self::getFileManagerTheme() ?: 'default';
        return [
            'html' => view("advanced-file-manager::$theme.partials._content", $dataArray)->render(),
            'html_files' => view("advanced-file-manager::$theme.partials._files-list", $dataArray)->render(),
        ];
    }
    
    
    public static function getFileMinifyString($inputString, $prefixLength = 15, $suffixLength = 8, $ellipsis = '.....')
    {
        if (strlen($inputString) <= $prefixLength + $suffixLength) {
            return $inputString;
        }
        $prefix = substr($inputString, 0, $prefixLength);
        $suffix = substr($inputString, -$suffixLength);
        return $prefix . $ellipsis . $suffix;
    }
    
    public static function getAdvancedFileFormatSize($size = 0): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public static function advancedFileManagerStorage(string $path): string
    {
        if (config('advanced-file-manager.system_processing_directory') == 'public') {
            $result = str_replace('storage/public', 'storage', str_replace('storage/app/public', 'storage', $path));
        } else {
            $result = $path;
        }
        return asset($result);
    }
    
    public static function cacheKeys($cacheKey): void
    {
        $cacheKeys = Cache::get('advancedFileManagerCacheKeys', []);
        if (!in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put('advancedFileManagerCacheKeys', $cacheKeys, 60 * 60 * 3);
        }
    }
    
    
    public static function forgotCacheKeys(): void
    {
        $cacheKeys = Cache::get('advancedFileManagerCacheKeys', []);
        foreach ($cacheKeys as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }
    
    public static function getFileManagerTheme(): string
    {
        if (Str::lower(config('advanced-file-manager.theme')) == 'modern') {
            return 'modern';
        } elseif (Str::lower(config('advanced-file-manager.theme')) == 'material') {
            return 'material';
        } else {
            return 'classic';
        }
    }
    
    
}
