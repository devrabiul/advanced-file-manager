<?php

namespace Devrabiul\AdvancedFileManager;

class AdvancedFileManager
{
    protected $config;
    protected string $theme;

    function __construct()
    {
        $this->config = config('advanced-file-manager');
        $this->theme = config('advanced-file-manager.theme') ?? 'classic';
    }

    public function styles()
    {
        if ($this->theme == 'modern') {
            return view('advanced-file-manager::modern.style');
        } elseif ($this->theme == 'material') {
            return view('advanced-file-manager::material.style');
        } else {
            return view('advanced-file-manager::classic.style');
        }
    }

    public function content(array $option = [])
    {
//        if (isset($option['language']) && !empty($option['language'])) {
//            session()->put('advanced-file-manager-language', $option['language']);
//        }

        return view('advanced-file-manager::classic.index-new');
        if ($this->theme == 'modern') {
            return view('advanced-file-manager::modern.index');
        } elseif ($this->theme == 'material') {
            return view('advanced-file-manager::material.index');
        } else {
            return view('advanced-file-manager::classic.index');
        }
    }

    public function scripts()
    {
        if ($this->theme == 'modern') {
            return view('advanced-file-manager::modern.script');
        } elseif ($this->theme == 'material') {
            return view('advanced-file-manager::material.script');
        } else {
            return view('advanced-file-manager::classic.script');
        }
    }

    public function version(): string
    {
        return '1.0';
    }
}