<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributesCodeInterface;

final class ProductPluginPool extends PluginPool
{
    /** @var \FriendsOfSylius\SyliusImportExportPlugin\Service\AttributesCodeInterface */
    private $attributesCode;

    public function __construct(
        array $plugins,
        array $exportKeys,
        AttributesCodeInterface $attributesCode
    ) {
        parent::__construct($plugins, $exportKeys);
        $this->attributesCode = $attributesCode;
    }

    public function initPlugins(array $ids): void
    {
        $this->exportKeys = \array_merge($this->exportKeys, $this->attributesCode->getAttributeCodesList());
        parent::initPlugins($ids);
    }
}
