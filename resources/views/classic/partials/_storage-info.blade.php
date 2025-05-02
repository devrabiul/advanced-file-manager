<?php

use Devrabiul\AdvancedFileManager\Services\FileManagerHelperService;

$diskPath = storage_path('app');
$totalSpace = disk_total_space($diskPath);
$availableSpace = disk_free_space($diskPath);
?>

<div class="storage-info-section">
    <!-- Storage Summary -->
    <div class="storage-overview">
        <div class="storage-header">
            <div class="storage-title">
                <i class="fi fi-sr-hdd"></i>
                <span>{{ fileManagerTrans('Storage') }}</span>
            </div>
            <span class="storage-percentage">
                {{ (int)(($availableSpace*100)/$totalSpace) }}%
            </span>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="storage-progress">
        <div class="progress-bar">
            <div class="progress" style="width: {{ (int)(($availableSpace*100)/$totalSpace) }}%">
                <div class="progress-glow"></div>
            </div>
        </div>
    </div>

    <!-- Storage Details -->
    <div class="storage-details">
        <div class="storage-used">
            <span class="storage-label">
                {{ fileManagerTrans('Used') }}
            </span>
            <span class="storage-value">
                {{ FileManagerHelperService::getAdvancedFileFormatSize($availableSpace) }}
            </span>
        </div>
        <div class="storage-divider"></div>
        <div class="storage-total">
            <span class="storage-label">
                {{ fileManagerTrans('Total') }}
            </span>
            <span class="storage-value">
                {{ FileManagerHelperService::getAdvancedFileFormatSize($totalSpace) }}
            </span>
        </div>
    </div>
</div>
