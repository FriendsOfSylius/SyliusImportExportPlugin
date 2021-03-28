<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Interop\Queue\PsrConnectionFactory;
use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrQueue;

class MqItemWriter implements ItemWriterInterface
{
    /** @var PsrConnectionFactory */
    private $psrConnectionFactory;

    /** @var PsrContext */
    private $context;

    /** @var PsrQueue */
    private $queue;

    /** @var PsrConsumer */
    private $consumer;

    public function __construct(PsrConnectionFactory $psrConnectionFactory)
    {
        $this->psrConnectionFactory = $psrConnectionFactory;
    }

    public function initQueue(string $queueName): void
    {
        $this->context = $this->psrConnectionFactory->createContext();
        $this->queue = $this->context->createQueue($queueName);
        $this->consumer = $this->context->createConsumer($this->queue);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        foreach ($items as $item) {
            $message = $this->context->createMessage(
                false !== json_encode($item) ? json_encode($item) : '',
                [],
                ['recordedOn' => (new \DateTime())->format('Y-m-d H:i:s')]
            );
            $this->context->createProducer()->send($this->queue, $message);
        }
    }
}
