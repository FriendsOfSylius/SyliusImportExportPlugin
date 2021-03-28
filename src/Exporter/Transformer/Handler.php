<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

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
    final public function handle(?string $key, $value)
    {
        $response = $this->allows($key, $value) ? $this->process($key, $value) : null;

        if (($response === null) && ($this->successor !== null)) {
            $response = $this->successor->handle($key, $value);
        }

        if ($response === null) {
            return $value;
        }

        return $response;
    }

    /**
     * Process the data. Return null to send to following handler.
     *
     * @param mixed|int|\DateTime|array $value
     * @return mixed|null
     */
    abstract protected function process(?string $key, $value);

    /**
     * Will define whether this request will be handled by this handler (e.g. check on object type)
     *
     * @param mixed|int|\DateTime|array $value
     */
    abstract protected function allows(?string $key, $value): bool;
}
