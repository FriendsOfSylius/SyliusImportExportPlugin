<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface PluginFactoryInterface
{
    /**
     * @param string $pluginNamespace
     *
     * @return PluginInterface
     */
    public function create(string $pluginNamespace): PluginInterface;
}
