<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\AccessorNotFoundException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
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

    /** @var array */
    private $headerKeys;

    /**
     * ResourceProcessor constructor.
     *
     * @param FactoryInterface $resourceFactory
     * @param RepositoryInterface $resourceRepository
     * @param PropertyAccessorInterface $propertyAccessor
     * @param MetadataValidatorInterface $metadataValidator
     * @param array $headerKeys
     */
    public function __construct(
        FactoryInterface $resourceFactory,
        RepositoryInterface $resourceRepository,
        PropertyAccessorInterface $propertyAccessor,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceRepository = $resourceRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessorNotFoundException
     * @throws ItemIncompleteException
     * @throws \Symfony\Component\PropertyAccess\Exception\AccessException
     * @throws \Symfony\Component\PropertyAccess\Exception\InvalidArgumentException
     * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $resource = $this->getResource($data['Code']);

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

            $this->propertyAccessor->setValue($resource, $headerKey, $data[$headerKey]);
        }

        $this->resourceRepository->add($resource);
    }

    /**
     * @param string $code
     *
     * @return ResourceInterface
     */
    private function getResource(string $code): ResourceInterface
    {
        /** @var ResourceInterface|null $resource */
        $resource = $this->resourceRepository->findOneBy(['code' => $code]);

        if (null === $resource) {
            /** @var ResourceInterface $resource */
            $resource = $this->resourceFactory->createNew();
        }

        return $resource;
    }
}
