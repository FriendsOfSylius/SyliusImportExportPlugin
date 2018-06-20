<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

interface ItemReaderInterface
{
    /**
     * @param string $queueName
     */
    public function initQueue(string $queueName): void;

    /**
     * @param array $idsToExport
     */
    public function read(): void;
}
