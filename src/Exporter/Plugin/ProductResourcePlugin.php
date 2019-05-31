<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class ProductResourcePlugin extends ResourcePlugin
{
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

        /** @var \Sylius\Component\Core\Model\TaxonInterface $taxon */
        $mainTaxon = $resource->getMainTaxon();
        if (null !== $mainTaxon) {
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

        /** @var \Sylius\Component\Core\Model\ChannelInterface[] $channel */
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
}
