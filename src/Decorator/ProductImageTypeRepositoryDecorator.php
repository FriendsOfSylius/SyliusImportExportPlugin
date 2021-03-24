<?php
declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Decorator;

use Doctrine\ORM\EntityRepository;

class ProductImageTypeRepositoryDecorator
{
    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * ProductImageTypeRepositoryDecorator constructor.
     * @param EntityRepository $entityRepository
     */
    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /***
     * @return array
     */
    public function findTypes(): array
    {
        return $this->entityRepository->createQueryBuilder('p')
            ->groupBy('p.type')
            ->addGroupBy('p.id')
            ->getQuery()
            ->getArrayResult();
    }
}
