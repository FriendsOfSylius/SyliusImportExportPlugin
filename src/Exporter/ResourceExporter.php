<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

/**
 * Class ResourceExporter
 */
class ResourceExporter implements ResourceExporterInterface
{
    /**
     * @var array
     */
    private $resourceKeys;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var PluginPoolInterface
     */
    private $pluginPool;

    /**
     * @var TransformerPoolInterface|null
     */
    private $transformerPool;

    /**
     * @param WriterInterface $writer
     * @param PluginPoolInterface $pluginPool
     * @param array $resourceKeys
     * @param TransformerPoolInterface|null $transformerPool
     */
    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        ?TransformerPoolInterface $transformerPool
    ) {
        $this->writer = $writer;
        $this->pluginPool = $pluginPool;
        $this->transformerPool = $transformerPool;
        $this->resourceKeys = $resourceKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportFile(string $filename): void
    {
        $this->writer->setFile($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportedData(string $filename): string
    {
        return $this->writer->getFileContent($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $idsToExport): void
    {
        $this->pluginPool->initPlugins($idsToExport);
        $this->writer->write($this->resourceKeys);

        foreach ($idsToExport as $id) {
            $this->writeDataForId((string) $id);
        }
    }

    /**
     * @param string $id
     */
    private function writeDataForId(string $id): void
    {
        $dataForId = $this->getDataForId($id);

        $this->writer->write($dataForId);
    }

    /**
     * @param string $id
     *
     * @return array
     */
    private function getDataForId(string $id): array
    {
        $data = $this->pluginPool->getDataForId($id);

        if (null !== $this->transformerPool) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->transformerPool->handle($key, $value);
            }
        }

        return $data;
    }
}
