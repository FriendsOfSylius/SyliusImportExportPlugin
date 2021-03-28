<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

final class ArrayToStringHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    protected function process(?string $key, $value): string
    {
        return \implode('|', $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function allows(?string $key, $value): bool
    {
        return \is_array($value);
    }
}
