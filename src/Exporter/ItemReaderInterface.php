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
     * @return array
     */
    public function readAndImport(): array;
}
