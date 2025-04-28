<?php

namespace Devrabiul\AdvancedFileManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class FileManagerConfigServiceProvider extends ServiceProvider
{

    public function register()
    {
        $configPath = config_path('advanced-file-manager.php');

        if (!file_exists($configPath)) {
            config(['advanced-file-manager' => require __DIR__ . '/../config/advanced-file-manager.php']);
        }

        $this->app->singleton('AdvancedFileManager', function ($app) {
            return new AdvancedFileManager($app['session'], $app['config']);
        });
    }

    public function boot(): void
    {
        $this->updateProcessingDirectoryConfig();
        $this->updateProcessingAssetRoutes();
        $this->registerResources();

        // Check the paginator setting from the config file and apply it
        $paginatorStyle = Str::lower(config('advanced-file-manager.paginator'));

        if ($paginatorStyle === 'bootstrap') {
            Paginator::useBootstrap();
        } elseif ($paginatorStyle === 'tailwind') {
            Paginator::useTailwind();
        } elseif ($paginatorStyle === 'simple') {
            Paginator::defaultView('pagination::simple-default');
        } else {
            Paginator::useBootstrap();
        }

    }

    private function registerResources(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/advanced-file-manager.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'advanced-file-manager');
        $this->commands($this->registerCommands());
    }

    private function registerCommands(): array
    {
        return [
            // EmptyTrash::class,
        ];
    }

    private function updateProcessingAssetRoutes(): void
    {
        Route::get('/vendor/advanced-file-manager/assets/{path}', function ($path) {
            $file = __DIR__ . '/../assets/' . $path;

            if (file_exists($file)) {
                // Get file extension
                $extension = pathinfo($file, PATHINFO_EXTENSION);

                // Mime types based on file extension
                $mimeTypes = [
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    'woff' => 'font/woff',
                    'woff2' => 'font/woff2',
                    'ttf' => 'font/ttf',
                    'otf' => 'font/otf',
                    'eot' => 'application/vnd.ms-fontobject',
                    'json' => 'application/json',
                    'ico' => 'image/x-icon',
                ];

                // Default to application/octet-stream if the extension is not recognized
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

                return Response::file($file, [
                    'Content-Type' => $mimeType,
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            abort(404);
        })->where('path', '.*');
    }

    private function updateProcessingDirectoryConfig(): void
    {
        // Get the current script's directory
        $scriptPath = realpath(dirname($_SERVER['SCRIPT_FILENAME']));

        // Get Laravel base and public paths
        $basePath = realpath(base_path());
        $publicPath = realpath(public_path());

        // Determine where the script is running from
        if ($scriptPath === $publicPath) {
            $systemProcessingDirectory = 'public';
            $this->checkAndCreateStorageLink();
        } elseif ($scriptPath === $basePath) {
            $systemProcessingDirectory = 'root';
        } else {
            $systemProcessingDirectory = 'unknown';
        }

        // Update the configuration
        config(['advanced-file-manager.system_processing_directory' => $systemProcessingDirectory]);
    }

    public function checkAndCreateStorageLink(): void
    {
        if (!File::exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }
    }

}
