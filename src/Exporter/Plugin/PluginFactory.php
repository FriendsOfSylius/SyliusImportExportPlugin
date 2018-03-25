<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

class PluginFactory implements PluginFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(string $pluginNamespace): PluginInterface
    {
        if (!class_exists($pluginNamespace)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist', $pluginNamespace));
        }

        return new $pluginNamespace();
    }
}
