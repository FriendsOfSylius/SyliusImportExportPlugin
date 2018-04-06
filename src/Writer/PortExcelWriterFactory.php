<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

class PortExcelWriterFactory implements PortExcelWriterFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(string $filename): \Port\Excel\ExcelWriter
    {
        return new \Port\Excel\ExcelWriter(new \SplFileObject($filename, 'w'), null, 'Excel2007', $prependHeaderRow = false);
    }
}
