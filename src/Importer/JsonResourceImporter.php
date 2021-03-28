<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Port\Reader\ReaderFactory;

final class JsonResourceImporter extends ResourceImporter implements SingleDataArrayImporterInterface
{
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

        if ($this->batchCount < 0) {
            $this->objectManager->flush();
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
        $this->objectManager->flush();
    }
}
