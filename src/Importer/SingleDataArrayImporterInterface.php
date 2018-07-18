<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

interface SingleDataArrayImporterInterface extends ImporterInterface
{
    /**
     * @param array $dataToImport
     */
    public function importSingleDataArrayWithoutResult(array $dataToImport): void;
}
