<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;
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
    private $productVariantRepository;
    /** @var RepositoryInterface */
    private $productOptionRepository;
    /** @var RepositoryInterface */
    private $productOptionValueRepository;

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
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $productTaxonFactory,
        FactoryInterface $productImageFactory,
        FactoryInterface $productVariantFactory,
        FactoryInterface $channelPricingFactory,
        ProductTaxonRepository $productTaxonRepository,
        ProductImageRepositoryInterface $productImageRepository,
        RepositoryInterface $productVariantRepository,
        RepositoryInterface $productOptionRepository,
        RepositoryInterface $productOptionValueRepository,
        RepositoryInterface $channelPricingRepository,
        ImageTypesProviderInterface $imageTypesProvider,
        SlugGeneratorInterface $slugGenerator,
        ?TransformerPoolInterface $transformerPool,
        EntityManagerInterface $manager,
        array $headerKeys
    )
    {
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
        $this->channelRepository = $channelRepository;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->productImageFactory = $productImageFactory;
        $this->productImageRepository = $productImageRepository;
        $this->imageTypesProvider = $imageTypesProvider;
        $this->productVariantFactory = $productVariantFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->channelPricingRepository = $channelPricingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): void
    {
        $this->attrCode = $this->attributeCodesProvider->getAttributeCodesList();
        $this->imageCode = $this->imageTypesProvider->getProductImagesCodesWithPrefixList();

        $this->headerKeys = \array_merge($this->headerKeys, $this->attrCode);
        $this->headerKeys = \array_merge($this->headerKeys, $this->imageCode);
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ProductInterface $product */
        $product = $this->productRepository->findOneByCode($data['Code']);

        if (null === $product) {
            $product = $this->resourceProductFactory->createNew();
            $product->setCode($data['Code']);

            $this->setDetails($product, $data);
            $this->setOptions($product, $data);
            $this->setVariant($product, $data);
            $this->setAttributesData($product, $data);
            $this->setMainTaxon($product, $data);
            $this->setTaxons($product, $data);
            $this->setChannel($product, $data);
            $this->setImage($product, $data);
        } else {
            $this->setOptions($product, $data);
            $this->setVariant($product, $data);
        }

        $this->productRepository->add($product);
    }

    private function setDetails(ProductInterface $product, array $data): void
    {
        $product->setCurrentLocale($data['Locale']);
        $product->setFallbackLocale($data['Locale']);

        $product->setName(substr($data['Name'], 0, 255));
        $product->setEnabled((bool)$data['Enabled']);
        $product->setDescription($data['Description']);
        $product->setShortDescription(substr($data['Short_description'], 0, 255));
        $product->setMetaDescription(substr($data['Meta_description'], 0, 255));
        $product->setMetaKeywords(substr($data['Meta_keywords'], 0, 255));
        $product->setSlug($product->getSlug() ?: $this->slugGenerator->generate($product->getName()));
    }

    private function setOptions(ProductInterface $product, array $data)
    {
        $productOptionCodes = $this->getProductOptionCodesFromData($data);

        foreach ($productOptionCodes as $productOptionCode) {
            $productOptionItem = $this->productOptionRepository->findOneBy(['code' => $productOptionCode]);
            $product->addOption($productOptionItem);
        }
    }

    private function getProductOptionCodesFromData(array $data): array
    {
        $productOptionColumns = array_keys($this->getNonEmptyProductOptionColumnsFromData($data));
        $productOptionCodes = array_map(
            function (string $productOptionColumnName) {
                return substr($productOptionColumnName, strlen(self::COLUMN_PRODUCT_OPTION_PREFIX));
            },
            $productOptionColumns
        );

        return $productOptionCodes;
    }

    private function getNonEmptyProductOptionColumnsFromData(array $data): array
    {
        return array_filter(
            $data,
            function ($value, $key) {
                return !empty($value) && strpos($key, self::COLUMN_PRODUCT_OPTION_PREFIX) === 0;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function setVariant(ProductInterface $product, array $data): void
    {
        $productVariant = $this->getProductVariant($product, $data);
        $productVariant->setCurrentLocale($this->getVariantLocale($data, $product));
        $productVariant->setName($this->generateVariantName($data, $product->getName()));

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

    private function getProductVariant(ProductInterface $product, array $data): ProductVariantInterface
    {
        $productVariant = null;
        $productVariants = $product->getVariants();
        $productOptionValueListFromData = $this->getProductOptionValueListFromData($data);

        if ($productVariants->isEmpty()) {
            $productVariant = $this->createNewProductVariant($product->getCode(), 1, $productOptionValueListFromData);
        } else {
            // look for the corresponding Product variant with the given option values
            $productVariantFound = false;

            /** @var ProductVariantInterface $productVariantItem */
            foreach ($productVariants as $productVariantItem) {
                $productVariantOptionValueCheckResult = array_map(
                    function (ProductOptionValueInterface $optionValue) use ($productVariantItem) {
                        return $productVariantItem->hasOptionValue($optionValue);
                    },
                    $productOptionValueListFromData
                );

                $productVariantFound = array_product($productVariantOptionValueCheckResult);
                if ($productVariantFound) {
                    $productVariant = $productVariantItem;
                    break;
                }
            }

            if (!$productVariantFound) {
                $nextVariantSuffixNumber = count($productVariants) + 1;
                $productVariant = $this->createNewProductVariant($product->getCode(), $nextVariantSuffixNumber, $productOptionValueListFromData);
            }
        }

        return $productVariant;
    }

    private function getProductOptionValueListFromData(array $data): array
    {
        $productOptionValues = array_values($this->getNonEmptyProductOptionColumnsFromData($data));
        $productOptionValueList = array_map(
            function (string $optionValueCode) use ($data) {
                /** @var ProductOptionValueInterface $productOptionValueItem */
                $productOptionValueItem = $this->productOptionValueRepository->findOneBy(['code' => $optionValueCode]);

                if ($productOptionValueItem === null) {
                    throw new ImporterException('Product Option Value does not exist, please add it to your system: ' .
                        $optionValueCode . ' for Product: ' . $data['Name']);
                }

                return $productOptionValueItem;
            },
            $productOptionValues
        );

        return $productOptionValueList;
    }

    private function createNewProductVariant(string $productCode,
                                             int $variantSuffixNumber,
                                             array $productOptionValueList): ProductVariantInterface
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->productVariantFactory->createNew();
        $productVariant->setCode($this->generateVariantCode($productCode, $variantSuffixNumber));

        /** @var ProductOptionValueInterface $productOptionValue */
        foreach ($productOptionValueList as $productOptionValue) {
            $productVariant->addOptionValue($productOptionValue);
        }

        return $productVariant;
    }

    private function generateVariantCode(string $productCode, int $suffixNumber): string
    {
        return sprintf("%s-%02d", $productCode, $suffixNumber);
    }

    private function getVariantLocale(array $data, ProductInterface $product): string
    {
        return $data['Locale'] ?: $product->getTranslation()->getLocale();
    }

    private function generateVariantName(array $data, $fallbackName): string
    {
        $variantName = substr($data['Name'], 0, 255);

        if (empty($variantName) && empty($fallbackName)) {
            throw new ImporterException('Missing name for Product variant with code: ' . $data['Code']);
        }

        return $variantName ?: $fallbackName;
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

    private function setMainTaxon(ProductInterface $product, array $data): void
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $data['Main_taxon']]);
        if ($taxon === null) {
            return;
        }

        /** @var ProductInterface $product */
        $product->setMainTaxon($taxon);

        $this->addTaxonToProduct($product, $data['Main_taxon']);
    }

    private function addTaxonToProduct(ProductInterface $product, string $taxonCode): void
    {
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
            $channel = $this->channelRepository->findOneBy(['code' => $channelCode]);
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

                    continue;
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
            $productImage->setPath($data[ImageTypesProvider::IMAGES_PREFIX . $imageType]);
            $product->addImage($productImage);
        }

        // create image if import has new one
        foreach ($this->imageTypesProvider->extractImageTypeFromImport(\array_keys($data)) as $imageType) {
            if (\in_array($imageType, $productImageCodes) || empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType])) {
                continue;
            }

            /** @var ProductImageInterface $productImage */
            $productImage = $this->productImageFactory->createNew();
            $productImage->setType($imageType);
            $productImage->setPath($data[ImageTypesProvider::IMAGES_PREFIX . $imageType]);
            $product->addImage($productImage);
        }
    }
}
