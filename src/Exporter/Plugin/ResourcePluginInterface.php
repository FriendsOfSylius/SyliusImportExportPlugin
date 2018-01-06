<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

interface ResourcePluginInterface extends PluginInterface
{
    /**
     * @return array
     */
    public function getFieldNames(): array;
}
