<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

final class StringToIntegerHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    protected function process($type, $value)
    {
        return (int) $value;
    }

    protected function allows($type, $value): bool
    {
        return $type === 'integer';
    }
}
