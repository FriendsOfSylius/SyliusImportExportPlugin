<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Sylius\Component\Registry\ServiceRegistry;

class ImporterRegistry extends ServiceRegistry
{
    const EVENT_HOOK_NAME_PREFIX_ADMIN_CRUD_AFTER_CONTENT = 'app.block_event_listener.admin.crud.after_content';

    /**
     * @param string $type
     * @param string $format
     *
     * @return string
     */
    static public function buildServiceName($type, $format): string
    {
        return sprintf('%s.%s', $type, $format);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    static public function buildEventHookName($type): string
    {
        return sprintf('%s_%s', self::EVENT_HOOK_NAME_PREFIX_ADMIN_CRUD_AFTER_CONTENT, $type);
    }
}
