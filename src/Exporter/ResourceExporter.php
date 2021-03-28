<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

class ResourceExporter implements ResourceExporterInterface
{
    /** @var string[] */
    protected $resourceKeys;

    /** @var ?WriterInterface */
    protected $writer;

    /** @var PluginPoolInterface */
    protected $pluginPool;

    /** @var TransformerPoolInterface|null */
    protected $transformerPool;

    /**
     * @param string[] $resourceKeys
     */
    public function __construct(
        ?WriterInterface $writer,
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
        if (null === $this->writer) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s of class %s require a writer parameter', __METHOD__, __CLASS__
            ));
        }

        $this->writer->setFile($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportedData(): string
    {
        if (null === $this->writer) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s of class %s require a writer parameter', __METHOD__, __CLASS__
            ));
        }

        return $this->writer->getFileContent();
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $idsToExport): void
    {
        if (null === $this->writer) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s of class %s require a writer parameter', __METHOD__, __CLASS__
            ));
        }
        $this->pluginPool->initPlugins($idsToExport);
        $this->writer->write($this->resourceKeys);

        foreach ($idsToExport as $id) {
            $this->writeDataForId((string) $id);
        }
    }

    /**
     * @param int[] $idsToExport
     *
     * @return array[]
     */
    public function exportData(array $idsToExport): array
    {
        $this->pluginPool->initPlugins($idsToExport);

        if (null === $this->writer) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s of class %s require a writer parameter', __METHOD__, __CLASS__
            ));
        }

        $this->writer->write($this->resourceKeys);

        $exportIdDataArray = [];

        foreach ($idsToExport as $id) {
            $exportIdDataArray[$id] = $this->getDataForId((string) $id);
        }

        return $exportIdDataArray;
    }

    private function writeDataForId(string $id): void
    {
        $dataForId = $this->getDataForId($id);

        if (null === $this->writer) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s of class %s require a writer parameter', __METHOD__, __CLASS__
            ));
        }

        $this->writer->write($dataForId);
    }

    /**
     * @return array[]
     */
    protected function getDataForId(string $id): array
    {
        $data = $this->pluginPool->getDataForId($id);

        if (null !== $this->transformerPool) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->transformerPool->handle($key, $value);
            }
        }

        return $data;
    }

    public function finish(): void
    {
        if (null === $this->writer) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s of class %s require a writer parameter', __METHOD__, __CLASS__
            ));
        }

        $this->writer->finish();
    }
}
