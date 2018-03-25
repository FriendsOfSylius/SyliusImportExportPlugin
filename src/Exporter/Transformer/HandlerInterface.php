<?php

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

interface HandlerInterface
{
    /**
     * Sets the next handler to use in case it's not handled on the current implementation
     *
     * @param HandlerInterface $handler
     */
    public function setSuccessor(HandlerInterface $handler): void;

    /**
     * Loops through handlers until it gets satisfying result
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return string
     */
    public function handle($key, $value);
}
