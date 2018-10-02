<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonomyProcessor implements ResourceProcessorInterface
{
    /** @var TaxonFactoryInterface */
    private $resourceTaxonFactory;

    /** @var TaxonRepositoryInterface */
    private $taxonRepository;

    /** @var @var LocaleProviderInterface */
    private $localeProvider;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var array */
    private $headerKeys;

    /** @var array */
    private $availableLocalesCodes;

    public function __construct(
        TaxonFactoryInterface $taxonFactory,
        TaxonRepositoryInterface $taxonRepository,
        LocaleProviderInterface $localeProviderInterface,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->resourceTaxonFactory = $taxonFactory;
        $this->taxonRepository = $taxonRepository;
        $this->localeProviderInterface = $localeProviderInterface;
        $this->metadataValidator = $metadataValidator;
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

        /** @var TaxonInterface $taxon */
        $taxon = $this->getTaxon($data['Code'], $data['Parent']);

        $locale = $this->getLocale($data['Locale']);
        $taxon->setCurrentLocale($locale);
        $taxon->setFallbackLocale($locale);

        $taxon->setName($data['Name']);
        $taxon->setSlug($data['Slug']);

        $descriptionValue = $data['Description'];
        if (strlen((string) $descriptionValue) === 0 && !is_bool($descriptionValue)) {
            $descriptionValue = null;
        }
        $taxon->setDescription($descriptionValue);

        $this->taxonRepository->add($taxon);
    }

    private function getTaxon(string $code, string $parentCode = null): TaxonInterface
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $code]);

        if (null === $taxon) {
            /** @var TaxonInterface $parentTaxon */
            $parentTaxon = $this->taxonRepository->findOneBy(['code' => $parentCode]);
            if (null === $parentTaxon) {
                $taxon = $this->resourceTaxonFactory->createNew();
            } else {
                $taxon = $this->resourceTaxonFactory->createForParent($parentTaxon);
            }
            $taxon->setCode($code);
        }

        return $taxon;
    }

    private function getLocale(string $locale)
    {
        if ('' === $locale) {
            return  $this->localeProviderInterface->getDefaultLocaleCode();
        }

        if (null === $this->availableLocalesCodes) {
            $this->availableLocalesCodes = $this->localeProviderInterface->getAvailableLocalesCodes();
        }

        if (in_array($locale, $this->availableLocalesCodes)) {
            return $locale;
        }

        throw new \Exception("Locale $locale does not exist in Sylius");
    }
}
