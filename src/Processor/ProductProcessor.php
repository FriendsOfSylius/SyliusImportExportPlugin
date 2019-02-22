<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributesCodeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductProcessor implements ResourceProcessorInterface
{
    /** @var ProductFactoryInterface */
    private $resourceProductFactory;
    /** @var TaxonFactoryInterface */
    private $resourceTaxonFactory;
    /** @var ProductRepositoryInterface */
    private $productRepository;
    /** @var TaxonRepositoryInterface */
    private $taxonRepository;
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;
    /** @var MetadataValidatorInterface */
    private $metadataValidator;
    /** @var array */
    private $headerKeys;
    /** @var array */
    private $attrCode;
    /** @var RepositoryInterface */
    private $productAttributeRepository;
    /** @var FactoryInterface */
    private $productAttributeValueFactory;
    /** @var AttributesCodeInterface */
    private $attributesCode;

    public function __construct(
        ProductFactoryInterface $productFactory,
        TaxonFactoryInterface $taxonFactory,
        ProductRepositoryInterface $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        MetadataValidatorInterface $metadataValidator,
        PropertyAccessorInterface $propertyAccessor,
        RepositoryInterface $productAttributeRepository,
        AttributesCodeInterface $attributesCode,
        FactoryInterface $productAttributeValueFactory,
        array $headerKeys
    ) {
        $this->resourceProductFactory = $productFactory;
        $this->resourceTaxonFactory = $taxonFactory;
        $this->productRepository = $productRepository;
        $this->taxonRepository = $taxonRepository;
        $this->metadataValidator = $metadataValidator;
        $this->propertyAccessor = $propertyAccessor;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->attributesCode = $attributesCode;
        $this->headerKeys = $headerKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): void
    {
        $this->attrCode = $this->attributesCode->getAttributeCodesList();
        $this->headerKeys = \array_merge($this->headerKeys, $this->attrCode);
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $product = $this->getProduct($data['Code']);

        $this->setDetails($product, $data);
        $this->setAttributesData($product, $data);
        $this->setMainTaxon($product, $data);

        $this->productRepository->add($product);
    }

    private function getProduct(string $code): ProductInterface
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);
        if (null === $product) {
            /** @var ProductInterface $product */
            $product = $this->resourceProductFactory->createNew();
            $product->setCode($code);
        }

        return $product;
    }

    private function setMainTaxon(ProductInterface $product, array $data)
    {
        /** @var TaxonInterface|null $mainTaxon */
        $mainTaxon = $this->taxonRepository->findOneBy(['code' => $data['Main_taxon']]);

        /** @var ProductInterface $product */
        $product->setMainTaxon($mainTaxon);
    }

    private function setAttributesData(ProductInterface $product, array $data): void
    {
        foreach ($this->attrCode as $attrCode) {
            if ($product->getAttributeByCodeAndLocale($attrCode) !== null) {
                $product->getAttributeByCodeAndLocale($attrCode)->setValue($data[$attrCode]);

                continue;
            }

            if (empty($data[$attrCode])) {
                continue;
            }

            $productAttr = $this->productAttributeRepository->findOneBy(['code' => $attrCode]);
            /** @var \Sylius\Component\Product\Model\ProductAttributeValueInterface $attr */
            $attr = $this->productAttributeValueFactory->createNew();
            $attr->setAttribute($productAttr);
            $attr->setProduct($product);
            $attr->setLocaleCode($product->getTranslation()->getLocale());
            $attr->setValue($data[$attrCode]);
            $product->addAttribute($attr);
            $this->productRepository->add($attr);
        }
    }

    private function setDetails(ProductInterface $product, array $data)
    {
        $product->setName($data['Name']);
        $product->setDescription($data['Description']);
        $product->setShortDescription($data['Short_description']);
        $product->setMetaDescription($data['Meta_description']);
        $product->setMetaKeywords($data['Meta_keywords']);
    }
}
