<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProviderInterface;

final class ProductPluginPool extends PluginPool
{
    /** @var ImageTypesProviderInterface */
    private $imageTypesProvider;
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;

    public function __construct(
        array $plugins,
        array $exportKeys,
        AttributeCodesProviderInterface $attributeCodesProvider,
        ImageTypesProviderInterface $imageTypesProvider
    ) {
        parent::__construct($plugins, $exportKeys);
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->imageTypesProvider = $imageTypesProvider;
    }

    public function initPlugins(array $ids): void
    {
        $this->exportKeys = \array_merge($this->exportKeys, $this->attributeCodesProvider->getAttributeCodesList());
        $this->exportKeys = \array_merge($this->exportKeys, $this->imageTypesProvider->getProductImagesCodesWithPrefixList());
        $this->exportKeysAvailable = $this->exportKeys;
        parent::initPlugins($ids);
    }
}
