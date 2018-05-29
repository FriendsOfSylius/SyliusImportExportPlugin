<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

use DCarbone\JsonWriterPlus;

class JsonWriter implements WriterInterface
{
    /**
     * @var JsonWriterPlus
     */
    private $writer;

    /**
     * @param JsonWriterPlus $writer
     */
    public function __construct(
        JsonWriterPlus $writer
    ) {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data): void
    {
        $this->writer->writeStartObject();
        foreach ($data as $dataName => $dataValue) {
            $this->writer->writeObjectProperty($dataName, $dataValue);
        }
        $this->writer->writeEndObject();

    }

    /**
     * {@inheritdoc}
     */
    public function setFile(string $filename): void
    {
        // not in use
    }

    /**
     * {@inheritdoc}
     */
    public function getFileContent(): string
    {
        $this->writer->writeEndArray();
        $this->writer->endJson();

        return $this->writer->getEncoded();
    }

    public function prepare(): void
    {
        $this->writer->startJson();
        $this->writer->writeStartArray();
    }
}
