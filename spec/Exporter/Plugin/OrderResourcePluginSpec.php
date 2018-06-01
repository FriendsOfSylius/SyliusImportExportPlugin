<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\OrderResourcePlugin;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AddressConcatenationInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderResourcePluginSpec extends ObjectBehavior
{
    function let(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        AddressConcatenationInterface $addressConcatenation
    ) {
        $this->beConstructedWith($repository, $propertyAccessor, $entityManager, $addressConcatenation);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OrderResourcePlugin::class);
    }

    function it_implements_the_resource_plugin_interface()
    {
        $this->shouldImplement(ResourcePlugin::class);
    }

    function it_should_add_customer_data(
        RepositoryInterface $repository,
        EntityManagerInterface $entityManager,
        OrderInterface $resource,
        PropertyAccessorInterface $propertyAccessor,
        ClassMetaData $classMetadata
    ) {
        $idsToExport = [1];

        $repository->findBy(
            [
                'id' => $idsToExport,
            ]
        )->willReturn(
            [
                $resource,
            ]
        );

        $entityManager->getClassMetadata(Argument::type('string'))->willReturn($classMetadata);
        $classMetadata->getColumnNames()->willReturn(
            [
                'ShippingAddressId',
                'BillingAddressId',
                'ChannelId',
                'PromotionCouponId',
                'CustomerId',
                'Number',
                'Notes',
                'State',
                'CheckoutCompletedAt',
                'ItemsTotal',
                'AdjustmentsTotal',
                'Total',
                'CreatedAt',
                'UpdatedAt',
                'CurrencyCode',
                'LocaleCode',
                'CheckoutState',
                'PaymentState',
                'ShippingState',
                'TokenValue',
                'CustomerIp',
            ]
        );

//        $resource->getId()->shouldBeCalled();
//        $resource->getBillingAddress()->shouldBeCalled();
//        $resource->getChannel()->shouldBeCalled();
//        $resource->getPromotionCoupon()->shouldBeCalled();
//        $resource->getCustomer()->shouldBeCalled();
//        $resource->getNumber()->shouldBeCalled();
//        $resource->getState()->shouldBeCalled();
//        $resource->getCheckoutCompletedAt()->shouldBeCalled();
//        $resource->getItemsTotal()->shouldBeCalled();
//        $resource->getAdjustmentsTotal()->shouldBeCalled();
//        $resource->getTotal()->shouldBeCalled();
//        $resource->getCreatedAt()->shouldBeCalled();
//        $resource->getUpdatedAt()->shouldBeCalled();
//        $resource->getCurrencyCode()->shouldBeCalled();
//        $resource->getLocaleCode()->shouldBeCalled();
//        $resource->getCheckoutState()->shouldBeCalled();
//        $resource->getPaymentState()->shouldBeCalled();
//        $resource->getShippingState()->shouldBeCalled();
//        $resource->getTokenValue()->shouldBeCalled();
//        $resource->getCustomerIp()->shouldBeCalled();
//
//        $propertyAccessor->isReadable($resource, 'Number')->willReturn('000000001');

//        $this->init($idsToExport);
    }
}
