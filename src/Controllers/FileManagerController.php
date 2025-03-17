<?php

namespace Devrabiul\AdvancedFileManager\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Devrabiul\AdvancedFileManager\Services\FileManagerHelperService;

class FileManagerController extends Controller
{
    public function getSidebarContent(Request $request)
    {
        $theme = config('advanced-file-manager.theme') ?: 'default';
        return response()->json([
            'html' => view("advanced-file-manager::$theme.partials._quick-access-items")->render(),
        ]);
    }

    public function getFolderContent(Request $request)
    {
        if (request()->has('fileType') && request('fileType') != '') {
            $getView = FileManagerHelperService::renderAdvancedFileManagerFilesView(type: request('fileType'));
        } else {
            $getView = FileManagerHelperService::renderAdvancedFileManagerView();
        }

        return response()->json([
            'html' => $getView['html'],
            'html_files' => $getView['html_files'],
        ]);
    }
    
    public function syncFileList(Request $request)
    {
        FileManagerHelperService::forgotCacheKeys();
        return response()->json(['status' => 'success']);
    }

    public function viewStyleSetup(Request $request)
    {
        Session::put('file_list_container_view_mode', request('view_mode') ?? 'grid-view');
        return response()->json([
            'status' => 'success',
            'view_mode' => session('file_list_container_view_mode'),
        ]);
    }
} 