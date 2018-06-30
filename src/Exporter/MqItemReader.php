<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Redis\RedisConsumer;
use Enqueue\Redis\RedisMessage;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
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
     * @var ImporterInterface
     */
    private $service;

    /**
     * MqItemReader constructor.
     *
     * @param RedisConnectionFactory $redisConnectionFactory
     * @param ImporterInterface $service
     */
    public function __construct(RedisConnectionFactory $redisConnectionFactory, ImporterInterface $service)
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
    }

    public function readAndImport(): void
    {
        /** @var RedisConsumer $consumer */
        $consumer = $this->redisContext->createConsumer($this->queue);

        /** @var RedisMessage $message */
        while ($message = $consumer->receive()) {
            $this->service->importSingleDataArrayWithoutResult((array) json_decode($message->getBody()));
        }
    }
}
