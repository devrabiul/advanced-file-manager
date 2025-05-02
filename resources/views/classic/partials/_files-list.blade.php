<div class="files-header">
    <div class="files-header-title">
        <h5 class="folders-section-title">
            <span><i class="fi fi-sr-apps"></i></span>
            <span>{{ fileManagerTrans('Files') }}</span>
        </h5>
        <p class="folders-section-subtitle">
            {{ fileManagerTrans('Browse_and_organize_your_files_effortlessly') }}
        </p>
    </div>

    <div class="files-header-end">
        <div class="search-container">
            <div class="search-wrapper">
                <i class="fi fi-rr-search search-icon"></i>
                <input type="text" class="file-search-input" placeholder="Search files..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="file-list-container-view" data-route="{{ route('advanced-file-manager.view-style-setup') }}">
            <div class="file-list-container-view-style {{ session('file_list_container_view_mode') == 'list-view' ? 'active' : '' }}" 
            data-style="list-view">
                <i class="fi fi-sr-list"></i>
            </div>
            <div class="file-list-container-view-style {{ session('file_list_container_view_mode') == 'grid-view' ? 'active' : '' }}" 
            data-style="grid-view">
                <i class="fi fi-sr-apps"></i>
            </div>
        </div>
    </div>
</div>

<div class="files-list-content-container">
    @if(!isset($AllFilesInCurrentFolderFiles))
        <div class="file-manager-files-section {{ session('file_list_container_view_mode') ?? 'list-view' }}" id="filesContainer">
            @for($i = 0; $i < 20; $i++)
                <div class="file-manager-files-item">
                    <div class="folder-icon">
                        <div class="placeholder-loading folder-item-icon-loader"></div>
                    </div>
                    <div class="files-information">
                        <div class="files-title">
                            <div class="placeholder-loading folder-item-title-loader"></div>
                        </div>

                        <div class="files-info">
                            <div class="placeholder-loading folder-item-info-loader"></div>
                            <br>
                            <div class="placeholder-loading folder-item-info-loader"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    @else
        @include("advanced-file-manager::classic.partials._files-list-content")
    @endif
</div>
