<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductResourceExporter extends ResourceExporter
{
    public function __construct(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        array $resourceKeys,
        RepositoryInterface $productAttributeRepository,
        ?TransformerPoolInterface $transformerPool
    ) {
        $attrSlug = [];
        $productAttr = $productAttributeRepository->findBy([], ['id' => 'ASC']);

        /** @var \Sylius\Component\Product\Model\ProductAttribute $attr */
        foreach ($productAttr as $attr) {
            $attrSlug[] = $attr->getCode();
        }

        $resourceKeys = \array_merge($resourceKeys, $attrSlug);
        parent::__construct($writer, $pluginPool, $resourceKeys, $transformerPool);
    }
}
