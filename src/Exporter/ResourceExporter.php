<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

/**
 * Class ResourceExporter
 */
class ResourceExporter implements ResourceExporterInterface
{
    /** @var array */
    private $resourceKeys;

    /** @var WriterInterface */
    private $writer;

    /** @var PluginPoolInterface */
    private $pluginPool;

    /**
     * ResourceExporter constructor.
     *
     * @param WriterInterface $writer
     * @param PluginPoolInterface $pluginPool
     * @param array $resourceKeys
     */
    public function __construct(WriterInterface $writer, PluginPoolInterface $pluginPool, array $resourceKeys)
    {
        $this->writer = $writer;
        $this->pluginPool = $pluginPool;
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
    public function export(array $idsToExport): void
    {
        $this->pluginPool->initPlugins($idsToExport);
        $this->writer->write($this->resourceKeys);
        foreach ($idsToExport as $id) {
            $this->writeDataForId($id);
        }
    }

    /**
     * @param int $id
     */
    private function writeDataForId($id): void
    {
        $dataForId = $this->getDataForId($id);
        $this->writer->write($dataForId);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getDataForId($id): array
    {
        return $this->pluginPool->getDataForId((string) $id);
    }
}
