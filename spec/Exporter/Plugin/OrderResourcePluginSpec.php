<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ORM\Hydrator\HydratorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\OrderResourcePlugin;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AddressConcatenationInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\PromotionCoupon;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderResourcePluginSpec extends ObjectBehavior
{
    function let(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        AddressConcatenationInterface $addressConcatenation,
        HydratorInterface $hydrator
    ) {
        $this->beConstructedWith($repository, $propertyAccessor, $entityManager, $addressConcatenation, $hydrator);
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
        EntityManagerInterface $entityManager,
        OrderInterface $resource,
        PropertyAccessorInterface $propertyAccessor,
        ClassMetaData $classMetadata,
        AddressConcatenationInterface $addressConcatenation,
        HydratorInterface $hydrator
    ) {
        $idsToExport = [1];

        $hydrator->getHydratedResources(
            $idsToExport
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

        $dateTimeNow = new \DateTime('now');

        $customer = new Customer();
        $customer->setBirthday($dateTimeNow);
        $customer->setCreatedAt($dateTimeNow);
        $customer->setEmail('this@isamail.net');
        $customer->setFirstName('Lorem');
        $customer->setLastName('Ipsum');
        $customer->setGender('M');

        $address = new Address();
        $address->setStreet('Foo');
        $address->setCustomer($customer);
        $address->setLastName('Ipsum');
        $address->setFirstName('Lorem');
        $address->setCity('Bar');
        $address->setPostcode('1234');
        $address->setCountryCode('US');

        $product = new Product();
        $product->setCurrentLocale('en_US');
        $product->setName('ProductName');

        $variant = new ProductVariant();
        $variant->setProduct($product);

        $channel = new Channel();

        $promotionCoupon = new PromotionCoupon();

        $addressConcatenation->getString($address)->shouldBeCalled()->willReturn('Fullname and address');

        $resource->getId()->willReturn(1);
        $resource->getShippingAddress()->willReturn($address);
        $resource->getBillingAddress()->willReturn($address);
        $resource->getChannel()->willReturn($channel);
        $resource->getPromotionCoupon()->willReturn($promotionCoupon);
        $resource->getCustomer()->willReturn($customer);
        $resource->getNumber()->willReturn(null);
        $resource->getNotes()->willReturn(null);
        $resource->getState()->willReturn('New');
        $resource->getCheckoutCompletedAt()->willReturn($dateTimeNow);
        $resource->getItemsTotal()->willReturn(123);
        $resource->getAdjustmentsTotal()->willReturn(123);
        $resource->getTotal()->willReturn(246);
        $resource->getCreatedAt()->willReturn(null);
        $resource->getUpdatedAt()->willReturn(null);
        $resource->getCurrencyCode()->willReturn(null);
        $resource->getLocaleCode()->willReturn(null);
        $resource->getCheckoutState()->willReturn(null);
        $resource->getPaymentState()->willReturn(null);
        $resource->getShippingState()->willReturn(null);
        $resource->getTokenValue()->willReturn(null);
        $resource->getCustomerIp()->willReturn(null);

        $orderItem = new OrderItem();
        $orderItem->setVariant($variant);

        $resource->getItems()->shouldBeCalled()->willReturn(new ArrayCollection([$orderItem, $orderItem]));

        $propertyAccessor->isReadable($resource, 'ShippingAddressId')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'BillingAddressId')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'ChannelId')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'PromotionCouponId')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'CustomerId')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'Number')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'Notes')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'State')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'CheckoutCompletedAt')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'ItemsTotal')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'AdjustmentsTotal')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'Total')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'CreatedAt')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'UpdatedAt')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'CurrencyCode')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'LocaleCode')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'CheckoutState')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'PaymentState')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'ShippingState')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'TokenValue')->willReturn(true);
        $propertyAccessor->isReadable($resource, 'CustomerIp')->willReturn(true);

        $propertyAccessor->getValue($resource, 'ShippingAddressId')->willReturn($address);
        $propertyAccessor->getValue($resource, 'BillingAddressId')->willReturn($address);
        $propertyAccessor->getValue($resource, 'ChannelId')->willReturn($channel);
        $propertyAccessor->getValue($resource, 'PromotionCouponId')->willReturn($promotionCoupon);
        $propertyAccessor->getValue($resource, 'CustomerId')->willReturn($customer);
        $propertyAccessor->getValue($resource, 'Number')->willReturn(null);
        $propertyAccessor->getValue($resource, 'Notes')->willReturn(null);
        $propertyAccessor->getValue($resource, 'State')->willReturn('New');
        $propertyAccessor->getValue($resource, 'CheckoutCompletedAt')->willReturn($dateTimeNow);
        $propertyAccessor->getValue($resource, 'ItemsTotal')->willReturn(123);
        $propertyAccessor->getValue($resource, 'AdjustmentsTotal')->willReturn(123);
        $propertyAccessor->getValue($resource, 'Total')->willReturn(246);
        $propertyAccessor->getValue($resource, 'CreatedAt')->willReturn(null);
        $propertyAccessor->getValue($resource, 'UpdatedAt')->willReturn(null);
        $propertyAccessor->getValue($resource, 'CurrencyCode')->willReturn(null);
        $propertyAccessor->getValue($resource, 'LocaleCode')->willReturn(null);
        $propertyAccessor->getValue($resource, 'CheckoutState')->willReturn(null);
        $propertyAccessor->getValue($resource, 'PaymentState')->willReturn(null);
        $propertyAccessor->getValue($resource, 'ShippingState')->willReturn(null);
        $propertyAccessor->getValue($resource, 'TokenValue')->willReturn(null);
        $propertyAccessor->getValue($resource, 'CustomerIp')->willReturn(null);

        $this->init($idsToExport);
        $this->getData('1', [
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
        ])
            ->shouldReturn([
                'ShippingAddressId' => $address,
                'BillingAddressId' => $address,
                'ChannelId' => $channel,
                'PromotionCouponId' => $promotionCoupon,
                'CustomerId' => $customer,
                'Number' => '',
                'Notes' => '',
                'State' => 'New',
                'CheckoutCompletedAt' => $dateTimeNow,
                'ItemsTotal' => 123,
                'AdjustmentsTotal' => 123,
                'Total' => 246,
                'CreatedAt' => '',
                'UpdatedAt' => '',
                'CurrencyCode' => '',
                'LocaleCode' => '',
                'CheckoutState' => '',
                'PaymentState' => '',
                'ShippingState' => '',
                'TokenValue' => '',
                'CustomerIp' => '',
            ]);

        // Should error when unknown ID is asked
        $this->shouldThrow()->during('getData', ['2', [
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
        ]]);
    }
}
