<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

interface MetadataValidatorInterface
{
    /**
     * @param string[] $headerKeys
     * @param mixed[] $dataset
     */
    public function validateHeaders(array $headerKeys, array $dataset): void;
}
