<?php
/**
 * Created by PhpStorm.
 * User: apitchen
 * Date: 17/01/18
 * Time: 11:41
 */

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;


use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
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
    private $taxonRepositry;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var array */
    private $headerKeys;

    /**
     * @param ProductFactoryInterface $productFactory
     * @param TaxonFactoryInterface $taxonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param TaxonRepositoryInterface $taxonRepository
     * @param MetadataValidatorInterface $metadataValidator
     * @param PropertyAccessorInterface $propertyAccessor
     * @param array $headerKeys
     */
    public function __construct(
        ProductFactoryInterface $productFactory,
        TaxonFactoryInterface $taxonFactory,
        ProductRepositoryInterface $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        MetadataValidatorInterface $metadataValidator,
        PropertyAccessorInterface $propertyAccessor,
        array $headerKeys
    ) {
        $this->resourceProductFactory = $productFactory;
        $this->resourceTaxonFactory = $taxonFactory;
        $this->productRepository = $productRepository;
        $this->taxonRepositry = $taxonRepository;
        $this->metadataValidator = $metadataValidator;
        $this->propertyAccessor = $propertyAccessor;
        $this->headerKeys = $headerKeys;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ImporterException
     */
    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);
        
        $product = $this->getProduct($data['Code']);
        var_dump($product);
        $mainTaxon = $this->getMainTaxon($data['MainTaxon']);

        $mainTaxon->setName($mainTaxon);
        $product->setName($data['Name']);
        $product->setSlug($data['Slug']);
        

        $this->productRepository->add($product);
        $this->taxonRepositry->add($mainTaxon);
    }

    /**
     * @param string $code
     *
     * @return ProductInterface
     */
    public function getProduct(string $code)
    {
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if (null === $product) {
            /** @var ProductInterface $product */
            $product = $this->resourceProductFactory->createNew();
            $product->setCode($code);
        }

        return $product;
    }

    /**
     * @param string $taxon
     *
     * @return TaxonInterface
     *
     */
    public function getMainTaxon(string $taxon)
    {
        $mainTaxon = $this->taxonRepositry->findOneBy(['code' => $taxon]);

        if(null === $mainTaxon) {
            /** @var TaxonInterface $mainTaxon */
            $mainTaxon = $this->resourceTaxonFactory->createNew();
            $mainTaxon->setCode($taxon);
        }

        return $mainTaxon;
    }
}