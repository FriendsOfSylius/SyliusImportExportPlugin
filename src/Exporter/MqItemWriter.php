<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Enqueue\Redis\RedisConnectionFactory;
use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrQueue;

class MqItemWriter implements ItemWriterInterface
{
    /**
     * @var RedisConnectionFactory
     */
    private $redisConnectionFactory;

    /**
     * @var PsrContext
     */
    private $redisContext;

    /**
     * @var PsrQueue
     */
    private $queue;

    /**
     * @var PsrConsumer
     */
    private $consumer;

    /**
     * @param RedisConnectionFactory $redisConnectionFactory
     */
    public function __construct(RedisConnectionFactory $redisConnectionFactory)
    {
        $this->redisConnectionFactory = $redisConnectionFactory;
    }

    /**
     * @param string $queueName
     */
    public function initQueue(string $queueName): void
    {
        $this->redisContext = $this->redisConnectionFactory->createContext();
        $this->queue = $this->redisContext->createQueue($queueName);
        $this->consumer = $this->redisContext->createConsumer($this->queue);
    }

    /**
     * @param array $items
     * @param string $entity
     */
    public function write(array $items, string $entity): void
    {
        foreach ($items as $item) {
            $message = $this->redisContext->createMessage(json_encode([
                'entity' => $entity,
                'payload' => $item,
                'recordedOn' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]));
            $this->redisContext->createProducer()->send($this->queue, $message);
        }
    }
}
