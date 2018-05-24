<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderResourcePlugin extends ResourcePlugin
{
    /**
     * @param RepositoryInterface $repository
     * @param PropertyAccessorInterface $propertyAccessor
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);
    }

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
                $customer = $resource->getCustomer();
                $this->addDataForResource($resource, 'Gender', $customer->getGender() ? $customer->getGender() : '');
                $this->addDataForResource($resource, 'Full_name', $customer->getFullName() ? $customer->getFullName() : '');
                $this->addDataForResource($resource, 'Telephone', $customer->getPhoneNumber() ? $customer->getPhoneNumber() : '');
                $this->addDataForResource($resource, 'Email', $customer->getEmail() ? $customer->getEmail() : '');
            }

            // insert shippingaddress to the specific field
            if ($resource->getShippingAddress()) {
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

            // insert billingaddress to the specific field
            if ($resource->getBillingAddress()) {
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
}
