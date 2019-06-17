<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;

final class ProductResourceExporter extends ResourceExporter
{
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;
    /** @var ImageTypesProviderInterface */
    private $imageTypesProvider;

    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        AttributeCodesProviderInterface $attributeCodesProvider,
        ImageTypesProviderInterface $imageTypesProvider,
        ?TransformerPoolInterface $transformerPool
    ) {
        parent::__construct($writer, $pluginPool, $resourceKeys, $transformerPool);
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->imageTypesProvider = $imageTypesProvider;
    }

    public function export(array $idsToExport): void
    {
        $this->resourceKeys = \array_merge($this->resourceKeys, $this->attributeCodesProvider->getAttributeCodesList());
        $this->resourceKeys = \array_merge($this->resourceKeys, $this->imageTypesProvider->getProductImagesCodesList());
        parent::export($idsToExport);
    }
}
