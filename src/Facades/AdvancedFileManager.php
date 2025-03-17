<?php

namespace Devrabiul\AdvancedFileManager\Facades;

use Illuminate\Support\Facades\Facade;

class AdvancedFileManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'AdvancedFileManager';
    }
}