<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Repository;

use Sylius\Component\Resource\Repository\RepositoryInterface;

interface ProductImageRepositoryInterface extends RepositoryInterface
{
    public function findTypes(): array;
}
