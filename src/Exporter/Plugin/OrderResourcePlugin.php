<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ORM\Hydrator\HydratorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AddressConcatenationInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderResourcePlugin extends ResourcePlugin
{
    /** @var AddressConcatenationInterface */
    private $addressConcatenation;

    /** @var HydratorInterface */
    private $orderHydrator;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        AddressConcatenationInterface $addressConcatenation,
        HydratorInterface $orderHydrator
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);

        $this->addressConcatenation = $addressConcatenation;
        $this->orderHydrator = $orderHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var OrderInterface $resource */
        foreach ($this->resources as $resource) {
            // insert general fields
            $this->addGeneralData($resource);

            // insert customer information to specific fields
            $this->addCustomerData($resource);

            // insert shippingaddress to the specific field
            $this->addShippingAddressData($resource);

            // insert billingaddress to the specific field
            $this->addBillingAddressData($resource);

            $items = $this->getItemsAndCount($resource);

            $this->addOrderItemData($items, $resource);
        }
    }

    private function addGeneralData(OrderInterface $resource): void
    {
        $this->addDataForResource($resource, 'Currency_code', $resource->getCurrencyCode());
        $this->addDataForResource($resource, 'Checkout_completed_at', $resource->getCheckoutCompletedAt());
        $this->addDataForResource($resource, 'Payment_state', $resource->getPaymentState());
        $this->addDataForResource($resource, 'Checkout_state', $resource->getCheckoutState());
        $this->addDataForResource($resource, 'Shipping_state', $resource->getShippingState());
        $this->addDataForResource($resource, 'Token_value', $resource->getTokenValue());
        $this->addDataForResource($resource, 'Customer_ip', $resource->getCustomerIp());
        $this->addDataForResource($resource, 'Notes', $resource->getNotes());
    }

    private function addCustomerData(OrderInterface $resource): void
    {
        $customer = $resource->getCustomer();

        if (null === $customer) {
            return;
        }

        $this->addDataForResource($resource, 'Gender', $customer->getGender());
        $this->addDataForResource($resource, 'Full_name', $customer->getFullName());
        $this->addDataForResource($resource, 'Telephone', $customer->getPhoneNumber());
        $this->addDataForResource($resource, 'Email', $customer->getEmail());
    }

    private function addShippingAddressData(OrderInterface $resource): void
    {
        $shippingAddress = $resource->getShippingAddress();

        if (null === $shippingAddress) {
            return;
        }

        $shippingInfoString = $this->addressConcatenation->getString($shippingAddress);

        $this->addDataForResource($resource, 'Shipping_address', $shippingInfoString);
    }

    private function addBillingAddressData(OrderInterface $resource): void
    {
        $billingAddress = $resource->getBillingAddress();

        if (null === $billingAddress) {
            return;
        }

        $billingInfoString = $this->addressConcatenation->getString($billingAddress);

        $this->addDataForResource($resource, 'Billing_address', $billingInfoString);
    }

    private function getItemsAndCount(OrderInterface $resource): array
    {
        $items = [];

        /** @var OrderItemInterface $orderItem */
        foreach ($resource->getItems() as $orderItem) {
            /** @var ProductVariantInterface $variant */
            $variant = $orderItem->getVariant();
            /** @var ProductInterface $product */
            $product = $variant->getProduct();

            if (!isset($items[$product->getId()])) {
                $items[$product->getId()] = [
                    'name' => $product->getName(),
                    'count' => 0,
                ];
            }
            $items[$product->getId()]['count'] += $orderItem->getQuantity();
        }

        return $items;
    }

    private function addOrderItemData(array $items, OrderInterface $resource): void
    {
        $str = '';

        foreach ($items as $itemId => $item) {
            if (null != $str && '' != $str) {
                $str .= ' | ';
            }
            $str .= sprintf('%dx %s(id:%d)', $item['count'], $item['name'], $itemId);
        }

        $this->addDataForResource($resource, 'Product_list', $str);
    }

    protected function findResources(array $idsToExport): array
    {
        /** @var ResourceInterface[] $items */
        $items = $this->orderHydrator->getHydratedResources($idsToExport);

        return $items;
    }
}
