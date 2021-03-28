<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;

final class JsonResourceExporter extends ResourceExporter
{
    /** @var array[] */
    private $data = [];

    /** @var string */
    private $filename;

    /**
     * {@inheritdoc}
     */
    public function export(array $idsToExport): void
    {
        $this->pluginPool->initPlugins($idsToExport);

        foreach ($idsToExport as $id) {
            $this->data[] = $this->getDataForId((string) $id);
        }

        if ($this->filename !== null) { // only true if command is used to export
            file_put_contents($this->filename, $this->getExportedData());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExportedData(): string
    {
        $data = json_encode($this->data);
        return $data !== false ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function setExportFile(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): void
    {
        // no finish needed
    }
}
