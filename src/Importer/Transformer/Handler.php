<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer;

abstract class Handler implements HandlerInterface
{
    /** @var HandlerInterface */
    private $successor;

    /**
     * {@inheritdoc}
     */
    final public function setSuccessor(HandlerInterface $handler): void
    {
        if ($this->successor === null) {
            $this->successor = $handler;

            return;
        }

        $this->successor->setSuccessor($handler);
    }

    /**
     * {@inheritdoc}
     */
    final public function handle(?string $type, string $value): string
    {
        $response = $this->allows($type, $value) ? $this->process($type, $value) : null;

        if (($response === null) && ($this->successor !== null)) {
            $response = $this->successor->handle($type, $value);
        }

        if ($response === null) {
            return $value;
        }

        return $response;
    }

    /**
     * Process the data. Return null to send to following handler.
     *
     * @return mixed|null
     */
    abstract protected function process(?string $type, string $value);

    /**
     * Will define whether this request will be handled by this handler (e.g. check on object type)
     */
    abstract protected function allows(?string $type, string $value): bool;
}
