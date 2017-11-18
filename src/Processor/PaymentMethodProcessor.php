<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class PaymentMethodProcessor implements ResourceProcessorInterface
{
    /** @var PaymentMethodFactoryInterface */
    private $resourceFactory;

    /** @var RepositoryInterface */
    private $resourceRepository;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var array */
    private $headerKeys;

    /**
     * @param PaymentMethodFactoryInterface $factory
     * @param RepositoryInterface           $repository
     * @param array                         $headerKeys
     */
    public function __construct(
        PaymentMethodFactoryInterface $factory,
        RepositoryInterface $repository,
        array $headerKeys
    ) {
        $this->resourceFactory = $factory;
        $this->resourceRepository = $repository;
        $this->headerKeys = $headerKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): void
    {
        $paymentMethod = $this->resourceRepository->findOneBy(['code' => $data['Code']]);

        if ($paymentMethod === null) {
            /** @var \Sylius\Component\Core\Model\PaymentMethodInterface $paymentMethod */
            $paymentMethod = $this->resourceFactory->createWithGateway($data['Gateway']);
            $paymentMethod->setCode($data['Code']);
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            throw new ImporterException('Gateway does not exist:' . $data['Gateway']);
        }

        $gatewayConfig->setGatewayName($data['Name']);
        $paymentMethod->setGatewayConfig($gatewayConfig);

        $paymentMethod->setName($data['Name']);
        $paymentMethod->setInstructions($data['Instructions']);
        $this->resourceRepository->add($paymentMethod);
    }
}
