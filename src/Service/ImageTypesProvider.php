<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use FriendsOfSylius\SyliusImportExportPlugin\Repository\ProductImageRepositoryInterface;

final class ImageTypesProvider implements ImageTypesProviderInterface
{
    public const IMAGES_PREFIX = 'Images_';

    /** @var ProductImageRepositoryInterface */
    private $productImageRepository;

    public function __construct(ProductImageRepositoryInterface $productImageRepository)
    {
        $this->productImageRepository = $productImageRepository;
    }

    public function getProductImagesCodesList(): array
    {
        return $this->extractProductImagesType();
    }

    public function getProductImagesCodesWithPrefixList(): array
    {
        return $this->extractProductImagesType(self::IMAGES_PREFIX);
    }

    public function extractImageTypeFromImport(array $keys): array
    {
        $keys = \array_filter($keys, function ($value): bool {
            return \mb_substr($value, 0, \mb_strlen(self::IMAGES_PREFIX)) === self::IMAGES_PREFIX;
        });

        $keys = \array_map([self::class, 'extractTypeOfImage'], $keys);

        return $keys;
    }

    private function extractProductImagesType(string $prefix = ''): array
    {
        $attrSlug = [];
        $productImages = $this->productImageRepository->findTypes();
        foreach ($productImages as $attr) {
            if (null === $attr['type'] || '' === $attr['type']) {
                continue;
            }

            $attrSlug[] = $prefix . $attr['type'];
        }

        $attrSlug = \array_unique($attrSlug);

        return $attrSlug;
    }

    private function extractTypeOfImage(string $value): string
    {
        return \mb_substr($value, \mb_strlen(self::IMAGES_PREFIX));
    }
}
