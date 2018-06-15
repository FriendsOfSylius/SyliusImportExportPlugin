<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;

final class JsonResourceImporter extends ResourceImporter
{
    public function __construct(
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->objectManager = $objectManager;
        $this->resourceProcessor = $resourceProcessor;
        $this->result = $importerResult;
        $this->batchSize = $batchSize;
        $this->failOnIncomplete = $failOnIncomplete;
        $this->stopOnFailure = $stopOnFailure;
    }

    /**
     * {@inheritdoc}
     */
    public function import(string $fileName): ImporterResultInterface
    {
        $this->result->start();

        $dataAsArray = json_decode(file_get_contents($fileName), true);

        foreach ($dataAsArray as $i => $row) {
            if ($this->importData($i, $row)) {
                break;
            }
        }

        $this->result->stop();

        return $this->result;
    }
}
