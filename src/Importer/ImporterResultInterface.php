<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Psr\Log\LoggerInterface;

interface ImporterResultInterface
{
    public function start(): void;

    public function stop(): void;

    public function success(int $rowNum): void;

    /**
     * @return int[]
     */
    public function getSuccessRows(): array;

    public function skipped(int $rowNum): void;

    /**
     * @return int[]
     */
    public function getSkippedRows(): array;

    public function failed(int $rowNum): void;

    /**
     * @return int[]
     */
    public function getFailedRows(): array;

    /**
     * @return float The duration (in milliseconds)
     */
    public function getDuration(): float;

    public function setMessage(string $message): void;

    public function getMessage(): ?string;

    public function getLogger(): LoggerInterface;
}
