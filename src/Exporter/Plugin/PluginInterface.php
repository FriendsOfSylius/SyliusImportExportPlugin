<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;

interface PluginInterface
{
    /**
     * @param string $id
     * @param array $resourceFields
     *
     * @return array
     */
    public function getData(string $id, array $resourceFields): array;

    /**
     * @param array $idsToExport
     *
     * @throws AccessException
     * @throws InvalidArgumentException
     * @throws UnexpectedTypeException
     * @throws \UnexpectedValueException
     */
    public function init(array $idsToExport): void;

    /**
     * @return array
     */
    public function getFieldNames(): array;
}
