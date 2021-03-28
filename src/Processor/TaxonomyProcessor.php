<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class TaxonomyProcessor implements ResourceProcessorInterface
{
    /** @var TaxonFactoryInterface */
    private $resourceTaxonFactory;

    /** @var TaxonRepositoryInterface */
    private $taxonRepository;

    /** @var LocaleProviderInterface */
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
        $this->localeProvider = $localeProviderInterface;
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
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $code]);

        if (null === $taxon) {
            /** @var TaxonInterface|null $parentTaxon */
            $parentTaxon = $this->taxonRepository->findOneBy(['code' => $parentCode]);
            if (null === $parentTaxon) {
                /** @var TaxonInterface $taxon */
                $taxon = $this->resourceTaxonFactory->createNew();
            } else {
                $taxon = $this->resourceTaxonFactory->createForParent($parentTaxon);
            }
            $taxon->setCode($code);
        }

        return $taxon;
    }

    private function getLocale(string $locale): string
    {
        if ('' === $locale) {
            return  $this->localeProvider->getDefaultLocaleCode();
        }

        if (null === $this->availableLocalesCodes) {
            $this->availableLocalesCodes = $this->localeProvider->getAvailableLocalesCodes();
        }

        if (in_array($locale, $this->availableLocalesCodes, true)) {
            return $locale;
        }

        throw new \Exception("Locale $locale does not exist in Sylius");
    }
}
