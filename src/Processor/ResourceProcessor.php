<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\AccessorNotFoundException;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ResourceProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $resourceFactory;

    /** @var RepositoryInterface */
    private $resourceRepository;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string[] */
    private $headerKeys;

    /**
     * @param string[] $headerKeys
     */
    public function __construct(
        FactoryInterface $resourceFactory,
        RepositoryInterface $resourceRepository,
        PropertyAccessorInterface $propertyAccessor,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager,
        array $headerKeys
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceRepository = $resourceRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ResourceInterface $resource */
        $resource = $this->getResource($data);

        foreach ($this->headerKeys as $headerKey) {
            if (false === $this->propertyAccessor->isReadable($resource, $headerKey)) {
                throw new AccessorNotFoundException(
                    sprintf(
                        'No Accessor found for %s in Resource %s, ' .
                        'please implement one or change the Header-Key to an existing field',
                        $headerKey,
                        \get_class($resource)
                    )
                );
            }
            $dataValue = $data[$headerKey];
            if (strlen((string) $dataValue) === 0 && !is_bool($dataValue)) {
                $dataValue = null;
            }
            $this->propertyAccessor->setValue($resource, $headerKey, $dataValue);
        }

        $this->entityManager->persist($resource);
    }

    /**
     * @param mixed[] $data
     */
    private function getResource(array $data): ResourceInterface
    {
        $lowerCaseKey = strtolower((string) key($data));

        /** @var ResourceInterface|null $resource */
        $resource = $this->resourceRepository->findOneBy([$lowerCaseKey => $data[key($data)]]);

        if (null === $resource) {
            /** @var ResourceInterface $resource */
            $resource = $this->resourceFactory->createNew();
        }

        return $resource;
    }
}
