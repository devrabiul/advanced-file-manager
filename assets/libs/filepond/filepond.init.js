let filePondInstant = null;

document.addEventListener("DOMContentLoaded", function () {
    // Register all required FilePond plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFilePoster,
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize,
        FilePondPluginFileRename,
        FilePondPluginImageEdit
    );

    // Ensure the FilePond container exists
    const filePondElement = document.querySelector('.filepond');
    if (!filePondElement) {
        console.error("FilePond input not found!");
        return;
    }

    // Get the file upload route from the data attribute
    const fileManagerFilesAllRoute = document.querySelector('.file-manager-files-container');
    const fileManagerFilesRevertRoute = fileManagerFilesAllRoute?.getAttribute('data-revert');
    const fileManagerFilesProcessImageRoute = fileManagerFilesAllRoute?.getAttribute('data-process-image');
    const fileManagerFilesRenameRoute = fileManagerFilesAllRoute?.getAttribute('data-rename-route');
    let fileManagerFilesUploadRoute = fileManagerFilesAllRoute?.getAttribute('data-upload');


    if (!fileManagerFilesUploadRoute) {
        console.error("File upload route not found!");
        return;
    } else {
        const url = new URL(window.location.href);
        const targetFolder = url.searchParams.get('targetFolder') ?? '';
        const driver = url.searchParams.get('driver') ?? 'public';

        // Create a new URL object based on the file upload route
        const uploadUrl = new URL(fileManagerFilesUploadRoute);

        // Append the parameters to the URL
        if (targetFolder) {
            uploadUrl.searchParams.set('targetFolder', targetFolder);
        }
        if (driver) {
            uploadUrl.searchParams.set('driver', driver);
        }

        // Get the updated URL with the parameters
        fileManagerFilesUploadRoute = uploadUrl.toString();
    }

    const fileManagerFilesAllConfig = document.querySelector('.file-manager-main-container');

    // Initialize FilePond
    function initializeFilePond() {
        filePondInstant = FilePond.create(filePondElement, {
            allowMultiple: true,
            // acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf', 'application/zip'],
            maxFileSize: fileManagerFilesAllConfig?.getAttribute('data-max-filesize') ?? '100MB', // Limit file size to 10MB
            filePosterHeight: 150, // Set poster height
            filePosterEnable: true, // Enable file poster preview
            imageEditEditor: true, // Enable image editing
    
            server: {
                process: {
                    url: fileManagerFilesUploadRoute,
                    method: 'POST', // Ensure POST method is being used
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    onload: (response) => {
                        // console.log('File uploaded:', response);
                        try {
                            const url = new URL(window.location.href);
                            const targetFolder = url.searchParams.get('targetFolder') ?? '';
                            const driver = url.searchParams.get('driver') ?? 'public';
                            openFolderByAjax(targetFolder, driver, false);
                        } catch (error) {
                            
                        }
                    },
                    onerror: (error) => {
                        console.error('Upload failed:', error);
                    }
                },
                revert: fileManagerFilesRevertRoute,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            },
    
            onaddfile: (error, file) => {                
                if (error) {
                    console.error('Error adding file:', error);
                    return;
                }
                // Validate file size
                if (!validateFileSize(file)) {
                    console.error('File size exceeds the limit.');
                    return;
                }
            },
            onremovefile: (file) => {
                console.log('File removed:', file);
            }
        });
    }

    // Validate file size
    function validateFileSize(file) {
        const maxSize = (fileManagerFilesAllConfig?.getAttribute('data-max-size') ?? '100') * 1024 * 1024;
        const fileSize = (file?.size ?? file?.fileSize); 
        if (!fileSize) {
            return true;
        }
        return fileSize <= maxSize;
    }

    // Rename file (optional functionality)
    async function renameFile(oldName, newName) {
        try {
            const response = await fetch(fileManagerFilesRenameRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ oldName, newName })
            });
            return response.json();
        } catch (err) {
            console.error('Error renaming file:', err);
            return null;
        }
    }

    // Process image (optional functionality)
    async function processImage(filePath, action, options) {
        try {
            const response = await fetch(fileManagerFilesProcessImageRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ filePath, action, options })
            });
            return response.json();
        } catch (err) {
            console.error('Error processing image:', err);
            return null;
        }
    }

    $('#createBtn').click(function () {
        $('#createModal').css('display', 'flex');
        // Initialize FilePond when modal opens
        initializeFilePond();
    });

    $('.close-modal, .btn-cancel').click(function () {
        $('.modal-section-root').css('display', 'none');
        if (filePondInstant) {
            filePondInstant.destroy();
            filePondInstant = null;
        }
    });

    $('#createModal').click(function (e) {
        if (e.target === this) {
            $(this).css('display', 'none');
            if (filePondInstant) {
                filePondInstant.destroy();
                filePondInstant = null;
            }
        }
    });

    $('.modal-section-root').click(function (e) {
        if (e.target === this) {
            $(this).css('display', 'none');
        }
    });
});
