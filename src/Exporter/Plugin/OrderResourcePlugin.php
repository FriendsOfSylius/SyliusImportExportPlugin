<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Entities\Address;
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
                $this->addDataForResource($resource, 'Gender', $resource->getCustomer()->getGender() ? $resource->getCustomer()->getGender() : '');
                $this->addDataForResource($resource, 'Full_name', $resource->getCustomer()->getFullName() ? $resource->getCustomer()->getFullName() : '');
                $this->addDataForResource($resource, 'Telephone', $resource->getCustomer()->getPhoneNumber() ? $resource->getCustomer()->getPhoneNumber() : '');
                $this->addDataForResource($resource, 'Email', $resource->getCustomer()->getEmail() ? $resource->getCustomer()->getEmail() : '');
            }

            // insert shippingadress to the address field
            if ($resource->getShippingAddress()) {
                $shippingAddress = $resource->getShippingAddress();
                $this->addDataForResource($resource, 'Address', sprintf(
                    '%s %s, %s, %s',
                    $shippingAddress->getStreet() ? $shippingAddress->getStreet() : '',
                    $shippingAddress->getPostcode() ? $shippingAddress->getPostcode() : '',
                    $shippingAddress->getCity() ? $shippingAddress->getCity() : '',
                    $shippingAddress->getCountryCode() ? $shippingAddress->getCountryCode() : ''
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
