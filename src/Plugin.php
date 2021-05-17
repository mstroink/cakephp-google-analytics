<?php

declare(strict_types=1);

namespace MStroink\Analytics;

use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;

/**
 * Plugin for Analytics
 */
class Plugin extends BasePlugin
{
    protected $name = 'Analytics';

    protected $consoleEnabled = false;
    protected $middlewareEnabled = false;
    protected $routesEnabled = false;

    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        if (file_exists(ROOT . DS . 'config' . DS . 'analytics.php')) {
            Configure::load('analytics');
        }
    }

    public function services(ContainerInterface $container): void
    {
        $container->addServiceProvider(new AnalyticsServiceProvider());
    }
}
