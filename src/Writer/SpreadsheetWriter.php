<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ExporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\InvalidOrderException;
use Port\Spreadsheet\SpreadsheetWriter as PortSpreadsheetWriter;

class SpreadsheetWriter implements WriterInterface
{
    /** @var PortSpreadsheetWriterFactoryInterface */
    private $portSpreadsheetWriterFactory;

    /** @var PortSpreadsheetWriter */
    private $writer;

    /** @var string|null */
    private $filename;

    /** @var string|null */
    private $temporaryFolder;

    public function __construct(PortSpreadsheetWriterFactoryInterface $portSpreadsheetWriterFactory, ?string $temporaryFolder = null)
    {
        $this->portSpreadsheetWriterFactory = $portSpreadsheetWriterFactory;
        $this->temporaryFolder = $temporaryFolder ?? sys_get_temp_dir();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data): void
    {
        $this->prepare();

        $this->writer->writeItem($data);
    }

    public function setFile(string $filename): void
    {
        if (null !== $this->writer) {
            throw new InvalidOrderException('Make sure method `setFile` is called before calling the `write` method');
        }

        $this->filename = $filename;
    }

    public function getFileContent(): string
    {
        $this->finish();

        $contents = file_get_contents($this->filename);
        if (false === $contents) {
            throw new ExporterException(sprintf('File %s could not be loaded', $this->filename));
        }

        unlink($this->filename);

        return $contents;
    }

    private function prepare(): void
    {
        if (null === $this->filename) {
            $tmp = tempnam($this->temporaryFolder, 'exp');
            if (false === $tmp) {
                throw new ExporterException(sprintf('Could not create temporary file in "%s"', $this->temporaryFolder));
            }
            $this->filename = $tmp;
        }

        if (null === $this->writer) {
            $this->writer = $this->portSpreadsheetWriterFactory->get($this->filename);
            $this->writer->prepare();
        }
    }

    public function finish(): void
    {
        $this->writer->finish();
    }
}
