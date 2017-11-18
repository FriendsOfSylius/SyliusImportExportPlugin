<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

final class ImporterResult implements ImporterResultInterface
{
    /** @var Stopwatch */
    private $stopwatch;

    /** @var array */
    private $success = [];

    /** @var array */
    private $skipped = [];

    /** @var array */
    private $failed = [];

    /** @var StopwatchEvent */
    private $stopWatchEvent;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function start(): void
    {
        $this->stopwatch->start('import');
    }

    public function stop(): void
    {
        $this->stopWatchEvent = $this->stopwatch->stop('import');
    }

    /**
     * @param int $rowNum
     */
    public function success(int $rowNum): void
    {
        $this->success[] = $rowNum;
    }

    /**
     * @return array
     */
    public function getSuccessRows(): array
    {
        return $this->success;
    }

    /**
     * @param int $rowNum
     */
    public function skipped(int $rowNum): void
    {
        $this->skipped[] = $rowNum;
    }

    /**
     * @return array
     */
    public function getSkippedRows(): array
    {
        return $this->skipped;
    }

    /**
     * @param int $rowNum
     */
    public function failed(int $rowNum): void
    {
        $this->failed[] = $rowNum;
    }

    /**
     * @return array
     */
    public function getFailedRows(): array
    {
        return $this->failed;
    }

    /**
     * @return int The duration (in milliseconds)
     */
    public function getDuration(): int
    {
        return $this->stopWatchEvent->getDuration();
    }
}
