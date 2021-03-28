<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
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
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductProcessor implements ResourceProcessorInterface
{
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

        $product = $this->getProduct($data['Code']);

        $this->setDetails($product, $data);
        $this->setVariant($product, $data);
        $this->setAttributesData($product, $data);
        $this->setMainTaxon($product, $data);
        $this->setTaxons($product, $data);
        $this->setChannel($product, $data);
        $this->setImage($product, $data);

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

    private function getProductVariant(string $code): ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $code]);
        if ($productVariant === null) {
            /** @var ProductVariantInterface $productVariant */
            $productVariant = $this->productVariantFactory->createNew();
            $productVariant->setCode($code);
        }

        return $productVariant;
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

    private function setTaxons(ProductInterface $product, array $data): void
    {
        $taxonCodes = \explode('|', $data['Taxons']);
        foreach ($taxonCodes as $taxonCode) {
            if ($taxonCode !== $data['Main_taxon']) {
                $this->addTaxonToProduct($product, $taxonCode);
            }
        }
    }

    private function setAttributesData(ProductInterface $product, array $data): void
    {
        foreach ($this->attrCode as $attrCode) {
            $attributeValue = $product->getAttributeByCodeAndLocale($attrCode);

            if ([] === $data[$attrCode]) {
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
        $product->setCurrentLocale($data['Locale']);
        $product->setFallbackLocale($data['Locale']);

        $product->setName(substr($data['Name'], 0, 255));
        $product->setEnabled((bool) $data['Enabled']);
        $product->setDescription($data['Description']);
        $product->setShortDescription(substr($data['Short_description'], 0, 255));
        $product->setMetaDescription(substr($data['Meta_description'], 0, 255));
        $product->setMetaKeywords(substr($data['Meta_keywords'], 0, 255));
        $product->setSlug($product->getSlug() ?? $this->slugGenerator->generate($product->getName()));
    }

    private function setVariant(ProductInterface $product, array $data): void
    {
        $productVariant = $this->getProductVariant($product->getCode());
        $productVariant->setCurrentLocale($data['Locale']);
        $productVariant->setName(substr($data['Name'], 0, 255));

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

            $channelPricing->setPrice((int) $data['Price']);
            $channelPricing->setOriginalPrice((int) $data['Price']);
        }

        $product->addVariant($productVariant);
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

    private function setImage(ProductInterface $product, array $data): void
    {
        $productImageCodes = $this->imageTypesProvider->getProductImagesCodesList();
        foreach ($productImageCodes as $imageType) {
            /** @var ProductImageInterface $productImage */
            $productImageByType = $product->getImagesByType($imageType);

            // remove old images if import is empty
            foreach ($productImageByType as $productImage) {
                if (null === $data[ImageTypesProvider::IMAGES_PREFIX . $imageType] || '' === $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]) {
                    if ($productImage != null) {
                        $product->removeImage($productImage);
                    }

                    continue;
                }
            }

            if (null === $data[ImageTypesProvider::IMAGES_PREFIX . $imageType] || '' === $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]) {
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
            if (\in_array($imageType, $productImageCodes, true)
                || (null === $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]
                    || '' === $data[ImageTypesProvider::IMAGES_PREFIX . $imageType])) {
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
