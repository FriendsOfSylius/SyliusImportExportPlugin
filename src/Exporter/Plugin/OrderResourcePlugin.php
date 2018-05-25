<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;

class OrderResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var OrderInterface $resource */
        foreach ($this->resources as $resource) {
            // insert customer information to specific fields
            if ($resource->getCustomer()) {
                $this->addCustomerData($resource);
            }

            // insert shippingaddress to the specific field
            if ($resource->getShippingAddress()) {
                $this->addShippingAddressData($resource);
            }

            // insert billingaddress to the specific field
            if ($resource->getBillingAddress()) {
                $this->addBillingAddressData($resource);
            }

            $items = $this->getItemsAndCount($resource);

            $this->addOrderItemData($items, $resource);
        }
    }

    /**
     * @param OrderInterface $resource
     */
    private function addCustomerData(OrderInterface $resource)
    {
        $customer = $resource->getCustomer();
        $this->addDataForResource($resource, 'Gender', $customer->getGender() ? $customer->getGender() : '');
        $this->addDataForResource($resource, 'Full_name', $customer->getFullName() ? $customer->getFullName() : '');
        $this->addDataForResource($resource, 'Telephone', $customer->getPhoneNumber() ? $customer->getPhoneNumber() : '');
        $this->addDataForResource($resource, 'Email', $customer->getEmail() ? $customer->getEmail() : '');
    }

    /**
     * @param OrderInterface $resource
     */
    private function addShippingAddressData(OrderInterface $resource)
    {
        $shippingAddress = $resource->getShippingAddress();
        $this->addDataForResource($resource, 'Shipping_address', sprintf(
            '%s, %s, %s, %s, %s',
            $shippingAddress->getFullName() ? $shippingAddress->getFullName() : '',
            $shippingAddress->getStreet() ? $shippingAddress->getStreet() : '',
            $shippingAddress->getCity() ? $shippingAddress->getCity() : '',
            $shippingAddress->getPostcode() ? $shippingAddress->getPostcode() : '',
            $shippingAddress->getCountryCode() ? $shippingAddress->getCountryCode() : ''
        ));
    }

    /**
     * @param OrderInterface $resource
     */
    private function addBillingAddressData(OrderInterface $resource)
    {
        $billingAddress = $resource->getBillingAddress();
        $this->addDataForResource($resource, 'Billing_address', sprintf(
            '%s, %s, %s, %s, %s',
            $billingAddress->getFullName() ? $billingAddress->getFullName() : '',
            $billingAddress->getStreet() ? $billingAddress->getStreet() : '',
            $billingAddress->getCity() ? $billingAddress->getCity() : '',
            $billingAddress->getPostcode() ? $billingAddress->getPostcode() : '',
            $billingAddress->getCountryCode() ? $billingAddress->getCountryCode() : ''
        ));
    }

    /**
     * @param OrderInterface $resource
     *
     * @return array
     */
    private function getItemsAndCount(OrderInterface $resource)
    {
        $items = [];

        /** @var OrderItemInterface $orderItem */
        foreach ($resource->getItems() as $orderItem) {
            /** @var ProductVariantInterface $variant */
            $variant = $orderItem->getVariant();
            /** @var ProductInterface $product */
            $product = $variant->getProduct();

            if (!isset($items[$product->getName()])) {
                $items[$product->getName()] = 0;
            }

            $items[$product->getName()] += $orderItem->getQuantity();
        }

        return $items;
    }

    /**
     * @param OrderInterface $resource
     */
    private function addOrderItemData(array $items, OrderInterface $resource)
    {
        $str = '';

        foreach ($items as $itemName => $quantity) {
            if (!empty($str)) {
                $str .= ' | ';
            }
            $str .= sprintf('%dx %s', $quantity, $itemName);
        }

        $this->addDataForResource($resource, 'Product_list', $str);
    }
}
