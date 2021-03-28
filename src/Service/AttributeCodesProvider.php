<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AttributeCodesProvider implements AttributeCodesProviderInterface
{
    /** @var RepositoryInterface */
    private $productAttributeRepository;

    public function __construct(RepositoryInterface $productAttributeRepository)
    {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    public function getAttributeCodesList(): array
    {
        $attrSlug = [];
        $productAttr = $this->productAttributeRepository->findBy([], ['id' => 'ASC']);
        /** @var ProductAttribute $attr */
        foreach ($productAttr as $attr) {
            if (!(null === $attr->getCode() || '' === $attr->getCode())) {
                $attrSlug[] = $attr->getCode();
            }
        }

        return $attrSlug;
    }
}
