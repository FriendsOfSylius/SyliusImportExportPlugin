<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

interface ResourceProcessorInterface
{
    /**
     * @param array $data
     */
    public function process(array $data): void;
}
