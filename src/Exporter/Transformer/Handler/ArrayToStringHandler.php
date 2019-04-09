<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

final class ArrayToStringHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    protected function process($key, $value)
    {
        return \implode('|', $value);
    }

    protected function allows($key, $value): bool
    {
        return \is_array($value);
    }
}
