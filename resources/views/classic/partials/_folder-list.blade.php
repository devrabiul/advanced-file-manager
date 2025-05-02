<div class="folders-section-header">
    <div>
        <h5 class="folders-section-title">
            <span><i class="fi fi-sr-folder"></i></span>
            <span>{{ fileManagerTrans('Folders') }}</span>
        </h5>
        <p class="folders-section-subtitle">{{ fileManagerTrans('Manage_your_folders_easily') }}</p>
    </div>
</div>

<div class="file-manager-folders-section">

    @if(!isset($folderArray))
        @for($i = 0; $i < 21; $i++)
        <div class="file-manager-folder-item">
            <div class="folder-icon">
                <div class="placeholder-loading folder-item-icon-loader"></div>
            </div>
            <div class="folder-title">
                <div class="placeholder-loading folder-item-title-loader"></div>
            </div>

            <div class="folder-info">
                <div class="placeholder-loading folder-item-info-loader"></div>
            </div>
        </div>
        @endfor
    @else
        @include("advanced-file-manager::classic.partials._folder-list-content")
    @endif

</div>

@if(isset($folderArray))
    @if(!request('targetFolder') && count($folderArray) <= 0)
        <div class="file-manager-empty-state">
            <div class="empty-state-content">
                <i class="fi fi-sr-remove-folder"></i>
                <h3>{{ fileManagerTrans('No_Folder_Found') }}</h3>
                <p>
                    {{ fileManagerTrans('This_folder_is_empty.') }}
                    {{ fileManagerTrans('Upload_some_Folder_to_get_started').' !' }}
                </p>
            </div>
        </div>
    @endif
@endif


