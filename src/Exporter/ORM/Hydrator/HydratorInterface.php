<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\ORM\Hydrator;

use Sylius\Component\Resource\Model\ResourceInterface;

interface HydratorInterface
{
    /**
     * @param int[]|string[] $idsToExport
     * @return ResourceInterface[]
     */
    public function getHydratedResources(array $idsToExport): array;
}
