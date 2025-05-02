<div class="storage-error-container">
    <div class="storage-error-icon">
        <i class="fi fi-rr-shield-exclamation"></i>
    </div>
    <div>
        <h3 class="storage-error-heading">
            {{ fileManagerTrans('Storage_Connection_Error') }}
        </h3>
        <p class="storage-error-text">
            {{ fileManagerTrans('Unable_to_connect_to_s3_storage.') }}
            {{ fileManagerTrans('This_could_be_due_to') }}:
        </p>
        <p class="storage-error-text">
            {{ fileManagerTrans('please_check_your_s3_configuration_and_try_again.') }}
        </p>
    </div>
</div>