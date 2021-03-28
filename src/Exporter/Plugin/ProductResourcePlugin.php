<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductResourcePlugin extends ResourcePlugin
{
    /** @var RepositoryInterface */
    private $channelPricingRepository;
    /** @var RepositoryInterface */
    private $productVariantRepository;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        RepositoryInterface $channelPricingRepository,
        RepositoryInterface $productVariantRepository
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);
        $this->channelPricingRepository = $channelPricingRepository;
        $this->productVariantRepository = $productVariantRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductInterface $resource */
        foreach ($this->resources as $resource) {
            $this->addTranslationData($resource);
            $this->addTaxonData($resource);
            $this->addAttributeData($resource);
            $this->addChannelData($resource);
            $this->addImageData($resource);
            $this->addPriceData($resource);
        }
    }

    private function addTranslationData(ProductInterface $resource): void
    {
        $translation = $resource->getTranslation();

        $this->addDataForResource($resource, 'Locale', $translation->getLocale());
        $this->addDataForResource($resource, 'Name', $translation->getName());
        $this->addDataForResource($resource, 'Description', $translation->getDescription());
        $this->addDataForResource($resource, 'Short_description', $translation->getShortDescription());
        $this->addDataForResource($resource, 'Meta_description', $translation->getMetaDescription());
        $this->addDataForResource($resource, 'Meta_keywords', $translation->getMetaKeywords());
    }

    private function addTaxonData(ProductInterface $resource): void
    {
        $mainTaxonSlug = '';

        /** @var \Sylius\Component\Core\Model\TaxonInterface $mainTaxon */
        $mainTaxon = $resource->getMainTaxon();
        if (null != $mainTaxon) {
            $mainTaxonSlug = $mainTaxon->getCode();
        }

        $this->addDataForResource($resource, 'Main_taxon', $mainTaxonSlug);

        $taxonsSlug = '';
        $taxons = $resource->getTaxons();
        foreach ($taxons as $taxon) {
            $taxonsSlug .= $taxon->getCode() . '|';
        }

        $taxonsSlug = \rtrim($taxonsSlug, '|');
        $this->addDataForResource($resource, 'Taxons', $taxonsSlug);
    }

    private function addChannelData(ProductInterface $resource): void
    {
        $channelSlug = '';

        /** @var \Sylius\Component\Core\Model\ChannelInterface[] $channels */
        $channels = $resource->getChannels();
        foreach ($channels as $channel) {
            $channelSlug .= $channel->getCode() . '|';
        }

        $channelSlug = \rtrim($channelSlug, '|');

        $this->addDataForResource($resource, 'Channels', $channelSlug);
    }

    private function addAttributeData(ProductInterface $resource): void
    {
        $attributes = $resource->getAttributes();

        /** @var AttributeValueInterface $attribute */
        foreach ($attributes as $attribute) {
            $this->addDataForResource($resource, $attribute->getCode(), $attribute->getValue());
        }
    }

    private function addImageData(ProductInterface $resource): void
    {
        $images = $resource->getImages();

        /** @var ImageInterface $image */
        foreach ($images as $image) {
            $this->addDataForResource($resource, ImageTypesProvider::IMAGES_PREFIX . $image->getType(), $image->getPath());
        }
    }

    private function addPriceData(ProductInterface $resource): void
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $resource->getCode()]);
        if ($productVariant === null) {
            return;
        }

        /** @var \Sylius\Component\Core\Model\ChannelInterface[] $channels */
        $channels = $resource->getChannels();
        foreach ($channels as $channel) {
            /** @var ChannelPricingInterface|null $channelPricing */
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channel->getCode(),
                'productVariant' => $productVariant,
            ]);

            if (null === $channelPricing) {
                continue;
            }

            $this->addDataForResource($resource, 'Price', $channelPricing->getPrice());
        }
    }
}
