<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

use Port\Excel\ExcelWriter;

class PortExcelWriterFactory implements PortExcelWriterFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(string $filename): ExcelWriter
    {
        return new ExcelWriter(
            new \SplFileObject($filename, 'w'),
            null,
            'Excel2007',
            $prependHeaderRow = false
        );
    }
}
