# Advanced File Manager

A powerful and flexible file manager package for Laravel applications.

## Features

- 📁 Easy file and folder management
- 🖼️ Image preview and thumbnail generation
- 📋 Copy, cut, paste operations
- 🗑️ Soft delete with trash management
- 📤 File upload with drag and drop support
- 📎 Multiple file upload
- 🔍 Advanced file search and filtering
- 📱 Responsive design
- 🔒 File access permissions
- 💾 Multiple storage disk support

## Installation

First, install the package via Composer:

```bash
composer require devrabiul/advanced-file-manager
```

Then publish the package resources by running:

```bash
php artisan vendor:publish --provider="Devrabiul\AdvancedFileManager\AdvancedFileManagerServiceProvider"
```

## Configuration

After publishing the assets, you can find the configuration file at `config/advanced-file-manager.php`. Here you can customize:

- Maximum upload file size
- Allowed file types
- Storage disk settings
- Thumbnail settings
- Access permissions
- Route prefix

## Usage

### 1. Basic Setup

Add the following includes to your blade template:

1. Add the styles in your `<head>` section:
```php
{!! AdvancedFileManager::styles() !!}
```

2. Add the file manager component where you want it to appear:
```php
{!! AdvancedFileManager::content() !!}
```

3. Add the scripts before closing `</body>` tag:
```php
{!! AdvancedFileManager::scripts() !!}
```

### 2. Custom Implementation

You can customize the file manager by passing parameters:

```php
@include('advanced-file-manager::file-manager', [
    'disk' => 'public',
    'maxFileSize' => 10240, // in KB
    'allowedFileTypes' => ['image/*', 'application/pdf'],
    'showThumbnails' => true
])
```

## Available Methods

### File Operations

```php
// Upload file
FileManager::upload($file, $path);

// Create directory
FileManager::createDirectory($name, $path);

// Move file
FileManager::move($source, $destination);

// Copy file
FileManager::copy($source, $destination);

// Delete file/folder
FileManager::delete($path);
```

### File Information

```php
// Get file details
FileManager::getFileInfo($path);

// List directory contents
FileManager::listContents($path);

// Search files
FileManager::search($query, $path);
```

## Events

The package dispatches various events that you can listen to:

- `FileUploaded`
- `FileDeleted`
- `DirectoryCreated`
- `FileMoved`
- `FileCopied`

## Security

The package includes built-in security features:

- File type validation
- Size restrictions
- Malware scanning (optional)
- User authentication integration
- Custom middleware support

## Troubleshooting

Common issues and solutions:

1. **Permission Issues**
   - Ensure proper directory permissions (usually 755 for folders and 644 for files)
   - Check storage link creation: `php artisan storage:link`

2. **Upload Problems**
   - Verify PHP upload limits in php.ini
   - Check maximum file size configuration

3. **Display Issues**
   - Clear browser cache
   - Run `php artisan view:clear`
   - Ensure all assets are published

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).