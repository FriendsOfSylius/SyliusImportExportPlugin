<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface PluginPoolInterface
{
    /**
     * @return PluginInterface[]
     */
    public function getPlugins(): array;

    public function initPlugins(array $ids): void;

    public function getDataForId(string $id): array;
}
