<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ResourcePlugin implements ResourcePluginInterface
{
    /**
     * @var array
     */
    protected $fieldNames = [];

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $data;

    /**
     * @param RepositoryInterface $repository
     * @param PropertyAccessorInterface $propertyAccessor
     * @param EntityManagerInterface $entityManager
     */
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
     * {@inheritdoc}
     */
    public function getData(string $id, array $keysToExport): array
    {
        if (!isset($this->data[$id])) {
            throw new \InvalidArgumentException(sprintf('Requested ID "%s", but it does not exist', $id));
        }

        $result = [];

        foreach ($keysToExport as $exportKey) {
            if ($this->hasPluginDataForExportKey($id, $exportKey)) {
                $result[$exportKey] = $this->getDataForExportKey($id, $exportKey);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getFieldNames(): array
    {
        return $this->fieldNames;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\PropertyAccess\Exception\AccessException
     * @throws InvalidArgumentException
     */
    private function addDataForId(ResourceInterface $resource): void
    {
        $fields = $this->entityManager->getClassMetadata(\get_class($resource));

        foreach ($fields->getColumnNames() as $index => $field) {
            $this->fieldNames[$index] = ucfirst($field);

            if ($this->propertyAccessor->isReadable($resource, $field)) {
                $this->data[$resource->getId()][ucfirst($field)] = $this->propertyAccessor->getValue($resource, $field);
            }
        }
    }

    /**
     * @param string $id
     * @param string $exportKey
     *
     * @return bool
     */
    private function hasPluginDataForExportKey(string $id, string $exportKey): bool
    {
        return isset($this->data[$id][$exportKey]);
    }

    /**
     * @param string $id
     * @param string $exportKey
     *
     * @return mixed
     */
    private function getDataForExportKey(string $id, string $exportKey)
    {
        return $this->data[$id][$exportKey];
    }
}
