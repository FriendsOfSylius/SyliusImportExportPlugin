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

    public function getProductImagesCodesList(?bool $prefix = true): array
    {
        $attrSlug = [];
        $productImages = $this->productImageRepository->findTypes();
        foreach ($productImages as $attr) {
            if (!empty($attr['type'])) {
                if ($prefix) {
                    $attrSlug[] = self::IMAGES_PREFIX . $attr['type'];
                } else {
                    $attrSlug[] = $attr['type'];
                }
            }
        }

        return $attrSlug;
    }

    public function extractImageTypeFromImport(array $keys): array
    {
        $types = [];
        foreach ($keys as $key) {
            if (\mb_substr($key, 0, \mb_strlen(self::IMAGES_PREFIX)) === self::IMAGES_PREFIX) {
                $types[] = \mb_substr($key, \mb_strlen(self::IMAGES_PREFIX));
            }
        }

        return $types;
    }
}
