<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

interface PortExcelWriterFactoryInterface
{
    public function get(string $filename): \Port\Excel\ExcelWriter;
}
