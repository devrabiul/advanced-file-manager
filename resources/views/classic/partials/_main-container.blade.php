<!-- Main Container | Start -->
<section class="file-manager-main-container temp-border" data-max-filesize="512MB" data-max-size="512">

    <!-- Sidebar Container | Start -->
    @include('advanced-file-manager::classic.partials._sidebar')
    <!-- Sidebar Container | End -->

    <!-- Files Container | Start -->
    <section class="file-manager-files-container temp-border"
             data-route="{{ route('advanced-file-manager.folder-content') }}"
             data-upload="{{ route('advanced-file-manager.upload-files') }}"
             data-revert="{{ route('advanced-file-manager.upload-revert') }}"
             data-metadata="{{ route('advanced-file-manager.file-metadata') }}"
             data-process-image="{{ route('advanced-file-manager.process-image') }}"
             data-rename-route="{{ route('advanced-file-manager.rename-file') }}"
    >
        <div class="advanced-file-manager-loader-container loader-container-hide">
            <div class="loader">
                <div class="loader-circle"></div>
                <div class="loader-text">{{ fileManagerTrans('Loading') }}...</div>
            </div>
        </div>

        <div class="advanced-file-manager-content">
            @include('advanced-file-manager::classic.partials._content')
        </div>

    </section>
    <!-- Files Container | End -->

</section>
<!-- Main Container | End -->