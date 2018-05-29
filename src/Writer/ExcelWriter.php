<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\InvalidOrderException;
use Port\Excel\ExcelWriter as PortExcelWriter;

class ExcelWriter implements WriterInterface
{
    /**
     * @var PortExcelWriterFactoryInterface
     */
    private $portExcelWriterFactory;

    /**
     * @var PortExcelWriter
     */
    private $writer;

    /**
     * @var string|null
     */
    private $filename;

    /**
     * @var string|null
     */
    private $temporaryFolder;

    /**
     * @param PortExcelWriterFactoryInterface $portExcelWriterFactory
     * @param string|null $temporaryFolder
     */
    public function __construct(PortExcelWriterFactoryInterface $portExcelWriterFactory, ?string $temporaryFolder = null)
    {
        $this->portExcelWriterFactory = $portExcelWriterFactory;
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

    /**
     * {@inheritdoc}
     */
    public function setFile(string $filename): void
    {
        if (null !== $this->writer) {
            throw new InvalidOrderException('Make sure method `setFile` is called before calling the `write` method');
        }

        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileContent(): string
    {
        $this->finish();

        $contents = file_get_contents($this->filename);

        unlink($this->filename);

        return $contents;
    }

    private function prepare(): void
    {
        if (null === $this->filename) {
            $this->filename = tempnam($this->temporaryFolder, 'exp');
        }

        if (null === $this->writer) {
            $this->writer = $this->portExcelWriterFactory->get($this->filename);
            $this->writer->prepare();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): void
    {
        $this->writer->finish();
    }
}
