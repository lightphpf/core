<?php

declare(strict_types=1);

namespace LightPHPF\Core\Http\Controllers;

use Twig\Environment;

class BaseController
{
    private array $config;
    protected Environment $twig;
    protected string $appName;
    protected string $appUrl;
    protected string $appRoot;

    public function __construct(array $config, Environment $twig)
    {
        $this->twig = $twig;
        $this->config = $config;

        $this->appName = $config['app_name'];
        $this->appUrl = $config['app_url'];
        $this->appRoot = $config['app_root'];
    }

    /**
     * @param  string $model
     * @return mixed
     */
    public function model(string $model): mixed
    {
        // instantiate model
        return new $model($this->config['database']);
    }

    /**
     * @param  string $view
     * @param  array $data
     * @return void
     */
    public function view(string $view, array $data = []): void
    {
        $data['app_name'] = $this->appName;
        $data['app_root'] = $this->appRoot;

        if (file_exists($this->appRoot . "/Views/{$view}.php")) {
            require_once __DIR__ . '/../Views/' . $view . '.php';
        } else {
            dump("View does not exists.");
        }
    }
}
