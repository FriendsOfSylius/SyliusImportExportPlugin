<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

interface ResourceExporterInterface
{
    /**
     * @param array $idsToExport
     */
    public function export(array $idsToExport): void;

    /**
     * @param string $filename
     */
    public function setExportFile(string $filename): void;

    /**
     * @return string
     */
    public function getExportedData(): string;
}
