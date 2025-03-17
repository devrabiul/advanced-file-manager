<?php

use Illuminate\Support\Facades\Route;
use Devrabiul\AdvancedFileManager\Controllers\FolderController;
use Devrabiul\AdvancedFileManager\Controllers\FileManagerController;
use Devrabiul\AdvancedFileManager\Controllers\FileUploadController;

Route::group(['prefix' => 'advanced-file-manager'], function () {
    Route::controller(FileManagerController::class)->group(function () {
        Route::post('sidebar-content', 'getSidebarContent')->name('advanced-file-manager.sidebar-content');
        Route::post('folder-content', 'getFolderContent')->name('advanced-file-manager.folder-content');
        Route::post('files-content', 'getFilesContent')->name('advanced-file-manager.files-content');
        Route::post('smart-file-sync', 'syncFileList')->name('advanced-file-manager.smart-file-sync');
        Route::post('view-style-setup', 'viewStyleSetup')->name('advanced-file-manager.view-style-setup');
    });

    Route::controller(FileUploadController::class)->group(function () {
        // File upload route
        Route::post('upload-files', 'uploadFiles')->name('advanced-file-manager.upload-files');
        
        // Revert file (delete file) route
        Route::delete('upload-revert', 'revertUpload')->name('advanced-file-manager.upload-revert');
        
        // File metadata route
        Route::post('file-metadata', 'getFileMetadata')->name('advanced-file-manager.file-metadata');
        
        // Rename file route
        Route::post('rename-file', 'renameFile')->name('advanced-file-manager.rename-file');
        
        // Process image route
        Route::post('process-image', 'processImage')->name('advanced-file-manager.process-image');
    });

    Route::group(['prefix' => 'folders'], function () {
        Route::controller(FolderController::class)->group(function () {
            Route::post('/rename', 'rename')->name('advanced-file-manager.folders.rename');
            Route::post('/copy',  'copy')->name('advanced-file-manager.folders.copy');
            Route::post('/move', 'move')->name('advanced-file-manager.folders.move');
            Route::get('/info',  'info')->name('advanced-file-manager.folders.info');
            Route::delete('/delete', 'delete')->name('advanced-file-manager.folders.delete');

            Route::post('/get-file-info', 'getFileInfo')->name('advanced-file-manager.folders.get-file-info');
        });
        Route::post('/rename-file', [FolderController::class, 'renameFile']);
        Route::post('/move-file', [FolderController::class, 'moveFile']);
        Route::post('/delete-file', [FolderController::class, 'deleteFile']);
    });
});