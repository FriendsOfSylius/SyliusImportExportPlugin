<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

class PluginFactory implements PluginFactoryInterface
{
    public function create(string $namespaceOfPlugin): PluginInterface
    {
        if (class_exists($namespaceOfPlugin)) {
            return new $namespaceOfPlugin();
        }
    }
}
