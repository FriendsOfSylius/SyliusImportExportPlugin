<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Enqueue\Redis\RedisConnectionFactory;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrQueue;

class MqItemReader implements ItemReaderInterface
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

//        should be imported inside of here!
        exit;
    }
}
