<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;

interface MetadataValidatorInterface
{
    /**
     * @param array $headerKeys
     * @param array $dataset
     *
     * @throws ItemIncompleteException
     */
    public function validateHeaders(array $headerKeys, array $dataset): void;
}
