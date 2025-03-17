<?php

    use Devrabiul\AdvancedFileManager\Services\AdvancedFileManagerService;
    use Devrabiul\AdvancedFileManager\Services\FileManagerHelperService;
    use Devrabiul\AdvancedFileManager\Services\S3FileManagerService;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Facades\Storage;

    $storageDriver = S3FileManagerService::getStorageDriver();
    $requestData = !empty($request) ? $request : request()->all();
    $targetFolder = urldecode($requestData['targetFolder'] ?? '');

    $cacheKeyAllFiles = "files_in_{$storageDriver}";
    $cacheKeyFiles = "files_in_{$targetFolder}_{$storageDriver}";
    $cacheKeyFolders = "folders_in_{$targetFolder}_{$storageDriver}";
    $cacheKeyOverview = "overview_in_{$targetFolder}_{$storageDriver}";

    FileManagerHelperService::cacheKeys($cacheKeyAllFiles);
    FileManagerHelperService::cacheKeys($cacheKeyFiles);
    FileManagerHelperService::cacheKeys($cacheKeyFolders);
    FileManagerHelperService::cacheKeys($cacheKeyOverview);

    $driverCredentialStatus = true;
    if (S3FileManagerService::getStorageDriver() == 's3' && S3FileManagerService::checkS3DriverCredential('status') == false) {
        $driverCredentialStatus = false;
    }

    if ($driverCredentialStatus) {
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
    }
?>