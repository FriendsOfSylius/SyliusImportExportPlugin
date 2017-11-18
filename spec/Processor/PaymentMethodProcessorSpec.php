<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Processor\PaymentMethodProcessor;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PaymentMethodProcessorSpec extends ObjectBehavior
{
    function let(
        PaymentMethodFactoryInterface $factory,
        RepositoryInterface $repository
    ) {
        $this->beConstructedWith($factory, $repository, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PaymentMethodProcessor::class);
    }

    function it_implements_the_resource_processor_interface()
    {
        $this->shouldImplement(ResourceProcessorInterface::class);
    }

    function it_can_process_an_array_of_payment_method_data(
        PaymentMethodFactoryInterface $factory,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        PropertyAccessorInterface $propertyAccessor,
        RepositoryInterface $repository
    ) {
        $this->beConstructedWith($factory, $repository, ['Code', 'Name', 'Instructions', 'Gateway']);
        $repository->findOneBy(['code' => 'OFFLINE'])->willReturn(null);
        $factory->createWithGateway('offline')->willReturn($paymentMethod);
        $repository->add($paymentMethod)->shouldBeCalledTimes(1);

        $gatewayConfig->setGatewayName('Offline')->shouldBeCalled();

        $paymentMethod->setCode('OFFLINE')->shouldBeCalled();
        $paymentMethod->setName('Offline')->shouldBeCalled();
        $paymentMethod->setInstructions('Offline payment method instructions.')->shouldBeCalled();
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $paymentMethod->setGatewayConfig($gatewayConfig)->shouldBeCalled();

        $this->process(['Code' => 'OFFLINE', 'Name' => 'Offline', 'Instructions' => 'Offline payment method instructions.', 'Gateway' => 'offline']);
    }
}
