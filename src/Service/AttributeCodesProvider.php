<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeCodesProvider implements AttributeCodesProviderInterface
{
    /** @var \Sylius\Component\Resource\Repository\RepositoryInterface */
    private $productAttributeRepository;

    public function __construct(RepositoryInterface $productAttributeRepository)
    {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    public function getAttributeCodesList(): array
    {
        $attrSlug = [];
        $productAttr = $this->productAttributeRepository->findBy([], ['id' => 'ASC']);
        /** @var \Sylius\Component\Product\Model\ProductAttribute $attr */
        foreach ($productAttr as $attr) {
            if (!empty($attr->getCode())) {
                $attrSlug[] = $attr->getCode();
            }
        }

        return $attrSlug;
    }
}
