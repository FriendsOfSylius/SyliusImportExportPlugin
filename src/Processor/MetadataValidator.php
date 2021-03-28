<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;

class MetadataValidator implements MetadataValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validateHeaders(array $headerKeys, array $dataset): void
    {
        $missingHeaderKeys = array_diff($headerKeys, array_keys($dataset));

        if (false === ([] === $missingHeaderKeys)) {
            throw new ItemIncompleteException(
                sprintf(
                    'The mandatory header-keys, "%s", are missing in the data-set. Found header-keys are "%s". ' .
                    'Either change the service definition of the processor accordingly or update your import-data',
                    implode(', ', $missingHeaderKeys),
                    implode(', ', array_keys($dataset))
                )
            );
        }
    }
}
