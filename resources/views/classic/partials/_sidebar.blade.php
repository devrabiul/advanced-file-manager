<section class="file-manager-sidebar-container">
    <button class="sidebar-close-mobile">
        <i class="fi fi-rr-circle-xmark"></i>
    </button>

    <!-- Quick Access -->
    <h5 class="quick-access-section-title">{{ fileManagerTrans('Quick_Access') }}</h5>

    <?php
        $storageDrivers = config('advanced-file-manager.disks');
        $enabledDrivers = [];
        foreach (config('advanced-file-manager.disks') as $storageDiskKey => $storageDisk) {
            if (in_array($storageDiskKey, config('advanced-file-manager.enabled_drivers'))) {
                $enabledDrivers[$storageDiskKey] = $storageDisk;
            }
        }
    ?>
    <div class="quick-access-dropdown">
        <?php
            $selectedStorageDriver = request('driver') ?? config('advanced-file-manager.filesystem.default_disk');
        ?>
        <select class="custom-select">
            @foreach($enabledDrivers as $key => $driver)
            <option value="{{ $driver['driver'] }}" {{ $selectedStorageDriver == $driver['driver'] ? 'selected':''}}>
                {{ Str::upper($key) }}
            </option>
            @endforeach
        </select>
        <i class="fi fi-rr-chevron-double-down chevron-down"></i>
    </div>

    <div class="quick-access-section" data-route="{{ route('advanced-file-manager.sidebar-content') }}">
        <div class="quick-access-items">
            @for($i = 0; $i < 10; $i++) 
                <div class="placeholder-loading quick-access-loader"></div>  
            @endfor
        </div>
    </div>

    <!-- Storage Info -->
    @include('advanced-file-manager::classic.partials._storage-info')
</section>