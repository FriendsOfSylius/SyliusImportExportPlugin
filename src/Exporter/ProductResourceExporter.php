<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributesCodeInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

final class ProductResourceExporter extends ResourceExporter
{
    /** @var AttributesCodeInterface */
    protected $attributesCode;

    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        AttributesCodeInterface $attributesCode,
        ?TransformerPoolInterface $transformerPool
    ) {
        parent::__construct($writer, $pluginPool, $resourceKeys, $transformerPool);
        $this->attributesCode = $attributesCode;
    }

    public function export(array $idsToExport): void
    {
        $this->resourceKeys = \array_merge($this->resourceKeys, $this->attributesCode->getAttributeCodesList());
        parent::export($idsToExport);
    }
}
