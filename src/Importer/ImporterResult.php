<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

final class ImporterResult
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

    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
    }

    public function start()
    {
        $this->stopwatch->start('import');
    }

    public function stop()
    {
        $this->stopWatchEvent = $this->stopwatch->stop('import');
    }

    /**
     * @param int $rowNum
     */
    public function success(int $rowNum)
    {
        $this->success[] = $rowNum;
    }

    /**
     * @return array
     */
    public function getSuccessRows()
    {
        return $this->success;
    }

    /**
     * @param int $rowNum
     */
    public function skipped(int $rowNum)
    {
        $this->skipped[] = $rowNum;
    }

    /**
     * @return array
     */
    public function getSkippedRows()
    {
        return $this->skipped;
    }

    /**
     * @param int $rowNum
     */
    public function failed(int $rowNum)
    {
        $this->failed[] = $rowNum;
    }

    /**
     * @return array
     */
    public function getFailedRows()
    {
        return $this->failed;
    }

    /**
     * @return int The duration (in milliseconds)
     */
    public function getDuration()
    {
        return $this->stopWatchEvent->getDuration();
    }
}
