<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;

final class JsonResourceExporter extends ResourceExporter
{
    private $data = [];

    /**
     * @var string
     */
    private $filename;

    /**
     * @param string[] $resourceKeys
     */
    public function __construct(
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        ?TransformerPoolInterface $transformerPool
    ) {
        $this->pluginPool = $pluginPool;
        $this->transformerPool = $transformerPool;
        $this->resourceKeys = $resourceKeys;
    }

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
        return json_encode($this->data);
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
