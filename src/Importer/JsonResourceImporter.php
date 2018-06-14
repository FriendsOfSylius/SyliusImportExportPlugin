<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use pcrov\JsonReader\JsonReader;

class JsonResourceImporter extends ResourceImporter
{
    /** @var JsonReader */
    private $reader;

    public function __construct(
        JsonReader $reader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->reader = $reader;
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
        $reader = $this->reader;

        $reader->open($fileName);

        $this->result->start();

        $batchCount = 0;

        $depth = $reader->depth(); // Check in a moment to break when the array is done

        $reader->read(); // Step to the first element

        foreach ($reader->value() as $i => $row) {
            $breakBool = $this->importData($i, $row);
            if ($breakBool) {
                break;
            }
        }

        $reader->close(); // Close the reader

        if ($batchCount) {
            $this->objectManager->flush();
        }

        $this->result->stop();

        return $this->result;
    }
}
