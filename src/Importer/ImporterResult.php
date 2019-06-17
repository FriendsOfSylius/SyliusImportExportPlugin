<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

final class ImporterResult implements ImporterResultInterface, ImportResultLoggerAwareInterface
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var Stopwatch */
    private $stopwatch;

    /** @var int[] */
    private $success = [];

    /** @var int[] */
    private $skipped = [];

    /** @var int[] */
    private $failed = [];

    /** @var StopwatchEvent */
    private $stopWatchEvent;

    /** @var string */
    private $message;

    public function __construct(
        Stopwatch $stopwatch,
        LoggerInterface $logger
    ) {
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
    }

    public function start(): void
    {
        $this->stopwatch->start('import');
    }

    public function stop(): void
    {
        $this->stopWatchEvent = $this->stopwatch->stop('import');
    }

    public function success(int $rowNum): void
    {
        $this->success[] = $rowNum;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessRows(): array
    {
        return $this->success;
    }

    public function skipped(int $rowNum): void
    {
        $this->skipped[] = $rowNum;
    }

    /**
     * {@inheritdoc}
     */
    public function getSkippedRows(): array
    {
        return $this->skipped;
    }

    public function failed(int $rowNum): void
    {
        $this->failed[] = $rowNum;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailedRows(): array
    {
        return $this->failed;
    }

    /**
     * {@inheritdoc}
     */
    public function getDuration(): float
    {
        return $this->stopWatchEvent->getDuration();
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
