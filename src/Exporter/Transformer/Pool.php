<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

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
     * @param int|\DateTime|array $value
     */
    public function handle(?string $key, $value)
    {
        if (null === $this->handler) {
            $this->registerHandlers();
        }

        return $this->handler->handle($key, $value);
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
