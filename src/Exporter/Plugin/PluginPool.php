<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

class PluginPool implements PluginPoolInterface
{
    /** @var array */
    private $ids;

    /** @var PluginFactoryInterface */
    private $pluginFactory;

    /** @var PluginInterface[] */
    private $plugins;

    public function __construct(PluginFactoryInterface $pluginFactory, array $plugins)
    {
        $this->pluginFactory = $pluginFactory;
        foreach ($plugins as $namespace) {
            /** @var PluginInterface $plugin */
            $plugin = $this->pluginFactory->create($namespace);
            $this->plugins[] = $plugin;
        }
    }

    /**
     * @return PluginInterface[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @param array $plugins
     */
    public function initPlugins(array $ids): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->init($ids);
        }
    }
}
