<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface PluginPoolInterface
{
    /**
     * @return PluginInterface[]
     */
    public function getPlugins(): array;

    /**
     * @param array $ids
     */
    public function initPlugins(array $ids): void;

    /**
     * @param string $id
     *
     * @return array
     */
    public function getDataForId(string $id): array;
}
