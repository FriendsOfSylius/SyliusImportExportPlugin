<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

final class StringToArrayHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    protected function process($type, $value)
    {
        return \explode('|', $value);
    }

    protected function allows($type, $value): bool
    {
        return $type === 'json' || $type === 'array' || $type === 'select';
    }
}
