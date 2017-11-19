<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

interface ImporterResultInterface
{
    public function start(): void;

    public function stop(): void;

    /**
     * @param int $rowNum
     */
    public function success(int $rowNum): void;

    /**
     * @return array
     */
    public function getSuccessRows(): array;

    /**
     * @param int $rowNum
     */
    public function skipped(int $rowNum): void;

    /**
     * @return array
     */
    public function getSkippedRows(): array;

    /**
     * @param int $rowNum
     */
    public function failed(int $rowNum): void;

    /**
     * @return array
     */
    public function getFailedRows(): array;

    /**
     * @return int The duration (in milliseconds)
     */
    public function getDuration(): int;
}
