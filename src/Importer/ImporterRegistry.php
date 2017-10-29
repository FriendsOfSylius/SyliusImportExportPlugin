<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Sylius\Component\Registry\ServiceRegistry;

class ImporterRegistry extends ServiceRegistry
{
    /**
     * @param string $type
     * @param string $format
     */
    static public function buildServiceName($type, $format)
    {
        return $name = sprintf('%s.%s', $type, $format);
    }
}
