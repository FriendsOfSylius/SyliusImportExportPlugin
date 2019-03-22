<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class PaymentMethodProcessor implements ResourceProcessorInterface
{
    /** @var PaymentMethodFactoryInterface */
    private $resourceFactory;

    /** @var RepositoryInterface */
    private $resourceRepository;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    /**
     * @param string[]                         $headerKeys
     */
    public function __construct(
        PaymentMethodFactoryInterface $factory,
        RepositoryInterface $repository,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->resourceFactory = $factory;
        $this->resourceRepository = $repository;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $paymentMethod = $this->getPaymentMethod($data['Code'], $data['Gateway']);
        $gatewayConfig = $this->getGatewayConfig($data, $paymentMethod);
        $gatewayConfig->setGatewayName($data['Name']);

        $paymentMethod->setGatewayConfig($gatewayConfig);
        $paymentMethod->setName($data['Name']);
        $paymentMethod->setInstructions($data['Instructions']);

        $this->resourceRepository->add($paymentMethod);
    }

    private function getPaymentMethod(string $code, string $gateway): PaymentMethodInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $this->resourceRepository->findOneBy(['code' => $code]);

        if ($paymentMethod === null) {
            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $this->resourceFactory->createWithGateway($gateway);
            $paymentMethod->setCode($code);
        }

        return $paymentMethod;
    }

    /**
     * @param mixed[] $data
     */
    private function getGatewayConfig(array $data, PaymentMethodInterface $paymentMethod): GatewayConfigInterface
    {
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (null === $gatewayConfig) {
            throw new ImporterException('Gateway does not exist:' . $data['Gateway']);
        }

        return $gatewayConfig;
    }
}
