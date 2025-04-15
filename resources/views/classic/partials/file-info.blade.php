<div class="file-info">
    <h5><strong>Name:</strong> {{ $items['name'] }}</h5>

    @if ($type === 'directory')
        <p><strong>Type:</strong> Folder</p>
        <p><strong>Path:</strong> {{ $items['path'] }}</p>
        <p><strong>Parent Directory:</strong> {{ $items['lastPath'] ?: 'Root' }}</p>
        <p><strong>Last Modified:</strong> {{ $items['last_modified']->diffForHumans() }}</p>
        <p><strong>Total Files:</strong> {{ $items['totalFiles'] }}</p>
        <p><strong>Size:</strong> {{ $items['size'] }}</p>
    @else
        <p><strong>Size:</strong> {{ number_format($items['sizeInInteger'] / 1024, 2) }} KB</p>
        <p><strong>Type:</strong> {{ ucfirst($items['type']) }}</p>
        <p><strong>Extension:</strong> {{ $items['extension'] }}</p>
        <p><strong>Last Modified:</strong> {{ $items['last_modified']->diffForHumans() }}</p>
        <p><strong>Path:</strong> {{ $items['path'] }}</p>
        <p><strong>Full Path:</strong> {{ $items['full_path'] }}</p>
        <p><strong>Storage Driver:</strong> {{ ucfirst($items['driver']) }}</p>
    @endif
</div>
