<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Service\StringArrayConcatenation;
use FriendsOfSylius\SyliusImportExportPlugin\Service\StringArrayConcatenationInterface;
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
            $this->addCustomerData($resource);

            // insert shippingaddress to the specific field
            $this->addShippingAddressData($resource);

            // insert billingaddress to the specific field
            $this->addBillingAddressData($resource);

            $items = $this->getItemsAndCount($resource);

            $this->addOrderItemData($items, $resource);
        }
    }

    /**
     * @param OrderInterface $resource
     */
    private function addCustomerData(OrderInterface $resource): void
    {
        $customer = $resource->getCustomer();
        if (null !== $customer) {
            $this->addDataForResource($resource, 'Gender', $customer->getGender());
            $this->addDataForResource($resource, 'Full_name', $customer->getFullName());
            $this->addDataForResource($resource, 'Telephone', $customer->getPhoneNumber());
            $this->addDataForResource($resource, 'Email', $customer->getEmail());
        }
    }

    /**
     * @param OrderInterface $resource
     */
    private function addShippingAddressData(OrderInterface $resource): void
    {
        $shippingAddress = $resource->getShippingAddress();
        if (null !== $shippingAddress) {
            /** @var StringArrayConcatenationInterface $stringArrayConcatenation */
            $stringArrayConcatenation = new StringArrayConcatenation();

            $shippingInfoString = $stringArrayConcatenation->getConcatenatedString([
                $shippingAddress->getFullName(),
                $shippingAddress->getStreet(),
                $shippingAddress->getCity(),
                $shippingAddress->getPostcode(),
                $shippingAddress->getCountryCode(),
            ]);

            $this->addDataForResource($resource, 'Shipping_address', $shippingInfoString);
        }
    }

    /**
     * @param OrderInterface $resource
     */
    private function addBillingAddressData(OrderInterface $resource): void
    {
        $billingAddress = $resource->getBillingAddress();
        if (null !== $billingAddress) {
            /** @var StringArrayConcatenationInterface $stringArrayConcatenation */
            $stringArrayConcatenation = new StringArrayConcatenation();

            $billingInfoString = $stringArrayConcatenation->getConcatenatedString([
                $billingAddress->getFullName(),
                $billingAddress->getStreet(),
                $billingAddress->getCity(),
                $billingAddress->getPostcode(),
                $billingAddress->getCountryCode(),
            ]);

            $this->addDataForResource($resource, 'Billing_address', $billingInfoString);
        }
    }

    /**
     * @param OrderInterface $resource
     *
     * @return array
     */
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

    /**
     * @param OrderInterface $resource
     */
    private function addOrderItemData(array $items, OrderInterface $resource): void
    {
        $str = '';

        foreach ($items as $itemId => $item) {
            if (!empty($str)) {
                $str .= ' | ';
            }
            $str .= sprintf('%dx %s(id:%d)', $item['count'], $item['name'], $itemId);
        }

        $this->addDataForResource($resource, 'Product_list', $str);
    }
}
