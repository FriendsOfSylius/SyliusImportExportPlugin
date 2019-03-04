<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;

final class ProductPluginPool extends PluginPool
{
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;

    public function __construct(
        array $plugins,
        array $exportKeys,
        AttributeCodesProviderInterface $attributeCodesProvider
    ) {
        parent::__construct($plugins, $exportKeys);
        $this->attributeCodesProvider = $attributeCodesProvider;
    }

    public function initPlugins(array $ids): void
    {
        $this->exportKeys = \array_merge($this->exportKeys, $this->attributeCodesProvider->getAttributeCodesList());
        parent::initPlugins($ids);
    }
}
