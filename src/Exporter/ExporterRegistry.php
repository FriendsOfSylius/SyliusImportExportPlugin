<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use Sylius\Component\Registry\ServiceRegistry;

/**
 * Class ExporterRegistry
 */
class ExporterRegistry extends ServiceRegistry
{
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
}
