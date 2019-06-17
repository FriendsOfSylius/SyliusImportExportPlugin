<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Psr\Log\LoggerInterface;

interface ImportResultLoggerAwareInterface
{
    public function setMessage(string $message): void;

    public function getMessage(): ?string;

    public function getLogger(): LoggerInterface;
}
