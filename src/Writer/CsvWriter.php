<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

class CsvWriter implements WriterInterface
{
    /** @var \Port\Writer */
    private $writer;

    public function __construct(\Port\Writer $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data): void
    {
        $this->writer->writeItem($data);
    }
}
