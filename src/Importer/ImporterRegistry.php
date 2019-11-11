<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Sylius\Component\Registry\ServiceRegistry;

class ImporterRegistry extends ServiceRegistry
{
    public const EVENT_HOOK_NAME_PREFIX_ADMIN_CRUD_AFTER_CONTENT = 'app.block_event_listener.admin.crud.after_content';

    public static function buildServiceName(string $type, string $format): string
    {
        if (strpos($type, '.') === false) {
            $type = 'sylius.' . $type;
        }

        return sprintf('%s.%s', $type, $format);
    }
}
