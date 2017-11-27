<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ResourcePlugin implements ResourcePluginInterface
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $data;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        $this->repository = $repository;
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getData(string $id): array
    {
        if (false === isset($this->data[$id])) {
            return [];
        }

        return $this->data[$id];
    }

    /**
     * @param array $idsToExport
     */
    public function init(array $idsToExport): void
    {
        $resources = $this->repository->findBy(['id' => $idsToExport]);
        foreach ($resources as $resource) {
            /** @var ResourceInterface $resource */
            $this->addDataForId($resource);
        }
    }

    /**
     * @param ResourceInterface $resource
     */
    private function addDataForId($resource): void
    {
        $fields = $this->entityManager->getClassMetadata(\get_class($resource));
        foreach ($fields->getColumnNames() as $field) {
            if ($field !== 'id') {
                if ($this->propertyAccessor->isReadable($resource, $field)) {
                    $this->data[$resource->getId()][ucfirst($field)] = $this->propertyAccessor->getValue($resource, $field);
                }
            }
        }
    }
}
