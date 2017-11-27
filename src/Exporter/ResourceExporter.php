<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

class ResourceExporter implements ResourceExporterInterface
{
    /** @var WriterInterface */
    private $writer;

    /** @var PluginPoolInterface */
    private $pluginPool;

    /**
     * ResourceExporter constructor.
     *
     * @param WriterInterface $writer
     * @param PluginPoolInterface $pluginPool
     */
    public function __construct(WriterInterface $writer, PluginPoolInterface $pluginPool)
    {
        $this->writer = $writer;
        $this->pluginPool = $pluginPool;
    }

    public function export(array $idsToExport)
    {
        foreach ($idsToExport as $id) {
            $this->writeDataForId($id);
        }
    }

    /**
     * @param $id
     */
    private function writeDataForId($id): void
    {
        $dataForId = $this->getDataForId($id);
        $this->writer->write($dataForId);
    }

    /**
     * @param $id
     *
     * @return array
     */
    private function getDataForId($id): array
    {
        $dataForId = [[]];
        $plugins = $this->pluginPool->getPlugins();
        foreach ($plugins as $plugin) {
            /** @var PluginInterface $plugin */
            $dataForId[] = $plugin->getData($id);
        }
        $dataForId = array_merge(...$dataForId);

        return $dataForId;
    }
}
