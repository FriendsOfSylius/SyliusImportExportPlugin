<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer;

final class Pool implements TransformerPoolInterface
{
    /** @var HandlerInterface */
    private $handler;

    /** @var \IteratorAggregate */
    private $generator;

    public function __construct(\IteratorAggregate $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(?string $type, string $value)
    {
        if (null === $this->handler) {
            $this->registerHandlers();
        }

        return $this->handler->handle($type, $value);
    }

    private function registerHandlers(): void
    {
        foreach ($this->generator->getIterator() as $key => $handler) {
            if ($key === 0) {
                $this->handler = $handler;

                continue;
            }

            $this->handler->setSuccessor($handler);
        }
    }
}
