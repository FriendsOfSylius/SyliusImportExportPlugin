<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

/**
 * Class JsonResourceExporter
 */
class JsonResourceExporter extends ResourceExporter
{

    /**
     * {@inheritdoc}
     */
    public function export(array $idsToExport): void
    {
        $this->pluginPool->initPlugins($idsToExport);
        $this->writer->prepare();
        foreach ($idsToExport as $id) {
            $this->writeDataForId((string) $id);
        }
    }

    /**
     * @param string $id
     */
    protected function writeDataForId(string $id): void
    {
        $dataForId = $this->getDataForId($id);

        // filter out only resourcekeys
        foreach ($dataForId as $dataName => $data) {
            if (!in_array($dataName, $this->resourceKeys)) {
                unset($dataForId[$dataName]);
            }
        }

        $this->writer->write($dataForId);
    }

}
