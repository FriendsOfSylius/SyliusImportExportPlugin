<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentMethodsImporter extends AbstractImporter
{
    /** @var  PaymentMethodFactoryInterface */
    protected $factory;

    /** @var array */
    protected $headerKeys = ['Code', 'Gateway', 'Name', 'Instructions'];

    protected function createOrUpdateObject(array $row): void
    {
        $paymentMethod = $this->repository->findOneBy(['code' => $row['Code']]);

        if ($paymentMethod === null) {
            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $this->factory->createWithGateway($row['Gateway']);
            $paymentMethod->setCode($row['Code']);
            $this->objectManager->persist($paymentMethod);
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            throw new ImporterException('Gateway does not exist:'. $row['Gateway']);
        }

        $gatewayConfig->setGatewayName($row['Name']);
        $paymentMethod->setGatewayConfig($gatewayConfig);

        $paymentMethod->setName($row['Name']);
        $paymentMethod->setInstructions($row['Instructions']);
    }
}
