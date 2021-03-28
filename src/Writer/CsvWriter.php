<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ExporterException;
use Port\Csv\CsvWriter as PortCsvWriter;

class CsvWriter implements WriterInterface
{
    /** @var PortCsvWriter */
    private $writer;

    public function __construct(PortCsvWriter $writer)
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

    public function setFile(string $filename): void
    {
        $file = fopen($filename, 'w+');
        if (false === $file) {
            throw new \Exception('File open failed.');
        }

        $this->writer->setStream($file);
    }

    public function getFileContent(): string
    {
        $this->writer->setCloseStreamOnFinish(true);

        rewind($this->writer->getStream());
        $contents = stream_get_contents($this->writer->getStream());
        if (false === $contents) {
            throw new ExporterException(sprintf('Data stream could not be opened'));
        }

        $this->finish();

        return $contents;
    }

    public function finish(): void
    {
        $this->writer->finish();
    }
}
