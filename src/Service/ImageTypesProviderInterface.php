<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

interface ImageTypesProviderInterface
{
    public function getProductImagesCodesList(?bool $prefix = true): array;
}
