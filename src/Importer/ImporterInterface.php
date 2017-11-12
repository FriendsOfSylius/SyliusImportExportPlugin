<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

interface ImporterInterface
{
    /**
     * @param string $fileName
     *
     * @return ImporterResult
     */
    public function import(string $fileName): ImporterResult;
}
