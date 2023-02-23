<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Repository\ProductImageRepositoryInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProviderInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductTaxonRepository;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class ProductProcessor implements ResourceProcessorInterface
{
    private const COLUMN_PRODUCT_OPTION_PREFIX = "Product_Option_";

    /** @var RepositoryInterface */
    private $channelPricingRepository;
    /** @var FactoryInterface */
    private $channelPricingFactory;
    /** @var ChannelRepositoryInterface */
    private $channelRepository;
    /** @var FactoryInterface */
    private $productTaxonFactory;
    /** @var ProductTaxonRepository */
    private $productTaxonRepository;
    /** @var FactoryInterface */
    private $productImageFactory;
    /** @var ProductImageRepositoryInterface */
    private $productImageRepository;
    /** @var ImageTypesProviderInterface */
    private $imageTypesProvider;
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
    /** @var MetadataValidatorInterface */
    private $metadataValidator;
    /** @var array */
    private $headerKeys;
    /** @var array */
    private $attrCode;
    /** @var array */
    private $imageCode;
    /** @var RepositoryInterface */
    private $productAttributeRepository;
    /** @var FactoryInterface */
    private $productAttributeValueFactory;
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;
    /** @var SlugGeneratorInterface */
    private $slugGenerator;
    /** @var FactoryInterface */
    private $productVariantFactory;
    /** @var RepositoryInterface */
    private $productOptionValueRepository;

    public function __construct(
        ProductFactoryInterface $productFactory,
        TaxonFactoryInterface $taxonFactory,
        ProductRepositoryInterface $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        MetadataValidatorInterface $metadataValidator,
        RepositoryInterface $productAttributeRepository,
        AttributeCodesProviderInterface $attributeCodesProvider,
        FactoryInterface $productAttributeValueFactory,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $productTaxonFactory,
        FactoryInterface $productImageFactory,
        FactoryInterface $productVariantFactory,
        FactoryInterface $channelPricingFactory,
        ProductTaxonRepository $productTaxonRepository,
        ProductImageRepositoryInterface $productImageRepository,
        RepositoryInterface $productOptionValueRepository,
        RepositoryInterface $channelPricingRepository,
        ImageTypesProviderInterface $imageTypesProvider,
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
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->headerKeys = $headerKeys;
        $this->slugGenerator = $slugGenerator;
        $this->transformerPool = $transformerPool;
        $this->manager = $manager;
        $this->channelRepository = $channelRepository;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->productImageFactory = $productImageFactory;
        $this->productImageRepository = $productImageRepository;
        $this->imageTypesProvider = $imageTypesProvider;
        $this->productVariantFactory = $productVariantFactory;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->channelPricingRepository = $channelPricingRepository;
    }

    /**
     * {@inheritdoc}
     * @throws ImporterException
     */
    public function process(array $data): void
    {
        $this->loadAttributeCodes();
        $this->mergeHeaderKeys();
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        if ($this->isProductVariant($data)) {
            $mainProduct = $this->loadProductFromParentCode($data);
            $this->setVariantAndOptions($mainProduct, $data);
            $this->productRepository->add($mainProduct);
            return;
        }

        /** @var ProductInterface $mainProduct */
        $mainProduct = $this->productRepository->findOneByCode($data['Code']);
        if (null === $mainProduct) {
            $mainProduct = $this->resourceProductFactory->createNew();
        }

        $mainProduct->setCode($data['Code']);
        $this->setDetails($mainProduct, $data);
        $this->setVariantAndOptions($mainProduct, $data);
        $this->setAttributesData($mainProduct, $data);
        $this->setMainTaxon($mainProduct, $data);
        $this->setTaxons($mainProduct, $data);
        $this->setChannel($mainProduct, $data);
        $this->setImage($mainProduct, $data);

        $this->productRepository->add($mainProduct);
    }

    private function loadAttributeCodes()
    {
        $this->attrCode = $this->attributeCodesProvider->getAttributeCodesList();
    }

    private function mergeHeaderKeys()
    {
        $this->imageCode = $this->imageTypesProvider->getProductImagesCodesWithPrefixList();
        $this->headerKeys = \array_merge($this->headerKeys, $this->imageCode);
    }

    private function isProductVariant(array $data): bool
    {
        return !empty($data['Parent_Code']);
    }

    private function loadProductFromParentCode(array $data): ProductInterface
    {
        $mainProduct = $this->productRepository->findOneByCode($data['Parent_Code']);
        if (null === $mainProduct) {
            throw new ImporterException(
                "Parent Product with code {$data['Parent_Code']} does not exist yet. Create it first."
            );
        }
        return $mainProduct;
    }

    /**
     * @throws ImporterException
     */
    private function setVariantAndOptions(ProductInterface $product, array $data): void
    {
        $productOptionValueList = $this->getProductOptionValueListFromData($data);

        $productVariant = $this->getOrCreateProductVariant($product, $productOptionValueList);

        $productVariant->setCode($data['Code']);
        $productVariant->setCurrentLocale(
            $this->getVariantLocale($data, $product)
        );
        $productVariant->setName(
            $this->generateVariantName($data, $product->getName())
        );

        /** @var ProductOptionValueInterface $productOptionValue */
        foreach ($productOptionValueList as $productOptionValue) {
            $product->addOption($productOptionValue->getOption());
            $productVariant->addOptionValue($productOptionValue);
        }

        $channels = \explode('|', $data['Channels']);
        foreach ($channels as $channelCode) {
            /** @var ChannelPricingInterface|null $channelPricing */
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channelCode,
                'productVariant' => $productVariant,
            ]);

            if (null === $channelPricing) {
                /** @var ChannelPricingInterface $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channelCode);
                $productVariant->addChannelPricing($channelPricing);
            }

            $channelPricing->setPrice((int)$data['Price']);
            $channelPricing->setOriginalPrice((int)$data['Price']);
        }

        $product->addVariant($productVariant);
    }

    private function getProductOptionValueListFromData(array $data): array
    {
        $productOptionValues = array_values(
            $this->getProductOptionColumnsFromData($data)
        );
        $productOptionValueList = array_map(
            function (string $optionValueCode) {
                // Empty string values should be allowed
                // Don't throw an exception for this case
                if ('' === $optionValueCode) {
                    return null;
                }

                /** @var $productOptionValue ProductOptionValueInterface */
                $productOptionValue = $this->productOptionValueRepository->findOneBy(
                    ['code' => $optionValueCode]
                );

                if (null === $productOptionValue) {
                    throw new ImporterException(
                        "Product option value with code: $optionValueCode does not exist."
                    );
                }
                return $productOptionValue;
            },
            $productOptionValues
        );

        return array_filter($productOptionValueList);
    }

    private function getProductOptionColumnsFromData(array $data): array
    {
        return array_filter(
            $data,
            function ($key) {
                return strpos($key, self::COLUMN_PRODUCT_OPTION_PREFIX) === 0;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getOrCreateProductVariant(
        ProductInterface $product,
        array $productOptionValueListFromData
    ): ProductVariantInterface {
        $productVariant = null;
        $productVariants = $product->getVariants();

        if ($productVariants->isEmpty()) {
            $productVariant = $this->productVariantFactory->createNew();
        } else {
            $productVariant = $this->findProductVariant(
                $productVariants,
                $productOptionValueListFromData,
            );

            if (null === $productVariant) {
                $productVariant = $this->productVariantFactory->createNew();
            }
        }

        return $productVariant;
    }

    /**
     * Looks up the appropriate Product variant with the given option
     * values.
     *
     * @param Collection $productVariants ProductVariantInterface collection
     * @param array $productOptionValueListFromData A list of ProductOptionValueInterface objects
     * @return ProductVariantInterface $productVariant The found product variant or null
     */
    private function findProductVariant(
        Collection $productVariants,
        array $productOptionValueListFromData
    ): ?ProductVariantInterface {
        /** @var ProductVariantInterface $productVariantItem */
        foreach ($productVariants as $productVariantItem) {
            $productVariantOptionValueCheckResult = array_map(
                function (ProductOptionValueInterface $optionValue) use (
                    $productVariantItem
                ) {
                    return $productVariantItem->hasOptionValue(
                        $optionValue
                    );
                },
                $productOptionValueListFromData
            );

            $productVariantFound = array_product(
                $productVariantOptionValueCheckResult
            );

            if ($productVariantFound) {
                return $productVariantItem;
            }
        }

        return null;
    }

    private function getVariantLocale(array $data, ProductInterface $product): string
    {
        return $data['Locale'] ?: $product->getTranslation()->getLocale();
    }

    private function generateVariantName(array $data, $fallbackName): string
    {
        $variantName = substr($data['Name'], 0, 255);
        return $variantName ?: $fallbackName;
    }

    private function setDetails(ProductInterface $product, array $data): void
    {
        $product->setCurrentLocale($data['Locale']);
        $product->setFallbackLocale($data['Locale']);

        $product->setName(substr($data['Name'], 0, 255));
        $product->setEnabled((bool)$data['Enabled']);
        $product->setDescription($data['Description']);
        $product->setShortDescription(
            substr($data['Short_description'], 0, 255)
        );
        $product->setMetaDescription(substr($data['Meta_description'], 0, 255));
        $product->setMetaKeywords(substr($data['Meta_keywords'], 0, 255));
        $product->setSlug(
            $product->getSlug() ?: $this->slugGenerator->generate(
                "{$product->getCode()}-{$product->getName()}"
            )
        );
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

    private function setAttributeValue(
        ProductInterface $product,
        array $data,
        string $attrCode
    ): void {
        /** @var ProductAttribute $productAttr */
        $productAttr = $this->productAttributeRepository->findOneBy(
            ['code' => $attrCode]
        );
        /** @var ProductAttributeValueInterface $attr */
        $attr = $this->productAttributeValueFactory->createNew();
        $attr->setAttribute($productAttr);
        $attr->setProduct($product);
        $attr->setLocaleCode($product->getTranslation()->getLocale());

        if (null !== $this->transformerPool) {
            $data[$attrCode] = $this->transformerPool->handle(
                $productAttr->getType(),
                $data[$attrCode]
            );
        }

        $attr->setValue($data[$attrCode]);
        $product->addAttribute($attr);
        $this->manager->persist($attr);
    }

    private function setMainTaxon(ProductInterface $product, array $data): void
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(
            ['code' => $data['Main_taxon']]
        );
        if ($taxon === null) {
            return;
        }

        $product->setMainTaxon($taxon);

        $this->addTaxonToProduct($product, $data['Main_taxon']);
    }

    private function addTaxonToProduct(
        ProductInterface $product,
        string $taxonCode
    ): void {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        if ($taxon === null) {
            return;
        }

        $productTaxon = $this->productTaxonRepository->findOneByProductCodeAndTaxonCode(
            $product->getCode(),
            $taxon->getCode()
        );

        if (null !== $productTaxon) {
            return;
        }

        /** @var ProductTaxonInterface $productTaxon */
        $productTaxon = $this->productTaxonFactory->createNew();
        $productTaxon->setTaxon($taxon);
        $product->addProductTaxon($productTaxon);
    }

    private function setTaxons(ProductInterface $product, array $data): void
    {
        $taxonCodes = \explode('|', $data['Taxons']);
        foreach ($taxonCodes as $taxonCode) {
            if ($taxonCode !== $data['Main_taxon']) {
                $this->addTaxonToProduct($product, $taxonCode);
            }
        }
    }

    private function setChannel(ProductInterface $product, array $data): void
    {
        $channels = \explode('|', $data['Channels']);
        foreach ($channels as $channelCode) {
            /** @var ChannelInterface|null $channel */
            $channel = $this->channelRepository->findOneBy(
                ['code' => $channelCode]
            );
            if ($channel === null) {
                continue;
            }
            $product->addChannel($channel);
        }
    }

    private function setImage(ProductInterface $product, array $data): void
    {
        $productImageCodes = $this->imageTypesProvider->getProductImagesCodesList();
        foreach ($productImageCodes as $imageType) {
            /** @var ProductImageInterface $productImage */
            $productImageByType = $product->getImagesByType($imageType);

            // remove old images if import is empty
            foreach ($productImageByType as $productImage) {
                if (empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType])) {
                    if ($productImage !== null) {
                        $product->removeImage($productImage);
                    }
                }
            }

            if (empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType])) {
                continue;
            }

            if (count($productImageByType) === 0) {
                /** @var ProductImageInterface $productImage */
                $productImage = $this->productImageFactory->createNew();
            } else {
                $productImage = $productImageByType->first();
            }

            $productImage->setType($imageType);
            $productImage->setPath(
                $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]
            );
            $product->addImage($productImage);
        }

        // create image if import has new one
        foreach (
            $this->imageTypesProvider->extractImageTypeFromImport(
                \array_keys($data)
            ) as $imageType
        ) {
            if (\in_array(
                    $imageType,
                    $productImageCodes
                ) || empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType])) {
                continue;
            }

            /** @var ProductImageInterface $productImage */
            $productImage = $this->productImageFactory->createNew();
            $productImage->setType($imageType);
            $productImage->setPath(
                $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]
            );
            $product->addImage($productImage);
        }
    }
}
