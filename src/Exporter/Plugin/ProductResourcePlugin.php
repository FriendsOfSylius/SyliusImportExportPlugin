<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ProductResourcePlugin extends ResourcePlugin
{
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

        /** @var ProductInterface $resource */
        foreach ($this->resources as $resource) {
            // insert Translation information to specific fields
            $this->addTranslationData($resource);
            // insert Taxon information
            $this->addTaxonData($resource);
            // insert Attribute Values information
            $this->addAttributeData($resource);
        }
    }

    private function addTranslationData(ProductInterface $resource): void
    {
        $translation = $resource->getTranslation();

        $this->addDataForResource($resource, 'Name', $translation->getName());
        $this->addDataForResource($resource, 'Description', $translation->getDescription());
        $this->addDataForResource($resource, 'Short_description', $translation->getShortDescription());
        $this->addDataForResource($resource, 'Meta_description', $translation->getMetaDescription());
        $this->addDataForResource($resource, 'Meta_keywords', $translation->getMetaKeywords());
    }

    private function addTaxonData(ProductInterface $resource)
    {
        $taxonSlug = '';

        /** @var \Sylius\Component\Core\Model\TaxonInterface $taxon */
        $taxon = $resource->getMainTaxon();
        if ($taxon !== null) {
            $taxonSlug = $taxon->getSlug();
        }

        $this->addDataForResource($resource, 'Main_taxon', $taxonSlug);
    }

    private function addAttributeData(ProductInterface $resource)
    {
        $attributes = $resource->getAttributes();

        /** @var AttributeValueInterface $attribute */
        foreach ($attributes as $attribute) {
            $this->addDataForResource($resource, $attribute->getCode(), $attribute->getValue());
        }
    }
}
