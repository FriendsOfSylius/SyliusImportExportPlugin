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
        RepositoryInterface           $repository,
        PropertyAccessorInterface     $propertyAccessor,
        EntityManagerInterface        $entityManager,
        AddressConcatenationInterface $addressConcatenation,
        HydratorInterface             $orderHydrator
    )
    {
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
            $items = $this->getItemsAndCount($resource);
            foreach ($items as $item) {

                $this->addPromotionAndPromotionCouponerData($item, (string)$resource->getId());

                $this->addGeneralData($item, (string)$resource->getId(), (string)$item->getId());

                // insert customer information to specific fields
                $this->addCustomerData($item, (string)$item->getId(), (string)$resource->getId());

                // insert shippingaddress to the specific field
                $this->addShippingAddressData($item, (string)$item->getId(), (string)$resource->getId());

                // insert billingaddress to the specific field
                $this->addBillingAddressData($item, (string)$item->getId(), (string)$resource->getId());

                $this->addOrderItemData($item);

            }
        }
    }
    protected  function  addDataForCustomResource($resource,$field)
    {
        foreach ($resource->getItems() as $item) {
            $this->addDataForResource(
                $item,
                ucfirst($field),
                $this->propertyAccessor->getValue($resource, $field)

            );
        }
    }
    private function addPromotionAndPromotionCouponerData(OrderItemInterface $resource, $id): void
    {
        $promo = $resource->getOrder()->getPromotionCoupon();
        if (!is_null($promo)) {
            $this->addDataForResource($resource, 'Promotion_ID', $promo->getPromotion()->getId());
            $this->addDataForResource($resource, 'Promotion_Name', $promo->getPromotion()->getName());
            $this->addDataForResource($resource, 'Promotion_Description', $promo->getPromotion()->getDescription());
            $this->addDataForResource($resource, 'Promotion_Coupon_ID', $promo->getId());
            $this->addDataForResource($resource, 'Promotion_Coupon_Name', $promo->getCode());
        }
    }

    private function addGeneralData(OrderItemInterface $resource, $id): void
    {
        $order = $this->getOrder($resource);

        $this->addDataForResource($resource, 'Order_Total', $order->getTotal());
        $this->addDataForResource($resource, 'Total_After_Discount', $order->getOrderPromotionTotal());
        $this->addDataForResource($resource, 'Order_created_Date', $order->getCreatedAt());
        $this->addDataForResource($resource, 'Order_updated_Date', $order->getUpdatedAt());
        $this->addDataForResource($resource, 'Shipping_Total', $order->getAdjustmentsTotal());
        $this->addDataForResource($resource, 'Currency_code', $order->getCurrencyCode());
        $this->addDataForResource($resource, 'Checkout_completed_at', $order->getCheckoutCompletedAt());
        $this->addDataForResource($resource, 'Payment_state', $order->getPaymentState());
        $this->addDataForResource($resource, 'Checkout_state', $order->getCheckoutState());
        $this->addDataForResource($resource, 'Shipping_state', $order->getShippingState());
        $this->addDataForResource($resource, 'Token_value', $order->getTokenValue());
        $this->addDataForResource($resource, 'Customer_ip', $order->getCustomerIp());
        $this->addDataForResource($resource, 'Notes', $order->getNotes());
    }

    private function addCustomerData(OrderItemInterface $resource, $id): void
    {
        $order = $this->getOrder($resource);
        $customer = $order->getCustomer();

        if (null === $customer) {
            return;
        }
        $this->addDataForResource($resource, 'Gender', $customer->getGender());
        $this->addDataForResource($resource, 'Full_name', $customer->getFullName());
        $this->addDataForResource($resource, 'Telephone', $customer->getPhoneNumber());
        $this->addDataForResource($resource, 'Email', $customer->getEmail());
        $this->addDataForResource($resource, 'Customer_Id', $customer->getId());
    }

    private function addShippingAddressData(OrderItemInterface $resource, $id): void
    {
        $order = $this->getOrder($resource);
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return;
        }
        $shippingInfoString = $this->addressConcatenation->getString($shippingAddress);
        $this->addDataForResource($resource, 'provinceCode', $shippingAddress->getProvinceCode());
        $this->addDataForResource($resource, 'provinceName', $shippingAddress->getProvinceName());
        $this->addDataForResource($resource, 'Address_Street', $shippingAddress->getStreet());
        $this->addDataForResource($resource, 'Shipping_address', $shippingInfoString);
        $this->addDataForResource($resource, 'First_Name', $shippingAddress->getFirstName());
        $this->addDataForResource($resource, 'Last_Name', $shippingAddress->getLastName());
        $this->addDataForResource($resource, 'Phone_Number', $shippingAddress->getPhoneNumber());
    }

    private function addBillingAddressData(OrderItemInterface $resource, $id): void
    {
        $order = $this->getOrder($resource);
        $billingAddress = $order->getBillingAddress();

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
            $items[] = $orderItem;
        }
        return $items;
    }

    private function addOrderItemData(OrderItemInterface $resource): void
    {
        $variant = $resource->getVariant();
        $product = $variant->getProduct();
        $this->addDataForResource($resource, 'Item_Total', $resource->getTotal());
        $this->addDataForResource($resource, 'Item_Price', $resource->getUnitPrice());
        $this->addDataForResource($resource, 'Item_Quantity', $resource->getQuantity());
        $this->addDataForResource($resource, 'Product', $resource->getProductName());
        $this->addDataForResource($resource, 'Vendor_Id', !is_null($product->getVendor()) ? $product->getVendor()->getId() : '');
        $this->addDataForResource($resource, 'Vendor_Name', !is_null($product->getVendor()) ? $product->getVendor()->getName() : '');
        $this->addDataForResource($resource, 'Vendor_Email', !is_null($product->getVendor()) ? $product->getVendor()->getEmail() : '');
    }

    protected function findResources(array $idsToExport): array
    {
        /** @var ResourceInterface[] $items */
        $items = $this->orderHydrator->getHydratedResources($idsToExport);

        return $items;
    }

    private function getOrder(OrderItemInterface $resource)
    {
        return $resource->getOrder();
    }
}
