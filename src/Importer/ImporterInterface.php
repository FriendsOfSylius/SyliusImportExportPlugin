<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

interface ImporterInterface
{
    /**
     * @param string $fileName
     *
     * @return ImporterResultInterface
     */
    public function import(string $fileName): ImporterResultInterface;

    /**
     * @param array $dataToImport
     */
    public function importSingleDataArrayWithoutResult(array $dataToImport): void;
}
