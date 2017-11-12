<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ResourceProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $resourceFactory;

    /** @var RepositoryInterface */
    private $resourceRepository;

    /** @var array */
    private $headerKeys;

    public function __construct(
        FactoryInterface $resourceFactory,
        RepositoryInterface $resourceRepository,
        array $headerKeys = []
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceRepository = $resourceRepository;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data)
    {
        /** @var ResourceInterface $resource */
        $resource = $this->resourceRepository->findOneBy(['code' => $data['Code']]);

        if (null === $resource) {
            $resource = $this->resourceFactory->createNew();
        }
        foreach ($this->headerKeys as $headerKey) {
            $method = 'set' . ucfirst($headerKey);
            if (!method_exists($resource, $method)) {
                throw new ImporterException(sprintf('Method for %s not found in resource %s', $method, $resource));
            }
            call_user_func_array([$resource, $method], [$data[$headerKey]]);
        }
        $this->resourceRepository->add($resource);
    }
}
