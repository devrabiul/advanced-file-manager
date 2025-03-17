<!-- Header Container | Start -->
<section class="file-manager-header">
    <div class="header-left">
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="bi bi-list"></i>
        </button>
        <h4>
            {{ config('advanced-file-manager.name') }}
            <span class="version-badge">{!! 'v'.AdvancedFileManager::version() !!}</span>
        </h4>
    </div>

    <div class="header-right">
        <div class="search-container">
            <label>
                <i class="bi bi-search"></i>
                <input type="search" placeholder="{{ fileManagerTrans('Search_files,_folders') }}...">
            </label>
        </div>

        <button id="createBtn" class="header-responsive-btn">
            <i class="bi bi-plus"></i>
            <span>{{ fileManagerTrans('Upload') }}</span>
        </button>

        <button id="actionSmartFileSync" class="header-responsive-btn"
            data-route="{{ route('advanced-file-manager.smart-file-sync') }}">
            <i class="bi bi-arrow-repeat"></i>
            <span>{{ fileManagerTrans('Refresh_List') }}</span>
        </button>
    </div>
</section>
<!-- Header Container | End -->