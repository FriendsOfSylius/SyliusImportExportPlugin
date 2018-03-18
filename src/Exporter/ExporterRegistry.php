<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Sylius\Component\Registry\ServiceRegistry;

/**
 * Class ExporterRegistry
 */
class ExporterRegistry extends ServiceRegistry
{
    const EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS = 'app.grid_event_listener.admin.crud';

    /**
     * @param string $type
     * @param string $format
     *
     * @return string
     */
    public static function buildServiceName(string $type, string $format): string
    {
        return sprintf('%s.%s', $type, $format);
    }

    /**
     * @param string $type
     * @param array $formats
     * @return string
     */
    public static function buildGridButtonsEventHookName(string $type, array $formats): string
    {
        $format = implode('_', $formats);

        return sprintf('%s_%s_%s', self::EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS, $type, $format);
    }
}
