<?php

use Devrabiul\AdvancedFileManager\Services\AdvancedFileManagerService;

$quickAccess = AdvancedFileManagerService::getQuickAccessStats();
?>
<div class="quick-access-items">
    <div class="quick-access-item item-cursor-pointer" onclick="openFolderByAjax('')">
        <div class="quick-access-content">
            <i class="bi bi-folder"></i>
            <span>{{ fileManagerTrans('Main_Directory') }}</span>
        </div>
        <span class="type-size">
            <i class="bi bi-activity"></i>
        </span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('recent')">
        <div class="quick-access-content">
            <i class="bi bi-clock-history"></i>
            <span>{{ fileManagerTrans('Recent_Files') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['recent']['totalFiles'] }} {{ fileManagerTrans('Files') }}</span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('images')">
        <div class="quick-access-content">
            <i class="bi bi-image"></i>
            <span>{{ fileManagerTrans('Images') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['images']['size'] }}</span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('documents')">
        <div class="quick-access-content">
            <i class="bi bi-file-earmark-text"></i>
            <span>{{ fileManagerTrans('Documents') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['documents']['size'] }}</span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('videos')">
        <div class="quick-access-content">
            <i class="bi bi-camera-video"></i>
            <span>{{ fileManagerTrans('Videos') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['videos']['size'] }}</span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('music')">
        <div class="quick-access-content">
            <i class="bi bi-music-note-beamed"></i>
            <span>{{ fileManagerTrans('Music') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['music']['size'] }}</span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('archives')">
        <div class="quick-access-content">
            <i class="bi bi-file-earmark-zip"></i>
            <span>{{ fileManagerTrans('Archives') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['archives']['size'] ?? '0 KB' }}</span>
    </div>
    <div class="quick-access-item" onclick="openFilesByAjax('others')">
        <div class="quick-access-content">
            <i class="bi bi-files"></i>
            <span>{{ fileManagerTrans('Others') }}</span>
        </div>
        <span class="type-size">{{ $quickAccess['others']['size'] ?? '0 KB' }}</span>
    </div>
</div>