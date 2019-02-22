<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

final class ProductResourceExporter extends ResourceExporter
{
    /** @var AttributeCodesProviderInterface */
    protected $attributeCodesProvider;

    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        AttributeCodesProviderInterface $attributeCodesProvider,
        ?TransformerPoolInterface $transformerPool
    ) {
        parent::__construct($writer, $pluginPool, $resourceKeys, $transformerPool);
        $this->attributeCodesProvider = $attributeCodesProvider;
    }

    public function export(array $idsToExport): void
    {
        $this->resourceKeys = \array_merge($this->resourceKeys, $this->attributeCodesProvider->getAttributeCodesList());
        parent::export($idsToExport);
    }
}
