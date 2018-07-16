<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Redis\RedisConsumer;
use Enqueue\Redis\RedisMessage;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\SingleDataArrayImporterInterface;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrQueue;

class MqItemReader implements ItemReaderInterface
{
    /**
     * @var PsrContext
     */
    private $redisContext;

    /**
     * @var PsrQueue
     */
    private $queue;

    /**
     * @var RedisConnectionFactory
     */
    private $redisConnectionFactory;

    /**
     * @var SingleDataArrayImporterInterface
     */
    private $service;

    /**
     * @var int
     */
    private $messagesImportedCount;

    /**
     * @var int
     */
    private $messagesSkippedCount;

    /**
     * MqItemReader constructor.
     *
     * @param RedisConnectionFactory $redisConnectionFactory
     * @param SingleDataArrayImporterInterface $service
     */
    public function __construct(RedisConnectionFactory $redisConnectionFactory, SingleDataArrayImporterInterface $service)
    {
        $this->redisConnectionFactory = $redisConnectionFactory;
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function initQueue(string $queueName): void
    {
        $this->redisContext = $this->redisConnectionFactory->createContext();
        $this->queue = $this->redisContext->createQueue($queueName);
        $this->messagesImportedCount = 0;
        $this->messagesSkippedCount = 0;
    }

    public function readAndImport(): void
    {
        /** @var RedisConsumer $consumer */
        $consumer = $this->redisContext->createConsumer($this->queue);

        $dataTimestampArray = [];

        /** @var RedisMessage $message */
        while ($message = $consumer->receive()) {
            $dataArrayToImport = (array) json_decode($message->getBody());
            $dataTimestamp = strtotime($message->getHeader('recordedOn'));

            if (array_key_exists($dataArrayToImport['Id'], $dataTimestampArray)) {
                if ($dataTimestampArray[$dataArrayToImport['Id']] >= $dataTimestamp) {
                    ++$this->messagesSkippedCount;

                    continue;
                }
            }

            $dataTimestampArray[$dataArrayToImport['Id']] = $dataTimestamp;
            $this->service->importSingleDataArrayWithoutResult($dataArrayToImport);
            ++$this->messagesImportedCount;
        }
    }

    /**
     * @return int
     */
    public function getMessagesImportedCount(): int
    {
        return $this->messagesImportedCount;
    }

    /**
     * @return int
     */
    public function getMessagesSkippedCount(): int
    {
        return $this->messagesSkippedCount;
    }
}
