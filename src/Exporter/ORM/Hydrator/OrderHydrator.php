<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\ORM\Hydrator;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class OrderHydrator implements HydratorInterface
{
    /** @var RepositoryInterface */
    private $repository;

    public function __construct(
        RepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getHydratedResources(array $idsToExport): array
    {
        if (!$this->repository instanceof \Doctrine\ORM\EntityRepository) {
            /** @var ResourceInterface[] $items */
            $items = $this->repository->findBy(['id' => $idsToExport]);

            return $items;
        }

        $query = $this->findOrdersQb($idsToExport)->getQuery();
        $items = $this->enableEagerLoading($query)->getResult();
        $this->hydrateOrderItemsQb($idsToExport)->getQuery()->getResult(); // This result can be discarded

        return $items;
    }

    /**
     * @param int[]|string[] $idsToExport
     */
    private function findOrdersQb(array $idsToExport): QueryBuilder
    {
        /**
         * @var \Doctrine\ORM\EntityRepository<Order>
         */
        $repository = $this->repository;

        return $repository->createQueryBuilder('o')
            ->andWhere('o.id IN (:exportIds)')
            ->setParameter('exportIds', $idsToExport)
        ;
    }

    /**
     * @param int[]|string[] $idsToExport
     */
    private function hydrateOrderItemsQb(array $idsToExport): QueryBuilder
    {
        /**
         * @var \Doctrine\ORM\EntityRepository<Order>
         */
        $repository = $this->repository;

        // Partial hydration to make sure order items don't get lazy-loaded
        return $repository->createQueryBuilder('o')
            ->select('PARTIAL o.{id}, items')
            ->leftJoin('o.items', 'items')
            ->andWhere('o.id IN (:exportIds)')
            ->setParameter('exportIds', $idsToExport)
        ;
    }

    private function enableEagerLoading(Query $query): Query
    {
        return $query
            ->setFetchMode(
                $this->repository->getClassName(),
                'customer',
                ClassMetadata::FETCH_EAGER
            )
            ->setFetchMode(
                $this->repository->getClassName(),
                'shippingAddress',
                ClassMetadata::FETCH_EAGER
            )
            ->setFetchMode(
                $this->repository->getClassName(),
                'billingAddress',
                ClassMetadata::FETCH_EAGER
            )
            ;
    }
}
