<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

class PluginPool implements PluginPoolInterface
{
    /** @var array */
    protected $exportKeys;

    /** @var array */
    protected $exportKeysAvailable = [];

    /** @var PluginInterface[] */
    private $plugins;

    /** @var array */
    private $exportKeysNotFound;

    /**
     * @param PluginInterface[] $plugins
     * @param string[] $exportKeys
     */
    public function __construct(array $plugins, array $exportKeys)
    {
        $this->plugins = $plugins;
        $this->exportKeys = $exportKeys;

        $this->exportKeysNotFound = $exportKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * {@inheritdoc}
     */
    public function initPlugins(array $ids): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->init($ids);

            $this->exportKeysAvailable = array_merge($this->exportKeysAvailable, $plugin->getFieldNames());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDataForId(string $id): array
    {
        $result = [];

        foreach ($this->plugins as $index => $plugin) {
            $result = $this->getDataForIdFromPlugin($id, $plugin, $result);
        }

        if (null != $this->exportKeysNotFound && '' != $this->exportKeysNotFound) {
            throw new \InvalidArgumentException(sprintf(
                'Not all defined export keys have been found: "%s". Choose from: "%s"',
                implode(', ', $this->exportKeysNotFound),
                implode(', ', $this->exportKeysAvailable)
            ));
        }

        return $result;
    }

    /**
     * @param mixed[] $result
     *
     * @return mixed[]
     */
    private function getDataForIdFromPlugin(string $id, PluginInterface $plugin, array $result): array
    {
        foreach ($plugin->getData($id, $this->exportKeys) as $exportKey => $exportValue) {
            if (false === ([] === $result[$exportKey])) {
                continue;
            }

            // no other plugin has delivered a value till now
            $result[$exportKey] = $exportValue;

            $foundKey = array_search($exportKey, $this->exportKeysNotFound, true);
            unset($this->exportKeysNotFound[$foundKey]);
        }

        return $result;
    }
}
