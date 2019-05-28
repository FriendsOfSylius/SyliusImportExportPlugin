<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class ProductImageImageRepository extends EntityRepository implements ProductImageRepositoryInterface
{
    public function findTypes(): array
    {
        return $this->createQueryBuilder('p')
            ->groupBy('p.type')
            ->addGroupBy('p.id')
            ->getQuery()
            ->getArrayResult();
    }
}
