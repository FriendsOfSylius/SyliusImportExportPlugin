<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

/**
 * Class PluginPool
 */
class PluginPool implements PluginPoolInterface
{
    /** @var PluginInterface[] */
    private $plugins;

    /** @var array */
    private $exportKeys;

    public function __construct(array $plugins, array $exportKeys)
    {
        $this->plugins = $plugins;
        $this->exportKeys = $exportKeys;
    }

    /**
     * @return PluginInterface[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @param array $ids
     */
    public function initPlugins(array $ids): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->init($ids);
        }
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getDataForId(string $id): array
    {
        $result = [];
        foreach ($this->plugins as $index => $plugin) {
            $result = $this->getDataForIdFromPlugin($id, $plugin, $result);
        }

        return $result;
    }

    /**
     * @param string $id
     * @param PluginInterface $plugin
     * @param array $result
     *
     * @return array
     */
    private function getDataForIdFromPlugin(string $id, PluginInterface $plugin, array $result): array
    {
        foreach ($plugin->getData($id, $this->exportKeys) as $exportKey => $exportValue) {
            if (true === empty($result[$exportKey])) {
                // no other plugin has delivered a value till now
                $result[$exportKey] = $exportValue;
            }
        }

        return $result;
    }
}
