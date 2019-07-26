<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

interface ImageTypesProviderInterface
{
    public function getProductImagesCodesList(): array;

    public function getProductImagesCodesWithPrefixList(): array;

    public function extractImageTypeFromImport(array $keys): array;
}
