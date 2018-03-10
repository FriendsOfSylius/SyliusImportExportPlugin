<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Sylius\Component\Registry\ServiceRegistry;

/**
 * Class ExporterRegistry
 */
class ExporterRegistry extends ServiceRegistry
{
    const EVENT_HOOK_NAME_PREFIX_ADMIN_CRUD_AFTER_CONTENT = 'app.block_event_listener.admin.crud.after_content';
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
     *
     * @return string
     */
    public static function buildEventHookName(string $type): string
    {
        return sprintf('%s_%s', self::EVENT_HOOK_NAME_PREFIX_ADMIN_CRUD_AFTER_CONTENT, $type);
    }

    public static function buildGridButtonsEventHookName(string $type, string $format): string
    {
        return sprintf('%s_%s', self::EVENT_HOOK_NAME_PREFIX_GRID_BUTTONS, $type);
    }
}
