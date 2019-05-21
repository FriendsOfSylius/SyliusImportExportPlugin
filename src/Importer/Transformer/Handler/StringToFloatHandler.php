<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

final class StringToFloatHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    protected function process($type, $value)
    {
        return (float) $value;
    }

    protected function allows($type, $value): bool
    {
        return $type === 'float' || $type === 'percent';
    }
}
