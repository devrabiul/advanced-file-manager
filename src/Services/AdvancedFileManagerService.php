<?php

namespace Devrabiul\AdvancedFileManager\Services;

use Carbon\Carbon;
use Aws\S3\S3Client;
use Devrabiul\AdvancedFileManager\Services\S3FileManagerService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Devrabiul\AdvancedFileManager\Services\FileManagerHelperService;

class AdvancedFileManagerService
{
    public static function getAllFilesInCurrentFolder($cacheKeyFiles, $targetFolder, $requestData)
    {
        $AllFilesInCurrentFolder = Cache::remember($cacheKeyFiles, 3600, function () use ($targetFolder, $requestData) {
            return AdvancedFileManagerService::getAllFiles(targetFolder: $targetFolder, request: $requestData);
        });

        $AllFilesInCurrentFolderFiles = collect($AllFilesInCurrentFolder['files']);
        if (request()->has('search') && !empty(request('search'))) {
            $AllFilesInCurrentFolderFiles = $AllFilesInCurrentFolderFiles->filter(function ($file) use ($requestData) {
                return str_contains(strtolower($file['name']), strtolower($requestData['search']));
            });
        }

        $perPage = 20;
        $page = request()->get('page', 1);
        $items = $AllFilesInCurrentFolderFiles->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($items, count($AllFilesInCurrentFolderFiles), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    public static function getAllFiles($targetFolder = null, object|array $request = []): array
    {
        $GenData = [];
        $request = !empty($request) ? $request : request()->all();
        $targetFolder = !empty($targetFolder) ? $targetFolder : request('targetFolder') ?? '/';

        // Get all files and directories in the target folder
        $AllFilesInCurrentFolder = Storage::disk(S3FileManagerService::getStorageDriver())->files($targetFolder);

        $AllDirectories = Storage::disk(S3FileManagerService::getStorageDriver())->directories($targetFolder);

        $GenData['path'] = array_merge($AllFilesInCurrentFolder, $AllDirectories);

        $FilesWithInfo = AdvancedFileManagerService::getFilesWithInfo(filePaths: $AllFilesInCurrentFolder);

        $DirectoriesWithInfo = [];
        foreach ($AllDirectories as $directory) {
            $dirName = explode('/', $directory);

            // Get files inside the directory
            $filesInside = Storage::disk(S3FileManagerService::getStorageDriver())->files($directory);

            // Get the latest modified file timestamp inside the directory
            $latestTimestamp = !empty($filesInside)
                ? max(array_map(fn($file) => Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file), $filesInside))
                : null;

            $DirectoriesWithInfo[] = [
                'name' => end($dirName),
                'short_name' => FileManagerHelperService::getFileMinifyString(end($dirName)),
                'path' => $directory,
                'driver' => S3FileManagerService::getStorageDriver(),
                'encodePath' => Crypt::encryptString($directory),
                'type' => 'directory',
                'icon' => 'folder',
                'size' => null,
                'sizeInInteger' => null,
                'extension' => null,
                // 'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($directory)))->diffForHumans()
                'last_modified' => $latestTimestamp
            ];
        }

        $totalFileSize = 0;
        foreach ($AllFilesInCurrentFolder as $file) {
            $totalFileSize += Storage::disk(S3FileManagerService::getStorageDriver())->size($file);
        }

        $GenData['size'] = FileManagerHelperService::getAdvancedFileFormatSize($totalFileSize);
        $GenData['files'] = $FilesWithInfo;
        $GenData['directories'] = $DirectoriesWithInfo;
        $GenData['totalFiles'] = count($AllFilesInCurrentFolder);
        $GenData['totalDirectories'] = count($AllDirectories);

        try {
            $GenData['last_modified'] = Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified('')))->diffForHumans();
            if ($targetFolder && Storage::exists($targetFolder)) {
                $GenData['last_modified'] = Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($targetFolder)))->diffForHumans();
            }
        } catch (\Exception $e) {
        }

        return $GenData;
    }

    public static function getFilesWithInfo($filePaths = []): array
    {
        $FilesWithInfo = [];
        foreach ($filePaths as $file) {
            $type = explode('/', Storage::disk(S3FileManagerService::getStorageDriver())->mimeType($file))[0];
            $name = explode('/', $file);
            $FilesWithInfo[] = [
                'name' => end($name),
                'short_name' => FileManagerHelperService::getFileMinifyString(end($name)),
                'driver' => S3FileManagerService::getStorageDriver(),
                'path' => $file,
                'full_path' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file)['path'],
                'full_path_info' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file),
                'encodePath' => Crypt::encryptString($file),
                'type' => $type,
                'icon' => self::getIconByExtension(extension: pathinfo($file, PATHINFO_EXTENSION)),
                'size' => FileManagerHelperService::getAdvancedFileFormatSize(Storage::disk(S3FileManagerService::getStorageDriver())->size($file)),
                'sizeInInteger' => Storage::disk(S3FileManagerService::getStorageDriver())->size($file),
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file)))->diffForHumans()
            ];
        }
        return $FilesWithInfo;
    }

    public static function getAllFolders($targetFolder = null): array
    {
        $allFolders = Storage::disk(S3FileManagerService::getStorageDriver())->allDirectories($targetFolder);
        $onlyFolder = Storage::disk(S3FileManagerService::getStorageDriver())->Directories($targetFolder);

        $folderArray = [];
        foreach ($onlyFolder as $folder) {
            $name = explode('/', $folder);
            $getAllFilesData = self::getAllFiles($folder);

            // Get all files inside the directory
            $filesInside = Storage::disk(S3FileManagerService::getStorageDriver())->files($folder);

            // Get last modified timestamp from the latest file inside the folder
            $latestTimestamp = !empty($filesInside)
                ? max(array_map(fn($file) => Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file), $filesInside))
                : null; // No files inside

            $folderArray[] = [
                'name' => end($name),
                'path' => $folder,
                'encodePath' => Crypt::encryptString($folder),
                'lastPath' => str_replace(end($name), '', $folder),
                'type' => 'Folder',
                'icon' => self::getIconByExtension(extension: 'folder'),
                // 'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($folder)))->diffForHumans(),
                'last_modified' => $latestTimestamp
                    ? Carbon::parse(date('Y-m-d H:i:s', $latestTimestamp))->diffForHumans()
                    : 'No files found',
                'totalFiles' => $getAllFilesData['totalFiles'],
                'size' => $getAllFilesData['size'],
                'AllFiles' => $getAllFilesData,
                'AllFolders' => self::getAllFolders($folder),
            ];
        }

        usort($folderArray, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $folderArray;
    }

    public static function getIconByExtension($extension = null): string
    {
        $iconType = config('advanced-file-manager.font_type') ?? 'bootstrap';

        $iconMappings = [
            'bootstrap' => [
                'folder' => '<i class="bi bi-folder"></i>',
                'jpg' => '<i class="bi bi-file-image"></i>',
                'jpeg' => '<i class="bi bi-file-image"></i>',
                'png' => '<i class="bi bi-file-image"></i>',
                'gif' => '<i class="bi bi-file-image"></i>',
                'svg' => '<i class="bi bi-file-image"></i>',
                'pdf' => '<i class="bi bi-file-pdf"></i>',
                'zip' => '<i class="bi bi-file-zip"></i>',
                'rar' => '<i class="bi bi-file-zip"></i>',
                '7z' => '<i class="bi bi-file-zip"></i>',
                'doc' => '<i class="bi bi-file-word"></i>',
                'docx' => '<i class="bi bi-file-word"></i>',
                'xls' => '<i class="bi bi-file-excel"></i>',
                'xlsx' => '<i class="bi bi-file-excel"></i>',
                'ppt' => '<i class="bi bi-file-ppt"></i>',
                'pptx' => '<i class="bi bi-file-ppt"></i>',
                'txt' => '<i class="bi bi-file-text"></i>',
                'mp3' => '<i class="bi bi-file-music"></i>',
                'wav' => '<i class="bi bi-file-music"></i>',
                'mp4' => '<i class="bi bi-file-play"></i>',
                'avi' => '<i class="bi bi-file-play"></i>',
                'mov' => '<i class="bi bi-file-play"></i>',
                'default' => '<i class="bi bi-file"></i>'
            ],
            'font-awesome' => [
                'folder' => '<i class="fas fa-folder"></i>',
                'jpg' => '<i class="fas fa-image"></i>',
                'jpeg' => '<i class="fas fa-image"></i>',
                'png' => '<i class="fas fa-image"></i>',
                'gif' => '<i class="fas fa-image"></i>',
                'svg' => '<i class="fas fa-image"></i>',
                'pdf' => '<i class="fas fa-file-pdf"></i>',
                'zip' => '<i class="far fa-file-archive"></i>',
                'rar' => '<i class="far fa-file-archive"></i>',
                '7z' => '<i class="far fa-file-archive"></i>',
                'doc' => '<i class="fas fa-file-word"></i>',
                'docx' => '<i class="fas fa-file-word"></i>',
                'xls' => '<i class="fas fa-file-excel"></i>',
                'xlsx' => '<i class="fas fa-file-excel"></i>',
                'ppt' => '<i class="fas fa-file-powerpoint"></i>',
                'pptx' => '<i class="fas fa-file-powerpoint"></i>',
                'txt' => '<i class="fas fa-file-alt"></i>',
                'mp3' => '<i class="fas fa-music"></i>',
                'wav' => '<i class="fas fa-music"></i>',
                'mp4' => '<i class="fas fa-film"></i>',
                'avi' => '<i class="fas fa-film"></i>',
                'mov' => '<i class="fas fa-film"></i>',
                'default' => '<i class="fas fa-file"></i>'
            ],
            'material' => [
                'folder' => '<i class="material-icons">folder</i>',
                'jpg' => '<i class="material-icons">image</i>',
                'jpeg' => '<i class="material-icons">image</i>',
                'png' => '<i class="material-icons">image</i>',
                'gif' => '<i class="material-icons">image</i>',
                'svg' => '<i class="material-icons">image</i>',
                'pdf' => '<i class="material-icons">picture_as_pdf</i>',
                'zip' => '<i class="material-icons">folder_zip</i>',
                'rar' => '<i class="material-icons">folder_zip</i>',
                '7z' => '<i class="material-icons">folder_zip</i>',
                'doc' => '<i class="material-icons">article</i>',
                'docx' => '<i class="material-icons">article</i>',
                'xls' => '<i class="material-icons">table_chart</i>',
                'xlsx' => '<i class="material-icons">table_chart</i>',
                'ppt' => '<i class="material-icons">slideshow</i>',
                'pptx' => '<i class="material-icons">slideshow</i>',
                'txt' => '<i class="material-icons">description</i>',
                'mp3' => '<i class="material-icons">audio_file</i>',
                'wav' => '<i class="material-icons">audio_file</i>',
                'mp4' => '<i class="material-icons">video_file</i>',
                'avi' => '<i class="material-icons">video_file</i>',
                'mov' => '<i class="material-icons">video_file</i>',
                'default' => '<i class="material-icons">insert_drive_file</i>'
            ]
        ];

        // Get the icon mapping for the selected icon type
        $selectedMapping = $iconMappings[$iconType] ?? $iconMappings['bootstrap'];

        // Return the icon for the extension, or the default icon if not found
        return $selectedMapping[strtolower($extension)] ?? $selectedMapping['default'];
    }

    public static function getAllFilesOverview(array $AllFilesWithInfo): array
    {
        $typeImageTotalSize = 0;
        $typeImageCount = 0;
        $typeVideoTotalSize = 0;
        $typeVideoCount = 0;
        $typeAudioTotalSize = 0;
        $typeAudioCount = 0;
        $typeDocTotalSize = 0;
        $typeDocCount = 0;
        $typeOthersTotalSize = 0;
        $typeOthersCount = 0;

        foreach ($AllFilesWithInfo['files'] as $fileName) {
            if ($fileName['type'] == "image") {
                $typeImageTotalSize += $fileName['sizeInInteger'];
                $typeImageCount += 1;
            }

            if ($fileName['type'] == "video") {
                $typeVideoTotalSize += $fileName['sizeInInteger'];
                $typeVideoCount += 1;
            }

            if ($fileName['type'] == "audio") {
                $typeAudioTotalSize += $fileName['sizeInInteger'];
                $typeAudioCount += 1;
            }

            if ($fileName['type'] == "application") {
                $typeDocTotalSize += $fileName['sizeInInteger'];
                $typeDocCount += 1;
            }

            $avoidTypes = ["image", "video", "audio", "application", "text"];
            if (!in_array($fileName['type'], $avoidTypes)) {
                $typeOthersTotalSize += $fileName['sizeInInteger'];
                $typeOthersCount++;
            }
        }

        return [
            'image' => ['size' => FileManagerHelperService::getAdvancedFileFormatSize($typeImageTotalSize), 'count' => $typeImageCount],
            'video' => ['size' => FileManagerHelperService::getAdvancedFileFormatSize($typeVideoTotalSize), 'count' => $typeVideoCount],
            'audio' => ['size' => FileManagerHelperService::getAdvancedFileFormatSize($typeAudioTotalSize), 'count' => $typeAudioCount],
            'others' => ['size' => FileManagerHelperService::getAdvancedFileFormatSize($typeOthersTotalSize), 'count' => $typeOthersCount],
            'document' => ['size' => FileManagerHelperService::getAdvancedFileFormatSize($typeDocTotalSize), 'count' => $typeDocCount],
        ];
    }

    public function getFileInformation($encodePath): array
    {
        $file = Crypt::decryptString($encodePath);
        $name = explode('/', $file);
        return [
            'name' => end($name),
            'short_name' => FileManagerHelperService::getFileMinifyString(end($name)),
            'driver' => S3FileManagerService::getStorageDriver(),
            'path' => $file,
            'full_path' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file)['path'],
            'full_path_info' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file),
            'encodePath' => $encodePath,
            'type' => explode('/', Storage::disk(S3FileManagerService::getStorageDriver())->mimeType($file))[0],
            'icon' => self::getIconByExtension(extension: pathinfo($file, PATHINFO_EXTENSION)),
            'size' => FileManagerHelperService::getAdvancedFileFormatSize(Storage::disk(S3FileManagerService::getStorageDriver())->size($file)),
            'sizeInInteger' => Storage::disk(S3FileManagerService::getStorageDriver())->size($file),
            'extension' => pathinfo($file, PATHINFO_EXTENSION),
            'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file)))->diffForHumans()
        ];
    }

    public static function getRecentFiles($limit = 10): array
    {
        $targetFolder = null;
        $allFiles = self::getAllFiles($targetFolder);

        // Sort files by last modified time
        usort($allFiles['files'], function ($a, $b) {
            $timeA = Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($a['path']);
            $timeB = Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($b['path']);
            return $timeB - $timeA;
        });

        // Get only the specified number of recent files
        $recentFiles = array_slice($allFiles['files'], 0, $limit);

        return [
            'files' => $recentFiles,
            'totalFiles' => count($recentFiles),
            'size' => self::calculateTotalSize($recentFiles)
        ];
    }

    public static function getFavoriteFiles(): array
    {
        // Assuming favorites are stored in a JSON file or database
        $favoritesPath = Storage::disk(S3FileManagerService::getStorageDriver())->path('favorites.json');
        $favorites = [];

        if (file_exists($favoritesPath)) {
            $favoritesList = json_decode(file_get_contents($favoritesPath), true) ?? [];

            foreach ($favoritesList as $filePath) {
                if (Storage::disk(S3FileManagerService::getStorageDriver())->exists($filePath)) {
                    $fileInfo = self::getFileInformation(Crypt::encryptString($filePath));
                    $favorites[] = $fileInfo;
                }
            }
        }

        return [
            'files' => $favorites,
            'totalFiles' => count($favorites),
            'size' => self::calculateTotalSize($favorites)
        ];
    }

    private static function calculateTotalSize(array $files): string
    {
        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += $file['sizeInInteger'] ?? 0;
        }
        return FileManagerHelperService::getAdvancedFileFormatSize($totalSize);
    }

    public static function toggleFavorite($filePath): bool
    {
        $favoritesPath = Storage::disk(S3FileManagerService::getStorageDriver())->path('favorites.json');
        $favorites = [];

        if (file_exists($favoritesPath)) {
            $favorites = json_decode(file_get_contents($favoritesPath), true) ?? [];
        }

        $index = array_search($filePath, $favorites);
        if ($index !== false) {
            // Remove from favorites
            unset($favorites[$index]);
            $favorites = array_values($favorites);
        } else {
            // Add to favorites
            $favorites[] = $filePath;
        }

        return file_put_contents($favoritesPath, json_encode($favorites)) !== false;
    }

    public static function getAllFilesWithSubdirectories(string|null $targetFolder = null, array $request = []): array
    {
        $GenData = [];
        $request = !empty($request) ? $request : request()->all();
        $targetFolder = !empty($targetFolder) ? $targetFolder : request('targetFolder') ?? '/';

        // Get all files recursively from subdirectories
        $AllFiles = Storage::disk(S3FileManagerService::getStorageDriver())->allFiles($targetFolder);

        $FilesWithInfo = [];

        foreach ($AllFiles as $file) {
            $type = explode('/', Storage::disk(S3FileManagerService::getStorageDriver())->mimeType($file))[0];
            $name = explode('/', $file);
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Check if file matches the requested filter
            $includeFile = false;
            if (empty($request['filter']) || $request['filter'] == 'all') {
                $includeFile = true;
            } else {
                switch ($request['filter']) {
                    case 'images':
                        $includeFile = $type === 'image';
                        break;
                    case 'videos':
                        $includeFile = $type === 'video';
                        break;
                    case 'music':
                        $includeFile = $type === 'audio';
                        break;
                    case 'documents':
                        $includeFile = in_array($extension, ['doc', 'docx', 'txt', 'rtf', 'odt', 'pages', 'tex']);
                        break;
                    case 'archives':
                        $includeFile = in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso']);
                        break;
                    case 'pdfs':
                        $includeFile = $extension === 'pdf';
                        break;
                    case 'spreadsheets':
                        $includeFile = in_array($extension, ['xls', 'xlsx', 'csv', 'ods', 'numbers']);
                        break;
                    case 'presentations':
                        $includeFile = in_array($extension, ['ppt', 'pptx', 'key', 'odp']);
                        break;
                    case 'fonts':
                        $includeFile = in_array($extension, ['ttf', 'otf', 'woff', 'woff2', 'eot']);
                        break;
                    default:
                        $includeFile = $type === $request['filter'];
                }
            }

            if ($includeFile) {
                $FilesWithInfo[] = [
                    'name' => end($name),
                    'short_name' => FileManagerHelperService::getFileMinifyString(end($name)),
                    'driver' => S3FileManagerService::getStorageDriver(),
                    'path' => $file,
                    'full_path' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file)['path'],
                    'full_path_info' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file),
                    'encodePath' => Crypt::encryptString($file),
                    'type' => $type,
                    'icon' => self::getIconByExtension(extension: pathinfo($file, PATHINFO_EXTENSION)),
                    'size' => FileManagerHelperService::getAdvancedFileFormatSize(Storage::disk(S3FileManagerService::getStorageDriver())->size($file)),
                    'sizeInInteger' => Storage::disk(S3FileManagerService::getStorageDriver())->size($file),
                    'extension' => $extension,
                    'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file)))->diffForHumans()
                ];
            }
        }

        return [
            'files' => $FilesWithInfo,
            'totalFiles' => count($FilesWithInfo),
            'size' => self::calculateTotalSize($FilesWithInfo),
        ];
    }

    public static function getQuickAccessFilesByType(string $type, string|null $targetFolder = null): array
    {
        return self::getAllFilesWithSubdirectories($targetFolder, ['filter' => $type]);
    }

    public static function getQuickAccessStats(): array
    {
        // Cache key with driver prefix to avoid conflicts
        $cacheKey = S3FileManagerService::getStorageDriver() . '_quick_access_stats';
        FileManagerHelperService::cacheKeys($cacheKey);

        if (S3FileManagerService::getStorageDriver() == 's3' && S3FileManagerService::checkS3DriverCredential('status') == false) {
            return [
                'recent' => ['totalFiles' => 0],
                'favorites' => ['size' => 0],
                'images' => ['size' => 0],
                'videos' => ['size' => 0],
                'music' => ['size' => 0],
                'documents' => ['size' => 0],
                'archives' => ['size' => 0],
            ];
        }

        return Cache::remember($cacheKey, 3600, function () {
            return [
                'recent' => self::getRecentFiles(),
                'favorites' => self::getFavoriteFiles(),
                'images' => self::getQuickAccessFilesByType('images', '/'),
                'videos' => self::getQuickAccessFilesByType('videos', '/'),
                'music' => self::getQuickAccessFilesByType('music', '/'),
                'documents' => self::getQuickAccessFilesByType('documents', '/'),
                'archives' => self::getQuickAccessFilesByType('archives', '/'),
            ];
        });
    }
}
