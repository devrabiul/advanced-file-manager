<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File Manager Name
    |--------------------------------------------------------------------------
    |
    | This value sets the display name of your file manager application.
    | It will be used across the UI to represent the file manager.
    |
    */

    'name' => 'Advance File Manager',

    /*
    |--------------------------------------------------------------------------
    | System Processing Directory
    |--------------------------------------------------------------------------
    |
    | This defines the root directory where file management operations
    | such as uploads, deletions, and modifications will take place.
    | You can change it to a specific folder if required.
    |
    */

    'system_processing_directory' => 'root',

    /*
    |--------------------------------------------------------------------------
    | File Manager Theme
    |--------------------------------------------------------------------------
    |
    | This setting determines the visual appearance of the file manager.
    | Available themes:
    | - 'Modern': A sleek and contemporary design.
    | - 'Material': Inspired by Google's Material Design.
    | - 'Classic': A traditional, simple layout.
    |
    | You can override this value using the environment variable FILE_MANAGER_THEME.
    | If not set, it defaults to 'classic'.
    |
    */

    'theme' => env('FILE_MANAGER_THEME', 'classic'),

    /*
    |--------------------------------------------------------------------------
    | Pagination Style
    |--------------------------------------------------------------------------
    |
    | This setting defines the pagination style for displaying files and folders.
    | The available options align with Laravel's pagination themes:
    | - 'simple': Uses simple previous/next links.
    | - 'bootstrap': Uses Bootstrap-styled pagination.
    | - 'tailwind': Uses Tailwind-styled pagination.
    | - 'default': Uses Laravel's default pagination style.
    |
    | You can configure this based on your frontend framework requirements.
    |
    */

    'paginator' => 'bootstrap',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the filesystem settings for the file manager including
    | default disk, allowed disks, and various file handling settings.
    |
    */

    'filesystem' => [
        // The default disk to use for file operations
        'default_disk' => env('FILE_MANAGER_DISK', 'public'),

        // List of allowed disks that can be accessed through the file manager
        'allowed_disks' => [
            'public',
            'local',
            's3',
        ],

        // Upload settings
        'upload' => [
            'max_file_size' => env('FILE_MANAGER_MAX_FILE_SIZE', 10), // in MB
            'max_files_per_upload' => env('FILE_MANAGER_MAX_FILES', 10),
            'chunk_size' => env('FILE_MANAGER_CHUNK_SIZE', 1024 * 1024), // 1MB chunks
            'parallel_uploads' => env('FILE_MANAGER_PARALLEL_UPLOADS', 3),
            'overwrite_existing' => false,
            'sanitize_filenames' => true,
        ],

        // File type restrictions
        'allowed_files' => [
            'extensions' => [
                // Images
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
                // Documents
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                // Text
                'txt', 'md', 'rtf',
                // Archives
                'zip', 'rar', '7z',
                // Media
                'mp3', 'mp4', 'avi', 'mov',
            ],
            'mimetypes' => [
                // Images
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                // Documents
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                // Text
                'text/plain', 'text/markdown', 'application/rtf',
                // Archives
                'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
                // Media
                'audio/mpeg', 'video/mp4', 'video/x-msvideo', 'video/quicktime',
            ],
        ],

        // Image handling
        'image' => [
            'create_thumbnails' => true,
            'thumbnail_sizes' => [
                'tiny' => [50, 50],
                'small' => [150, 150],
                'medium' => [300, 300],
                'large' => [600, 600],
                'xl' => [1000, 1000],
            ],
            'optimize_images' => true,
            'max_image_dimensions' => [3840, 2160], // 4K resolution
            'preserve_original' => true,
            'default_quality' => 85,
        ],

        // File organization
        'organization' => [
            'create_date_folders' => true, // Organize files by upload date
            'date_folder_format' => 'Y/m/d', // Year/Month/Day
            'sanitize_folder_names' => true,
            'max_folder_depth' => 5,
            'default_permissions' => [
                'file' => 0644,
                'directory' => 0755,
            ],
        ],

        // Cache settings
        'cache' => [
            'enabled' => true,
            'duration' => 3600, // 1 hour
            'thumbnail_cache' => true,
            'list_cache' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'public',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'public',
            'throw' => false,
        ],

    ],

    'enabled_drivers' => [
        'public',
        'local',
        's3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon Font Library
    |--------------------------------------------------------------------------
    |
    | Choose which icon library to use for file and folder icons in the file manager.
    | Available options:
    | - 'bootstrap': Uses Bootstrap Icons (requires bootstrap-icons CSS)
    | - 'font-awesome': Uses Font Awesome 5 icons (requires @fortawesome/fontawesome-free)
    | - 'material': Uses Google Material Icons (requires material-icons font)
    |
    | Required CDN includes:
    | bootstrap: <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    | font-awesome: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    | material: <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    |
    */

    'font_type' => env('FILE_MANAGER_FONT_TYPE', 'bootstrap'),
    
];
