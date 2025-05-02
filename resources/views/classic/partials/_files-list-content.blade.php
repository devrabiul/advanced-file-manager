@if(isset($AllFilesInCurrentFolderFiles) && count($AllFilesInCurrentFolderFiles) > 0)
    <div class="file-manager-files-section {{ session('file_list_container_view_mode') ?? 'list-view' }}" id="filesContainer">
        @foreach ($AllFilesInCurrentFolderFiles as $key => $File)
            <div class="file-manager-files-item" data-filename="{{ strtolower($File['short_name']) }}">
                <div class="files-icon">
                    @if ($File['type'] == 'image')
                        <a href="{{ $File['full_path'] }}" class="rixet-lightbox" data-gallery="gallery" data-title="{{ $File['name'] }} | {{ $File['size'] }}">
                            <img src="{{ $File['full_path'] }}" alt="" srcset="" class="image-file">
                        </a>
                    @elseif($File['type'] == 'video')
                        <a href="{{ $File['full_path'] }}" class="rixet-lightbox rixet-lightbox-video" data-gallery="gallery" data-title="{{ $File['name'] }} | {{ $File['size'] }}">
                            <video src="{{ $File['full_path'] }}" style="display:none;" preload="metadata" 
                            data-thumbnail="{{ url('vendor/advanced-file-manager/assets/images/image.svg') }}"></video>
                        </a>                                      
                    @else
                        <img src="{{ url('vendor/advanced-file-manager/assets/images/zip.svg') }}" alt="" srcset="">
                    @endif
                </div>
                <div class="files-information">
                    <div class="files-title">
                        {{ $File['short_name'] }}
                    </div>

                    <div class="files-info">
                        {{ fileManagerTrans(ucwords($File['type'] ?? 'Others')) }} / {{ $File['size'] }}
                        <br>
                        {{ $File['last_modified']->diffForHumans() }}
                    </div>
                </div>

                <div class="files-option-element">
                    <button class="menu-dot" onclick="toggleDropdown(event, this)">
                        <i class="fi fi-rr-menu-dots"></i>
                    </button>
                    <div class="files-dropdown-menu">
                        <a href="#" onclick="openFile('{{ $File['encodePath'] }}')">
                            <i class="fi fi-rr-eye"></i> {{ fileManagerTrans('Open') }}
                        </a>
                        <a href="#" onclick="renameFile('{{ $File['encodePath'] }}')">
                            <i class="fi fi-rr-pencil"></i> {{ fileManagerTrans('Rename') }}
                        </a>
                        <a href="#" onclick="copyFile('{{ $File['encodePath'] }}')">
                            <i class="fi fi-rr-document"></i> {{ fileManagerTrans('Copy') }}
                        </a>
                        <a href="#" onclick="moveFile('{{ $File['encodePath'] }}')">
                            <i class="fi fi-rr-move-to-folder-2"></i> {{ fileManagerTrans('Move') }}
                        </a>
                        <a href="{{ $File['full_path'] }}" download>
                            <i class="fi fi-rr-cloud-download-alt"></i> {{ fileManagerTrans('Download') }}
                        </a>
                        <a href="#" onclick="getFileInfo('{{ $File['encodePath'] }}')" class="info-option">
                            <i class="fi fi-rr-interrogation"></i> {{ fileManagerTrans('Get_Info') }}
                        </a>
                        <a href="#" onclick="deleteFile('{{ $File['encodePath'] }}')" class="delete-option">
                            <i class="fi fi-rr-trash"></i> {{ fileManagerTrans('Delete') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    <div class="pagination-wrapper">
        {{ $AllFilesInCurrentFolderFiles->links() }}
    </div>
@else
    <div class="file-manager-empty-state">
        <div class="empty-state-content">
            <i class="fi fi-rr-remove-folder"></i>
            <h3>{{ fileManagerTrans('No_Files_Found') }}</h3>
            <p>
                {{ fileManagerTrans('This_folder_is_empty.') }}
                {{ fileManagerTrans('Upload_some_files_to_get_started').' !' }}
            </p>
            <button class="upload-btn">
                <i class="fi fi-rr-cloud-upload-alt"></i>
                {{ fileManagerTrans('Upload_Files') }}
            </button>
        </div>
    </div>
@endif