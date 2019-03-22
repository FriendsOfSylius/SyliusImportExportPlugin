<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Sylius\Component\Registry\ServiceRegistry;

final class ExporterRegistry extends ServiceRegistry
{
    public const EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS = 'app.grid_event_listener.admin.crud';

    public static function buildServiceName(string $type, string $format): string
    {
        return sprintf('%s.%s', $type, $format);
    }

    /**
     * @param string[] $formats
     */
    public static function buildGridButtonsEventHookName(string $type, array $formats): string
    {
        $format = implode('_', $formats);

        return sprintf('%s_%s_%s', self::EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS, $type, $format);
    }
}
