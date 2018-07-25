<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

interface ItemReaderInterface
{
    public function initQueue(string $queueName): void;

    public function readAndImport(int $timeout): void;

    public function getMessagesImportedCount(): int;

    public function getMessagesSkippedCount(): int;
}
