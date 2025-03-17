@if(isset($folderArray))

    @if(request('targetFolder'))
        <div class="file-manager-folder-item" onclick="openFolderByAjax('')">
            <div class="folder-icon folder-icon-back">
                {{--                <img src="{{ url('vendor/advanced-file-manager/assets/images/return-back.svg') }}" alt="" srcset=""--}}
                {{--                     class="svg">--}}
                <img src="{{ url('vendor/advanced-file-manager/assets/classic/images/classic-return-back.svg') }}" alt="" srcset=""
                     class="svg">
            </div>
            <div class="folder-title">
                {{ fileManagerTrans('Back_to_Root') }}
            </div>

            <div class="folder-info">
                --
            </div>
        </div>
    @endif
    @foreach($folderArray as $folder)
        <div class="file-manager-folder-item"
             onclick="openFolderByAjax('{{ $folder['path'] }}')"
             role="button"
             @if(isset($folder['isImage']) && $folder['isImage'])
                 data-preview-url="{{ $folder['url'] ?? '' }}"
             data-is-image="true"
             @endif
             style="cursor: pointer;">
            <div class="folder-icon">
                @if(isset($folder['isImage']) && $folder['isImage'])
                    <img src="{{ $folder['thumbnail'] ?? $folder['url'] }}" alt="{{ $folder['name'] }}"
                         class="folder-thumbnail">
                @else
                    {{-- <img src="{{ url('vendor/advanced-file-manager/assets/images/folder.svg') }}" alt="" srcset=""
                         class="svg"> --}}
                    <img src="{{ url('vendor/advanced-file-manager/assets/classic/images/classic-folder.svg') }}" alt="" srcset=""
                         class="svg">
                @endif
            </div>
            <div class="folder-title">
                {{ ucwords(str_replace('-', ' ', str_replace('_', ' ', $folder['name']))) }}
            </div>

            <div class="folder-info">
                {{ $folder['totalFiles'] }} {{ fileManagerTrans('Files') }}
                /
                {{ $folder['size'] }}
            </div>

            <div class="files-option-element" onclick="event.stopPropagation()">
                <button class="menu-dot" onclick="toggleDropdown(event, this)">
                    <i class="bi bi-three-dots"></i>
                </button>
                <div class="files-dropdown-menu">
                    <a href="#" onclick="openFolderByAjax('{{ $folder['path'] }}')">
                        <i class="bi bi-folder2-open"></i> {{ fileManagerTrans('Open') }}
                    </a>
                    <a href="#" onclick="renameFolder('{{ $folder['encodePath'] }}')">
                        <i class="bi bi-pencil"></i> {{ fileManagerTrans('Rename') }}
                    </a>
                    <a href="#" onclick="copyFolder('{{ $folder['encodePath'] }}')">
                        <i class="bi bi-files"></i> {{ fileManagerTrans('Copy') }}
                    </a>
                    <a href="#" onclick="moveFolder('{{ $folder['encodePath'] }}')">
                        <i class="bi bi-arrows-move"></i> {{ fileManagerTrans('Move') }}
                    </a>
                    <a href="#" onclick="getFileInfo('{{ $folder['encodePath'] }}')" class="info-option">
                        <i class="bi bi-info-circle"></i> {{ fileManagerTrans('Get_Info') }}
                    </a>
                    <a href="#" onclick="deleteFolder('{{ $folder['encodePath'] }}')" class="delete-option">
                        <i class="bi bi-trash"></i> {{ fileManagerTrans('Delete') }}
                    </a>
                </div>
            </div>
        </div>
    @endforeach
@endif