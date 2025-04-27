<?php

namespace Devrabiul\AdvancedFileManager;

use Illuminate\Session\SessionManager as Session;
use Illuminate\Config\Repository as Config;

class AdvancedFileManager
{
    /**
     * The session manager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * The Config handler instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    protected string $theme;

    /**
     * Constructor.
     *
     * @param Session $session The session manager instance.
     * @param Config $config The configuration repository instance.
     */
    function __construct(Session $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;
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
        $fileManagerConfig = (array)$this->config->get('advanced-file-manager');
        $fileManagerConfig = array_merge($fileManagerConfig, $option);
        if (isset($fileManagerConfig['enabled']) && ($fileManagerConfig['enabled'] === false || $fileManagerConfig['enabled'] === 'false')) {
            return '';
        }

        return view('advanced-file-manager::classic.index-new', compact('fileManagerConfig'));
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