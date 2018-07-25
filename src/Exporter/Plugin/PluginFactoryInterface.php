<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface PluginFactoryInterface
{
    public function create(string $pluginNamespace): PluginInterface;
}
