<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

interface AttributeCodesProviderInterface
{
    /**
     * @return string[]
     */
    public function getAttributeCodesList(): array;
}
