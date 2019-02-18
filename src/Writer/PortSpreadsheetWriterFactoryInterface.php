<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

interface PortSpreadsheetWriterFactoryInterface
{
    public function get(string $filename): \Port\Spreadsheet\SpreadsheetWriter;
}
