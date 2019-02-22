<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

interface AttributeCodesProviderInterface
{
    public function getAttributeCodesList(): array;
}
