<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\ORM\EntityManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;

final class JsonResourceImporter extends ResourceImporter implements SingleDataArrayImporterInterface
{
    public function __construct(
        EntityManager $entityManager,
        ResourceProcessorInterface $resourceProcessor,
        ImportResultLoggerInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->entityManager = $entityManager;
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

        $contents = file_get_contents($fileName);
        if (false === $contents) {
            throw new ImporterException(sprintf('File %s could not be loaded', $fileName));
        }

        $data = json_decode($contents, true);

        if (null === $data) {
            throw new ImporterException(sprintf('File %s is not a valid json', $fileName));
        }

        foreach ($data as $i => $row) {
            if ($this->importData($i, $row)) {
                break;
            }
        }

        if ($this->batchCount) {
            $this->entityManager->flush();
        }

        $this->result->stop();

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function importSingleDataArrayWithoutResult(array $dataToImport): void
    {
        $this->resourceProcessor->process($dataToImport);
        $this->entityManager->flush();
    }
}
