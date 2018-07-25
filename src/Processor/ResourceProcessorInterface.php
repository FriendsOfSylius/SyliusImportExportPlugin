<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

interface ResourceProcessorInterface
{
    /**
     * @param mixed[] $data
     */
    public function process(array $data): void;
}
