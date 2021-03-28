<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

interface HandlerInterface
{
    /**
     * Sets the next handler to use in case it's not handled on the current implementation
     *
     * @param HandlerInterface $handler
     */
    public function setSuccessor(self $handler): void;

    /**
     * Loops through handlers until it gets satisfying result
     *
     * @param int|\DateTime|array $value
     * @return mixed
     */
    public function handle(?string $key, $value);
}
