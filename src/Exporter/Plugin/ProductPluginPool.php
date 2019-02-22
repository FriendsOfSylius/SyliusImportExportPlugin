<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductPluginPool extends PluginPool
{
    public function __construct(array $plugins, array $exportKeys, RepositoryInterface $productAttributeRepository)
    {
        $attrSlug = [];
        $productAttr = $productAttributeRepository->findBy([], ['id' => 'ASC']);
        /** @var \Sylius\Component\Product\Model\ProductAttributeInterface $attr */
        foreach ($productAttr as $attr) {
            if (!empty($attr->getCode())) {
                $attrSlug[] = $attr->getCode();
            }
        }

        $exportKeys = \array_merge($exportKeys, $attrSlug);
        parent::__construct($plugins, $exportKeys);
    }
}
