<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

interface ItemReaderInterface
{
    /**
     * @param string $queueName
     */
    public function initQueue(string $queueName): void;

    public function readAndImport(int $timeout): void;

    /**
     * @return int
     */
    public function getMessagesImportedCount(): int;

    /**
     * @return int
     */
    public function getMessagesSkippedCount(): int;
}
