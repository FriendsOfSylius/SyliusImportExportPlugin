<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

final class StringToArrayHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    protected function process(?string $type, string $value): array
    {
        return \explode('|', $value);
    }

    protected function allows(?string $type, string $value): bool
    {
        return $type === 'json' || $type === 'array' || $type === 'select';
    }
}
