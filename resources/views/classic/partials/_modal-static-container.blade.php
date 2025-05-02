<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="advanced-file-manager-modal">
    <div class="advanced-file-manager-modal-content">
        <span class="advanced-file-manager-modal-close">&times;</span>
        <img id="modalImage" src="" alt="">
    </div>
</div>

<!-- File Info Modal -->
<div class="modal-overlay modal-section-root" id="fileInfoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>{{ fileManagerTrans('File_Information') }}</h5>
            <button class="close-modal">
                <i class="fi fi-rr-circle-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="file-info-content" data-route="{{ route('advanced-file-manager.folders.get-file-info') }}">
                {{ fileManagerTrans('Loading') }}...
            </div>
        </div>
    </div>
</div>

<!-- Add this before the closing body tag -->
<div class="modal-overlay modal-section-root" id="createModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>{{ fileManagerTrans('Upload_Files') }}</h5>
            <button class="close-modal">
                <i class="fi fi-rr-circle-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="upload-area">
                <div class="filepond-wrapper">
                    <input type="file" class="filepond" name="files" multiple>
                </div>
            </div>
        </div>
        {{-- <div class="upload-actions">
            <button type="button" class="btn-cancel">Cancel</button>
            <button type="button" class="btn-upload">
                <i class="fi fi-rr-cloud-upload-alt"></i>
                Upload
            </button>
        </div> --}}
    </div>
</div>