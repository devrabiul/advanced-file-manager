<?php

namespace Devrabiul\AdvancedFileManager\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use Devrabiul\AdvancedFileManager\Services\AdvancedFileManagerService;
use Devrabiul\AdvancedFileManager\Services\FileManagerHelperService;
use Devrabiul\AdvancedFileManager\Services\S3FileManagerService;

class FolderController extends Controller
{
    public function rename(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'newName' => 'required|string|max:255'
        ]);

        try {
            $oldPath = $request->path;
            $newPath = dirname($oldPath) . '/' . $request->newName;

            if (Storage::exists($newPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A folder with this name already exists'
                ], 422);
            }

            Storage::move($oldPath, $newPath);

            return response()->json([
                'success' => true,
                'html' => view('partials.file-manager-content')->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error renaming folder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function copy(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $newPath = $this->generateUniqueCopyPath($request->path);
            
            // Recursively copy the directory
            $this->recursiveCopy($request->path, $newPath);

            return response()->json([
                'success' => true,
                'html' => view('partials.file-manager-content')->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error copying folder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function move(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'destination' => 'required|string'
        ]);

        try {
            if (!Storage::exists($request->destination)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination folder does not exist'
                ], 422);
            }

            Storage::move($request->path, $request->destination . '/' . basename($request->path));

            return response()->json([
                'success' => true,
                'html' => view('partials.file-manager-content')->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error moving folder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function info(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $info = [
                'name' => basename($request->path),
                'size' => $this->getFolderSize($request->path),
                'created' => Storage::lastModified($request->path),
                'files' => $this->countFiles($request->path),
                'folders' => $this->countFolders($request->path)
            ];

            return response()->json([
                'success' => true,
                'html' => view('partials.folder-info-modal', compact('info'))->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting folder info: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            Storage::deleteDirectory($request->path);

            return response()->json([
                'success' => true,
                'html' => view('partials.file-manager-content')->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting folder: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateUniqueCopyPath($path)
    {
        $original = $path;
        $counter = 1;
        
        while (Storage::exists($path)) {
            $path = dirname($original) . '/' . basename($original) . " ($counter)";
            $counter++;
        }
        
        return $path;
    }

    private function recursiveCopy($from, $to)
    {
        $files = Storage::files($from);
        $directories = Storage::directories($from);

        // Create the destination directory
        Storage::makeDirectory($to);

        // Copy all files
        foreach ($files as $file) {
            Storage::copy($file, $to . '/' . basename($file));
        }

        // Recursively copy subdirectories
        foreach ($directories as $directory) {
            $this->recursiveCopy(
                $directory,
                $to . '/' . basename($directory)
            );
        }
    }

    private function getFolderSize($path)
    {
        $size = 0;
        foreach (Storage::allFiles($path) as $file) {
            $size += Storage::size($file);
        }
        return $size;
    }

    private function countFiles($path)
    {
        return count(Storage::files($path));
    }

    private function countFolders($path)
    {
        return count(Storage::directories($path));
    }

    public function viewStyleSetup(Request $request)
    {
        Session::put('file_list_container_view_mode', request('view_mode') ?? 'grid-view');
        return response()->json([
            'status' => 'success',
            'view_mode' => session('file_list_container_view_mode'),
        ]);
    }

    public function getFileInfo(Request $request)
    {
        try {
            $filePath = Crypt::decryptString($request->file_path);
            $driver = $request->input('driver', 'public');

            // Check if the path exists
            if (!Storage::disk($driver)->exists($filePath)) {
                return response()->json(['html' => '<p class="text-danger">File or directory not found.</p>']);
            }

            // Determine if it's a file or directory
            $isDirectory = Storage::disk($driver)->directories($filePath) || Storage::disk($driver)->files($filePath);

            if ($isDirectory) {
                $type = 'directory';
                $folder = $filePath;
                $name = explode('/', $folder);
                $getAllFilesData = AdvancedFileManagerService::getAllFiles($folder);

                // Get all files inside the directory
                $filesInside = Storage::disk(S3FileManagerService::getStorageDriver())->files($folder);

                // Get last modified timestamp from the latest file inside the folder
                $latestTimestamp = !empty($filesInside)
                    ? max(array_map(fn($file) => Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file), $filesInside))
                    : null; // No files inside
                $items = [
                    'directories' => Storage::disk($driver)->directories($filePath),
                    'files' => Storage::disk($driver)->files($filePath),

                    'name' => end($name),
                    'path' => $folder,
                    'encodePath' => Crypt::encryptString($folder),
                    'lastPath' => str_replace(end($name), '', $folder),
                    'type' => 'Folder',
                    'icon' => AdvancedFileManagerService::getIconByExtension(extension: 'folder'),
                    // 'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($folder)))->diffForHumans(),
                    'last_modified' => $latestTimestamp
                        ? Carbon::parse(date('Y-m-d H:i:s', $latestTimestamp))->diffForHumans()
                        : 'No files found',
                    'totalFiles' => $getAllFilesData['totalFiles'],
                    'size' => $getAllFilesData['size'],
                    'AllFiles' => $getAllFilesData,
                    'AllFolders' => AdvancedFileManagerService::getAllFolders($folder),
                ];
            } else {
                $file = $filePath;
                $type = explode('/', Storage::disk(S3FileManagerService::getStorageDriver())->mimeType($file))[0];
                $name = explode('/', $file);
                $items = [
                    'name' => end($name),
                    'short_name' => FileManagerHelperService::getFileMinifyString(end($name)),
                    'driver' => S3FileManagerService::getStorageDriver(),
                    'path' => $file,
                    'full_path' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file)['path'],
                    'full_path_info' => S3FileManagerService::getFileFullPath(S3FileManagerService::getStorageDriver(), $file),
                    'encodePath' => Crypt::encryptString($file),
                    'type' => $type,
                    'icon' => AdvancedFileManagerService::getIconByExtension(extension: pathinfo($file, PATHINFO_EXTENSION)),
                    'size' => FileManagerHelperService::getAdvancedFileFormatSize(Storage::disk(S3FileManagerService::getStorageDriver())->size($file)),
                    'sizeInInteger' => Storage::disk(S3FileManagerService::getStorageDriver())->size($file),
                    'extension' => pathinfo($file, PATHINFO_EXTENSION),
                    'last_modified' => Carbon::parse(date('Y-m-d H:i:s', Storage::disk(S3FileManagerService::getStorageDriver())->lastModified($file)))
                ];
            }

            // Render the file or directory info view
            $html = view('advanced-file-manager::classic.partials.file-info', compact('type', 'items'))->render();

            return response()->json(['html' => $html]);

        } catch (\Exception $e) {
            return response()->json([
                'html' => '<p class="text-danger">Error fetching file info.</p>',
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ]);
        }
    }

} 