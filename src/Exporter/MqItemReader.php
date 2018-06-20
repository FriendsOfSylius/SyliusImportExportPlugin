<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Redis\RedisContext;
use Enqueue\Redis\RedisDestination;

class MqItemReader implements ItemReaderInterface
{
    /**
     * @var RedisConnectionFactory
     */
    private $redisConnectionFactory;

    /**
     * @var RedisContext
     */
    private $redisContext;

    /**
     * @var RedisDestination
     */
    private $queue;

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
    }

    public function read(): void
    {
        $consumer = $this->redisContext->createConsumer($this->queue);

        while ($message = $consumer->receive()) {
            dump($message);
        }

        dump('consumed!');
        exit;
    }
}
