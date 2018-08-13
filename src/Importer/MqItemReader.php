<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Interop\Queue\PsrConnectionFactory;
use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrQueue;

class MqItemReader implements ItemReaderInterface
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var PsrQueue
     */
    private $queue;

    /**
     * @var PsrConnectionFactory
     */
    private $psrConnectionFactory;

    /**
     * @var int
     */
    private $messagesImportedCount;

    /**
     * @var int
     */
    private $messagesSkippedCount;

    public function __construct(PsrConnectionFactory $psrConnectionFactory)
    {
        $this->psrConnectionFactory = $psrConnectionFactory;
    }

    public function initQueue(string $queueName): void
    {
        $this->context = $this->psrConnectionFactory->createContext();
        $this->queue = $this->context->createQueue($queueName);
        $this->messagesImportedCount = 0;
        $this->messagesSkippedCount = 0;
    }

    public function readAndImport(SingleDataArrayImporterInterface $service): void
    {
        /** @var PsrConsumer $consumer */
        $consumer = $this->context->createConsumer($this->queue);

        /** @var PsrMessage $message */
        while ($message = $consumer->receive()) {
            $dataArrayToImport = (array) json_decode($message->getBody());

            $service->importSingleDataArrayWithoutResult($dataArrayToImport);
            ++$this->messagesImportedCount;
        }
    }

    public function getMessagesImportedCount(): int
    {
        return $this->messagesImportedCount;
    }

    public function getMessagesSkippedCount(): int
    {
        return $this->messagesSkippedCount;
    }
}
