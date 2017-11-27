<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface PluginInterface
{
    /**
     * @param string $id
     *
     * @return array
     */
    public function getData(string $id): array;

    public function init(array $idsToExport): void;
}
