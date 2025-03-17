@if (Str::lower(config('advanced-file-manager.theme')) == 'modern')
    @include('advanced-file-manager::modern.index')
@elseif (Str::lower(config('advanced-file-manager.theme')) == 'material')
    @include('advanced-file-manager::material.index')
@else
    @include('advanced-file-manager::classic.index')
@endif