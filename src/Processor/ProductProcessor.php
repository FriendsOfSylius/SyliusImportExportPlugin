<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductProcessor implements ResourceProcessorInterface
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $manager;
    /** @var TransformerPoolInterface|null */
    private $transformerPool;
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
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;
    /** @var SlugGeneratorInterface */
    private $slugGenerator;

    public function __construct(
        ProductFactoryInterface $productFactory,
        TaxonFactoryInterface $taxonFactory,
        ProductRepositoryInterface $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        MetadataValidatorInterface $metadataValidator,
        PropertyAccessorInterface $propertyAccessor,
        RepositoryInterface $productAttributeRepository,
        AttributeCodesProviderInterface $attributeCodesProvider,
        FactoryInterface $productAttributeValueFactory,
        SlugGeneratorInterface $slugGenerator,
        ?TransformerPoolInterface $transformerPool,
        EntityManagerInterface $manager,
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
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->headerKeys = $headerKeys;
        $this->slugGenerator = $slugGenerator;
        $this->transformerPool = $transformerPool;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): void
    {
        $this->attrCode = $this->attributeCodesProvider->getAttributeCodesList();
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

    private function setMainTaxon(ProductInterface $product, array $data): void
    {
        /** @var ProductInterface $product */
        $product->setMainTaxon($this->taxonRepository->findOneBy(['code' => $data['Main_taxon']]));
    }

    private function setAttributesData(ProductInterface $product, array $data): void
    {
        foreach ($this->attrCode as $attrCode) {
            $attributeValue = $product->getAttributeByCodeAndLocale($attrCode);

            if (empty($data[$attrCode])) {
                if ($attributeValue !== null) {
                    $product->removeAttribute($attributeValue);
                }

                continue;
            }

            if ($attributeValue !== null) {
                if (null !== $this->transformerPool) {
                    $data[$attrCode] = $this->transformerPool->handle(
                        $attributeValue->getType(),
                        $data[$attrCode]
                    );
                }

                $attributeValue->setValue($data[$attrCode]);

                continue;
            }

            $this->setAttributeValue($product, $data, $attrCode);
        }
    }

    private function setDetails(ProductInterface $product, array $data): void
    {
        $product->setName($data['Name']);
        $product->setDescription($data['Description']);
        $product->setShortDescription($data['Short_description']);
        $product->setMetaDescription($data['Meta_description']);
        $product->setMetaKeywords($data['Meta_keywords']);
        $product->setSlug($product->getSlug() ?: $this->slugGenerator->generate($product->getName()));
    }

    private function setAttributeValue(ProductInterface $product, array $data, string $attrCode): void
    {
        /** @var ProductAttribute $productAttr */
        $productAttr = $this->productAttributeRepository->findOneBy(['code' => $attrCode]);
        /** @var ProductAttributeValueInterface $attr */
        $attr = $this->productAttributeValueFactory->createNew();
        $attr->setAttribute($productAttr);
        $attr->setProduct($product);
        $attr->setLocaleCode($product->getTranslation()->getLocale());

        if (null !== $this->transformerPool) {
            $data[$attrCode] = $this->transformerPool->handle($productAttr->getType(), $data[$attrCode]);
        }

        $attr->setValue($data[$attrCode]);
        $product->addAttribute($attr);
        $this->manager->persist($attr);
    }
}
