<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

interface ItemReaderInterface
{
    public function initQueue(string $queueName): void;

    public function readAndImport(SingleDataArrayImporterInterface $service): void;

    public function getMessagesImportedCount(): int;

    public function getMessagesSkippedCount(): int;
}
