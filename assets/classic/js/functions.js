// Close modal when clicking on X
document.querySelectorAll('.advanced-file-manager-modal-close').forEach(closeBtn => {
    closeBtn.onclick = function() {
        this.closest('.advanced-file-manager-modal').style.display = "none";
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('advanced-file-manager-modal')) {
        event.target.style.display = "none";
    }
}


function toggleDropdown(event, button) {
    event.stopPropagation();

    // Close all other open dropdowns
    const allDropdowns = document.querySelectorAll('.files-dropdown-menu');
    allDropdowns.forEach(dropdown => {
        if (dropdown !== button.nextElementSibling) {
            dropdown.classList.remove('show');
        }
    });

    // Toggle current dropdown
    const dropdown = button.nextElementSibling;
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.files-dropdown-menu');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});

// Prevent dropdown from closing when clicking inside it
document.querySelectorAll('.files-dropdown-menu').forEach(dropdown => {
    dropdown.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});

function renameFolder(path) {
    event.preventDefault();

    const newName = prompt("Enter new folder name:");
    if (!newName) return;

    $.ajax({
        url: '/folders/rename',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            path: path,
            newName: newName
        },
        beforeSend: function() {
            $(".advanced-file-manager-loader-container").removeClass('loader-container-hide');
        },
        success: function(response) {
            if (response.success) {
                // Refresh the file manager content
                $('.advanced-file-manager-content').fadeOut('fast', function() {
                    $(this).empty().html(response.html).fadeIn('fast');
                });
                toastr.success('Folder renamed successfully');
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON.message || 'Error renaming folder');
        },
        complete: function() {
            $(".advanced-file-manager-loader-container").addClass('loader-container-hide');
        }
    });
}

function copyFolder(path) {
    event.preventDefault();

    $.ajax({
        url: '/folders/copy',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            path: path
        },
        beforeSend: function() {
            $(".advanced-file-manager-loader-container").removeClass('loader-container-hide');
        },
        success: function(response) {
            if (response.success) {
                $('.advanced-file-manager-content').fadeOut('fast', function() {
                    $(this).empty().html(response.html).fadeIn('fast');
                });
                toastr.success('Folder copied successfully');
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON.message || 'Error copying folder');
        },
        complete: function() {
            $(".advanced-file-manager-loader-container").addClass('loader-container-hide');
        }
    });
}

function moveFolder(path) {
    event.preventDefault();

    // You might want to show a modal with available destinations here
    const destination = prompt("Enter destination path:");
    if (!destination) return;

    $.ajax({
        url: '/folders/move',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            path: path,
            destination: destination
        },
        beforeSend: function() {
            $(".advanced-file-manager-loader-container").removeClass('loader-container-hide');
        },
        success: function(response) {
            if (response.success) {
                $('.advanced-file-manager-content').fadeOut('fast', function() {
                    $(this).empty().html(response.html).fadeIn('fast');
                });
                toastr.success('Folder moved successfully');
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON.message || 'Error moving folder');
        },
        complete: function() {
            $(".advanced-file-manager-loader-container").addClass('loader-container-hide');
        }
    });
}

function getFileInfo(filePath) {
    const fileInfoModal = document.getElementById('fileInfoModal');
    const fileInfoModalContent = document.getElementById('file-info-content');

    const url = new URL(window.location.href);
    const driver = url.searchParams.get('driver') ?? 'public';

    $.ajax({
        url: fileInfoModalContent.getAttribute('data-route'),
        type: "POST",
        data: {
            file_path: filePath,
            _token: $('meta[name="csrf-token"]').attr('content'),
            driver: driver,
        },
        beforeSend: function() {
            fileInfoModalContent.innerHTML = 'Loading...';
            fileInfoModal.style.display = 'flex';
        },
        success: function(response) {
            fileInfoModalContent.innerHTML = response.html;
        },
        error: function() {
            fileInfoModalContent.innerHTML = "<p class='text-danger'>Failed to fetch file info.</p>";
        }
    });
}

function deleteFolder(path) {
    event.preventDefault();

    if (!confirm('Are you sure you want to delete this folder?')) return;

    $.ajax({
        url: '/folders/delete',
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            path: path
        },
        beforeSend: function() {
            $(".advanced-file-manager-loader-container").removeClass('loader-container-hide');
        },
        success: function(response) {
            if (response.success) {
                $('.advanced-file-manager-content').fadeOut('fast', function() {
                    $(this).empty().html(response.html).fadeIn('fast');
                });
                toastr.success('Folder deleted successfully');
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON.message || 'Error deleting folder');
        },
        complete: function() {
            $(".advanced-file-manager-loader-container").addClass('loader-container-hide');
        }
    });
}