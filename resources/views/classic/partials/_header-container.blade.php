<!-- Header Container | Start -->
<section class="file-manager-header">
    <div class="header-left">
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fi fi-rr-list"></i>
        </button>
        <h4>
            {{ config('advanced-file-manager.name') }}
            <span class="version-badge">{!! 'v'.AdvancedFileManager::version() !!}</span>
        </h4>
    </div>

    <div class="header-right">
        <div class="search-container">
            <label>
                <i class="fi fi-rr-search"></i>
                <input type="search" class="global-search-input"
                    placeholder="{{ fileManagerTrans('Search_files,_folders') }}..."
                    data-route="{{ route('advanced-file-manager.folder-content') }}">
            </label>
        </div>

        @if (isset($fileManagerConfig) && $fileManagerConfig['upload_enabled'])
        <button id="createBtn" class="header-responsive-btn">
            <i class="fi fi-rr-multiple"></i>
            <span>{{ fileManagerTrans('Upload') }}</span>
        </button>
        @endif

        <button id="actionSmartFileSync" class="header-responsive-btn"
            data-route="{{ route('advanced-file-manager.smart-file-sync') }}">
            <i class="fi fi-rr-refresh"></i>
            <span>{{ fileManagerTrans('Refresh_List') }}</span>
        </button>
    </div>
</section>
<!-- Header Container | End -->